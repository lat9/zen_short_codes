<?php
// -----
// Part of the "Zen Cart Shortcodes" plugin for Zen Cart v1.5.8 or later
//
// Copyright (c) 2024, Vinos de Frutas Tropicales (lat9)
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
        foreach ($data['lang'] as $lang_code => &$lang_info) {
            $lang_info['products_description'] = $this->zcsc->convertShortCodes($lang_info['products_description']);
        }
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
        $data->fields['products_description'] = $this->zcsc->convertShortCodes($data->fields['products_description']);
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

    private function loadShortCodeController()
    {
        if (isset($this->zcsc)) {
            return;
        }
        $classes_dir = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
        require $classes_dir . 'ShortCodes/ZenShortcode.php';
        require $classes_dir . 'ShortCodes/ShortCodeController.php';

        $this->zcsc = new ShortCodeController($classes_dir);
    }
}
