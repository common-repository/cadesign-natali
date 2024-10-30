<?php
/**
 * Класс для обработки свойств, значения которых мы получаем по api и заносим в hl инфоблок
 */
namespace Cadesign\NataliApi;

use Cadesign\NataliApi\Api;


abstract class AbstractProp
{
    public $bindings = false;




    // for example brand

    /**
     * @return array
     */
    public function getApi()
    {
        $urlPath = $this->URL;
        $obLabels = new Api($urlPath);
        $data = $obLabels->fetchArray();
        return (array)$data['data'][$this->TYPE];
    }

    /**
     * Заполнение свойства значениями
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    public function fill()
    {
        $arItems = $this->getApi();
        $connection = \Bitrix\Main\Application::getConnection();
        foreach($arItems as $item)
        {
            $sql = "SELECT ID FROM " . $this->TABLE . " where UF_XML_ID='" . $item["id"] . "'";
            $record = $connection->query($sql)->fetch();
            if(!$record)
            {
                $connection->add($this->TABLE,
                    [
                        "UF_XML_ID" => $item["id"],
//                        "UF_IMAGE" => $item["image"],
                        "UF_NAME" => $item["title"],
//                        "UF_SHORTTITLE" => $item["titleShort"],
                    ]
                );
            }
        }

    }

    /**
     * TODO:Удалить метод
     * deprecated
     * Возращает массив  связи Внешнего Id с Id на нашем сайте
     * @return array
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    protected function getBinding()
    {
        if(!$this->bindings)
        {

            $connection = \Bitrix\Main\Application::getConnection();
            $sql = "SELECT * FROM " . $this->TABLE;
            $query = $connection->query($sql);

            $arBindings = [];
            while($record = $query->fetch())
            {
                $arBindings[$record['UF_XML_ID']] = $record['ID'];
            }
            $this->bindings = $arBindings;


        }
        return $this->bindings;

    }

    /**
     * TODO:Удалить метод
     * @deprecated
     * Возращает ID соответствующий внешнему Id для Лейблов
     * @param $externalId
     * @return int
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    public function convertBinding($externalId)
    {
        $arBinding = $this->getBinding();
        return (int)$arBinding[$externalId];
    }


}