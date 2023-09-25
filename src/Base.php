<?php

namespace PhalApi\Wordpress;

use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use PhalApi\Exception\BadRequestException;
use Exception;

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
        $logBase = __NAMESPACE__ . DIRECTORY_SEPARATOR . __CLASS__ . DIRECTORY_SEPARATOR . __FUNCTION__ . ' # ' . $method . ' # ' . $route;
        if (!empty($wordpress)) {
            try {
                $results = null;
                switch ($method) {
                    case 'post':
                        $results = $wordpress->post($route, $parameters);
                        $code = $results->getStatusCode();
                        if ($code >= 400) {
                            throw new BadRequestException('Error Code', $code);
                        }
                        return json_decode($results->getBody(), true);
                    case 'put':
                        $results = $wordpress->put($route, $parameters);
                        $code = $results->getStatusCode();
                        if ($code >= 400) {
                            throw new BadRequestException('Error Code', $code);
                        }
                        return json_decode($results->getBody(), true);
                    case 'delete':
                        $results = $wordpress->delete($route, $parameters);
                        $code = $results->getStatusCode();
                        if ($code >= 400) {
                            throw new BadRequestException('Error Code', $code);
                        }
                        return json_decode($results->getBody(), true);
                    default:
                        $rs = $wordpress->get($route, $parameters);
                        $code = $rs->getStatusCode();
                        if ($code >= 400) {
                            throw new BadRequestException('Error Code', $code);
                        }
                        $data = json_decode($rs->getBody(), true);
                        if ($returnArray) {
                            $total = 0;
                            $totalPage = 0;
                            $queries = 0;
                            $seconds = 0;
                            $memory = 0;
                            $headers = $rs->getHeaders();
                            if (is_array($headers) && !empty($headers)) {
                                $total = $headers['X-WP-Total'][0] ?? $headers['x-wp-total'][0] ?? $headers['X-WP-TOTAL'][0] ?? 0;
                                $totalPage = $headers['X-WP-TotalPages'][0] ?? $headers['x-wp-totalpages'] ?? $headers['X-WP-TOTALPAGES'] ?? 0;
                                $queries = $headers['X-WP-Queries'][0] ?? $headers['x-wp-queries'][0] ?? $headers['X-WP-QUERIES'][0] ?? 0;
                                $seconds = $headers['X-WP-Seconds'][0] ?? $headers['x-wp-seconds'][0] ?? $headers['X-WP-SECONDS'][0] ?? 0;
                                $memory = $headers['X-WP-Memory'][0] ?? $headers['x-wp-memory'][0] ?? $headers['X-WP-MEMORY'][0] ?? 0;
                            }
                            $results = [
                                'items' => $data,
                                'total' => intval($total),
                                'totalPage' => intval($totalPage),
                                'queries' => $queries,
                                'seconds' => $seconds,
                                'memory' => $memory,
                            ];
                        } else {
                            $results = $data;
                        }
                }
                return $results;
            } catch (RequestException $e) {
                $di->logger->error($logBase . ' # RequestException');
                if ($e->hasResponse()) {
                    $di->logger->error($logBase, ['RequestException' => Psr7\Message::toString($e->getResponse())]);
                }
                return null;
            } catch (ClientException $e) {
                $di->logger->error($logBase . ' # ClientException');
                if ($e->hasResponse()) {
                    $di->logger->error($logBase, ['ClientException' => Psr7\Message::toString($e->getResponse())]);
                }
            } catch (ServerException $e) {
                $di->logger->error($logBase . ' # ServerException');
                if ($e->hasResponse()) {
                    $di->logger->error($logBase, ['ServerException' => Psr7\Message::toString($e->getResponse())]);
                }
            } catch (Exception $e) {
                $di->logger->error($logBase, ['Exception' => $e->getMessage()]);
            }
        } else {
            return null;
        }
    }
}
