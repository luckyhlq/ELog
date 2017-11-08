<?php

namespace ELog\Logger;

use ELog\Handler;
use ELog\Handler\RotatingStrategy;

class RotatingFileLogger {
    private $defaultChannel = 'all';
    private $defaultHandler = null;

    private $loggers = array();
    private function __construct() {}

    static public $instance;
    static public function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init($logpath = '/tmp/', $rotatingStrategy = "daily", $maxFileCount = 7, $defaultLevel = Monolog\Logger\Logger::INFO) {
        $this->logpath = $logpath;
        $this->maxFileCount = $maxFileCount;
        $this->rotatingStrategy = $rotatingStrategy;
        $this->defaultLevel = $defaultLevel;

        $rotatingStrategyObj = $this->getRotatingStrategy($this->rotatingStrategy, $this->maxFileCount);
        $this->defaultHandler = new Handler\RotatingFileHandler($this->logpath . $this->defaultChannel, $rotatingStrategyObj, $this->defaultLevel, false, 0666);
    }

    public function doLog($level, $message, $channel, $context) {
        try {
            if (!isset($this->loggers[$channel])) {
                $this->createChannelLogger($channel, $this->logpath, $level);
            }
            $this->loggers[$channel]->addRecord($level, $message, $context);
        } catch (Exception $e) {
            return false;
        }
    }

    private function createChannelLogger($channel) {
        $logger = new \Monolog\Logger($channel);
        $rotatingStrategyObj = $this->getRotatingStrategy($this->rotatingStrategy, $this->maxFileCount);
        $handler = new Handler\RotatingFileHandler($this->logpath . $channel, $rotatingStrategyObj, $this->defaultLevel, true, 0666);
        $logger->pushHandler($this->defaultHandler);
        $logger->pushHandler($handler);
        $this->loggers[$channel] = $logger;
    }

    private function getRotatingStrategy($rotatingStrategy, $maxFileCount = 0) {
        switch ($rotatingStrategy) {
            case 'daily':
                return new RotatingStrategy\DailyRotatingStrategy($maxFileCount);
            case 'hourly':
                return new RotatingStrategy\HourlyRotatingStrategy($maxFileCount);
            default:
                throw new Exception("Unsupported rotatingStrategy:$rotatingStrategy", 1);
                break;
        }
    }
}