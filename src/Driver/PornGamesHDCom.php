<?php

namespace Yamete\Driver;

class PornGamesHDCom extends \Yamete\DriverAbstract
{
    private $aMatches = [];
    const DOMAIN = 'porngameshd.com';

    public function canHandle(): bool
    {
        return (bool)preg_match(
            '~^https?://(' . strtr(self::DOMAIN, ['.' => '\.']) . ')/doujin/(?<album>[^/]+)~',
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
         * @var \Traversable $oPages
         * @var \PHPHtmlParser\Dom\AbstractNode $oLink
         * @var \PHPHtmlParser\Dom\AbstractNode $oImg
         */
        $oRes = $this->getClient()->request('GET', $this->sUrl);
        $oPages = $this->getDomParser()->load((string)$oRes->getBody())->find('.a-thumb img.img-responsive');
        $index = 0;
        $aReturn = [];
        foreach ($oPages as $oLink) {
            $sFilename = $oLink->getAttribute('data-original');
            $sBasename = $this->getFolder() . DIRECTORY_SEPARATOR . str_pad($index++, 5, '0', STR_PAD_LEFT)
                . '-' . basename($sFilename);
            $aReturn[$sBasename] = $sFilename;
        }
        return array_merge(array_slice($aReturn, -1, 1), array_slice($aReturn, 0, $index - 1));
    }

    private function getFolder(): string
    {
        return implode(DIRECTORY_SEPARATOR, [self::DOMAIN, $this->aMatches['album']]);
    }
}
