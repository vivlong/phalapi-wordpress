<?php

declare(strict_types=1);

/**
 * Wordpress REST API HTTP Client Options.
 */

namespace PhalApi\Wordpress\HttpClient;

/**
 * REST API HTTP Client Options class.
 */
class Options
{
    /**
     * Default Wordpress REST API version.
     */
    public const VERSION = 'wp/v2';

    /**
     * Default request timeout.
     */
    public const TIMEOUT = 15;

    /**
     * Default WP API prefix.
     * Including leading and trailing slashes.
     */
    public const WP_API_PREFIX = '/wp-json/';

    /**
     * Default User Agent.
     * No version number.
     */
    public const USER_AGENT = 'Wordpress API Client-PHP';

    /**
     * Initialize HTTP client options.
     *
     * @param array $options Client options.
     */
    public function __construct(
        private array $options = []
    ) {
    }

    /**
     * Get API version.
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->options['version'] ?? self::VERSION;
    }

    /**
     * Check if need to verify SSL.
     *
     * @return bool
     */
    public function verifySsl(): bool
    {
        return (bool) ($this->options['verify_ssl'] ?? true);
    }

    /**
     * Get timeout.
     *
     * @return int
     */
    public function getTimeout(): int
    {
        return (int) ($this->options['timeout'] ?? self::TIMEOUT);
    }

    /**
     * Check if is WP REST API.
     *
     * @return bool
     */
    public function isWPAPI(): bool
    {
        return (bool) ($this->options['wp_api'] ?? true);
    }

    /**
     * Custom API Prefix for WP API.
     *
     * @return string
     */
    public function apiPrefix(): string
    {
        return $this->options['wp_api_prefix'] ?? self::WP_API_PREFIX;
    }

    /**
     * Custom user agent.
     *
     * @return string
     */
    public function userAgent(): string
    {
        return $this->options['user_agent'] ?? self::USER_AGENT;
    }

    /**
     * Get follow redirects.
     *
     * @return bool
     */
    public function getFollowRedirects(): bool
    {
        return (bool) ($this->options['follow_redirects'] ?? false);
    }
}
