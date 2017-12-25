<?php

namespace Yamete\Driver;

class VercomicsPorno extends \Yamete\DriverAbstract
{
    private $aMatches = [];
    const DOMAIN = 'vercomicsporno.com';

    public function canHandle()
    {
        return (bool)preg_match(
            '~^https?://' . strtr(self::DOMAIN, ['.' => '\.', '-' => '\-']) . '/(?<album>.+)$~',
            $this->sUrl,
            $this->aMatches
        );
    }

    public function getDownloadables()
    {
        $oRes = $this->getClient()->request('GET', $this->sUrl);
        $aReturn = [];
        $i = 0;
        $bSkipFirst = true;
        foreach ($this->getDomParser()->load((string)$oRes->getBody())->find('#posts .col-md-12 p img') as $oImg) {
            /**
             * @var \DOMElement $oImg
             */
            $sFilename = $oImg->getAttribute('data-lazy-src');
            if ($bSkipFirst || empty($sFilename)) {
                $bSkipFirst = false;
                continue;
            }
            $sBasename = $this->getFolder() . DIRECTORY_SEPARATOR . str_pad(++$i, 5, '0', STR_PAD_LEFT)
                . '-' . basename($sFilename);
            $aReturn[$sBasename] = $sFilename;
        }
        array_pop($aReturn);
        return $aReturn;
    }

    private function getFolder()
    {
        return implode(DIRECTORY_SEPARATOR, [self::DOMAIN, $this->aMatches['album']]);
    }
}
