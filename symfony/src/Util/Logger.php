<?php

namespace Chs\Messages\Util;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Monolog\Logger as MonoLogger;

class Logger {

    protected static $instance;

    public static function getLog($name = 'messages'): MonoLogger {
        if (!self::$instance instanceof MonoLogger) {
            $level = $_ENV['LOG_LEVEL'] ?? 'debug';
            $logLevel = Level::fromName($level);
            $logger = new MonoLogger($name);
            $logger->pushHandler(new RotatingFileHandler("/tmp/logs/${name}.log", 90, $logLevel));
            self::$instance = $logger;
        }
        return self::$instance;
    }
}