<?php

declare(strict_types=1);

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      Jan Kozak <galvani78@gmail.com>
 */

use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException;
use MauticPlugin\IntegrationsBundle\Sync\Logger\DebugLogger;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\AuthenticationException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException;
use MauticPlugin\MauticVtigerCrmBundle\Integration\Provider\VtigerSettingProvider;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Model\Credentials;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Class Connection.
 */
class Connection
{
    /**
     * @var string
     */
    private $apiDomain;

    /**
     * @var array
     */
    private $requestHeaders = [
        'Accept'       => 'application/json',
        'Content-type' => 'application/json',
    ];

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var bool
     */
    private $authenticateOnDemand = true;

    /**
     * @var Credentials
     */
    private $credentials;

    /**
     * @var VtigerSettingProvider
     */
    private $vtigerSettingProvider;

    /**
     * Connection constructor.
     *
     * @param Client            $client
     * @param IntegrationHelper $integrationsHelper
     *
     * @throws VtigerPluginException
     */
    public function __construct(Client $client, VtigerSettingProvider $vtigerSettingProvider)
    {
        $this->vtigerSettingProvider = $vtigerSettingProvider;

        $credentialsCfg = $this->vtigerSettingProvider->getCredentials();

        if (isset($credentialsCfg['accessKey']) && isset($credentialsCfg['username']) && isset($credentialsCfg['url'])) {
            $this->httpClient = $client;

            $this->setCredentials((new Credentials())
                ->setAccesskey($credentialsCfg['accessKey'])
                ->setUsername($credentialsCfg['username']));

            $this->apiDomain = $credentialsCfg['url'];
        }
    }

