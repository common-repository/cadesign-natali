<?php

namespace Cadesign\NataliApi;

use Cadesign\Natali\Helper;

class Api
{
    protected const BASE_URL = 'https://natali37.ru/api';
    protected $data;

    public function __construct($urlPath)
    {
        $url = self::BASE_URL . $urlPath;

        $response = wp_remote_get( $url );
        $dataEncoded = wp_remote_retrieve_body( $response );

//        $dataEncoded = file_get_contents($url);
        $this->data = json_decode($dataEncoded, true);
        if (empty($this->data))
        {
            Helper::Log('Получены пустые данные: ' . $urlPath);
        }
    }

    public function fetchArray()
    {
        return (array)$this->data;
    }

}
