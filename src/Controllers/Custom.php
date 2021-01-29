<?php

namespace PhalApi\Wordpress\Controllers;

use PhalApi\Wordpress\Base;

class Custom extends Base
{
    public function getCustom($route, $args = [], $returnArray = false)
    {
        return $this->request('get', $route, $args, $returnArray);
    }

    public function postCustom($route, $args = [])
    {
        return $this->request('post', $route, $args);
    }

    public function putCustom($route, $args = [])
    {
        return $this->request('put', $route, $args);
    }

    public function deleteCustom($route, $args = [])
    {
        return $this->request('delete', $route, $args);
    }
}
