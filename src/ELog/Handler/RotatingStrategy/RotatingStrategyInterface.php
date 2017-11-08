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
 * log rotating strategy interface
 *
 * This rotation is only intended to be used as a workaround. Using logrotate to
 * handle the rotation is strongly encouraged when you can use it.
 *
 * @author liqiao <liqiao@urvips.com>
 */
Interface RotatingStrategyInterface {
    public function getMaxFileCount();
    public function getNextRotationTime();
    public function getTimeFormat();
}