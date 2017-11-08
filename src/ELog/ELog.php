<?php

/*
 * This file is part of the ELog package.
 *
 * (c) liqiao <liqiao@urvips.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ELog;

use ELog\Handler;
use ELog\Logger;

/**
 * easy log facade
 *
 * usage:
 * ELog::initLogger();  // do it once
 * ELog::debug("debug message");
 * ELog::info("info message");
 * ELog::error("error message");
 * 
 * @author liqiao <liqiao@urvips.com>
 */
class ELog {
    private static $logger;
    private static $logid;

    /**
     * do init, call it before using any other method
     * @param  string     $logpath          [log path]
     * @param  string     $rotatingStrategy ["daily", "hourly"]
     * @param  integer    $maxFileCount     [number of files to keep]
     * @param  [type]     $defaultLevel     
     * @return [type]                       
     * @author liqiao@hecom.cn
     * @date   2017-11-06
     */
    public static function initLogger($logpath = '/tmp/', $rotatingStrategy = "daily", $maxFileCount = 7, $defaultLevel = \Monolog\Logger::INFO) {
        if (isset($_SERVER['HTTP_LOGID']) && !empty($_SERVER['HTTP_LOGID'])) {
            self::$logid = $_SERVER['HTTP_LOGID'];
        } else {
            self::$logid = uniqid();
        }

        self::$logger = Logger\RotatingFileLogger::getInstance();
        self::$logger->init($logpath, $rotatingStrategy, $maxFileCount, $defaultLevel);
        return true;
    }

    public static function getLogId() {
        return self::$logid;
    }

    public static function alert($message, $channel = 'all', array $context = []) {
        self::log(\Monolog\Logger::ALERT, $message, $channel, $context);
    }

    public static function critical($message, $channel = 'all', array $context = []) {
        self::log(\Monolog\Logger::CRITICAL, $message, $channel, $context);
    }

    public static function error($message, $channel = 'all', array $context = []) {
        self::log(\Monolog\Logger::ERROR, $message, $channel, $context);
    }

    public static function warning($message, $channel = 'all', array $context = []) {
        self::log(\Monolog\Logger::WARNING, $message, $channel, $context);
    }

    public static function notice($message, $channel = 'all', array $context = []) {
        self::log(\Monolog\Logger::NOTICE, $message, $channel, $context);
    }

    public static function info($message, $channel = 'all', array $context = []) {
        self::log(\Monolog\Logger::INFO, $message, $channel, $context);
    }

    public static function debug($message, $channel = 'all', array $context = []) {
        self::log(\Monolog\Logger::DEBUG, $message, $channel, $context);
    }

    private static function log($level, $message, $channel = 'all', array $context = []) {
        if (self::$logger == null) {
            throw new Exception("please init logger first", 1);
        }

        $context = array_merge(array('logid' => self::$logid), $context);
        self::$logger->doLog($level, $message, $channel, $context);
    }
}