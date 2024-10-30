<?php

namespace Cadesign\NataliApi;

use Cadesign\NataliApi\Api;
use Cadesign\NataliApi\ProductItem;
use Cadesign\NataliApi\Main;

class ProductList
{
    /**
     * @deprecated
     * @param int $offset
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    public function products($offset = 0)
    {
        $arProducts = $this->getProducts($offset);

        foreach($arProducts as $product)
        {
            $oProd = new ProductItem();
            $oProd->createProduct($product);
            $_SESSION["CAD_IMPORT"]["UPDATED"]++;
        }

        if(empty($arProducts))
            $_SESSION["CAD_IMPORT"]["STOP"] = true;
    }

    /**
     * @param int $offset
     * @return array
     */
    function getProducts($offset = 0)
    {
        $products = $this->getProductList($offset);
        $arProducts = [];
        foreach($products as $product)
        {
            $item = new ProductItem;
            $arProducts[] = $item->getProductInfo($product["productId"]);
        }
        return $arProducts;
    }

    /**
     * Получить список продуктов
     * @param int $offset
     * @return mixed
     */
    function getProductList($offset = 0, $category = false)
    {
        $urlPath = '/product/list';
        if($offset)
        {
            $urlPath .= '?offset=' . $offset;
        }
        if($category)
        {
            $urlPath .= '?categoryId=' . $category . '&pageLimit=10000';
        }
        $arProductsObj = new Api($urlPath);
        $arProducts = $arProductsObj->fetchArray();

        return $arProducts['data']['products'];
    }
}

?>