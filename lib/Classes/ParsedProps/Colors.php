<?php
namespace Cadesign\NataliApi;


use Cadesign\NataliApi\AbstractProp;

class Colors extends AbstractProp
{
    public $URL = '/colors/list';
    public $TABLE = 'cad_color_reference';
    public $TYPE = 'colors';

    public function fill()
    {

            $arColors = $this->getApi();
            $connection = \Bitrix\Main\Application::getConnection();
            foreach($arColors as $color)
            {
                $sql = "SELECT ID FROM " . $this->TABLE . " where UF_XML_ID='" . $color["colorID"] . "'";
                $record = $connection->query($sql)->fetch();
                if(!$record)
                {
                    $connection->add($this->TABLE,
                        [
                            "UF_XML_ID" => $color["colorID"],
                            "UF_NAME" => $color["title"],
                        ]
                    );
                }
            }

    }

}