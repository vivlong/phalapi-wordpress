<?php

namespace PhalApi\Wordpress;

use PhalApi\Wordpress\HttpClient\HttpClientException;

abstract class Base
{
    protected $instance;

    public function __construct($instance)
    {
        $this->instance = $instance;
    }

    public function request($method = 'get', $route = '/', $parameters = [], $returnArray = false)
    {
        $di = \PhalApi\DI();
        $wordpress = $this->instance;
        if (!empty($wordpress)) {
            try {
                $results = null;
                switch ($method) {
                    case 'post':
                        $results = $wordpress->post($route, $parameters);
                        break;
                    case 'put':
                        $results = $wordpress->put($route, $parameters);
                        break;
                    case 'delete':
                        $results = $wordpress->delete($route, $parameters);
                        break;
                    default:
                        $rs = $wordpress->get($route, $parameters);
                        if ($returnArray) {
                            $total = 0;
                            $totalPage = 0;
                            $queries = 0;
                            $seconds = 0;
                            $memory = 0;
                            $lastResponse = $wordpress->http->getResponse();
                            $headers = $lastResponse->getHeaders();
                            if (is_array($headers) && !empty($headers)) {
                                $total = $headers['X-WP-Total'] ?? 0;
                                $totalPage = $headers['X-WP-TotalPages'] ?? 0;
                                $queries = $headers['X-WP-Queries'] ?? 0;
                                $seconds = $headers['X-WP-Seconds'] ?? 0;
                                $memory = $headers['X-WP-Memory'] ?? 0;
                            }
                            $results = [
                                'items' => $rs,
                                'total' => intval($total),
                                'totalPage' => intval($totalPage),
                                'queries' => $queries,
                                'seconds' => $seconds,
                                'memory' => $memory,
                            ];
                        } else {
                            $results = $rs;
                        }
                }

                return $results;
            } catch (HttpClientException $e) {
                $lastRequest = $wordpress->http->getRequest();
                $lastResponse = $wordpress->http->getResponse();
                $rs = json_decode($lastResponse->getBody());
                if ($rs?->data?->status === 400) {
                    return $rs;
                } else {
                    $di->logger->error(__CLASS__.DIRECTORY_SEPARATOR.__FUNCTION__.' # '.$method.' # '.$route, ['request' => $lastRequest->getBody()]);
                    $di->logger->error(__CLASS__.DIRECTORY_SEPARATOR.__FUNCTION__.' # '.$method.' # '.$route, ['response' => $lastResponse->getBody()]);
                }
                $di->logger->error(__CLASS__.DIRECTORY_SEPARATOR.__FUNCTION__.' # '.$method.' # '.$route, ['HttpClientException' => $e->getMessage()]);

                return null;
            }
        } else {
            return null;
        }
    }
}
