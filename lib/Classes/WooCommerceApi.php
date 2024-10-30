<?php

namespace Cadesign\Natali;

class WooCommerceApi extends \Automattic\WooCommerce\Client
{
    private $woocommerce = false;

    public function __construct()
    {
        $keysBindings = Helper::keysBindings();

        $this->woocommerce = new parent(
            get_option('siteurl'),
            $keysBindings["user_key"],
            $keysBindings["secret_key"],
            [
                'wp_api' => true,
                'version' => 'wc/v3',
                'query_string_auth' => true // Force Basic Authentication as query string true and using under HTTPS
            ]
        );
    }

    public function getClient()
    {
        return $this->woocommerce;
    }
}