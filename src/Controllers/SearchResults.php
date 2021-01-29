<?php

namespace PhalApi\Wordpress\Controllers;

use PhalApi\Wordpress\Base;

class SearchResults extends Base
{
    public function listSearchResults($args = [])
    {
        return $this->request('get', 'search', $args);
    }
}
