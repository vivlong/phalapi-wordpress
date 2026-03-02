<?php

declare(strict_types=1);

/**
 * Wordpress REST API HTTP Client Exception.
 */

namespace PhalApi\Wordpress\HttpClient;

use Exception;
use Throwable;

/**
 * REST API HTTP Client Exception class.
 */
class HttpClientException extends Exception
{
    /**
     * Initialize exception.
     *
     * @param string $message Error message.
     * @param int $code Error code.
     * @param Request $request Request data.
     * @param Response $response Response data.
     * @param Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message,
        int $code,
        private Request $request,
        private Response $response,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get request data.
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Get response data.
     *
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}
