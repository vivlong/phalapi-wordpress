<?php

namespace PhalApi\Wordpress\Controllers;

use PhalApi\Wordpress\Base;

class Pages extends Base
{
    public function listPages($args = [])
    {
        return $this->request('get', 'pages', $args, true);
    }

    public function retrievePage($id, $args = [])
    {
        return $this->request('get', 'pages/'.$id, $args);
    }
}
