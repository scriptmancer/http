<?php

declare(strict_types=1);

namespace Nazim\Http\Session;

class Session
{
    /**
     * @var bool
     */
    private bool $started = false;

    /**
     * @var array
     */
    private array $options;

    /**
     * Constructor
     *
     * @param array $options Session options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'name' => 'NAZIMSESSID',
            'cookie_lifetime' => 0,
            'cookie_path' => '/',
            'cookie_domain' => '',
            'cookie_secure' => false,
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
            'use_strict_mode' => true,
            'use_cookies' => true,
            'use_only_cookies' => true,
            'cache_limiter' => 'nocache',
            'cache_expire' => 180,
        ], $options);
    }

    /**
     * Start the session
     *
     * @return bool True if the session was started successfully, false otherwise
     */
    public function start(): bool
    {
        if ($this->started) {
            return true;
        }

        if (PHP_SESSION_ACTIVE === session_status()) {
            $this->started = true;
            return true;
        }

        // Set session cookie parameters
        session_set_cookie_params([
            'lifetime' => $this->options['cookie_lifetime'],
            'path' => $this->options['cookie_path'],
            'domain' => $this->options['cookie_domain'],
            'secure' => $this->options['cookie_secure'],
            'httponly' => $this->options['cookie_httponly'],
            'samesite' => $this->options['cookie_samesite'],
        ]);

        // Set session name
        session_name($this->options['name']);

        // Set session options
        session_cache_limiter($this->options['cache_limiter']);
        session_cache_expire($this->options['cache_expire']);

        // Start the session
        $this->started = session_start();

        return $this->started;
    }

    /**
     * Get a session value
     *
     * @param string $key The key
     * @param mixed $default The default value if the key doesn't exist
     * @return mixed The value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set a session value
     *
     * @param string $key The key
     * @param mixed $value The value
     * @return self
     */
    public function set(string $key, mixed $value): self
    {
        $this->start();
        $_SESSION[$key] = $value;
        return $this;
    }

    /**
     * Check if a session key exists
     *
     * @param string $key The key
     * @return bool True if the key exists, false otherwise
     */
    public function has(string $key): bool
    {
        $this->start();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session value
     *
     * @param string $key The key
     * @return self
     */
    public function remove(string $key): self
    {
        $this->start();
        unset($_SESSION[$key]);
        return $this;
    }

    /**
     * Get all session values
     *
     * @return array All session values
     */
    public function all(): array
    {
        $this->start();
        return $_SESSION;
    }

    /**
     * Clear all session values
     *
     * @return self
     */
    public function clear(): self
    {
        $this->start();
        $_SESSION = [];
        return $this;
    }

    /**
     * Destroy the session
     *
     * @return bool True if the session was destroyed successfully, false otherwise
     */
    public function destroy(): bool
    {
        if (!$this->started) {
            return true;
        }

        // Clear session data
        $this->clear();

        // Clear the session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                [
                    'expires' => time() - 42000,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'] ?? null,
                ]
            );
        }

        // Destroy the session
        $destroyed = session_destroy();
        $this->started = false;

        return $destroyed;
    }

    /**
     * Regenerate the session ID
     *
     * @param bool $deleteOldSession Whether to delete the old session data
     * @return bool True if the session ID was regenerated successfully, false otherwise
     */
    public function regenerate(bool $deleteOldSession = true): bool
    {
        $this->start();
        return session_regenerate_id($deleteOldSession);
    }

    /**
     * Get the session ID
     *
     * @return string The session ID
     */
    public function getId(): string
    {
        $this->start();
        return session_id();
    }

    /**
     * Set the session ID
     *
     * @param string $id The session ID
     * @return self
     */
    public function setId(string $id): self
    {
        if (!$this->started) {
            session_id($id);
        }
        return $this;
    }

    /**
     * Get the session name
     *
     * @return string The session name
     */
    public function getName(): string
    {
        return session_name();
    }

    /**
     * Set flash data that will be available only for the next request
     *
     * @param string $key The key
     * @param mixed $value The value
     * @return self
     */
    public function flash(string $key, mixed $value): self
    {
        $this->start();
        
        // Get current flash data
        $flash = $this->get('_flash', []);
        
        // Add new flash data
        $flash[$key] = $value;
        
        // Store flash data
        $this->set('_flash', $flash);
        
        return $this;
    }

    /**
     * Get flash data
     *
     * @param string $key The key
     * @param mixed $default The default value if the key doesn't exist
     * @return mixed The value
     */
    public function getFlash(string $key, mixed $default = null): mixed
    {
        $this->start();
        
        // Get current flash data
        $flash = $this->get('_flash', []);
        
        // Get flash value
        $value = $flash[$key] ?? $default;
        
        // Remove flash value
        unset($flash[$key]);
        
        // Update flash data
        $this->set('_flash', $flash);
        
        return $value;
    }

    /**
     * Check if flash data exists
     *
     * @param string $key The key
     * @return bool True if the key exists, false otherwise
     */
    public function hasFlash(string $key): bool
    {
        $this->start();
        
        // Get current flash data
        $flash = $this->get('_flash', []);
        
        return isset($flash[$key]);
    }
} 