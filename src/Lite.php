<?php

namespace PhalApi\Wordpress;

use PhalApi\Wordpress\HttpClient\HttpClientException;

/**
 * Wordpress操作类.
 */
class Lite
{
    protected $config;
    protected $instance;
    protected $controllers = [];

    public function __construct($config = null)
    {
        $di = \PhalApi\DI();
        $this->config = $config;
        if (null == $this->config) {
            $this->config = $di->config->get('app.Wordpress');
        }
        try {
            $authType = strtolower($this->config['auth']);
            $basicAuth = null;
            $jwtToken = null;
            $jwtKeyPairs = null;
            if ('basic' === $authType && !empty($this->config['basic_user']) && !empty($this->config['basic_pwd'])) {
                $basicAuth = base64_encode($this->config['basic_user'].':'.$this->config['basic_pwd']);
            } elseif ('jwt' === $authType && !empty($this->config['jwt_token'])) {
                $jwtToken = $this->config['jwt_token'];
            } else {
                $jwtKeyPairs = [
                    'apiKey' => $this->config['api_key'],
                    'apiSecret' => $this->config['api_secret'],
                ];
            }
            $wordpress = new Client(
                $this->config['url'],
                $authType,
                $this->config['options'],
                $basicAuth,
                $jwtToken,
                $jwtKeyPairs
            );
            $this->instance = $wordpress;
        } catch (Exception $e) {
            $di->logger->error(__CLASS__.DIRECTORY_SEPARATOR.__FUNCTION__, ['Exception' => $e->getMessage()]);
        }
        foreach ($this->get_controllers() as $namespace => $controller_name) {
            $controller_class = __NAMESPACE__.'\\Controllers\\'.$controller_name;
            $this->controllers[$namespace] = new $controller_class($this->instance);
        }
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getInstance()
    {
        return $this->instance;
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this, $method)) {
            return call_user_func_array([&$this, $method], $arguments);
        } elseif (!empty($this->instance) && $this->instance) {
            foreach ($this->controllers as $controller) {
                if (method_exists($controller, $method)) {
                    return call_user_func_array([&$controller, $method], $arguments);
                }
            }
        }
    }

    protected function get_controllers()
    {
        return [
            'categories' => 'Categories',
            'comments' => 'Comments',
            'media' => 'Media',
            'pages' => 'Pages',
            'posts' => 'Posts',
            'tags' => 'Tags',
            'taxonomies' => 'Taxonomies',
            'users' => 'Users',
        ];
    }
}
