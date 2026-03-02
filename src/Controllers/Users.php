<?php

declare(strict_types=1);

namespace PhalApi\Wordpress\Controllers;

use PhalApi\Wordpress\Base;

/**
 * WordPress Users Controller.
 */
class Users extends Base
{
    /**
     * List users.
     *
     * @param array $args Query parameters
     * @return array|null Users list with pagination info
     */
    public function listUsers(array $args = []): array|null
    {
        return $this->request('get', 'users', $args, true);
    }

    /**
     * Retrieve a single user.
     *
     * @param int $id User ID
     * @param array $args Query parameters
     * @return array|null User data
     */
    public function retrieveUser(int $id, array $args = []): array|null
    {
        return $this->request('get', 'users/' . $id, $args);
    }

    /**
     * Create a new user.
     *
     * @param array $args User data
     * @return array|null Created user data
     */
    public function createUser(array $args = []): array|null
    {
        return $this->request('post', 'users', $args);
    }

    /**
     * Update an existing user.
     *
     * @param int $id User ID
     * @param array $args User data
     * @return array|null Updated user data
     */
    public function updateUser(int $id, array $args = []): array|null
    {
        return $this->request('post', 'users/' . $id, $args);
    }

    /**
     * Delete a user.
     *
     * @param int $id User ID
     * @param array $args Query parameters
     * @return array|null Deletion result
     */
    public function deleteUser(int $id, array $args = []): array|null
    {
        return $this->request('delete', 'users/' . $id, $args);
    }
}
