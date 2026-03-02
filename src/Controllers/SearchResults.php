<?php

declare(strict_types=1);

namespace PhalApi\Wordpress\Controllers;

use PhalApi\Wordpress\Base;

/**
 * WordPress Search Results Controller.
 */
class SearchResults extends Base
{
    /**
     * List search results.
     *
     * @param array $args Query parameters
     * @return array|null Search results
     */
    public function listSearchResults(array $args = []): array|null
    {
        return $this->request('get', 'search', $args);
    }
}
