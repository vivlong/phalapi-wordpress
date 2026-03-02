<?php

declare(strict_types=1);

namespace PhalApi\Wordpress\Controllers;

use PhalApi\Wordpress\Base;

/**
 * WordPress Comments Controller.
 */
class Comments extends Base
{
    /**
     * List comments.
     *
     * @param array $args Query parameters
     * @return array|null Comments list with pagination info
     */
    public function listComments(array $args = []): array|null
    {
        return $this->request('get', 'comments', $args, true);
    }

    /**
     * Retrieve a single comment.
     *
     * @param int $id Comment ID
     * @param array $args Query parameters
     * @return array|null Comment data
     */
    public function retrieveComment(int $id, array $args = []): array|null
    {
        return $this->request('get', 'comments/' . $id, $args);
    }
}
