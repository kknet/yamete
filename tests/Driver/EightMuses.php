<?php

namespace YameteTests\Driver;


class EightMuses extends \PHPUnit\Framework\TestCase
{
    public function testDownload()
    {
        $url = 'https://www.8muses.com/comix/album/JAB-Comics/A-Model-Life/Issue-1';
        $driver = new \Yamete\Driver\EightMuses();
        $driver->setUrl($url);
        $this->assertNotFalse($driver->canHandle());
        $this->assertEquals(30, count($driver->getDownloadables()));
    }
}
