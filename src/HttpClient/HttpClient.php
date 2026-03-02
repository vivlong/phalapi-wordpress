<?php

declare(strict_types=1);

/**
 * Wordpress REST API HTTP Client.
 */

namespace PhalApi\Wordpress\HttpClient;

use PhalApi\Wordpress\Client;
use Throwable;

/**
 * REST API HTTP Client class.
 */
class HttpClient
{
    protected \CurlHandle|false $ch = false;
    protected string $url = '';
    protected string $apiKey = '';
    protected string $apiSecret = '';
    protected Options $options;
    private Request $request;
    private Response $response;
    private string $responseHeaders = '';
    private string $accessToken = '';
    private string $basicAuth = '';

    /**
     * Initialize HTTP client with JWT key pairs.
     *
     * @param string $url Site URL.
     * @param string $authType Auth type.
     * @param string $apiKey JWT api key.
     * @param string $apiSecret JWT api Secret.
     * @param string $authToken Authentication token.
     * @param array $options Client options.
     */
    public function __construct(
        string $url,
        string $authType,
        string $apiKey = '',
        string $apiSecret = '',
        string $authToken = '',
        array $options = []
    ) {
        if (!function_exists('curl_version')) {
            throw new HttpClientException(
                'cURL is NOT installed on this server',
                -1,
                new Request(),
                new Response()
            );
        }

        $this->options = new Options($options);
        $this->url = $this->buildApiUrl($url);

        $di = \PhalApi\DI();
        if (!isset($di->cache)) {
            $di->cache = new \PhalApi\Cache\FileCache([
                'path' => API_ROOT . '/runtime',
                'prefix' => 'wp'
            ]);
        }

        $this->initializeAuthentication($authType, $authToken, $apiKey, $apiSecret, $di);
    }

    /**
     * Initialize authentication based on type.
     */
    private function initializeAuthentication(
        string $authType,
        string $authToken,
        string $apiKey,
        string $apiSecret,
        \PhalApi\DI $di
    ): void {
        if ($authType === 'basic' && !empty($authToken)) {
            $this->basicAuth = $authToken;
        } elseif ($authType === 'jwt' && !empty($authToken)) {
            $this->accessToken = $authToken;
        } elseif (!empty($apiKey) && !empty($apiSecret)) {
            $this->apiKey = $apiKey;
            $this->apiSecret = $apiSecret;
            $this->initializeJwtToken($di);
        }
    }

    /**
     * Initialize JWT token from cache or request new one.
     */
    private function initializeJwtToken(\PhalApi\DI $di): void
    {
        $jwt = $di->cache->get($this->apiKey);

        if (!empty($jwt)) {
            $jwtAuth = json_decode($jwt);
            $this->accessToken = $jwtAuth->access_token;
        } else {
            $jwtAuth = $this->request('token', 'POST', [
                'api_key' => $this->apiKey,
                'api_secret' => $this->apiSecret,
            ]);
            $di->cache->set($this->apiKey, json_encode($jwtAuth), $jwtAuth->exp);
            $this->accessToken = $jwtAuth->access_token;
        }
    }

    /**
     * Build API URL.
     *
     * @param string $url Store URL.
     * @return string
     */
    protected function buildApiUrl(string $url): string
    {
        $api = $this->options->apiPrefix();

        return rtrim($url, '/') . $api . $this->options->getVersion() . '/';
    }

    /**
     * Build URL.
     *
     * @param string $url URL.
     * @param array $parameters Query string parameters.
     * @return string
     */
    protected function buildUrlQuery(string $url, array $parameters = []): string
    {
        if (!empty($parameters)) {
            $url .= '?' . http_build_query($parameters);
        }

        return $url;
    }

