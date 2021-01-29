<?php

namespace PhalApi\Wordpress\Controllers;

use PhalApi\Wordpress\Base;

class Posts extends Base
{
    public function listPosts($args = [])
    {
        return $this->request('get', 'posts', $args, true);
    }

    public function retrievePost($id, $args = [])
    {
        return $this->request('get', 'posts/'.$id, $args);
    }

    public function createPost($args = [])
    {
        return $this->request('post', 'posts', $args);
    }

    public function updatePost($id, $args = [])
    {
        return $this->request('post', 'posts/'.$id, $args);
    }

    public function deletePost($id, $args = [])
    {
        return $this->request('delete', 'posts/'.$id, $args);
    }
}
