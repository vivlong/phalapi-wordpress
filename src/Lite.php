<?php

declare(strict_types=1);

namespace PhalApi\Wordpress;

use Exception;
use Throwable;

/**
 * Wordpress操作类.
 */
class Lite
{
    public function __construct(
        protected array|null $config = null,
        protected Client|null $instance = null,
        protected array $controllers = []
    ) {
        $di = \PhalApi\DI();
        $this->config = $config ?? $di->config->get('app.Wordpress');

        try {
            $this->instance = $this->createClient();
        } catch (Throwable $e) {
            $di->logger->error(__CLASS__ . DIRECTORY_SEPARATOR . __FUNCTION__, ['Exception' => $e->getMessage()]);
            $this->instance = null;
        }

        $this->initializeControllers();
    }

    /**
     * Create WordPress client instance.
     */
    private function createClient(): Client
    {
        $authType = strtolower($this->config['auth']);
        $basicAuth = null;
        $jwtToken = null;
        $jwtKeyPairs = null;

        if ($authType === 'basic' && !empty($this->config['basic_user'], $this->config['basic_pwd'])) {
            $basicAuth = base64_encode($this->config['basic_user'] . ':' . $this->config['basic_pwd']);
        } elseif ($authType === 'jwt' && !empty($this->config['jwt_token'])) {
            $jwtToken = $this->config['jwt_token'];
        } else {
            $jwtKeyPairs = [
                'apiKey' => $this->config['api_key'],
                'apiSecret' => $this->config['api_secret'],
            ];
        }

        return new Client(
            $this->config['url'],
            $authType,
            $this->config['options'] ?? [],
            $basicAuth,
            $jwtToken,
            $jwtKeyPairs
        );
    }

    /**
     * Initialize all controllers.
     */
    private function initializeControllers(): void
    {
        foreach ($this->getControllers() as $namespace => $controllerName) {
            $controllerClass = __NAMESPACE__ . '\\Controllers\\' . $controllerName;
            $this->controllers[$namespace] = new $controllerClass($this->instance);
        }
    }

    public function getConfig(): array|null
    {
        return $this->config;
    }

    public function getInstance(): Client|null
    {
        return $this->instance;
    }

    public function __call(string $method, array $arguments): mixed
    {
        if (method_exists($this, $method)) {
            return $this->$method(...$arguments);
        }

        if ($this->instance !== null) {
            foreach ($this->controllers as $controller) {
                if (method_exists($controller, $method)) {
                    return $controller->$method(...$arguments);
                }
            }
        }

        return null;
    }

    /**
     * Get available controllers.
     */
    protected function getControllers(): array
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
            'custom' => 'Custom',
        ];
    }
}
