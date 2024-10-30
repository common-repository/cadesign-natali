<?php
namespace Cadesign\NataliApi;


use Cadesign\NataliApi\AbstractProp;

class Sizes extends AbstractProp
{
    public $URL = '/sizes/list';
    public $TABLE = 'cad_size_reference';
    public $TYPE = 'sizes';

    public function fill()
    {
        $arSizes = $this->getApi();
        $connection = \Bitrix\Main\Application::getConnection();
        foreach($arSizes as $size)
        {
            $sql = "SELECT ID FROM " . $this->TABLE . " where UF_XML_ID='" . $size["sizeID"] . "'";
            $record = $connection->query($sql)->fetch();
            if(!$record)
            {
                $connection->add($this->TABLE,
                    [
                        "UF_XML_ID" => $size["sizeID"],
                        "UF_NAME" => $size["title"],
                    ]
                );
            }
        }
    }
}