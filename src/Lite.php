<?php

namespace PhalApi\Wordpress;

/**
 * Wordpress操作类.
 */
class Lite
{
    protected $config;

    protected $instance;

    public function __construct($config = null)
    {
        $di = \PhalApi\DI();
        $this->config = $config;
        if (null == $this->config) {
            $this->config = $di->config->get('app.Wordpress');
        }

        $wordpress = new Client(
            $this->config['url'],
            $this->config['api_key'],
            $this->config['api_secret'],
            $this->config['options']
        );
        $this->instance = $wordpress;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getInstance()
    {
        return $this->instance;
    }

    public function listPosts($args = [])
    {
        return $this->instance->get('posts', $args);
    }

    public function listPages($args = [])
    {
        return $this->instance->get('pages', $args);
    }

    public function retrievePage($id, $args = [])
    {
        return $this->instance->get('pages/'.$id, $args);
    }
}
