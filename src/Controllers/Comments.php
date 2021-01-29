<?php

namespace PhalApi\Wordpress\Controllers;

use PhalApi\Wordpress\Base;

class Comments extends Base
{
    public function listComments($args = [])
    {
        return $this->request('get', 'comments', $args, true);
    }

    public function retrieveComment($id, $args = [])
    {
        return $this->request('get', 'comments/'.$id, $args);
    }
}
