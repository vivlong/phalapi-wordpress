<?php
/**
 * Wordpress REST API Client.
 */

namespace PhalApi\Wordpress;

use PhalApi\Wordpress\HttpClient\HttpClient;

/**
 * REST API Client class.
 */
class Client
{
    /**
     * Wordpress REST API Client version.
     */
    const VERSION = '2.0.0';

    /**
     * HttpClient instance.
     *
     * @var HttpClient
     */
    public $http;

    /**
     * Initialize client.
     *
     * @param string $url       Site URL.
     * @param string $apiKey    JWT Api key.
     * @param string $apiSecret JWT Api secret.
     * @param array  $options   Options (version, timeout, verify_ssl).
     */
    public function __construct($url, $apiKey, $apiSecret, $options = [], $basicAuth = null)
    {
        if(!empty($basicAuth)) {
            $this->http = new HttpClient($url, $basicAuth, $options);
        } else {
            $this->http = new HttpClient($url, $apiKey, $apiSecret, $options);
        }
    }

    /**
     * POST method.
     *
     * @param string $endpoint API endpoint.
     * @param array  $data     Request data.
     *
     * @return array
     */
    public function post($endpoint, $data)
    {
        return $this->http->request($endpoint, 'POST', $data);
    }

    /**
     * PUT method.
     *
     * @param string $endpoint API endpoint.
     * @param array  $data     Request data.
     *
     * @return array
     */
    public function put($endpoint, $data)
    {
        return $this->http->request($endpoint, 'PUT', $data);
    }

    /**
     * GET method.
     *
     * @param string $endpoint   API endpoint.
     * @param array  $parameters Request parameters.
     *
     * @return array
     */
    public function get($endpoint, $parameters = [])
    {
        return $this->http->request($endpoint, 'GET', [], $parameters);
    }

    /**
     * DELETE method.
     *
     * @param string $endpoint   API endpoint.
     * @param array  $parameters Request parameters.
     *
     * @return array
     */
    public function delete($endpoint, $parameters = [])
    {
        return $this->http->request($endpoint, 'DELETE', [], $parameters);
    }

    /**
     * OPTIONS method.
     *
     * @param string $endpoint API endpoint.
     *
     * @return array
     */
    public function options($endpoint)
    {
        return $this->http->request($endpoint, 'OPTIONS', [], []);
    }
}
