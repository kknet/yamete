<?php

namespace Yamete\Driver;

use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Client;

class WieMangaCom extends \Yamete\DriverAbstract
{
    private $aMatches = [];
    const DOMAIN = 'wiemanga.com';

    public function canHandle(): bool
    {
        return (bool)preg_match(
            '~^https?://www\.(' . strtr(self::DOMAIN, ['.' => '\.']) . ')/manga/(?<album>.+)\.html~U',
            $this->sUrl,
            $this->aMatches
        );
    }

    /**
     * Where to download
     * @return string
     */
    private function getFolder(): string
    {
        return implode(DIRECTORY_SEPARATOR, [self::DOMAIN, $this->aMatches['album']]);
    }

    /**
     * @return array|string[]
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getDownloadables(): array
    {
        /**
         * @var \Traversable $oChapters
         * @var \PHPHtmlParser\Dom\AbstractNode $oLink
         * @var \PHPHtmlParser\Dom\AbstractNode $oImage
         */
        $oResult = $this->getClient()->request('GET', $this->sUrl);
        $oChapters = $this->getDomParser()->load((string)$oResult->getBody())->find('.chapterlist .col1 a');
        $aChapters = iterator_to_array($oChapters);
        krsort($aChapters);
        $aReturn = [];
        $index = 0;
        foreach ($aChapters as $oLink) {
            $oResult = $this->getClient()->request('GET', $oLink->getAttribute('href'));
            $oPages = $this->getDomParser()->load((string)$oResult->getBody())->find('.chapterselect #page option');
            $iNbPages = count($oPages) / 2;
            $iCurrentPage = 1;
            foreach ($oPages as $oPage) {
                $oResult = $this->getClient()->request('GET', $oPage->getAttribute('value'));
                $oImage = $this->getDomParser()->load((string)$oResult->getBody())->find('img#comicpic')[0];
                $sFilename = $oImage->getAttribute('src');
                $sBasename = $this->getFolder() . DIRECTORY_SEPARATOR . str_pad($index++, 5, '0', STR_PAD_LEFT)
                    . '-' . basename($sFilename);
                $aReturn[$sBasename] = $sFilename;
                if (++$iCurrentPage > $iNbPages) {
                    break;
                }
            }
        }
        return $aReturn;
    }
}
