<?php

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger;

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      Jan Kozak <galvani78@gmail.com>
 */

use GuzzleHttp\Psr7\Response;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\AuthenticationException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidArgumentException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerSettingProvider;
use MauticPlugin\MauticVtigerCrmBundle\Model\Credentials;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Connection
 * @package MauticPlugin\MauticVtigerCrmBundle\Vtiger
 */
class Connection
{
    /** @var string */
    private $apiDomain;

    /** @var array */
    private $requestHeaders = [
        'Accept' => 'application/json',
        'Content-type' => 'application/json',
    ];

    /** @var \GuzzleHttp\Client */
    private $httpClient;

    /** @var string */
    private $sessionId;

    /** @var bool */
    private $authenticateOnDemand = true;

    /** @var Credentials */
    private $credentials;

    /** @var VtigerSettingProvider  */
    private $settings;

    /**
     * Connection constructor.
     *
     * @param \GuzzleHttp\Client $client
     * @param IntegrationHelper  $integrationsHelper
     *
     * @throws VtigerPluginException
     */
    public function __construct(\GuzzleHttp\Client $client, VtigerSettingProvider $settings)
    {
        $this->settings = $settings;

        $credentialsCfg = $this->settings->getCredentials();

        if (!isset($credentialsCfg['accessKey']) || !isset($credentialsCfg['username']) || !isset($credentialsCfg['url'])) {
            throw new VtigerPluginException('Plugin is not fully configured');
        }

        $this->httpClient = $client;

        $this->setCredentials((new Credentials())
            ->setAccesskey($credentialsCfg['accessKey'])
            ->setUsername($credentialsCfg['username']));

        $this->apiDomain = $credentialsCfg['url'];
    }

    /**
     * @return bool
     */
    public function isAuthenticateOnDemand(): bool
    {
        return $this->authenticateOnDemand;
    }

    /**
     * @param bool $authenticateOnDemand
     *
     * @return Connection
     */
    public function setAuthenticateOnDemand(bool $authenticateOnDemand): Connection
    {
        $this->authenticateOnDemand = $authenticateOnDemand;

        return $this;
    }


