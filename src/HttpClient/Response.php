<?php

declare(strict_types=1);

/**
 * Wordpress REST API HTTP Client Response.
 */

namespace PhalApi\Wordpress\HttpClient;

/**
 * REST API HTTP Client Response class.
 */
class Response
{
    /**
     * Initialize response.
     *
     * @param int $code Response code.
     * @param array $headers Response headers.
     * @param string $body Response body.
     */
    public function __construct(
        private int $code = 0,
        private array $headers = [],
        private string $body = ''
    ) {
    }

    /**
     * Set code.
     *
     * @param int $code Response code.
     */
    public function setCode(int $code): void
    {
        $this->code = $code;
    }

    /**
     * Set headers.
     *
     * @param array $headers Response headers.
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    /**
     * Set body.
     *
     * @param string $body Response body.
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    /**
     * Get code.
     *
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * Get headers.
     *
     * @return array Response headers.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get body.
     *
     * @return string Response body.
     */
    public function getBody(): string
    {
        return $this->body;
    }
}
