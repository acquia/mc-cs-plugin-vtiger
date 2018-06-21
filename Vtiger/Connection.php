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
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerInvalidRequestException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerSessionException;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Model\Credentials;

class Connection
{
    private $apiDomain;

    private $requestHeaders;

    private $httpClient;

    private $sessionId;

    /** @var bool */
    private $authenticateOnDemand = true;

    /** @var Credentials */
    private $credentials;


    /**
     * Connection constructor.
     *
     * @param \GuzzleHttp\Client $client
     */
    public function __construct(\GuzzleHttp\Client $client, IntegrationHelper $integration)
    {
        $integrationEntity = $integration->getIntegrationObject('VtigerCrm');

        if ($integrationEntity===false) {

        }
        var_dump($integrationEntity); die();

        $this->httpClient = $client;
        $this->requestHeaders = [
            'Accept' => 'application/json',
            'Content-type' => 'application/json'
        ];
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
     * @return Connection
     */
    public function setAuthenticateOnDemand(bool $authenticateOnDemand): Connection
    {
        $this->authenticateOnDemand = $authenticateOnDemand;
        return $this;
    }


    /**
     * @param Credentials|null $credentials
     * @return Connection
     * @throws AuthenticationException
     */
    public function authenticate(Credentials $credentials = null): Connection
    {
        try {
            $credentials = $credentials ?: $this->credentials;

            if (is_null($credentials)) {
                throw new VtigerSessionException('No authentication credentials supplied');
            }

            $query = sprintf("%s?operation=%s",
                $this->getApiDomain(),
                'getchallenge');


            $query .= '&' . http_build_query(['username' => $credentials->getUsername()]);

            $response = $this->httpClient->get($query, ['headers' => $this->requestHeaders]);

            $response = $this->handleResponse($response, $query);

            $query = [
                'operation' => 'login',
                'username' => urlencode($credentials->getUsername()),
                'accessKey' => md5($response->token . $credentials->getAccesskey()),
            ];

            $response = $this->httpClient->post($this->apiDomain, ['form_params' => $query]);

            $loginResponse = $this->handleResponse($response, $this->apiDomain, $query);

            $this->sessionId = $loginResponse->sessionName;
        } catch (\Exception $e) {
            throw new AuthenticationException('Failed to authenticate. ' . $e->getMessage(), $e);
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
    public function getApiDomain()
    {
        return $this->apiDomain;
    }

    /**
     * @param mixed $apiDomain
     * @return Connection
     */
    public function setApiDomain($apiDomain)
    {
        $this->apiDomain = $apiDomain;
        return $this;
    }


    public function get(string $operation, array $payload = [])
    {
        $query = sprintf("%s?operation=%s",
            $this->getApiDomain(),
            $operation);


        if (!$this->isAuthenticated() && !$this->isAuthenticateOnDemand()) {
            throw new VtigerSessionException('Not authenticated.');
        } elseif ($this->isAuthenticateOnDemand()) {
            $this->authenticate();
        }

        $payload['sessionName'] = $this->sessionId;

        if (count($payload)) {
            if (isset($payload['queryString'])) {
                $queryString = '&queryString=' . urlencode($payload['queryString']);
                unset($payload['queryString']);
            }
            $query .= '&' . http_build_query($payload);
            if (isset($queryString)) {
                $query .= $queryString;
            }
        }

        $response = $this->httpClient->get($query, ['headers' => $this->requestHeaders]);

        $response = $this->handleResponse($response, $query);

        return $response;
    }


    public function post(string $operation, array $payload)
    {
        $payloadFinal['operation'] = $operation;

        if (!$this->isAuthenticated() && !$this->isAuthenticateOnDemand()) {
            throw new VtigerSessionException('Not authenticated.');
        } elseif ($this->isAuthenticateOnDemand()) {
            $this->authenticate();
        }

        $payload['sessionName'] = $this->sessionId;

        $payloadFinal = array_merge($payloadFinal, $payload);

        $response = $this->httpClient->post($this->apiDomain, ['form_params' => $payloadFinal]);

        return $this->handleResponse($response, $this->apiDomain, $payloadFinal);
    }

    private function handleResponse(Response $response, string $apiUrl, array $payload = [])
    {
        $content = $response->getBody()->getContents();

        if ($response->getReasonPhrase() != 'OK') {
            throw new VtigerSessionException('Server responded with an error');
        }

        $content = json_decode($content);

        if ($content->success) {
            return $content->result;
        }

        $error = isset($content->error) ? $content->error->code . ": " . $content->error->message : "No message";


        throw new VtigerInvalidRequestException($error, $apiUrl, $payload);
    }

}