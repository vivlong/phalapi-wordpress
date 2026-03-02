<?php

declare(strict_types=1);

namespace PhalApi\Wordpress;

use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use PhalApi\Exception\BadRequestException;
use Throwable;

abstract class Base
{
    public function __construct(
        protected Client $instance
    ) {
    }

    /**
     * Make request to WordPress API.
     *
     * @param string $method HTTP method (get, post, put, delete)
     * @param string $route API route
     * @param array $parameters Request parameters
     * @param bool $returnArray Return as array with pagination info
     * @return array|null Response data or null on error
     */
    public function request(
        string $method = 'get',
        string $route = '/',
        array $parameters = [],
        bool $returnArray = false
    ): array|null {
        $di = \PhalApi\DI();
        $wordpress = $this->instance;
        $logBase = __NAMESPACE__ . DIRECTORY_SEPARATOR . __CLASS__ . DIRECTORY_SEPARATOR . __FUNCTION__ . ' # ' . $method . ' # ' . $route;

        try {
            return match ($method) {
                'post' => $this->handlePostRequest($wordpress, $route, $parameters),
                'put' => $this->handlePutRequest($wordpress, $route, $parameters),
                'delete' => $this->handleDeleteRequest($wordpress, $route, $parameters),
                default => $this->handleGetRequest($wordpress, $route, $parameters, $returnArray),
            };
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
            return null;
        } catch (ServerException $e) {
            $di->logger->error($logBase . ' # ServerException');
            if ($e->hasResponse()) {
                $di->logger->error($logBase, ['ServerException' => Psr7\Message::toString($e->getResponse())]);
            }
            return null;
        } catch (Throwable $e) {
            $di->logger->error($logBase, ['Exception' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Handle POST request.
     */
    private function handlePostRequest(Client $wordpress, string $route, array $parameters): array|null
    {
        $results = $wordpress->post($route, $parameters);
        $code = $results->getStatusCode();
        if ($code >= 400) {
            throw new BadRequestException('Error Code', $code);
        }
        return json_decode($results->getBody(), true);
    }

    /**
     * Handle PUT request.
     */
    private function handlePutRequest(Client $wordpress, string $route, array $parameters): array|null
    {
        $results = $wordpress->put($route, $parameters);
        $code = $results->getStatusCode();
        if ($code >= 400) {
            throw new BadRequestException('Error Code', $code);
        }
        return json_decode($results->getBody(), true);
    }

    /**
     * Handle DELETE request.
     */
    private function handleDeleteRequest(Client $wordpress, string $route, array $parameters): array|null
    {
        $results = $wordpress->delete($route, $parameters);
        $code = $results->getStatusCode();
        if ($code >= 400) {
            throw new BadRequestException('Error Code', $code);
        }
        return json_decode($results->getBody(), true);
    }

    /**
     * Handle GET request.
     */
    private function handleGetRequest(
        Client $wordpress,
        string $route,
        array $parameters,
        bool $returnArray
    ): array|null {
        $rs = $wordpress->get($route, $parameters);
        $code = $rs->getStatusCode();
        if ($code >= 400) {
            throw new BadRequestException('Error Code', $code);
        }
        $data = json_decode($rs->getBody(), true);

        if ($returnArray) {
            return $this->buildArrayResponse($rs, $data);
        }

        return $data;
    }

    /**
     * Build array response with pagination info.
     */
    private function buildArrayResponse($rs, array|null $data): array
    {
        $headers = $rs->getHeaders();
        $total = 0;
        $totalPage = 0;
        $queries = 0;
        $seconds = 0;
        $memory = 0;

        if (is_array($headers) && !empty($headers)) {
            $total = $this->getHeader($headers, 'X-WP-Total');
            $totalPage = $this->getHeader($headers, 'X-WP-TotalPages');
            $queries = $this->getHeader($headers, 'X-WP-Queries');
            $seconds = $this->getHeader($headers, 'X-WP-Seconds');
            $memory = $this->getHeader($headers, 'X-WP-Memory');
        }

        return [
            'items' => $data,
            'total' => (int) $total,
            'totalPage' => (int) $totalPage,
            'queries' => $queries,
            'seconds' => $seconds,
            'memory' => $memory,
        ];
    }

    /**
     * Get header value case-insensitively.
     */
    private function getHeader(array $headers, string $name): string|int
    {
        $variants = [
            $name,
            strtolower($name),
            strtoupper($name),
        ];

        foreach ($variants as $variant) {
            if (isset($headers[$variant][0])) {
                return $headers[$variant][0];
            }
        }

        return 0;
    }
}
