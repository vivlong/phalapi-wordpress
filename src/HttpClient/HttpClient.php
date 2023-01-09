<?php
/**
 * Wordpress REST API HTTP Client.
 */

namespace PhalApi\Wordpress\HttpClient;

use PhalApi\Wordpress\Client;

/**
 * REST API HTTP Client class.
 */
class HttpClient
{
    /**
     * cURL handle.
     *
     * @var resource
     */
    protected $ch;

    /**
     * Site API URL.
     *
     * @var string
     */
    protected $url;

    /**
     * JWT api key.
     *
     * @var string
     */
    protected $apiKey;

    /**
     * JWT api secret.
     *
     * @var string
     */
    protected $apiSecret;

    /**
     * Client options.
     *
     * @var Options
     */
    protected $options;

    /**
     * Request.
     *
     * @var Request
     */
    private $request;

    /**
     * Response.
     *
     * @var Response
     */
    private $response;

    /**
     * Response headers.
     *
     * @var string
     */
    private $responseHeaders;

    /**
     * Jwt Access Token.
     *
     * @var string
     */
    private $accessToken;

    /**
     * Basic Auth.
     *
     * @var string
     */
    private $basicAuth;

    /**
     * Initialize HTTP client.
     */
    public function __construct()
    {
        $a = func_get_args(); //获取构造函数中的参数
        $i = count($a);
        if (method_exists($this, $f = '__construct'.$i)) {
            call_user_func_array([$this, $f], $a);
        }
    }

    /**
     * Initialize HTTP client.
     *
     * @param string $url       Site URL.
     * @param string $apiKey    JWT api key.
     * @param string $apiSecret JWT api Secret.
     * @param array  $options   Client options.
     */
    public function __construct5($url, $authType, $apiKey, $apiSecret, $options)
    {
        if (!\function_exists('curl_version')) {
            throw new HttpClientException('cURL is NOT installed on this server', -1, new Request(), new Response());
        }
        $this->options = new Options($options);
        $this->url = $this->buildApiUrl($url);
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $di = \PhalApi\DI();
        if(!isset($di->cache)) {
            $di->cache = new \PhalApi\Cache\FileCache(['path' => API_ROOT.'/runtime', 'prefix' => 'wp']);
        }
        $jwt = $di->cache->get($apiKey);
        if (!empty($jwt)) {
            $jwtAuth = json_decode($jwt);
            $this->accessToken = $jwtAuth->access_token;
        } else {
            $jwtAuth = $this->request('token', 'POST', ['api_key' => $apiKey, 'api_secret' => $apiSecret]);
            $di->cache->set($apiKey, json_encode($jwtAuth), $jwtAuth->exp);
            $this->accessToken = $jwtAuth->access_token;
        }
    }

    /**
     * Initialize HTTP client.
     *
     * @param string $url       Site URL.
     * @param string $authType  Auth Type.
     * @param string $authToken Auth Token.
     * @param array  $options   Client options.
     */
    public function __construct4($url, $authType, $authToken, $options)
    {
        if (!\function_exists('curl_version')) {
            throw new HttpClientException('cURL is NOT installed on this server', -1, new Request(), new Response());
        }
        $this->options = new Options($options);
        $this->url = $this->buildApiUrl($url);
        $di = \PhalApi\DI();
        if ('basic' === $authType && !empty($authToken)) {
            $this->basicAuth = $authToken;
        } elseif ('jwt' === $authType && !empty($authToken)) {
            $this->accessToken = $authToken;
        }
    }

    /**
     * Build API URL.
     *
     * @param string $url Store URL.
     *
     * @return string
     */
    protected function buildApiUrl($url)
    {
        $api = $this->options->apiPrefix();

        return \rtrim($url, '/').$api.$this->options->getVersion().'/';
    }

    /**
     * Build URL.
     *
     * @param string $url        URL.
     * @param array  $parameters Query string parameters.
     *
     * @return string
     */
    protected function buildUrlQuery($url, $parameters = [])
    {
        if (!empty($parameters)) {
            $url .= '?'.\http_build_query($parameters);
        }

        return $url;
    }

