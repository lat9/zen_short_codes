<?php
// -----
// Part of the "Zen Cart Shortcodes" plugin for Zen Cart v1.5.8 or later
//
// Copyright (c) 2024, Vinos de Frutas Tropicales (lat9)
//
use Zencart\Traits\NotifierManager;

class gpsfShortCodes extends gpsfBase
{
    use NotifierManager;

    // -----
    // Gives an extension the means to modify a product's description; for example, the description
    // could be appended with an additional field.
    //
    public function modifyProductsDescription(string $products_id, string $products_description, array $product): string
    {
        $this->notify('NOTIFY_GPSF_SHORTCODES_DESCRIPTION', [], $products_description);
        return $products_description;
    }
}
