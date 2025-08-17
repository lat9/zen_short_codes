<?php
// -----
// Part of the "Zen Cart Shortcodes" plugin for Zen Cart v1.5.8 or later
//
// Copyright (c) 2024-2025, Vinos de Frutas Tropicales (lat9)
//
class zenprod extends ZenShortcode
{
    public function __construct()
    {
        $this->register($this);
    }

    public function get(array $parameters): string
    {
        extract($parameters, EXTR_PREFIX_INVALID, 'sc_');

        $products_id = (int)($id ?? 0);
        if (zen_products_id_valid($products_id) === false) {
            return "<!-- zenprod, invalid products_id ($products_id) -->";
        }

        $product_link = zen_href_link(zen_get_info_page($products_id), "products_id=$products_id");

        $href_only = $href_only ?? 'false';
        if ($href_only !== 'true') {
            $text = $text ?? zen_get_products_name($products_id);
            if (($use_image ?? 'false') === 'true') {
                $width = $image_width ?? SMALL_IMAGE_WIDTH;
                $height = $image_height ?? SMALL_IMAGE_HEIGHT;
                $text = zen_get_products_image($products_id, $width, $height);
                if ($text === '') {
                    $text = "<!-- zenprod, product image is empty for id $products_id -->";
                }
            }
            $product_link = '<a href="' . $product_link . '">' . $text . '</a>';
        }
        return $product_link;
    }
}
