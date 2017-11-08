<?php

/*
 * This file is part of the ELog package.
 *
 * (c) liqiao <liqiao@urvips.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ELog\Handler;

use Monolog\Logger;
use ELog\Handler\RotatingStrategy;

/**
 * Stores logs to files that are rotated and a limited number of files are kept.
 *
 * This rotation is only intended to be used as a workaround. Using logrotate to
 * handle the rotation is strongly encouraged when you can use it.
 *
 * @author liqiao <liqiao@urvips.com>
 */
class RotatingFileHandler extends \Monolog\Handler\StreamHandler {
    protected $rotatingStrategy = null;
    protected $mustRotate = false;

    /**
     * @param string   $filename
     * @param int      $level          The minimum logging level at which this handler will be triggered
     * @param Boolean  $bubble         Whether the messages that are handled can bubble up the stack or not
     * @param int|null $filePermission Optional file permissions (default (0644) are only for owner read/write)
     * @param Boolean  $useLocking     Try to lock log file before doing any writes
     */
    public function __construct($filename, $rotatingStrategy = null, $level = Logger::DEBUG, $bubble = true, $filePermission = null, $useLocking = false) {
        $this->filename = $filename;
        if ($rotatingStrategy == null) {
            $this->rotatingStrategy = new DailyRotatingStrategy();
        } else {
            $this->rotatingStrategy = $rotatingStrategy;
        }
        $this->maxFiles = (int) $rotatingStrategy->getMaxFileCount();
        $this->filenameFormat = '{filename}-{time}';
        $this->nextRotation = $this->rotatingStrategy->getNextRotationTime();
        $this->timeFormat = $this->rotatingStrategy->getTimeFormat();

        parent::__construct($this->getTimedFilename(), $level, $bubble, $filePermission, $useLocking);
    }

    /**
     * {@inheritdoc}
     */
    public function close() {
        parent::close();

        if (true === $this->mustRotate) {
            $this->rotate();
        }
    }

    public function setRotatingStrategy($rotatingStrategy) {
        if (!$rotatingStrategy instanceof RotatingStrategyInterface) {
            trigger_error('not valid rotatingStrategy', E_USER_DEPRECATED);
        }

        $this->rotatingStrategy = $rotatingStrategy;
        $this->nextRotation = $this->rotatingStrategy->getNextRotationTime();
        $this->timeFormat = $this->rotatingStrategy->getTimeFormat();
        $this->url = $this->getTimedFilename();
        $this->close();
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record) {
        // on the first record written, if the log is new, we should rotate (once per day)
        if (null === $this->mustRotate) {
            $this->mustRotate = !file_exists($this->url);
        }

        if ($this->nextRotation < $record['datetime']) {
            $this->mustRotate = true;
            $this->close();
        }

        parent::write($record);
    }

    /**
     * Rotates the files.
     */
    protected function rotate() {
        // update filename
        $this->url = $this->getTimedFilename();
        $this->nextRotation = $this->rotatingStrategy->getNextRotationTime();

        // skip GC of old logs if files are unlimited
        if (0 === $this->maxFiles) {
            return;
        }

        $logFiles = glob($this->getGlobPattern());
        if ($this->maxFiles >= count($logFiles)) {
            // no files to remove
            return;
        }

        // Sorting the files by name to remove the older ones
        usort($logFiles, function ($a, $b) {
            return strcmp($b, $a);
        });

        foreach (array_slice($logFiles, $this->maxFiles) as $file) {
            if (is_writable($file)) {
                // suppress errors here as unlink() might fail if two processes
                // are cleaning up/rotating at the same time
                set_error_handler(function ($errno, $errstr, $errfile, $errline) {});
                unlink($file);
                restore_error_handler();
            }
        }

        $this->mustRotate = false;
    }

    protected function getTimedFilename() {
        $fileInfo = pathinfo($this->filename);
        $timedFilename = str_replace(
            array('{filename}', '{time}'),
            array($fileInfo['filename'], date($this->timeFormat)),
            $fileInfo['dirname'] . '/' . $this->filenameFormat
        );

        if (!empty($fileInfo['extension'])) {
            $timedFilename .= '.' . $fileInfo['extension'];
        }

        return $timedFilename;
    }

    protected function getGlobPattern() {
        $fileInfo = pathinfo($this->filename);
        $glob = str_replace(
            array('{filename}', '{time}'),
            array($fileInfo['filename'], '*'),
            $fileInfo['dirname'] . '/' . $this->filenameFormat
        );
        
        if (!empty($fileInfo['extension'])) {
            $glob .= '.' . $fileInfo['extension'];
        }

        return $glob;
    }
}