    /**
     * Setup method.
     *
     * @param string $method Request method.
     */
    protected function setupMethod(string $method): void
    {
        if ($method === 'POST') {
            curl_setopt($this->ch, CURLOPT_POST, true);
        } elseif (in_array($method, ['PUT', 'DELETE', 'OPTIONS'], true)) {
            curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);
        }
    }

    /**
     * Get request headers.
     *
     * @param bool $sendData If request send data or not.
     * @param array|null $formData Form data for multipart uploads.
     * @return array
     */
    protected function getRequestHeaders(bool $sendData = false, ?array $formData = null): array
    {
        $headers = [
            'Accept' => 'application/json',
            'User-Agent' => $this->options->userAgent() . '/' . Client::VERSION,
        ];

        if ($sendData) {
            if (!empty($formData)) {
                $headers['Content-Type'] = 'multipart/form-data; boundary=' . $formData['delimiter'];
                $headers['Content-Length'] = $formData['length'];
            } else {
                $headers['Content-Type'] = 'application/json;charset=utf-8';
            }
        }

        if (!empty($this->accessToken)) {
            $headers['Authorization'] = 'Bearer ' . $this->accessToken;
        } elseif (!empty($this->basicAuth)) {
            $headers['Authorization'] = 'Basic ' . $this->basicAuth;
        }

        return $headers;
    }

    /**
     * Create request.
     *
     * @param string $endpoint Request endpoint.
     * @param string $method Request method.
     * @param array $data Request data.
     * @param array $parameters Request parameters.
     * @return Request
     */
    protected function createRequest(
        string $endpoint,
        string $method,
        array $data = [],
        array $parameters = []
    ): Request {
        $body = '';
        $url = $this->url . $endpoint;
        $hasData = !empty($data);

        // Setup method.
        $this->setupMethod($method);

        $formData = null;
        // Include post fields.
        if ($hasData) {
            if (!empty($data['file'], $data['file']['tmp_name'])) {
                $formData = $this->buildMultipartFormData($data);
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $formData['stream']);
            } else {
                $body = json_encode($data);
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $body);
            }
        }

        $this->request = new Request(
            $this->buildUrlQuery($url, $parameters),
            $method,
            $parameters,
            $this->getRequestHeaders($hasData, $formData),
            $body
        );

        return $this->getRequest();
    }

    /**
     * Build multipart form data for file uploads.
     */
    private function buildMultipartFormData(array $data): array
    {
        $delimiter = uniqid();
        $fields = [
            'file' => file_get_contents($data['file']['tmp_name']),
            'filename' => $data['file']['name'],
        ];
        $streamData = '';
        $streamData .= '--' . $delimiter . "\r\n"
            . 'Content-Disposition: form-data; name="file"; filename="' . $fields['filename'] . '"' . "\r\n"
            . 'Content-Type:application/octet-stream' . "\r\n\r\n";
        $streamData .= $fields['file'] . "\r\n";
        $streamData .= '--' . $delimiter . "--\r\n";

        return [
            'delimiter' => $delimiter,
            'length' => strlen($streamData),
            'stream' => $streamData,
        ];
    }

    /**
     * Get response headers.
     *
     * @return array
     */
    protected function getResponseHeaders(): array
    {
        $headers = [];
        $lines = explode("\n", $this->responseHeaders);
        $lines = array_filter($lines, 'trim');

        foreach ($lines as $line) {
            // Remove HTTP/xxx params.
            if (false === strpos($line, ': ')) {
                continue;
            }

            [$key, $value] = explode(': ', $line, 2);

            $headers[$key] = isset($headers[$key])
                ? $headers[$key] . ', ' . trim($value)
                : trim($value);
        }

        return $headers;
    }

    /**
     * Create response.
     *
     * @return Response
     */
    protected function createResponse(): Response
    {
        // Set response headers.
        $this->responseHeaders = '';
        curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, function ($_, $headers): int {
            $this->responseHeaders .= $headers;

            return strlen($headers);
        });

        // Get response data.
        $body = curl_exec($this->ch);
        $code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $headers = $this->getResponseHeaders();

        // Register response.
        $this->response = new Response(
            (int) $code,
            $headers,
            $body !== false ? $body : ''
        );

        return $this->getResponse();
    }

    /**
     * Set default cURL settings.
     */
    protected function setDefaultCurlSettings(): void
    {
        $verifySsl = $this->options->verifySsl();
        $timeout = $this->options->getTimeout();
        $followRedirects = $this->options->getFollowRedirects();

        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $verifySsl);
        if (!$verifySsl) {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, $verifySsl);
        }
        if ($followRedirects) {
            curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        }
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->request->getRawHeaders());
        curl_setopt($this->ch, CURLOPT_URL, $this->request->getUrl());
    }

    /**
     * Look for errors in the request.
     *
     * @param object|array $parsedResponse Parsed body response.
     */
    protected function lookForErrors(object|array $parsedResponse): void
    {
        // Any non-200/201/202 response code indicates an error.
        if (!in_array($this->response->getCode(), [200, 201, 202], true)) {
            $errors = $parsedResponse->errors ?? $parsedResponse;
            $errorMessage = '';
            $errorCode = '';

            if (is_array($errors)) {
                $errorMessage = $errors[0]->message;
                $errorCode = $errors[0]->code;
            } elseif (isset($errors->message, $errors->code)) {
                $errorMessage = $errors->message;
                $errorCode = $errors->code;
            }

            throw new HttpClientException(
                sprintf('Error: %s [%s]', $errorMessage, $errorCode),
                $this->response->getCode(),
                $this->request,
                $this->response
            );
        }
    }

    /**
     * Process response.
     *
     * @return object|array
     */
    protected function processResponse(): object|array
    {
        $body = $this->response->getBody();

        // Look for UTF-8 BOM and remove.
        if (0 === strpos(bin2hex(substr($body, 0, 4)), 'efbbbf')) {
            $body = substr($body, 3);
        }

        $parsedResponse = json_decode($body);

        // Test if return a valid JSON.
        if (JSON_ERROR_NONE !== json_last_error()) {
            $message = json_last_error_msg();
            throw new HttpClientException(
                sprintf('JSON ERROR: %s', $message),
                $this->response->getCode(),
                $this->request,
                $this->response
            );
        }

        $this->lookForErrors($parsedResponse);

        return $parsedResponse;
    }

    /**
     * Make requests.
     *
     * @param string $endpoint Request endpoint.
     * @param string $method Request method.
     * @param array $data Request data.
     * @param array $parameters Request parameters.
     * @return object|array
     */
    public function request(
        string $endpoint,
        string $method,
        array $data = [],
        array $parameters = []
    ): object|array {
        // Initialize cURL.
        $this->ch = curl_init();

        // Set request args.
        $request = $this->createRequest($endpoint, $method, $data, $parameters);

        // Default cURL settings.
        $this->setDefaultCurlSettings();

        // Get response.
        $response = $this->createResponse();

        // Check for cURL errors.
        if (curl_errno($this->ch)) {
            throw new HttpClientException(
                'cURL Error: ' . curl_error($this->ch),
                0,
                $request,
                $response
            );
        }

        curl_close($this->ch);

        return $this->processResponse();
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
