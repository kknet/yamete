<?php

namespace YameteTests\Driver;


class PorncomixOnline extends \PHPUnit\Framework\TestCase
{
    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDownload()
    {
        $url = 'https://www.porncomixonline.net/felsalamy-friends-mom/';
        $driver = new \Yamete\Driver\PorncomixOnline();
        $driver->setUrl($url);
        $this->assertTrue($driver->canHandle());
        $this->assertEquals(22, count($driver->getDownloadables()));
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testDownloadNew()
    {
        $url = 'https://www.porncomixonline.net/the-earth-chapter-1/';
        $driver = new \Yamete\Driver\PorncomixOnline();
        $driver->setUrl($url);
        $this->assertTrue($driver->canHandle());
        $this->assertEquals(31, count($driver->getDownloadables()));
    }
}
