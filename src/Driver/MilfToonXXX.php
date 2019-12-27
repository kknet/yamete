<?php

namespace Yamete\Driver;

class MilfToonXXX extends \Yamete\DriverAbstract
{
    private $aMatches = [];
    const DOMAIN = 'milftoon.xxx';

    public function canHandle(): bool
    {
        return (bool)preg_match(
            '~^https?://(' . strtr(self::DOMAIN, ['.' => '\.']) . ')/comics/(?<album>[^/]+)~',
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
        /**
         * @var \Traversable $oChapters
         * @var \PHPHtmlParser\Dom\AbstractNode $oChapter
         * @var \PHPHtmlParser\Dom\AbstractNode $oImg
         */
        $sUrl = 'https://' . self::DOMAIN . '/comics/' . $this->aMatches['album'] . '/';
        $oRes = $this->getClient()->request('GET', $sUrl);
        $oChapters = $this->getDomParser()->load((string)$oRes->getBody())->find('li.wp-manga-chapter > a');
        $aChapters = iterator_to_array($oChapters);
        krsort($aChapters);
        $index = 0;
        $aReturn = [];
        foreach ($aChapters as $oChapter) {
            $oRes = $this->getClient()->request('GET', $oChapter->getAttribute('href'));
            foreach ($this->getDomParser()->load((string)$oRes->getBody())->find('.wp-manga-chapter-img') as $oImg) {
                $sFilename = trim($oImg->getAttribute('src'));
                $sBasename = $this->getFolder() . DIRECTORY_SEPARATOR . str_pad($index++, 5, '0', STR_PAD_LEFT)
                    . '-' . basename($sFilename);
                $aReturn[$sBasename] = $sFilename;
            }
        }
        return $aReturn;
    }

    private function getFolder(): string
    {
        return implode(DIRECTORY_SEPARATOR, [self::DOMAIN, $this->aMatches['album']]);
    }
}
