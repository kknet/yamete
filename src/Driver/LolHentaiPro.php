<?php

namespace Yamete\Driver;

class LolHentaiPro extends XXXComicPornCom
{
    const DOMAIN = 'lolhentai.pro';

    protected function getDomain(): string
    {
        return self::DOMAIN;
    }

    protected function getSelector(): string
    {
        return '.grid-portfolio figure a';
    }
}
