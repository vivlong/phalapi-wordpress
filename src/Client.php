<?php

/**
 * Wordpress REST API Client.
 */

namespace PhalApi\Wordpress;

// use PhalApi\Wordpress\HttpClient\HttpClient;
use GuzzleHttp\Psr7;

/**
 * REST API Client class.
 */
class Client
{
    /**
     * Wordpress REST API Client version.
     */
    const VERSION = 'wp/v2';

    /**
     * Default WP API prefix.
     * Including leading and trailing slashes.
     */
    const WP_API_PREFIX = '/wp-json/';

    /**
     * HttpClient instance.
     *
     * @var GuzzleHttpClient
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
    public function __construct($url, $authType = 'jwt', $options = [], $basicAuth = null, $jwtToken = null, $jwtKeyPairs = null)
    {
        $di = \PhalApi\DI();
        if(substr($url, -1) !== '/') {
            $url = $url.'/';
        }
        if (!empty($basicAuth)) {
            $setting = [
                'base_uri' => $url,
                'headers' => [
                    'Authorization' => 'Basic ' . $basicAuth,
                ]
            ];
            $config = array_merge($options, $setting);
            $this->http = new \GuzzleHttp\Client($config);
        } else if (!empty($jwtToken)) {
            $setting = [
                'base_uri' => $url,
                'headers' => [
                    'Authorization' => 'Bearer ' . $jwtToken,
                ]
            ];
            $config = array_merge($options, $setting);
            $this->http = new \GuzzleHttp\Client($config);
        } else if (!empty($jwtKeyPairs)) {
            $setting = [
                'base_uri' => $url,
                'verify' => false,
                'headers' => [
                    'Authorization' => 'Bearer ' . $jwtToken,
                ]
            ];
            $config = array_merge($options, $setting);
            $this->http = new \GuzzleHttp\Client($config);
            $jwtToken = null;
            if (!isset($di->cache)) {
                $di->cache = new \PhalApi\Cache\FileCache(['path' => API_ROOT . '/runtime', 'prefix' => 'wp']);
            }
            $jwt = $di->cache->get($jwtKeyPairs['apiKey']);
            if (!empty($jwt)) {
                $jwtAuth = json_decode($jwt);
                $jwtToken = $jwtAuth->access_token;
            } else {
                $tokenRequest = $this->post('token', ['api_key' => $jwtKeyPairs['apiKey'], 'api_secret' => $jwtKeyPairs['apiSecret']]);
                $di->logger->info('WordPress # getWordpress', ['tokenRequest' => $tokenRequest]);
                $jwtAuth = $tokenRequest->getBody();
                $di->cache->set($jwtKeyPairs['apiKey'], json_encode($jwtAuth), $jwtAuth->exp);
                $jwtToken = $jwtAuth->access_token;
            }
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
        $di = \PhalApi\DI();
        if(isset($data['file']) && isset($data['file']['tmp_name'])) {
            $multipart = [];
            foreach ($data as $key => $value) {
                if($key === 'file') {
                    array_push($multipart, [
                        'name' => $key,
                        'contents' => Psr7\Utils::tryFopen($value['tmp_name'], 'r'),
                        'filename' => $value['name'],
                    ]);
                } else {
                    array_push($multipart, [
                        'name' => $key,
                        'contents' => $value,
                    ]);
                }
            }
            return $this->http->request('POST', $endpoint, ['multipart' => $multipart]);
        } else {
            return $this->http->request('POST', $endpoint, ['form_params' => $data]);
        }
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
        return $this->http->request('PUT', $endpoint, ['form_params' => $data], $data);
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
        return $this->http->request('GET', $endpoint, [
            'query' => $parameters
        ]);
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
        return $this->http->request('DELETE', $endpoint, ['form_params' => $parameters], $parameters);
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
        return $this->http->request('OPTIONS', $endpoint, [], []);
    }
}
