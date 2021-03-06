<?php

namespace Yamete\Driver;

class MyHentaiComics extends \Yamete\DriverAbstract
{
    private $aMatches = [];
    const DOMAIN = 'myhentaicomics.com';

    public function canHandle(): bool
    {
        return (bool)preg_match(
            '~^https?://' . strtr(self::DOMAIN, ['.' => '\.', '-' => '\-']) . '/index\.php/(?<album>[^/?]+)~',
            $this->sUrl,
            $this->aMatches
        );
    }

    /**
     * @return array|string[]
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getDownloadables(): array
    {
        $oRes = $this->getClient()->request('GET', $this->sUrl);
        $aReturn = [];
        foreach ($this->getDomParser()->load((string)$oRes->getBody())->find('.g-item a') as $oLink) {
            /**
             * @var \PHPHtmlParser\Dom\AbstractNode $oLink
             * @var \PHPHtmlParser\Dom\AbstractNode $oImg
             */
            $oImg = $this->getDomParser()
                ->load(
                    (string)$this->getClient()->request('GET', 'http://' . self::DOMAIN . $oLink->getAttribute('href'))
                        ->getBody()
                )
                ->find('.g-resize');
            $sFilename = 'http://' . self::DOMAIN
                . substr($oImg->getAttribute('src'), 0, strpos($oImg->getAttribute('src'), '?'));
            $aReturn[$this->getFolder() . DIRECTORY_SEPARATOR . basename($sFilename)] = $sFilename;
        }
        return $aReturn;
    }

    private function getFolder(): string
    {
        return implode(DIRECTORY_SEPARATOR, [self::DOMAIN, $this->aMatches['album']]);
    }
}