    /**
     * returns love.
     */
    public function __destruct()
    {
        try {
            if (null !== $this->httpClient) {
                $this->logout();
            }
        } catch (Throwable $e) {
        }
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
    public function setAuthenticateOnDemand(bool $authenticateOnDemand): self
    {
        $this->authenticateOnDemand = $authenticateOnDemand;

        return $this;
    }

    /**
     * @param Credentials|null $credentials
     *
     * @return Connection
     *
     * @throws AuthenticationException
     */
    public function authenticate(?Credentials $credentials = null): self
    {
        try {
            $credentials = $credentials ?: $this->credentials;

            if (null === $credentials) {
                throw new SessionException('No authentication credentials supplied');
            }

            $query = sprintf('%s?operation=%s', $this->getApiUrl(), 'getchallenge');

            $query .= '&'.http_build_query(['username' => $credentials->getUsername()]);

            $response = $this->httpClient->get($query, ['headers' => $this->requestHeaders]);

            $response = $this->handleResponse($response, $query);

            $query = [
                'operation' => 'login',
                'username'  => $credentials->getUsername(),
                'accessKey' => md5($response->token.$credentials->getAccesskey()),
            ];

            $response = $this->httpClient->post($this->getApiUrl(), ['form_params' => $query]);

            $loginResponse = $this->handleResponse($response, $this->getApiUrl(), $query);

            $this->sessionId = $loginResponse->sessionName;
        } catch (Throwable $e) {
            throw new AuthenticationException('Failed to authenticate. '.$e->getMessage());
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return null !== $this->sessionId;
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
    public function setCredentials(Credentials $credentials): self
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
        return sprintf('%s/webservice.php', $this->getApiDomain());
    }

    /**
     * @param mixed $apiDomain
     *
     * @return Connection
     */
    public function setApiDomain($apiDomain): self
    {
        $this->apiDomain = $apiDomain;

        return $this;
    }

    /**
     * @param string $operation
     * @param array  $payload
     *
     * @return ResponseInterface
     *
     * @throws AccessDeniedException
     * @throws AuthenticationException
     * @throws DatabaseQueryException
     * @throws InvalidQueryArgumentException
     * @throws InvalidRequestException
     * @throws SessionException
     * @throws VtigerPluginException
     */
    public function get(string $operation, array $payload = []): ResponseInterface
    {
        $this->isConfigured();

        $query = sprintf('%s?operation=%s', $this->getApiUrl(), $operation);

        if (!$this->isAuthenticated() && !$this->isAuthenticateOnDemand()) {
            throw new SessionException('Not authenticated.');
        } elseif ($this->isAuthenticateOnDemand()) {
            $this->authenticate();
        }

        $payload['sessionName'] = $this->sessionId;

        if (count($payload)) {
            if (isset($payload['query'])) {
                $queryString = '&query='.$payload['query'];
                unset($payload['query']);
            }
            $query .= '&'.http_build_query($payload);
            if (isset($queryString)) {
                $query .= trim($queryString, ';').';';
            }
        }

        DebugLogger::log(VtigerCrmIntegration::NAME, $query);

        $response = $this->httpClient->get($query, ['headers' => $this->requestHeaders]);

        return $this->handleResponse($response, $query);
    }

    /**
     * @param string $operation
     * @param array  $payload
     *
     * @return mixed|ResponseInterface
     *
     * @throws AccessDeniedException
     * @throws AuthenticationException
     * @throws DatabaseQueryException
     * @throws InvalidQueryArgumentException
     * @throws InvalidRequestException
     * @throws SessionException
     * @throws VtigerPluginException
     */
    public function query(string $operation, array $payload = [])
    {
        $this->isConfigured();

        if (!$this->isAuthenticated() && !$this->isAuthenticateOnDemand()) {
            throw new SessionException('Not authenticated.');
        } elseif ($this->isAuthenticateOnDemand()) {
            $this->authenticate();
        }

        $query = sprintf('%s?operation=%s', $this->getApiUrl(), $operation);

        $query .= '&sessionName='.$this->sessionId;

        if (count($payload)) {
            if (isset($payload['query'])) {
                $queryString = '&query='.$payload['query'];
                unset($payload['query']);
            }
            $query .= '&'.http_build_query($payload);
            if (isset($queryString)) {
                $query .= trim($queryString, ';').';';
            }
        }

        DebugLogger::log(VtigerCrmIntegration::NAME, 'Running vtiger query: '.$query);
        $response = $this->httpClient->get($query, ['headers' => $this->requestHeaders]);

        return $this->handleResponse($response, $query);
    }

    /**
     * @param string $operation
     * @param array  $payload
     *
     * @return mixed
     *
     * @throws AccessDeniedException
     * @throws AuthenticationException
     * @throws DatabaseQueryException
     * @throws InvalidQueryArgumentException
     * @throws InvalidRequestException
     * @throws SessionException
     * @throws VtigerPluginException
     */
    public function post(string $operation, array $payload)
    {
        $this->isConfigured();

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
     * @return ResponseInterface
     *
     * @throws AccessDeniedException
     * @throws AuthenticationException
     * @throws DatabaseQueryException
     * @throws InvalidQueryArgumentException
     * @throws InvalidRequestException
     * @throws SessionException
     * @throws VtigerPluginException
     */
    public function logout(): ResponseInterface
    {
        return $this->get('logout');
    }

    /**
     * @param Response $response
     * @param string   $apiUrl
     * @param array    $payload
     *
     * @return mixed
     *
     * @throws AccessDeniedException
     * @throws DatabaseQueryException
     * @throws InvalidQueryArgumentException
     * @throws InvalidRequestException
     * @throws SessionException
     * @throws VtigerPluginException
     */
    private function handleResponse(Response $response, string $apiUrl, array $payload = [])
    {
        $content = $response->getBody()->getContents();

        if ('OK' !== $response->getReasonPhrase()) {
            throw new SessionException('Server responded with an error');
        }

        $content = json_decode($content);

        if (false === $content || null === $content) {
            throw new VtigerPluginException('Incorrect endpoint response');
        }

        if ($content->success) {
            return $content->result;
        }

        $error = property_exists(
            $content,
            'error'
        ) ? $content->error->code.': '.$content->error->message : 'No message';

        switch ($content->error->code) {
            case 'ACCESS_DENIED':
                throw new AccessDeniedException($error, $apiUrl, $payload);
            case 'DATABASE_QUERY_ERROR':
                throw new DatabaseQueryException($error, $apiUrl, $payload);
            case 'MANDATORY_FIELDS_MISSING':
                throw new InvalidQueryArgumentException($content->error->message, $apiUrl, $payload);
        }

        throw new InvalidRequestException($error, $apiUrl, $payload);
    }

    private function isConfigured(): void
    {
        $credentialsCfg = $this->vtigerSettingProvider->getCredentials();

        if ((!isset($credentialsCfg['accessKey']) || !isset($credentialsCfg['username']) || !isset($credentialsCfg['url']))) {
            throw new PluginNotConfiguredException(VtigerCrmIntegration::NAME.' is not configured');
        }
    }
}
