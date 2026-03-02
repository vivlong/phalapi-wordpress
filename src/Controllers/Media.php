<?php

declare(strict_types=1);

namespace PhalApi\Wordpress\Controllers;

use PhalApi\Wordpress\Base;

/**
 * WordPress Media Controller.
 */
class Media extends Base
{
    /**
     * List media items.
     *
     * @param array $args Query parameters
     * @return array|null Media list with pagination info
     */
    public function listMedia(array $args = []): array|null
    {
        return $this->request('get', 'media', $args, true);
    }

    /**
     * Retrieve a single media item.
     *
     * @param int $id Media ID
     * @param array $args Query parameters
     * @return array|null Media data
     */
    public function retrieveMedia(int $id, array $args = []): array|null
    {
        return $this->request('get', 'media/' . $id, $args);
    }

    /**
     * Create a new media item.
     *
     * @param array $args Media data (including file)
     * @return array|null Created media data
     */
    public function createMedia(array $args = []): array|null
    {
        return $this->request('post', 'media', $args);
    }

    /**
     * Update an existing media item.
     *
     * @param int $id Media ID
     * @param array $args Media data
     * @return array|null Updated media data
     */
    public function updateMedia(int $id, array $args = []): array|null
    {
        return $this->request('post', 'media/' . $id, $args);
    }

    /**
     * Delete a media item.
     *
     * @param int $id Media ID
     * @param array $args Query parameters
     * @return array|null Deletion result
     */
    public function deleteMedia(int $id, array $args = []): array|null
    {
        return $this->request('delete', 'media/' . $id, $args);
    }
}
