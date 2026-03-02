<?php

declare(strict_types=1);

namespace PhalApi\Wordpress\Controllers;

use PhalApi\Wordpress\Base;

/**
 * WordPress Tags Controller.
 */
class Tags extends Base
{
    /**
     * List tags.
     *
     * @param array $args Query parameters
     * @return array|null Tags list with pagination info
     */
    public function listTags(array $args = []): array|null
    {
        return $this->request('get', 'tags', $args, true);
    }

    /**
     * Retrieve a single tag.
     *
     * @param int $id Tag ID
     * @param array $args Query parameters
     * @return array|null Tag data
     */
    public function retrieveTag(int $id, array $args = []): array|null
    {
        return $this->request('get', 'tags/' . $id, $args);
    }
}
