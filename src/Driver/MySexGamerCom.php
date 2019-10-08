<?php

namespace Yamete\Driver;

class MySexGamerCom extends \Yamete\DriverAbstract
{
    private $aMatches = [];
    const DOMAIN = 'mysexgamer.com';

    public function canHandle(): bool
    {
        return (bool)preg_match(
            '~^https?://' . strtr(self::DOMAIN, ['.' => '\.', '-' => '\-']) . '/doujin/(?<album>[^/]+)~',
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
        $oRes = $this->getClient()->request('GET', 'https://' . self::DOMAIN . "/doujin/{$this->aMatches['album']}");
        $aReturn = [];
        $index = 0;
        foreach ($this->getDomParser()->load((string)$oRes->getBody())->find('img.img-responsive') as $oImg) {
            /** @var \PHPHtmlParser\Dom\AbstractNode $oImg */
            $sFilename = $oImg->getAttribute('data-original');
            if (!$sFilename) {
                continue;
            }
            $sBasename = $this->getFolder() . DIRECTORY_SEPARATOR . str_pad(++$index, 5, '0', STR_PAD_LEFT)
                . '-' . basename($sFilename);
            $aReturn[$sBasename] = $sFilename;
        }
        return $aReturn;
    }

    private function getFolder(): string
    {
        return implode(DIRECTORY_SEPARATOR, [self::DOMAIN, $this->aMatches['album']]);
    }
}