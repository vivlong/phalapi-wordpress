<?php
/**
 * Wordpress REST API Client.
 */

namespace PhalApi\Wordpress;

use PhalApi\Wordpress\HttpClient\HttpClient;
// use GuzzleHttp\Exception\RequestException;
// use GuzzleHttp\Exception\ClientException;

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
    public function __construct($url, $authType = 'jwt', $options = [], $basicAuth = null, $jwtToken = null, $jwtKeyPairs = null)
    {
        if(!empty($basicAuth)) {
            $this->http = new HttpClient($url, $authType, $basicAuth, $options);
        } else if(!empty($jwtToken)) {
            $this->http = new HttpClient($url, $authType, $jwtToken, $options);
        } else if(!empty($jwtKeyPairs)) {
            $this->http = new HttpClient($url, $authType, $jwtKeyPairs['apiKey'], $jwtKeyPairs['apiSecret'], $options);
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

    // private function request($endpoint, $method, $data = [], $parameters = [])
    // {
    //     try {
    //         $res = $this->client->request($method, $endpoint, [
    //             'json' => [
    //                 'usr' => $user['usr'],
    //                 'pwd' => $user['pwd'],
    //             ],
    //             'timeout' => 10,
    //             'connect_timeout' => 15,
    //             'verify' => false,
    //         ]);
    //         $code = $res->getStatusCode();
    //         $data = json_decode($res->getBody(), true);

    //         return $data;
    //     } catch (RequestException $e) {
    //         $di->logger->info('WordPress # getWordpress # guzzle error request');
    //         if ($e->hasResponse()) {
    //             $di->logger->info('WordPress # getWordpress # guzzle error response '.$e->getResponse()->getBody()->getContents());
    //         }
    //     } catch (ClientException $e) {
    //         $di->logger->info('WordPress # getWordpress # guzzle error client');
    //         if ($e->hasResponse()) {
    //             $di->logger->info('WordPress # getWordpress # guzzle error response '.$e->getResponse()->getBody()->getContents());
    //         }
    //     } catch (ServerException $e) {
    //         $di->logger->info('WordPress # getWordpress # guzzle error server');
    //         if ($e->hasResponse()) {
    //             $di->logger->info('WordPress # getWordpress # guzzle error response '.$e->getResponse()->getBody()->getContents());
    //         }
    //     } catch (Exception $e) {
    //         $di->logger->info('Zeus # getWordpress # guzzle error');
    //     }
    // }

    // private function getClient($url, $authType, $basicAuth, $options)
    // {
    //     $client = new \GuzzleHttp\Client([
    //         'base_uri' => $url,
    //         'timeout' => 10,
    //     ]);
    // }
}
