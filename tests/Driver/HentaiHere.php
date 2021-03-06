<?php

namespace YameteTests\Driver;


class HentaiHere extends \PHPUnit\Framework\TestCase
{
    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDownload()
    {
        $url = 'https://hentaihere.com/m/S12135/';
        $driver = new \Yamete\Driver\HentaiHere();
        $driver->setUrl($url);
        $this->assertTrue($driver->canHandle());
        $this->assertEquals(92, count($driver->getDownloadables()));
    }
}
