<?php

namespace Config;

use CodeIgniter\Cache\Handlers\DummyHandler;
use CodeIgniter\Cache\Handlers\FileHandler;
use CodeIgniter\Cache\Handlers\MemcachedHandler;
use CodeIgniter\Cache\Handlers\PredisHandler;
use CodeIgniter\Cache\Handlers\RedisHandler;
use CodeIgniter\Cache\Handlers\WincacheHandler;
use CodeIgniter\Config\BaseConfig;

class Cache extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Primary Handler
     * --------------------------------------------------------------------------
     *
     * The name of the preferred handler that should be used. If for some reason
     * it is not available, the $backupHandler will be used in its place.
     */
    public string $handler = 'redis';

    /**
     * --------------------------------------------------------------------------
     * Backup Handler
     * --------------------------------------------------------------------------
     *
     * The name of the handler that will be used in case the first one is
     * unreachable. Often, 'file' is used here since the filesystem is
     * always available, though that's not always practical for the app.
     */
    public string $backupHandler = 'file';

    /**
     * --------------------------------------------------------------------------
     * Cache Directory Path
     * --------------------------------------------------------------------------
     *
     * The path to where cache files should be stored, if using a file-based
     * system.
     *
     * @deprecated Use the driver-specific variant under $file
     */
    public string $storePath = WRITEPATH . 'cache/';

    /**
     * --------------------------------------------------------------------------
     * Cache Include Query String
     * --------------------------------------------------------------------------
     *
     * Whether to take the URL query string into consideration when generating
     * output cache files. Valid options are:
     *
     *    false      = Disabled
     *    true       = Enabled, take all query parameters into account.
     *                 Please be aware that this may result in numerous cache
     *                 files generated for the same page over and over again.
     *    array('q') = Enabled, but only take into account the specified list
     *                 of query parameters.
     *
     * @var bool|string[]
     */
    public $cacheQueryString = false;

    /**
     * --------------------------------------------------------------------------
     * Key Prefix
     * --------------------------------------------------------------------------
     *
     * This string is added to all cache item names to help avoid collisions
     * if you run multiple applications with the same cache engine.
     */
    public string $prefix = '';

    /**
     * --------------------------------------------------------------------------
     * Default TTL
     * --------------------------------------------------------------------------
     *
     * The default number of seconds to save items when none is specified.
     *
     * WARNING: This is not used by framework handlers where 60 seconds is
     * hard-coded, but may be useful to projects and modules. This will replace
     * the hard-coded value in a future release.
     */
    public int $ttl = 60;

    /**
     * --------------------------------------------------------------------------
     * Reserved Characters
     * --------------------------------------------------------------------------
     *
     * A string of reserved characters that will not be allowed in keys or tags.
     * Strings that violate this restriction will cause handlers to throw.
     * Default: {}()/\@:
     * Note: The default set is required for PSR-6 compliance.
     */
    public string $reservedCharacters = '{}()/\@:';

    /**
     * --------------------------------------------------------------------------
     * File settings
     * --------------------------------------------------------------------------
     * Your file storage preferences can be specified below, if you are using
     * the File driver.
     *
     * @var array<string, int|string|null>
     */
    public array $file = [
        'storePath' => WRITEPATH . 'cache/',
        'mode'      => 0640,
    ];

    /**
     * -------------------------------------------------------------------------
     * Memcached settings
     * -------------------------------------------------------------------------
     * Your Memcached servers can be specified below, if you are using
     * the Memcached drivers.
     *
     * @see https://codeigniter.com/user_guide/libraries/caching.html#memcached
     *
     * @var array<string, bool|int|string>
     */
    public array $memcached = [
        'host'   => '127.0.0.1',
        'port'   => 11211,
        'weight' => 1,
        'raw'    => false,
    ];

    /**
     * -------------------------------------------------------------------------
     * Redis settings
     * -------------------------------------------------------------------------
     * Your Redis server can be specified below, if you are using
     * the Redis or Predis drivers.
     *
     * @var array<string, int|string|null>
     */
    public array $redis = [
        'host'     => '127.0.0.1',   // Will be automatically set by cPanel
        'password' => null,          // Set by cPanel if needed
        'port'     => 6379,          // Default port
        'timeout'  => 0,
        'database' => 0,
        'prefix'   => 'ybb_app_'     // Add a prefix to avoid conflicts with other apps
    ];

    /**
     * --------------------------------------------------------------------------
     * Cache Fallback Settings
     * --------------------------------------------------------------------------
     * 
     * Enable automatic fallback to direct API calls when caching fails
     * This ensures the application continues to work even if Redis is unavailable
     */
    public bool $enableFallback = true;

    /**
     * Log cache fallback events for monitoring
     */
    public bool $logFallbacks = true;

    /**
     * --------------------------------------------------------------------------
     * Available Cache Handlers
     * --------------------------------------------------------------------------
     *
     * This is an array of cache engine alias' and class names. Only engines
     * that are listed here are allowed to be used.
     *
     * @var array<string, string>
     */
    public array $validHandlers = [
        'dummy'     => DummyHandler::class,
        'file'      => FileHandler::class,
        'memcached' => MemcachedHandler::class,
        'predis'    => PredisHandler::class,
        'redis'     => RedisHandler::class,
        'wincache'  => WincacheHandler::class,
    ];

    public function __construct()
    {
        parent::__construct();

        // Load cache settings from environment variables
        $this->handler = env('cache.handler', $this->handler);
        $this->backupHandler = env('cache.backupHandler', $this->backupHandler);
        $this->prefix = env('cache.prefix', $this->prefix);
        $this->ttl = (int) env('cache.ttl', $this->ttl);

        // Load Redis configuration from environment
        $this->redis['host'] = env('cache.redis.host', $this->redis['host']);
        $this->redis['port'] = (int) env('cache.redis.port', $this->redis['port']);
        $this->redis['password'] = env('cache.redis.password', $this->redis['password']);
        $this->redis['database'] = (int) env('cache.redis.database', $this->redis['database']);
        $this->redis['prefix'] = env('cache.redis.prefix', $this->redis['prefix']);
        $this->redis['timeout'] = (int) env('cache.redis.timeout', $this->redis['timeout']);

        // Load Memcached configuration from environment
        $this->memcached['host'] = env('cache.memcached.host', $this->memcached['host']);
        $this->memcached['port'] = (int) env('cache.memcached.port', $this->memcached['port']);
    }
}
