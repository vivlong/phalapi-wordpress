<?php

declare(strict_types=1);

namespace PhalApi\Wordpress\Controllers;

use PhalApi\Wordpress\Base;

/**
 * WordPress Pages Controller.
 */
class Pages extends Base
{
    /**
     * List pages.
     *
     * @param array $args Query parameters
     * @return array|null Pages list with pagination info
     */
    public function listPages(array $args = []): array|null
    {
        return $this->request('get', 'pages', $args, true);
    }

    /**
     * Retrieve a single page.
     *
     * @param int $id Page ID
     * @param array $args Query parameters
     * @return array|null Page data
     */
    public function retrievePage(int $id, array $args = []): array|null
    {
        return $this->request('get', 'pages/' . $id, $args);
    }
}
