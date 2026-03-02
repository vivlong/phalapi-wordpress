<?php

declare(strict_types=1);

namespace PhalApi\Wordpress\Controllers;

use PhalApi\Wordpress\Base;

/**
 * WordPress Posts Controller.
 */
class Posts extends Base
{
    /**
     * List posts.
     *
     * @param array $args Query parameters
     * @return array|null Posts list with pagination info
     */
    public function listPosts(array $args = []): array|null
    {
        return $this->request('get', 'posts', $args, true);
    }

    /**
     * Retrieve a single post.
     *
     * @param int $id Post ID
     * @param array $args Query parameters
     * @return array|null Post data
     */
    public function retrievePost(int $id, array $args = []): array|null
    {
        return $this->request('get', 'posts/' . $id, $args);
    }

    /**
     * Create a new post.
     *
     * @param array $args Post data
     * @return array|null Created post data
     */
    public function createPost(array $args = []): array|null
    {
        return $this->request('post', 'posts', $args);
    }

    /**
     * Update an existing post.
     *
     * @param int $id Post ID
     * @param array $args Post data
     * @return array|null Updated post data
     */
    public function updatePost(int $id, array $args = []): array|null
    {
        return $this->request('post', 'posts/' . $id, $args);
    }

    /**
     * Delete a post.
     *
     * @param int $id Post ID
     * @param array $args Query parameters
     * @return array|null Deletion result
     */
    public function deletePost(int $id, array $args = []): array|null
    {
        return $this->request('delete', 'posts/' . $id, $args);
    }
}
