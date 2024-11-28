<?php
// -----
// Part of the "Zen Cart Shortcodes" plugin for Zen Cart v1.5.8 or later
//
// Copyright (c) 2024, Vinos de Frutas Tropicales (lat9)
//
// Last updated: v1.1.0
//
class zcObserverZenShortcodes extends base
{
    protected $zcsc;

    public function __construct()
    {
        // -----
        // Attach to the various notifications associated with this plugin's processing.
        //
        $attach_array = [
            //- From /includes/modules/pages/index/main_template_vars.php
            'NOTIFY_HEADER_INDEX_MAIN_TEMPLATE_VARS_PAGE_BODY',

            //- From "News Box Manager v3" /includes/modules/pages/article/header_php.php
            'NOTIFY_HEADER_ARTICLE_END',

            //- From "Google Product Search Feed II" extension provided by this plugin
            'NOTIFY_GPSF_SHORTCODES_DESCRIPTION',
        ];
        if (class_exists('Product')) {
            $attach_array[] = 'NOTIFY_GET_PRODUCT_OBJECT_DETAILS'; //- From /includes/classes/Product.php, zc210+
        } else {
            $attach_array[] = 'NOTIFY_GET_PRODUCT_DETAILS'; //- From /includes/functions/functions_products.php, previously
        }
        $this->attach($this, $attach_array);
    }

    protected function notify_header_index_main_template_vars_page_body(&$class, $eventID, $not_used, &$tpl_page_body, &$current_categories_name, &$current_categories_description)
    {
        if (empty($current_categories_description)) {
            return;
        }
        $this->loadShortCodeController();
        if ($this->zcsc->getShortCodeHandlerCount() === 0) {
            return;
        }
        $current_categories_description = $this->zcsc->convertShortCodes($current_categories_description);
    }

    public function notify_get_product_object_details(&$class, $eventID, $products_id, &$data)
    {
        if (!isset($data['lang'])) {
            return;
        }
        $this->loadShortCodeController();
        if ($this->zcsc->getShortCodeHandlerCount() === 0) {
            return;
        }

        // -----
        // Temporarily detach from this notification to prevent potential recursion.
        //
        $this->detach($this, 'NOTIFY_GET_PRODUCT_OBJECT_DETAILS');

        foreach ($data['lang'] as $lang_code => &$lang_info) {
            $lang_info['products_description'] = $this->zcsc->convertShortCodes($lang_info['products_description']);
        }

        // -----
        // Re-attach to the notification ... to support product listing pages.
        //
        $this->attach($this, 'NOTIFY_GET_PRODUCT_OBJECT_DETAILS');
    }

    public function notify_get_product_details(&$class, $eventID, $products_id, &$data)
    {
        if (empty($data->fields['products_description'])) {
            return;
        }
        $this->loadShortCodeController();
        if ($this->zcsc->getShortCodeHandlerCount() === 0) {
            return;
        }

        // -----
        // Temporarily detach from this notification to prevent potential recursion.
        //
        $this->detach($this, 'NOTIFY_GET_PRODUCT_DETAILS');

        $data->fields['products_description'] = $this->zcsc->convertShortCodes($data->fields['products_description']);

        // -----
        // Re-attach to the notification ... to support product listing pages.
        //
        $this->attach($this, 'NOTIFY_GET_PRODUCT_DETAILS');
    }

    protected function notify_header_article_end(&$class, $eventID)
    {
        global $news_content;

        if (empty($news_content)) {
            return;
        }
        $this->loadShortCodeController();
        if ($this->zcsc->getShortCodeHandlerCount() === 0) {
            return;
        }
        $news_content = $this->zcsc->convertShortCodes($news_content);
    }

    public function notify_gpsf_shortcodes_description(&$class, $eventID, $unused, &$products_description)
    {
        if (empty($products_description)) {
            return;
        }
        $this->loadShortCodeController();
        if ($this->zcsc->getShortCodeHandlerCount() === 0) {
            return;
        }
        $products_description = $this->zcsc->convertShortCodes($products_description);
    }

    private function loadShortCodeController()
    {
        if (isset($this->zcsc)) {
            return;
        }
        $classes_dir = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
        require $classes_dir . 'ZenShortcode.php';
        require $classes_dir . 'ShortCodeController.php';

        $this->zcsc = new ShortCodeController($classes_dir);
    }
}
