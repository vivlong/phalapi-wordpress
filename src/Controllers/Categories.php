<?php

declare(strict_types=1);

namespace PhalApi\Wordpress\Controllers;

use PhalApi\Wordpress\Base;

/**
 * WordPress Categories Controller.
 */
class Categories extends Base
{
    /**
     * List categories.
     *
     * @param array $args Query parameters
     * @return array|null Categories list with pagination info
     */
    public function listCategories(array $args = []): array|null
    {
        return $this->request('get', 'categories', $args, true);
    }

    /**
     * Retrieve a single category.
     *
     * @param int $id Category ID
     * @param array $args Query parameters
     * @return array|null Category data
     */
    public function retrieveCategory(int $id, array $args = []): array|null
    {
        return $this->request('get', 'categories/' . $id, $args);
    }

    /**
     * Create a new category.
     *
     * @param array $args Category data
     * @return array|null Created category data
     */
    public function createCategory(array $args = []): array|null
    {
        return $this->request('post', 'categories', $args);
    }

    /**
     * Update an existing category.
     *
     * @param int $id Category ID
     * @param array $args Category data
     * @return array|null Updated category data
     */
    public function updateCategory(int $id, array $args = []): array|null
    {
        return $this->request('post', 'categories/' . $id, $args);
    }

    /**
     * Delete a category.
     *
     * @param int $id Category ID
     * @param array $args Query parameters
     * @return array|null Deletion result
     */
    public function deleteCategory(int $id, array $args = []): array|null
    {
        return $this->request('delete', 'categories/' . $id, $args);
    }
}
