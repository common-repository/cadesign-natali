<?php
namespace Cadesign\NataliApi;


use Cadesign\NataliApi\AbstractProp;

class Labels extends AbstractProp
{
    public $URL = '/label/list';
    public $TABLE = 'cad_label_reference';
    public $TYPE = 'labels';


    public function fill()
    {
        $arLabels = $this->getApi();
        $connection = \Bitrix\Main\Application::getConnection();
        foreach($arLabels as $label)
        {
            $sql = "SELECT ID FROM " . $this->TABLE . " where UF_XML_ID='" . $label["id"] . "'";
            $record = $connection->query($sql)->fetch();
            if(!$record)
            {
                $connection->add($this->TABLE,
                    [
                        "UF_XML_ID" => $label["id"],
                        "UF_NAME" => $label["title"],
                    ]
                );
            }
        }
    }

}