    /**
     * @param Credentials|null $credentials
     *
     * @return Connection
     * @throws AuthenticationException
     */
    public function authenticate(Credentials $credentials = null): Connection
    {
        try {
            $credentials = $credentials ?: $this->credentials;

            if (is_null($credentials)) {
                throw new SessionException('No authentication credentials supplied');
            }

            $query = sprintf("%s?operation=%s",
                $this->getApiUrl(),
                'getchallenge');


            $query .= '&' . http_build_query(['username' => $credentials->getUsername()]);

            $response = $this->httpClient->get($query, ['headers' => $this->requestHeaders]);

            $response = $this->handleResponse($response, $query);

            $query = [
                'operation' => 'login',
                'username' => $credentials->getUsername(),
                'accessKey' => md5($response->token . $credentials->getAccesskey()),
            ];

            $response = $this->httpClient->post($this->getApiUrl(), ['form_params' => $query]);

            $loginResponse = $this->handleResponse($response, $this->getApiUrl(), $query);

            $this->sessionId = $loginResponse->sessionName;
        }
        catch (\Exception $e) {
            throw new AuthenticationException('Failed to authenticate. ' . $e->getMessage());
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return !is_null($this->sessionId);
    }

    /**
     * @return Credentials
     */
    public function getCredentials(): Credentials
    {
        return $this->credentials;
    }

    /**
     * @param Credentials $credentials
     *
     * @return Connection
     */
    public function setCredentials(Credentials $credentials): Connection
    {
        $this->credentials = $credentials;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getApiDomain(): string
    {
        return $this->apiDomain;
    }

    /**
     * @return string
     */
    public function getApiUrl(): string
    {
        return sprintf("%s/webservice.php",
            $this->getApiDomain());
    }

    /**
     * @param mixed $apiDomain
     *
     * @return Connection
     */
    public function setApiDomain($apiDomain)
    {
        $this->apiDomain = $apiDomain;

        return $this;
    }

    /**
     * @param string $operation
     * @param array  $payload
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws AccessDeniedException
     * @throws AuthenticationException
     * @throws DatabaseQueryException
     * @throws InvalidArgumentException
     * @throws InvalidRequestException
     * @throws SessionException
     * @throws VtigerPluginException
     */
    public function get(string $operation, array $payload = [])
    {
        $query = sprintf("%s?operation=%s",
            $this->getApiUrl(),
            $operation);

        if (!$this->isAuthenticated() && !$this->isAuthenticateOnDemand()) {
            throw new SessionException('Not authenticated.');
        } elseif ($this->isAuthenticateOnDemand()) {
            $this->authenticate();
        }

        $payload['sessionName'] = $this->sessionId;

        if (count($payload)) {
            if (isset($payload['query'])) {
                $queryString = '&query=' . $payload['query'];
                unset($payload['query']);
            }
            $query .= '&' . http_build_query($payload);
            if (isset($queryString)) {
                $query .= trim($queryString, ';') . ';';
            }
        }

        $response = $this->httpClient->get($query, ['headers' => $this->requestHeaders]);

        $response = $this->handleResponse($response, $query);

        return $response;
    }

    /**
     * @param string $operation
     * @param array  $payload
     *
     * @return mixed|ResponseInterface
     * @throws AccessDeniedException
     * @throws AuthenticationException
     * @throws DatabaseQueryException
     * @throws InvalidArgumentException
     * @throws InvalidRequestException
     * @throws SessionException
     * @throws VtigerPluginException
     */
    public function query(string $operation, array $payload = []) {
        $query = sprintf("%s?operation=%s",
            $this->getApiUrl(),
            $operation);

        if (!$this->isAuthenticated() && !$this->isAuthenticateOnDemand()) {
            throw new SessionException('Not authenticated.');
        } elseif ($this->isAuthenticateOnDemand()) {
            $this->authenticate();
        }

        $query .= '&sessionName='.$this->sessionId;

        if (count($payload)) {
            if (isset($payload['query'])) {
                $queryString = '&query=' . $payload['query'];
                unset($payload['query']);
            }
            $query .= '&' . http_build_query($payload);
            if (isset($queryString)) {
                $query .= trim($queryString, ';') . ';';
            }
        }

        $response = $this->httpClient->get($query, ['headers' => $this->requestHeaders]);

        $response = $this->handleResponse($response, $query);

        return $response;
    }

    /**
     * @param string $operation
     * @param array  $payload
     *
     * @return mixed
     * @throws AccessDeniedException
     * @throws AuthenticationException
     * @throws DatabaseQueryException
     * @throws InvalidArgumentException
     * @throws InvalidRequestException
     * @throws SessionException
     * @throws VtigerPluginException
     */
    public function post(string $operation, array $payload)
    {
        $payloadFinal['operation'] = $operation;

        if (!$this->isAuthenticated() && !$this->isAuthenticateOnDemand()) {
            throw new SessionException('Not authenticated.');
        } elseif ($this->isAuthenticateOnDemand()) {
            $this->authenticate();
        }

        $payload['sessionName'] = $this->sessionId;

        $payloadFinal = array_merge($payloadFinal, $payload);

        $response = $this->httpClient->post($this->getApiUrl(), ['form_params' => $payloadFinal]);

        return $this->handleResponse($response, $this->getApiUrl(), $payloadFinal);
    }

    /**
     * @param Response $response
     * @param string   $apiUrl
     * @param array    $payload
     *
     * @return mixed
     * @throws AccessDeniedException
     * @throws DatabaseQueryException
     * @throws InvalidArgumentException
     * @throws InvalidRequestException
     * @throws SessionException
     * @throws VtigerPluginException
     */
    private function handleResponse(Response $response, string $apiUrl, array $payload = [])
    {
        $content = $response->getBody()->getContents();

        if ($response->getReasonPhrase() != 'OK') {
            throw new SessionException('Server responded with an error');
        }

        $content = json_decode($content);

        if ($content === false || $content === null) {
            throw new VtigerPluginException('Incorrect endpoint response');
        }

        if ($content->success) {
            return $content->result;
        }


        $error = property_exists($content, 'error') ? $content->error->code . ": " . $content->error->message : "No message";

        switch ($content->error->code) {
            case "ACCESS_DENIED":
                throw new AccessDeniedException($error, $apiUrl, $payload);
            case "DATABASE_QUERY_ERROR":
                throw new DatabaseQueryException($error, $apiUrl, $payload);
            case "MANDATORY_FIELDS_MISSING":
                throw new InvalidArgumentException($content->error->message, $apiUrl, $payload);
        }

        throw new InvalidRequestException($error, $apiUrl, $payload);
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     * @throws AccessDeniedException
     * @throws AuthenticationException
     * @throws DatabaseQueryException
     * @throws InvalidArgumentException
     * @throws InvalidRequestException
     * @throws SessionException
     * @throws VtigerPluginException
     */
    public function logout() {
        return $this->get('logout');
    }

    /**
     * returns love
     */
    public function __destruct()
    {
        try {
            $this->logout();
        } catch (\Exception $e) {

        }
    }
}