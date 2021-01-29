<?php

namespace PhalApi\Wordpress\Controllers;

use PhalApi\Wordpress\Base;

class Taxonomies extends Base
{
    public function listTaxonomies($args = [])
    {
        return $this->request('get', 'taxonomies', $args, true);
    }

    public function retrieveTaxonomy($taxonomy, $args = [])
    {
        return $this->request('get', 'taxonomies/'.$taxonomy, $args);
    }
}
