<?php

declare(strict_types=1);

namespace PhalApi\Wordpress\Controllers;

use PhalApi\Wordpress\Base;

/**
 * WordPress Custom Endpoints Controller.
 */
class Custom extends Base
{
    /**
     * Make a GET request to a custom endpoint.
     *
     * @param string $route Custom route
     * @param array $args Query parameters
     * @param bool $returnArray Return as array with pagination info
     * @return array|null Response data
     */
    public function getCustom(string $route, array $args = [], bool $returnArray = false): array|null
    {
        return $this->request('get', $route, $args, $returnArray);
    }

    /**
     * Make a POST request to a custom endpoint.
     *
     * @param string $route Custom route
     * @param array $args Request data
     * @return array|null Response data
     */
    public function postCustom(string $route, array $args = []): array|null
    {
        return $this->request('post', $route, $args);
    }

    /**
     * Make a PUT request to a custom endpoint.
     *
     * @param string $route Custom route
     * @param array $args Request data
     * @return array|null Response data
     */
    public function putCustom(string $route, array $args = []): array|null
    {
        return $this->request('put', $route, $args);
    }

    /**
     * Make a DELETE request to a custom endpoint.
     *
     * @param string $route Custom route
     * @param array $args Request data
     * @return array|null Response data
     */
    public function deleteCustom(string $route, array $args = []): array|null
    {
        return $this->request('delete', $route, $args);
    }
}
