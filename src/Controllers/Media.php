<?php

namespace PhalApi\Wordpress\Controllers;

use PhalApi\Wordpress\Base;

class Media extends Base
{
    public function listMedia($args = [])
    {
        return $this->request('get', 'media', $args, true);
    }

    public function retrieveMedia($id, $args = [])
    {
        return $this->request('get', 'media/'.$id, $args);
    }

    public function createMedia($args = [])
    {
        return $this->request('post', 'media', $args);
    }

    public function updateMedia($id, $args = [])
    {
        return $this->request('post', 'media/'.$id, $args);
    }

    public function deleteMedia($id, $args = [])
    {
        return $this->request('delete', 'media/'.$id, $args);
    }
}
