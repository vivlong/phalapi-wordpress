<?php

namespace PhalApi\Wordpress\Controllers;

use PhalApi\Wordpress\Base;

class Categories extends Base
{
    public function listCategories($args = [])
    {
        return $this->request('get', 'categories', $args, true);
    }

    public function retrieveCategory($id, $args = [])
    {
        return $this->request('get', 'categories/'.$id, $args);
    }

    public function createCategory($args = [])
    {
        return $this->request('post', 'categories', $args);
    }

    public function updateCategory($id, $args = [])
    {
        return $this->request('post', 'categories/'.$id, $args);
    }

    public function deleteCategory($id, $args = [])
    {
        return $this->request('delete', 'categories/'.$id, $args);
    }
}
