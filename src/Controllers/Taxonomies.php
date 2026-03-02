<?php

declare(strict_types=1);

namespace PhalApi\Wordpress\Controllers;

use PhalApi\Wordpress\Base;

/**
 * WordPress Taxonomies Controller.
 */
class Taxonomies extends Base
{
    /**
     * List taxonomies.
     *
     * @param array $args Query parameters
     * @return array|null Taxonomies list with pagination info
     */
    public function listTaxonomies(array $args = []): array|null
    {
        return $this->request('get', 'taxonomies', $args, true);
    }

    /**
     * Retrieve a single taxonomy.
     *
     * @param string $taxonomy Taxonomy slug
     * @param array $args Query parameters
     * @return array|null Taxonomy data
     */
    public function retrieveTaxonomy(string $taxonomy, array $args = []): array|null
    {
        return $this->request('get', 'taxonomies/' . $taxonomy, $args);
    }
}