    /**
     * Setup method.
     *
     * @param string $method Request method.
     */
    protected function setupMethod($method)
    {
        if ('POST' == $method) {
            \curl_setopt($this->ch, CURLOPT_POST, true);
        } elseif (\in_array($method, ['PUT', 'DELETE', 'OPTIONS'])) {
            \curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);
        }
    }

    /**
     * Get request headers.
     *
     * @param bool $sendData If request send data or not.
     *
     * @return array
     */
    protected function getRequestHeaders($sendData = false, $formData = null)
    {
        $headers = [
            'Accept' => 'application/json',
            'User-Agent' => $this->options->userAgent().'/'.Client::VERSION,
        ];

        if ($sendData) {
            if (!empty($formData)) {
                $headers['Content-Type'] = 'multipart/form-data; boundary='.$formData['delimiter'];
                $headers['Content-Length'] = $formData['length'];
            } else {
                $headers['Content-Type'] = 'application/json;charset=utf-8';
            }
        }

        if (isset($this->accessToken) && !empty($this->accessToken)) {
            $headers['Authorization'] = 'Bearer '.$this->accessToken;
        } elseif (isset($this->basicAuth) && !empty($this->basicAuth)) {
            $headers['Authorization'] = 'Basic '.$this->basicAuth;
        }

        return $headers;
    }

    /**
     * Create request.
     *
     * @param string $endpoint   Request endpoint.
     * @param string $method     Request method.
     * @param array  $data       Request data.
     * @param array  $parameters Request parameters.
     *
     * @return Request
     */
    protected function createRequest($endpoint, $method, $data = [], $parameters = [])
    {
        $body = '';
        $url = $this->url.$endpoint;
        $hasData = !empty($data);

        // Setup method.
        $this->setupMethod($method);

        $formData = null;
        // Include post fields.
        if ($hasData) {
            if (!empty($data['file']) && !empty($data['file']['tmp_name'])) {
                $delimiter = uniqid();
                $fields = [
                    'file' => file_get_contents($data['file']['tmp_name']),
                    'filename' => $data['file']['name'],
                ];
                $streamData = '';
                $streamData .= '--'.$delimiter."\r\n"
                    .'Content-Disposition: form-data; name="file"; filename="'.$fields['filename'].'"'."\r\n"
                    .'Content-Type:application/octet-stream'."\r\n\r\n";
                $streamData .= $fields['file']."\r\n";
                $streamData .= '--'.$delimiter."--\r\n";
                $formData = [
                    'delimiter' => $delimiter,
                    'length' => strlen($streamData),
                ];
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $streamData);
            } else {
                $body = \json_encode($data);
                \curl_setopt($this->ch, CURLOPT_POSTFIELDS, $body);
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
     * Get response headers.
     *
     * @return array
     */
    protected function getResponseHeaders()
    {
        $headers = [];
        $lines = \explode("\n", $this->responseHeaders);
        $lines = \array_filter($lines, 'trim');

        foreach ($lines as $index => $line) {
            // Remove HTTP/xxx params.
            if (false === strpos($line, ': ')) {
                continue;
            }

            list($key, $value) = \explode(': ', $line);

            $headers[$key] = isset($headers[$key]) ? $headers[$key].', '.trim($value) : trim($value);
        }

        return $headers;
    }

    /**
     * Create response.
     *
     * @return Response
     */
    protected function createResponse()
    {
        // Set response headers.
        $this->responseHeaders = '';
        \curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, function ($_, $headers) {
            $this->responseHeaders .= $headers;

            return \strlen($headers);
        });

        // Get response data.
        $body = \curl_exec($this->ch);
        $code = \curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $headers = $this->getResponseHeaders();

        // Register response.
        $this->response = new Response($code, $headers, $body);

        return $this->getResponse();
    }

    /**
     * Set default cURL settings.
     */
    protected function setDefaultCurlSettings()
    {
        $verifySsl = $this->options->verifySsl();
        $timeout = $this->options->getTimeout();
        $followRedirects = $this->options->getFollowRedirects();

        \curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $verifySsl);
        if (!$verifySsl) {
            \curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, $verifySsl);
        }
        if ($followRedirects) {
            \curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        }
        \curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        \curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);
        \curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->request->getRawHeaders());
        \curl_setopt($this->ch, CURLOPT_URL, $this->request->getUrl());
    }

    /**
     * Look for errors in the request.
     *
     * @param array $parsedResponse Parsed body response.
     */
    protected function lookForErrors($parsedResponse)
    {
        // Any non-200/201/202 response code indicates an error.
        if (!\in_array($this->response->getCode(), ['200', '201', '202'])) {
            $errors = isset($parsedResponse->errors) ? $parsedResponse->errors : $parsedResponse;
            $errorMessage = '';
            $errorCode = '';

            if (is_array($errors)) {
                $errorMessage = $errors[0]->message;
                $errorCode = $errors[0]->code;
            } elseif (isset($errors->message, $errors->code)) {
                $errorMessage = $errors->message;
                $errorCode = $errors->code;
            }

            throw new HttpClientException(\sprintf('Error: %s [%s]', $errorMessage, $errorCode), $this->response->getCode(), $this->request, $this->response);
        }
    }

    /**
     * Process response.
     *
     * @return array
     */
    protected function processResponse()
    {
        $body = $this->response->getBody();

        // Look for UTF-8 BOM and remove.
        if (0 === strpos(bin2hex(substr($body, 0, 4)), 'efbbbf')) {
            $body = substr($body, 3);
        }

        $parsedResponse = \json_decode($body);

        // Test if return a valid JSON.
        if (JSON_ERROR_NONE !== json_last_error()) {
            $message = function_exists('json_last_error_msg') ? json_last_error_msg() : 'Invalid JSON returned';
            throw new HttpClientException(sprintf('JSON ERROR: %s', $message), $this->response->getCode(), $this->request, $this->response);
        }

        $this->lookForErrors($parsedResponse);

        return $parsedResponse;
    }

    /**
     * Make requests.
     *
     * @param string $endpoint   Request endpoint.
     * @param string $method     Request method.
     * @param array  $data       Request data.
     * @param array  $parameters Request parameters.
     *
     * @return array
     */
    public function request($endpoint, $method, $data = [], $parameters = [])
    {
        // Initialize cURL.
        $this->ch = \curl_init();

        // Set request args.
        $request = $this->createRequest($endpoint, $method, $data, $parameters);

        // Default cURL settings.
        $this->setDefaultCurlSettings();

        // Get response.
        $response = $this->createResponse();

        // Check for cURL errors.
        if (\curl_errno($this->ch)) {
            throw new HttpClientException('cURL Error: '.\curl_error($this->ch), 0, $request, $response);
        }

        \curl_close($this->ch);

        return $this->processResponse();
    }

    /**
     * Get request data.
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get response data.
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
