<?php

namespace PhalApi\Wordpress;

use PhalApi\Wordpress\HttpClient\HttpClientException;

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
        try {
            $wordpress = new Client(
                $this->config['url'],
                $this->config['api_key'],
                $this->config['api_secret'],
                $this->config['options']
            );
            $this->instance = $wordpress;
        } catch (Exception $e) {
            $di->logger->error(__NAMESPACE__.DIRECTORY_SEPARATOR.__CLASS__.DIRECTORY_SEPARATOR.__FUNCTION__, ['Exception' => $e->getMessage()]);
        }
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getInstance()
    {
        return $this->instance;
    }

    private function request($method = 'get', $route = '/', $parameters = [])
    {
        $di = \PhalApi\DI();
        $wp = $this->instance;
        if (!empty($wp)) {
            try {
                $results = null;
                switch ($method) {
                    case 'post':
                        $results = $wp->post($route, $parameters);
                        break;
                    case 'delete':
                        $results = $wp->delete($route, $parameters);
                        break;
                    default:
                        $results = $wp->get($route, $parameters);
                }

                return $results;
            } catch (HttpClientException $e) {
                $lastRequest = $wp->http->getRequest();
                $lastResponse = $wp->http->getResponse();
                $rs = json_decode($lastResponse->getBody());
                if ($rs && 400 == $rs->data->status) {
                    return $rs;
                } else {
                    $di->logger->error(__NAMESPACE__.DIRECTORY_SEPARATOR.__CLASS__.DIRECTORY_SEPARATOR.__FUNCTION__.' # '.$method.' # '.$route, ['request' => $lastRequest->getBody()]);
                    $di->logger->error(__NAMESPACE__.DIRECTORY_SEPARATOR.__CLASS__.DIRECTORY_SEPARATOR.__FUNCTION__.' # '.$method.' # '.$route, ['response' => $lastResponse->getBody()]);
                }
                $di->logger->error(__NAMESPACE__.DIRECTORY_SEPARATOR.__CLASS__.DIRECTORY_SEPARATOR.__FUNCTION__.' # '.$method.' # '.$route, ['HttpClientException' => $e->getMessage()]);

                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * Posts.
     */
    public function listPosts($args = [])
    {
        return $this->request('get', 'posts', $args);
    }

    public function retrievePost($id, $args = [])
    {
        return $this->request('get', 'posts/'.$id, $args);
    }

    public function createPost($args = [])
    {
        return $this->request('post', 'posts', $args);
    }

    public function updatePost($id, $args = [])
    {
        return $this->request('post', 'posts/'.$id, $args);
    }

    public function deletePost($id, $args = [])
    {
        return $this->request('delete', 'posts/'.$id, $args);
    }

    /**
     * Pages.
     */
    public function listPages($args = [])
    {
        return $this->request('get', 'pages', $args);
    }

    public function retrievePage($id, $args = [])
    {
        return $this->request('get', 'pages/'.$id, $args);
    }

    /**
     * Categories.
     */
    public function listCategories($args = [])
    {
        return $this->request('get', 'categories', $args);
    }

    public function retrieveCategory($id, $args = [])
    {
        return $this->request('get', 'categories/'.$id, $args);
    }

    public function createCategory($args = [])
    {
        return $this->request('post', 'categories', $args);
    }

    public function updateCategory($id, $args = [])
    {
        return $this->request('post', 'categories/'.$id, $args);
    }

    public function deleteCategory($id, $args = [])
    {
        return $this->request('delete', 'categories/'.$id, $args);
    }

    /**
     * Tags.
     */
    public function listTags($args = [])
    {
        return $this->request('get', 'tags', $args);
    }

    public function retrieveTag($id, $args = [])
    {
        return $this->request('get', 'tags/'.$id, $args);
    }

    /**
     * Taxonomies.
     */
    public function listTaxonomies($args = [])
    {
        return $this->request('get', 'taxonomies', $args);
    }

    public function retrieveTaxonomy($taxonomy, $args = [])
    {
        return $this->request('get', 'taxonomies/'.$taxonomy, $args);
    }

    /**
     * Comments.
     */
    public function listComments($args = [])
    {
        return $this->request('get', 'comments', $args);
    }

    public function retrieveComment($id, $args = [])
    {
        return $this->request('get', 'comments/'.$id, $args);
    }

    /**
     * Media.
     */
    public function listMedia($args = [])
    {
        return $this->request('get', 'media', $args);
    }

    public function retrieveMedia($id, $args = [])
    {
        return $this->request('get', 'media/'.$id, $args);
    }

    public function createMedia($args = [])
    {
        return $this->request('post', 'media', $args);
    }

    public function updateMedia($id, $args = [])
    {
        return $this->request('post', 'media/'.$id, $args);
    }

    public function deleteMedia($id, $args = [])
    {
        return $this->request('delete', 'media/'.$id, $args);
    }

    /**
     * Search Results.
     */
    public function listSearchResults($args = [])
    {
        return $this->request('get', 'search', $args);
    }

    /**
     * Users.
     */
    public function listUsers($args = [])
    {
        return $this->request('get', 'users', $args);
    }

    public function retrieveUser($id, $args = [])
    {
        return $this->request('get', 'users/'.$id, $args);
    }

    /**
     * Custom.
     */
    public function get($route, $args = [])
    {
        return $this->request('get', $route, $args);
    }

    public function post($route, $args = [])
    {
        return $this->request('post', $route, $args);
    }
}
