<?php

namespace Yamete\Driver;

class Palcomix extends \Yamete\DriverAbstract
{
    private $aMatches = [];
    const DOMAIN = 'palcomix.com';

    public function canHandle(): bool
    {
        return (bool)preg_match(
            '~^https?://(' . strtr(self::DOMAIN, ['.' => '\.']) . ')/(?<album>[^/?]+)/?~',
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
        $oRes = $this->getClient()
            ->request('GET', 'http://' . self::DOMAIN . '/' . $this->aMatches['album'] . '/index.html');
        $aReturn = [];
        foreach ($this->getDomParser()->load((string)$oRes->getBody())->find('.thumbnail a') as $oLink) {
            /** @var \PHPHtmlParser\Dom\AbstractNode $oLink */
            $sLink = 'http://' . self::DOMAIN . '/' . $this->aMatches['album'] . '/' . $oLink->getAttribute('href');
            $oRes = $this->getClient()->request('GET', $sLink);
            foreach ($this->getDomParser()->load((string)$oRes->getBody())->find('img') as $oImg) {
                /**
                 * @var \PHPHtmlParser\Dom\AbstractNode $oImg
                 */
                if (strpos($oImg->getAttribute('alt'), 'page') !== 0) {
                    continue;
                }
                $sFilename = 'http://' . self::DOMAIN . '/' . $this->aMatches['album']
                    . str_replace('../', '/', $oImg->getAttribute('src'));
                $aReturn[$this->getFolder() . DIRECTORY_SEPARATOR . basename($sFilename)] = $sFilename;
            }
        }
        return $aReturn;
    }

    public function getClient(array $aOptions = []): \GuzzleHttp\Client
    {
        return parent::getClient(['headers' => ['Referer' => $this->sUrl]]);
    }

    private function getFolder(): string
    {
        return implode(DIRECTORY_SEPARATOR, [self::DOMAIN, $this->aMatches['album']]);
    }
}
