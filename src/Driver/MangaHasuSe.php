<?php

namespace Yamete\Driver;

use GuzzleCloudflare\Middleware;
use GuzzleHttp\Cookie\FileCookieJar;

class MangaHasuSe extends \Yamete\DriverAbstract
{
    private $aMatches = [];
    const DOMAIN = 'mangahasu.se';

    public function canHandle(): bool
    {
        return (bool)preg_match(
            '~^http://(' . strtr(self::DOMAIN, ['.' => '\.']) . ')/(?<album>[^/?]+.html)$~',
            $this->sUrl,
            $this->aMatches
        );
    }

    /**
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getDownloadables(): array
    {
        /**
         * @var \iterator $oChapters
         * @var \PHPHtmlParser\Dom\AbstractNode[] $aChapters
         */
        $oRes = $this->getClient()->request('GET', $this->sUrl);
        $oChapters = $this->getDomParser()->load((string)$oRes->getBody())->find('.list-chapter a');
        $aChapters = iterator_to_array($oChapters);
        krsort($aChapters);
        $aReturn = [];
        foreach ($aChapters as $aChapter) {
            $oRes = $this->getClient()->request('GET', $aChapter->getAttribute('href'));
            foreach ($this->getDomParser()->load((string)$oRes->getBody())->find('#img img') as $oImg) {
                /**
                 * @var \PHPHtmlParser\Dom\AbstractNode $oImg
                 */
                $sFilename = $oImg->getAttribute('src');
                $aReturn[$this->getFolder() . DIRECTORY_SEPARATOR . basename($sFilename)] = $sFilename;
            }
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
