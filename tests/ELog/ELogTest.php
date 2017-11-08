<?php

namespace ELog;

class ELogTest extends \PHPUnit\Framework\TestCase {

    /**
     * @covers ELog\ELog::testInitLogger
     */
    public function testInitLogger() {
        $this->assertEquals(true, ELog::initLogger('/tmp/', 'hourly'));
    }

    public function testDebug() {
        ELog::initLogger('/tmp/', 'hourly');
        $this->assertSame('string', ELog::DEBUG('debug'));
    }
}