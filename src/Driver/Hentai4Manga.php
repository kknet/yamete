<?php

namespace Yamete\Driver;

if (!class_exists(Hentai4Manga::class)) {
    class Hentai4Manga extends \Yamete\DriverAbstract
    {
        private $aMatches = [];
        const DOMAIN = 'hentai4manga.com';

        protected function getDomain(): string
        {
            return self::DOMAIN;
        }

        public function canHandle(): bool
        {
            return (bool)preg_match(
                '~^https?://(?<domain>(www\.)?' . strtr($this->getDomain(), ['.' => '\.']) .
                ')/(?<category>[^/]+)/(?<album>[^/]+)/~',
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
            $sMainUrl = "http://" . implode('/', [
                    (isset($this->aMatches['domain']) ? $this->aMatches['domain'] : $this->getDomain()),
                    $this->aMatches['category'],
                    $this->aMatches['album'],
                ]);
            $oRes = $this->getClient()->request('GET', $sMainUrl);
            $aReturn = [];
            $iNbPage = count(
                $this->getDomParser()->load((string)$oRes->getBody(), ['cleanupInput' => false])->find('#page div')
            ) - 2;
            $this->parse($this->sUrl, $aReturn);
            if ($iNbPage > 1) {
                for ($page = 2; $page <= $iNbPage; $page++) {
                    $sUrl = substr($this->sUrl, 0, strlen($sMainUrl));
                    $this->parse("${sUrl}_p${page}/", $aReturn);
                }
            }
            return $aReturn;
        }

        private function parse(string $sUrl, array &$aReturn): void
        {
            $sSelector = '#thumblist a';
            foreach ($this->getDomParser()->loadFromUrl($sUrl, ['cleanupInput' => false])->find($sSelector) as $oLink) {
                /**
                 * @var \PHPHtmlParser\Dom\AbstractNode $oLink
                 * @var \PHPHtmlParser\Dom\AbstractNode $oImg
                 */
                $sCurrentImg = 'http://' . $this->aMatches['domain'] . $oLink->getAttribute('href');
                $oImg = $this->getDomParser()->loadFromUrl($sCurrentImg, ['cleanupInput' => false])
                    ->find('#innerContent div a img, #view_main div a img')[0];
                $sFilename = 'http://' . $this->aMatches['domain'] . $oImg->getAttribute('src');
                $sBasename = $this->getFolder() . DIRECTORY_SEPARATOR .
                    str_pad(count($aReturn) + 1, 5, '0', STR_PAD_LEFT) . '-' . basename($sFilename);
                $aReturn[$sBasename] = $sFilename;
            }
        }

        private function getFolder(): string
        {
            return implode(DIRECTORY_SEPARATOR, [$this->getDomain(), $this->aMatches['album']]);
        }
    }
}
