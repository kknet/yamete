<?php

namespace Yamete\Driver;

class MyMangaComicsCom extends \Yamete\DriverAbstract
{
    private $aMatches = [];
    const DOMAIN = 'mymangacomics.com';

    public function canHandle(): bool
    {
        return (bool)preg_match(
            '~^https?://(' . strtr(self::DOMAIN, ['.' => '\.']) . ')/gallery/(thumbnails|show)/(?<album>[^/]+)~',
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
        $sUrl = 'https://' . self::DOMAIN . '/gallery/thumbnails/' . $this->aMatches['album'];
        $oRes = $this->getClient()->request('GET', $sUrl, ['cleanupInput' => false]);
        $aMatches = [];
        if (!preg_match_all('~<img src="([^"]+)" alt="">~', (string)$oRes->getBody(), $aMatches)) {
            return [];
        }
        $index = 0;
        $aReturn = [];
        foreach ($aMatches[1] as $sUrl) {
            $sFilename = str_replace('/thumbnail/', '/original/', $sUrl);
            $iPos = strpos($sFilename, '?');
            $sFilename = str_replace(' ', '%20', substr($sFilename, 0, $iPos));
            $sBasename = $this->getFolder() . DIRECTORY_SEPARATOR . str_pad($index++, 5, '0', STR_PAD_LEFT)
                . '-' . basename($sFilename);
            $aReturn[$sBasename] = $sFilename;
        }
        return array_slice($aReturn, 2);
    }

    private function getFolder(): string
    {
        return implode(DIRECTORY_SEPARATOR, [self::DOMAIN, $this->aMatches['album']]);
    }
}
