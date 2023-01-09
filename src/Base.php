<?php

namespace PhalApi\Wordpress;

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
                        if ($code !== 200) {
                            throw new BadRequestException('error code', $code);
                        }
                        return json_decode($results->getBody(), true);
                    case 'put':
                        $results = $wordpress->put($route, $parameters);
                        $code = $results->getStatusCode();
                        if ($code !== 200) {
                            throw new BadRequestException('error code', $code);
                        }
                        return json_decode($results->getBody(), true);
                    case 'delete':
                        $results = $wordpress->delete($route, $parameters);
                        $code = $results->getStatusCode();
                        if ($code !== 200) {
                            throw new BadRequestException('error code', $code);
                        }
                        return json_decode($results->getBody(), true);
                    default:
                        $rs = $wordpress->get($route, $parameters);
                        $code = $rs->getStatusCode();
                        if ($code !== 200) {
                            throw new BadRequestException('error code', $code);
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
                                $total = isset($headers['X-WP-Total']) ? $headers['X-WP-Total'][0] : 0;
                                $totalPage = isset($headers['X-WP-TotalPages']) ? $headers['X-WP-TotalPages'][0] : 0;
                                $queries = isset($headers['X-WP-Queries']) ? $headers['X-WP-Queries'][0] : 0;
                                $seconds = isset($headers['X-WP-Seconds']) ? $headers['X-WP-Seconds'][0] : 0;
                                $memory = isset($headers['X-WP-Memory']) ? $headers['X-WP-Memory'][0] : 0;
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
                    $di->logger->error($logBase, ['RequestException' => \Psr7\str($e->getResponse())]);
                }
                return null;
            } catch (ClientException $e) {
                $di->logger->error($logBase . ' # ClientException');
                if ($e->hasResponse()) {
                    $di->logger->error($logBase, ['ClientException' => \Psr7\str($e->getResponse())]);
                }
            } catch (ServerException $e) {
                $di->logger->error($logBase . ' # ServerException');
                if ($e->hasResponse()) {
                    $di->logger->error($logBase, ['ServerException' => \Psr7\str($e->getResponse())]);
                }
            } catch (Exception $e) {
                $di->logger->error($logBase, ['Exception' => $e->getMessage()]);
            }
        } else {
            return null;
        }
    }
}
