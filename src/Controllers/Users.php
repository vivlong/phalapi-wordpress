<?php

namespace PhalApi\Wordpress\Controllers;

use PhalApi\Wordpress\Base;

class Users extends Base
{
    public function listUsers($args = [])
    {
        return $this->request('get', 'users', $args, true);
    }

    public function retrieveUser($id, $args = [])
    {
        return $this->request('get', 'users/'.$id, $args);
    }

    public function createUser($args = [])
    {
        return $this->request('post', 'users', $args);
    }

    public function updateUser($id, $args = [])
    {
        return $this->request('post', 'users/'.$id, $args);
    }

    public function deleteUser($id, $args = [])
    {
        return $this->request('delete', 'users/'.$id, $args);
    }
}
