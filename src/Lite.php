<?php

namespace PhalApi\Wordpress;

/**
 * Wordpress操作类.
 */
class Lite
{
    protected $config;

    protected $instance;

    public function __construct($config = null)
    {
        $di = \PhalApi\DI();
        $this->config = $config;
        if (null == $this->config) {
            $this->config = $di->config->get('app.Wordpress');
        }

        $wordpress = new Client(
            $this->config['url'],
            $this->config['api_key'],
            $this->config['api_secret'],
            $this->config['options']
        );
        $this->instance = $wordpress;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * Posts.
     */
    public function listPosts($args = [])
    {
        return $this->instance->get('posts', $args);
    }

    public function retrievePost($id, $args = [])
    {
        return $this->instance->get('posts/'.$id, $args);
    }

    public function createPost($args = [])
    {
        return $this->instance->post('posts', $args);
    }

    public function updatePost($id, $args = [])
    {
        return $this->instance->post('posts/'.$id, $args);
    }

    public function deletePost($id, $args = [])
    {
        return $this->instance->delete('posts/'.$id, $args);
    }

    /**
     * Pages.
     */
    public function listPages($args = [])
    {
        return $this->instance->get('pages', $args);
    }

    public function retrievePage($id, $args = [])
    {
        return $this->instance->get('pages/'.$id, $args);
    }

    /**
     * Categories.
     */
    public function listCategories($args = [])
    {
        return $this->instance->get('categories', $args);
    }

    public function retrieveCategory($id, $args = [])
    {
        return $this->instance->get('categories/'.$id, $args);
    }

    public function createCategory($args = [])
    {
        return $this->instance->post('categories', $args);
    }

    public function updateCategory($id, $args = [])
    {
        return $this->instance->post('categories/'.$id, $args);
    }

    public function deleteCategory($id, $args = [])
    {
        return $this->instance->delete('categories/'.$id, $args);
    }

    /**
     * Tags.
     */
    public function listTags($args = [])
    {
        return $this->instance->get('tags', $args);
    }

    public function retrieveTag($id, $args = [])
    {
        return $this->instance->get('tags/'.$id, $args);
    }

    /**
     * Taxonomies.
     */
    public function listTaxonomies($args = [])
    {
        return $this->instance->get('taxonomies', $args);
    }

    public function retrieveTaxonomy($taxonomy, $args = [])
    {
        return $this->instance->get('taxonomies/'.$taxonomy, $args);
    }

    /**
     * Comments.
     */
    public function listComments($args = [])
    {
        return $this->instance->get('comments', $args);
    }

    public function retrieveComment($id, $args = [])
    {
        return $this->instance->get('comments/'.$id, $args);
    }

    /**
     * Media.
     */
    public function listMedia($args = [])
    {
        return $this->instance->get('media', $args);
    }

    public function retrieveMedia($id, $args = [])
    {
        return $this->instance->get('media/'.$id, $args);
    }

    public function createMedia($args = [])
    {
        return $this->instance->post('media', $args);
    }

    public function updateMedia($id, $args = [])
    {
        return $this->instance->post('media/'.$id, $args);
    }

    public function deleteMedia($id, $args = [])
    {
        return $this->instance->delete('media/'.$id, $args);
    }

    /**
     * Search Results.
     */
    public function listSearchResults($args = [])
    {
        return $this->instance->get('search', $args);
    }

    /**
     * Users.
     */
    public function listUsers($args = [])
    {
        return $this->instance->get('users', $args);
    }

    public function retrieveUser($id, $args = [])
    {
        return $this->instance->get('users/'.$id, $args);
    }

    /**
     * Custom.
     */
    public function getCustom($route, $args = [])
    {
        return $this->instance->get($route, $args);
    }
}
