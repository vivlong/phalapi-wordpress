<?php

declare(strict_types=1);

/**
 * Wordpress REST API HTTP Client Request.
 */

namespace PhalApi\Wordpress\HttpClient;

/**
 * REST API HTTP Client Request class.
 */
class Request
{
    /**
     * Initialize request.
     *
     * @param string $url Request url.
     * @param string $method Request method.
     * @param array $parameters Request paramenters.
     * @param array $headers Request headers.
     * @param string $body Request body.
     */
    public function __construct(
        private string $url = '',
        private string $method = 'POST',
        private array $parameters = [],
        private array $headers = [],
        private string $body = ''
    ) {
    }

    /**
     * Set url.
     *
     * @param string $url Request url.
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * Set method.
     *
     * @param string $method Request method.
     */
    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    /**
     * Set parameters.
     *
     * @param array $parameters Request paramenters.
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * Set headers.
     *
     * @param array $headers Request headers.
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    /**
     * Set body.
     *
     * @param string $body Request body.
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Get method.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get parameters.
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get headers.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get raw headers.
     *
     * @return array
     */
    public function getRawHeaders(): array
    {
        $headers = [];

        foreach ($this->headers as $key => $value) {
            $headers[] = $key . ': ' . $value;
        }

        return $headers;
    }

    /**
     * Get body.
     *
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }
}
