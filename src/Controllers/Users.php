<?php

namespace PhalApi\Wordpress\Controllers;

use PhalApi\Wordpress\Base;

class Pages extends Base
{
    public function listUsers($args = [])
    {
        return $this->request('get', 'users', $args, true);
    }

    public function retrieveUser($id, $args = [])
    {
        return $this->request('get', 'users/'.$id, $args);
    }
}
