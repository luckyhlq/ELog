<?php

/*
 * This file is part of the ELog package.
 *
 * (c) liqiao <liqiao@urvips.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ELog\Handler\RotatingStrategy;

/**
 * log rotate per hour 
 *
 * This rotation is only intended to be used as a workaround. Using logrotate to
 * handle the rotation is strongly encouraged when you can use it.
 *
 * @author liqiao <liqiao@urvips.com>
 */
class HourlyRotatingStrategy extends AbstractRotatingStrategy {

    public function __construct($maxFileCount = 0) {
        parent::__construct($maxFileCount);
    }

    public function getNextRotationTime() {
        $timeStr = date("Y-m-d H:00:00", time() + 3600);
        return new \DateTime($timeStr);
    }

    public function getTimeFormat() {
        return "Y-m-d-H";
    }
}