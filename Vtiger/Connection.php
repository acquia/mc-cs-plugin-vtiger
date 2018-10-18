<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://www.mautic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger;

use GuzzleHttp\Psr7\Response;
use MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException;
use MauticPlugin\IntegrationsBundle\Sync\Logger\DebugLogger;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException;
use MauticPlugin\MauticVtigerCrmBundle\Integration\Provider\VtigerSettingProvider;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Model\Credentials;
use Psr\Http\Message\ResponseInterface;

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
     * @var \GuzzleHttp\Client
     */
    private $httpClient;

    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var Credentials
     */
    private $credentials;

    /**
     * @var VtigerSettingProvider
     */
    private $settings;

    /**
     * @param \GuzzleHttp\Client    $client
     * @param VtigerSettingProvider $settings
     */
    public function __construct(\GuzzleHttp\Client $client, VtigerSettingProvider $settings)
    {
        $this->settings   = $settings;
        $this->httpClient = $client;
    }

    /**
     * @param string $operation
     * @param array  $payload
     *
     * @return mixed|ResponseInterface
     *
     * @throws AccessDeniedException
     * @throws DatabaseQueryException
     * @throws InvalidQueryArgumentException
     * @throws InvalidRequestException
     * @throws SessionException
     * @throws VtigerPluginException
     * @throws \MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException
     */
    public function get(string $operation, array $payload = [])
    {
        $this->settings->exceptConfigured();

        if (!$this->isAuthenticated()) {
            $this->authenticate();
        }

        $query = sprintf('%s?operation=%s',
            $this->getApiUrl(),
            $operation);

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

        $response = $this->handleResponse($response, $query);

        return $response;
    }

    /**
     * @param string $operation
     * @param array  $payload
     *
     * @return mixed|ResponseInterface
     *
     * @throws AccessDeniedException
     * @throws DatabaseQueryException
     * @throws InvalidQueryArgumentException
     * @throws InvalidRequestException
     * @throws SessionException
     * @throws VtigerPluginException
     * @throws \MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException
     */
    public function query(string $operation, array $payload = [])
    {
        $this->settings->exceptConfigured();

        if (!$this->isAuthenticated()) {
            $this->authenticate();
        }

        $query = sprintf('%s?operation=%s',
            $this->getApiUrl(),
            $operation);

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

        $response = $this->handleResponse($response, $query);

        return $response;
    }

    /**
     * @param string $operation
     * @param array  $payload
     *
     * @return mixed
     *
     * @throws AccessDeniedException
     * @throws DatabaseQueryException
     * @throws InvalidQueryArgumentException
     * @throws InvalidRequestException
     * @throws SessionException
     * @throws VtigerPluginException
     * @throws \MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException
     */
    public function post(string $operation, array $payload)
    {
        $this->settings->exceptConfigured();

        $payloadFinal['operation'] = $operation;

        if (!$this->isAuthenticated()) {
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

        if ('OK' != $response->getReasonPhrase()) {
            throw new SessionException('Server responded with an error');
        }

        $content = json_decode($content);

        if (false === $content || null === $content) {
            throw new VtigerPluginException('Incorrect endpoint response');
        }

        if ($content->success) {
            return $content->result;
        }

        $error = property_exists($content, 'error') ? $content->error->code.': '.$content->error->message : 'No message';

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

    /**
     * @return mixed|ResponseInterface
     *
     * @throws AccessDeniedException
     * @throws DatabaseQueryException
     * @throws InvalidQueryArgumentException
     * @throws InvalidRequestException
     * @throws PluginNotConfiguredException
     * @throws SessionException
     * @throws VtigerPluginException
     */
    public function logout()
    {
        return $this->get('logout');
    }

    /**
     * returns love.
     */
    public function __destruct()
    {
        try {
            if (!is_null($this->httpClient)) {
                $this->logout();
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * @return Connection
     *
     * @throws PluginNotConfiguredException
     */
    private function authenticate(): Connection
    {
        $this->setCredentials();

        try {
            $credentials = $this->credentials;

            if (is_null($credentials)) {
                throw new SessionException('No authentication credentials supplied');
            }

            $query = sprintf('%s?operation=%s',
                             $this->getApiUrl(),
                             'getchallenge');

            $query .= '&'.http_build_query(['username' => $credentials->getUsername()]);

            $response = $this->httpClient->get($query, ['headers' => $this->requestHeaders]);

            /** @noinspection PhpParamsInspection */
            $response = $this->handleResponse($response, $query);

            $query = [
                'operation' => 'login',
                'username'  => $credentials->getUsername(),
                'accessKey' => md5($response->token.$credentials->getAccesskey()),
            ];

            $response = $this->httpClient->post($this->getApiUrl(), ['form_params' => $query]);

            $loginResponse = $this->handleResponse($response, $this->getApiUrl(), $query);

            $this->sessionId = $loginResponse->sessionName;
        } catch (\Exception $e) {
            throw new PluginNotConfiguredException('Failed to authenticate. '.$e->getMessage());
        }

        return $this;
    }

    /**
     * @return string
     *
     * @throws PluginNotConfiguredException
     */
    private function getApiDomain(): string
    {
        if (!$this->apiDomain) {
            throw new PluginNotConfiguredException('No authentication credentials supplied');
        }

        return $this->apiDomain;
    }

    /**
     * @return string
     *
     * @throws PluginNotConfiguredException
     */
    private function getApiUrl(): string
    {
        return sprintf('%s/webservice.php',
                       $this->getApiDomain());
    }

    /**
     * @return bool
     */
    private function isAuthenticated(): bool
    {
        return !is_null($this->sessionId);
    }

    private function setCredentials(): void
    {
        $credentialsCfg = $this->settings->getCredentials();

        if (!empty($credentialsCfg['accessKey']) && !empty($credentialsCfg['username']) && !empty($credentialsCfg['url'])) {
            $this->credentials = new Credentials($credentialsCfg['accessKey'], $credentialsCfg['username']);

            $this->apiDomain = $credentialsCfg['url'];
        }
    }
}
