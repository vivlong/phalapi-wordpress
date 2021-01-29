<?php

namespace PhalApi\Wordpress\Controllers;

use PhalApi\Wordpress\Base;

class Custom extends Base
{
    public function get($route, $args = [], $returnArray = false)
    {
        return $this->request('get', $route, $args, $returnArray);
    }

    public function post($route, $args = [])
    {
        return $this->request('post', $route, $args);
    }

    public function put($route, $args = [])
    {
        return $this->request('put', $route, $args);
    }

    public function delete($route, $args = [])
    {
        return $this->request('delete', $route, $args);
    }
}
