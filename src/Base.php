<?php

declare(strict_types=1);

namespace PhalApi\Wordpress;

use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use PhalApi\Exception\BadRequestException;
use Psr\Http\Message\ResponseInterface;
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
        } catch (RequestException $e) {
            $di->logger->error($logBase . ' # RequestException');
            if ($e->hasResponse()) {
                $di->logger->error($logBase, ['RequestException' => Psr7\Message::toString($e->getResponse())]);
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
        return json_decode($results->getBody()->getContents(), true);
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
        return json_decode($results->getBody()->getContents(), true);
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
        return json_decode($results->getBody()->getContents(), true);
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
        $data = json_decode($rs->getBody()->getContents(), true);

        if ($returnArray) {
            return $this->buildArrayResponse($rs, $data);
        }

        return $data;
    }

    /**
     * Build array response with pagination info.
     */
    private function buildArrayResponse(ResponseInterface $rs, array|null $data): array
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
        // WordPress通常使用驼峰命名法，如 "X-Wp-Total"，我们需要匹配这些格式
        $variants = [
            $name,                                    // original: X-WP-Total
            strtolower($name),                        // lowercase: x-wp-total
            strtoupper($name),                        // uppercase: X-WP-TOTAL
            $this->convertToCamelCase($name),         // camelcase: X-Wp-Total
        ];

        foreach ($variants as $variant) {
            // Headers keys can be case-insensitive, normalize them
            foreach ($headers as $headerName => $headerValue) {
                if (strtolower($headerName) === strtolower($variant) && isset($headerValue[0])) {
                    return $headerValue[0];
                }
            }
        }

        return 0;
    }

    /**
     * Convert header name to WordPress camelCase format.
     * Example: X-WP-Total -> X-Wp-Total
     */
    private function convertToCamelCase(string $name): string
    {
        $parts = explode('-', $name);
        $result = [];
        
        foreach ($parts as $index => $part) {
            if ($index === 0) {
                $result[] = $part;  // First part stays as is
            } else {
                $result[] = ucfirst(strtolower($part));  // Subsequent parts are ucfirst'd
            }
        }
        
        return implode('-', $result);
    }
}
