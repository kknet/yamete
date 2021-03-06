<?php

namespace Yamete\Driver;

use GuzzleCloudflare\Middleware;
use GuzzleHttp\Cookie\FileCookieJar;

class Pururin extends \Yamete\DriverAbstract
{
    private $aMatches = [];
    const DOMAIN = 'pururin.io';

    public function canHandle(): bool
    {
        return (
            (bool)preg_match(
                '~^https?://(' . strtr(self::DOMAIN, ['.' => '\.']) . ')/gallery/(?<albumId>[^/]+)/(?<album>.+)~',
                $this->sUrl,
                $this->aMatches
            ) ||
            (bool)preg_match(
                '~^https?://(' . strtr(self::DOMAIN, ['.' => '\.']) . ')/read/(?<albumId>[^/]+)/[0-9]+/(?<album>.+)~',
                $this->sUrl,
                $this->aMatches
            )
        );
    }

    /**
     * @return array|string[]
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getDownloadables(): array
    {
        $sUrl = 'http://' . self::DOMAIN . '/read/' . $this->aMatches['albumId'] . '/01/' . $this->aMatches['album'];
        $oRes = $this->getClient()->request('GET', $sUrl);
        $aMatches = [];
        if (!preg_match('~<gallery\-read :gallery="([^"]+)"~', (string)$oRes->getBody(), $aMatches)) {
            return [];
        }
        $aAssets = \GuzzleHttp\json_decode(html_entity_decode($aMatches[1]), true);
        $aReturn = [];
        for ($index = 1; $index <= $aAssets['total_pages']; $index++) {
            $sFilename = "https://cdn.pururin.io/assets/images/data/${$this->aMatches['albumId']}"
                . "/$index.${aAssets['image_extension']}";
            $sBasename = $this->getFolder() . DIRECTORY_SEPARATOR . str_pad($index, 5, '0', STR_PAD_LEFT)
                . '-' . basename($sFilename);
            $aReturn[$sBasename] = $sFilename;
        }
        return $aReturn;
    }

    private function getFolder(): string
    {
        return implode(DIRECTORY_SEPARATOR, [self::DOMAIN, $this->aMatches['album']]);
    }

    /**
     * @param array $aOptions
     * @return \GuzzleHttp\Client
     */
    public function getClient(array $aOptions = []): \GuzzleHttp\Client
    {
        $oClient = parent::getClient(
            [
                'cookies' => new FileCookieJar(tempnam('/tmp', __CLASS__)),
                'headers' => ['User-Agent' => self::USER_AGENT],
            ]
        );
        /**
         * @var \GuzzleHttp\HandlerStack $oHandler
         */
        $oHandler = $oClient->getConfig('handler');
        $oHandler->remove('cloudflare');
        $oHandler->push(Middleware::create(), 'cloudflare');
        return $oClient;
    }
}
