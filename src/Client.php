<?php

declare(strict_types=1);

/**
 * Wordpress REST API Client.
 */

namespace PhalApi\Wordpress;

use GuzzleHttp\Psr7;
use Psr\Http\Message\ResponseInterface;

/**
 * REST API Client class.
 */
class Client
{
    /**
     * Wordpress REST API Client version.
     */
    public const VERSION = 'wp/v2';

    /**
     * @var \GuzzleHttp\Client HTTP client instance
     */
    protected \GuzzleHttp\Client $http;

    /**
     * Default WP API prefix.
     * Including leading and trailing slashes.
     */
    public const WP_API_PREFIX = '/wp-json/';

    /**
     * Initialize client.
     *
     * @param string $url Site URL.
     * @param string $authType Auth type (jwt, basic).
     * @param array $options Options (version, timeout, verify_ssl).
     * @param string|null $basicAuth Basic authentication token.
     * @param string|null $jwtToken JWT token.
     * @param array|null $jwtKeyPairs JWT key pairs.
     */
    public function __construct(
        protected string $url,
        protected string $authType = 'jwt',
        protected array $options = [],
        ?string $basicAuth = null,
        ?string $jwtToken = null,
        ?array $jwtKeyPairs = null
    ) {
        $di = \PhalApi\DI();

        // Ensure URL ends with slash
        if (!str_ends_with($this->url, '/')) {
            $this->url .= '/';
        }

        $config = $this->buildConfig($basicAuth, $jwtToken);

        if ($jwtKeyPairs !== null) {
            // Use the verify option from $this->options if available, otherwise default to false
            $verify = $this->options['verify'] ?? false;
            $this->http = new \GuzzleHttp\Client(array_merge($config, ['verify' => $verify]));
            $this->handleJwtKeyPairs($jwtKeyPairs, $di);
        } else {
            $this->http = new \GuzzleHttp\Client($config);
        }
    }

    /**
     * Build Guzzle configuration.
     */
    private function buildConfig(?string $basicAuth, ?string $jwtToken): array
    {
        $headers = [];

        if ($basicAuth !== null) {
            $headers['Authorization'] = 'Basic ' . $basicAuth;
        } elseif ($jwtToken !== null) {
            $headers['Authorization'] = 'Bearer ' . $jwtToken;
        }

        return array_merge($this->options, [
            'base_uri' => $this->url,
            'headers' => $headers,
        ]);
    }

    /**
     * Handle JWT key pairs authentication.
     */
    private function handleJwtKeyPairs(array $jwtKeyPairs, \PhalApi\DI $di): void
    {
        if (!isset($di->cache)) {
            $di->cache = new \PhalApi\Cache\FileCache(['path' => API_ROOT . '/runtime', 'prefix' => 'wp']);
        }

        $jwt = $di->cache->get($jwtKeyPairs['apiKey']);

        if (!empty($jwt)) {
            $jwtAuth = json_decode($jwt, true);
            if (json_last_error() !== JSON_ERROR_NONE || !isset($jwtAuth['access_token'])) {
                // 如果缓存的数据有问题，删除它并重新获取
                $di->cache->delete($jwtKeyPairs['apiKey']);
                $jwtAuth = null;
            }
        }

        if (empty($jwtAuth) || !isset($jwtAuth['access_token'])) {
            $tokenRequest = $this->post('token', [
                'api_key' => $jwtKeyPairs['apiKey'],
                'api_secret' => $jwtKeyPairs['apiSecret'],
            ]);
            $di->logger->info('WordPress # getWordpress', ['tokenRequest' => $tokenRequest]);
            $jwtAuth = json_decode($tokenRequest->getBody()->getContents(), true);
            if (json_last_error() === JSON_ERROR_NONE && isset($jwtAuth['access_token'], $jwtAuth['exp'])) {
                $di->cache->set($jwtKeyPairs['apiKey'], json_encode($jwtAuth), $jwtAuth['exp']);
            } else {
                throw new \Exception("Failed to retrieve JWT token or invalid token response");
            }
        }

        $jwtToken = $jwtAuth['access_token'];

        // Update HTTP client with JWT token
        $headers = array_merge($this->options['headers'] ?? [], [
            'Authorization' => 'Bearer ' . $jwtToken,
        ]);

        $clientOptions = [
            'base_uri' => $this->url,
            'headers' => $headers,
        ];

        // Only override verify if specifically set in options
        if (isset($this->options['verify'])) {
            $clientOptions['verify'] = $this->options['verify'];
        } else {
            $clientOptions['verify'] = false;
        }

        $this->http = new \GuzzleHttp\Client($clientOptions);
    }

    /**
     * POST method.
     *
     * @param string $endpoint API endpoint.
     * @param array $data Request data.
     * @return ResponseInterface
     */
    public function post(string $endpoint, array $data): ResponseInterface
    {
        // Check if this is a file upload request
        if (isset($data['file'], $data['file']['tmp_name']) && file_exists($data['file']['tmp_name'])) {
            return $this->http->request('POST', $endpoint, [
                'multipart' => $this->buildMultipartData($data),
            ]);
        }

        return $this->http->request('POST', $endpoint, ['form_params' => $data]);
    }

    /**
     * Build multipart data for file uploads.
     */
    private function buildMultipartData(array $data): array
    {
        $multipart = [];

        foreach ($data as $key => $value) {
            if ($key === 'file') {
                $multipart[] = [
                    'name' => $key,
                    'contents' => Psr7\Utils::tryFopen($data['file']['tmp_name'], 'r'),
                    'filename' => $value['name'],
                ];
            } else {
                $multipart[] = [
                    'name' => $key,
                    'contents' => $value,
                ];
            }
        }

        return $multipart;
    }

    /**
     * PUT method.
     *
     * @param string $endpoint API endpoint.
     * @param array $data Request data.
     * @return ResponseInterface
     */
    public function put(string $endpoint, array $data): ResponseInterface
    {
        return $this->http->request('PUT', $endpoint, ['form_params' => $data]);
    }

    /**
     * GET method.
     *
     * @param string $endpoint API endpoint.
     * @param array $parameters Request parameters.
     * @return ResponseInterface
     */
    public function get(string $endpoint, array $parameters = []): ResponseInterface
    {
        return $this->http->request('GET', $endpoint, ['query' => $parameters]);
    }

    /**
     * DELETE method.
     *
     * @param string $endpoint API endpoint.
     * @param array $parameters Request parameters.
     * @return ResponseInterface
     */
    public function delete(string $endpoint, array $parameters = []): ResponseInterface
    {
        // For DELETE requests, some APIs expect parameters in query string
        if (!empty($parameters)) {
            return $this->http->request('DELETE', $endpoint, ['form_params' => $parameters]);
        }
        
        return $this->http->request('DELETE', $endpoint);
    }

    /**
     * OPTIONS method.
     *
     * @param string $endpoint API endpoint.
     * @return ResponseInterface
     */
    public function options(string $endpoint): ResponseInterface
    {
        return $this->http->request('OPTIONS', $endpoint, []);
    }
}
