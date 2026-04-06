<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Logger extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Error Logging Threshold
     * --------------------------------------------------------------------------
     *
     * You can enable error logging by setting a threshold over zero. The
     * threshold determines what gets logged. Any values below or equal to the
     * threshold will be logged.
     *
     * Threshold options are:
     *
     * - 0 = Disables logging, Error logging TURNED OFF
     * - 1 = Emergency Messages - System is unusable
     * - 2 = Alert Messages - Action Must Be Taken Immediately
     * - 3 = Critical Messages - Application component unavailable, unexpected exception.
     * - 4 = Runtime Errors - Don't need immediate action, but should be monitored.
     * - 5 = Warnings - Exceptional occurrences that are not errors.
     * - 6 = Notices - Normal but significant events.
     * - 7 = Info - Interesting events, like user logging in, etc.
     * - 8 = Debug - Detailed debug information.
     * - 9 = All Messages
     *
     * You can also pass an array with threshold levels to show individual error types
     *
     *     array(1, 2, 3, 8) = Emergency, Alert, Critical, and Debug messages
     *
     * For a live site you'll usually enable Critical or higher (3) to be logged otherwise
     * your log files will fill up very fast.
     *
     * @var array|int
     */
    public $threshold = 3; // Critical and above only (overridden per environment via .env)

    /**
     * --------------------------------------------------------------------------
     * Date Format for Logs
     * --------------------------------------------------------------------------
     *
     * Each item that is logged has an associated date. You can use PHP date
     * codes to set your own date formatting
     */
    public string $dateFormat = 'Y-m-d H:i:s';

    /**
     * --------------------------------------------------------------------------
     * Log Handlers
     * --------------------------------------------------------------------------
     *
     * The logging system supports multiple actions to be taken when something
     * is logged. This is done by allowing for multiple Handlers, special classes
     * designed to write the log to their chosen destinations, whether that is
     * a file on the getServer, a cloud-based service, or even taking actions such
     * as emailing the dev team.
     *
     * Each handler is defined by the class name used for that handler, and it
     * MUST implement the `CodeIgniter\Log\Handlers\HandlerInterface` interface.
     *
     * The value of each key is an array of configuration items that are sent
     * to the constructor of each handler. The only required configuration item
     * is the 'handles' element, which must be an array of integer log levels.
     * This is most easily handled by using the constants defined in the
     * `Psr\Log\LogLevel` class.
     *
     * Handlers are executed in the order defined in this array, starting with
     * the handler on top and continuing down.
     */
    public array $handlers = [

        /*
         * Writes to PHP's error_log(), which Apache routes to /dev/stderr.
         * Docker captures stderr and manages rotation — no log files on disk.
         */
        'CodeIgniter\Log\Handlers\ErrorlogHandler' => [
            'handles'     => ['critical', 'alert', 'emergency', 'error', 'warning', 'notice', 'info', 'debug'],
            'messageType' => 0,
        ],
    ];
}
