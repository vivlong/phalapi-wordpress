<?php

namespace PhalApi\Wordpress\Controllers;

use PhalApi\Wordpress\Base;

class Tags extends Base
{
    public function listTags($args = [])
    {
        return $this->request('get', 'tags', $args, true);
    }

    public function retrieveTag($id, $args = [])
    {
        return $this->request('get', 'tags/'.$id, $args);
    }
}
