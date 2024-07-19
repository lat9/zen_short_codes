<?php
class zencat extends ZenShortcode
{
    public function __construct()
    {
        $this->register($this);
    }

    public function get(array $parameters): string
    {
        extract($parameters, EXTR_PREFIX_INVALID, 'sc_');

        $categories_id = (int)($id ?? '');
        $categories_id = ($categories_id < 1) ? -9999 : $categories_id;
        if (zen_get_categories_status($categories_id) === '') {
            return "<!-- zencat, invalid categories_id ($categories_id) -->";
        }

        $category_link = zen_href_link(FILENAME_DEFAULT, 'cPath=' . zen_get_generated_category_path_rev($categories_id));

        $href_only = $href_only ?? 'false';
        if ($href_only !== 'true') {
            $text = $text ?? zen_get_category_name($categories_id);
            $category_link = '<a href="' . $category_link . '">' . $text . '</a>';
        }
        return $category_link;
    }
}
