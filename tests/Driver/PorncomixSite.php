<?php

namespace YameteTests\Driver;


class PorncomixSite extends \PHPUnit\Framework\TestCase
{
    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDownload()
    {
        $url = 'http://porncomix.site/linart-unexpected-surprise-porncomics/';
        $driver = new \Yamete\Driver\PorncomixSite();
        $driver->setUrl($url);
        $this->assertTrue($driver->canHandle());
        $this->assertEquals(4, count($driver->getDownloadables()));
    }
}
