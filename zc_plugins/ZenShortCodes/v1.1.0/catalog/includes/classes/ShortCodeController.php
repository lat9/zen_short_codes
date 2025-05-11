<?php
// -----
// Part of the "Zen Cart Shortcodes" plugin for Zen Cart v1.5.8 or later
//
// Copyright (c) 2024, Vinos de Frutas Tropicales (lat9)
//
use App\Models\PluginControl;
use App\Models\PluginControlVersion;
use Zencart\PluginManager\PluginManager;

class ShortCodeController extends ZenShortcode
{
    //- Concept provided by https://developer.wordpress.org/reference/functions/get_shortcode_atts_regex/
    protected const ATTS_REGEX = '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|\'([^\']*)\'(?:\s|$)|(\S+)(?:\s|$)/';

    private array $handlers = [];

    private string $classesDir;

    public function __construct($classes_dir)
    {
        $this->classesDir = $classes_dir;

        // -----
        // Pull in any shortcode handlers provided by *this* plugin.
        //
        $handlers = glob($classes_dir . 'ShortCodes/*.php');

        // -----
        // Next, check to see if any shortcodes have been provided by *other*
        // zc_plugins.
        //
        $pluginManager = new PluginManager(new PluginControl(), new \App\Models\PluginControlVersion());
        $installedPlugins = $pluginManager->getInstalledPlugins();
        foreach ($installedPlugins as $plugin) {
            if ($plugin['unique_key'] === 'ZenShortCodes') {
                continue;
            }

            $dir_plugin_fs_shortcode_classes = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/catalog/includes/classes/ShortCodes/';
            if (!is_dir($dir_plugin_fs_shortcode_classes)) {
                continue;
            }
            $handlers = array_merge($handlers, glob($dir_plugin_fs_shortcode_classes . '*.php'));
        }

        // -----
        // Finally, check the /includes/classes/ShortCodes directory.  Any
        // shortcodes there will override those provided in this
        // plugin and any provided by additional zc_plugins!
        //
        if (is_dir(DIR_WS_CLASSES . 'ShortCodes/')) {
            $handlers = array_merge($handlers, glob(DIR_WS_CLASSES . 'ShortCodes/*.php'));
        }

        // -----
        // Now that all shortcode handlers have been determined, load the associated
        // handler's class-file and set its instance into this class' handlers
        // array.
        //
        foreach ($handlers as $next_handler) {
            require $next_handler;

            $handler_name = pathinfo($next_handler, PATHINFO_FILENAME);
            $this->handlers[] = new $handler_name();
        }
    }

    public function getShortCodeHandlerCount(): int
    {
        return count($this::getAllShortcodes());
    }

    public function convertShortCodes(string $text): string
    {
        $short_codes = $this->findShortCodesInText($text);
        if (empty($short_codes)) {
            return $text;
        }

        $short_code_handlers = $this::getAllShortcodes();
        foreach ($short_codes[1] as $next_short_code) {
            $short_code_attributes = $this->parseShortCodeAttribute($next_short_code);
            $short_code_name = $short_code_attributes[0] ?? '??';
            if (!in_array($short_code_name, array_keys($short_code_handlers))) {
                continue;
            }

            unset($short_code_attributes[0]);
            $text = str_replace("[$next_short_code]", $short_code_handlers[$short_code_name]->get($short_code_attributes), $text);
        }
        return $text;
    }

    // -----
    // Non-functional, but required since this class extends the abstract
    // class ZenShortcode, which includes this method.
    //
    public function get(array $parameters): string
    {
        return '';
    }

    // -----
    // Finds all shortcode elements in a given string, essentially anything
    // contained within a set of brackets.
    //
    protected function findShortCodesInText(string $text): array
    {
        if (empty(preg_match_all('/\[(.*?)\]/', $text, $output_array))) {
            return [];
        }
        return $output_array;
    }

    //- Concept provided by https://developer.wordpress.org/reference/functions/shortcode_parse_atts/
    protected function parseShortCodeAttribute(string $text): array
    {
        $atts = [];
        $text = preg_replace("/[\x{00a0}\x{200b}]+/u", ' ', $text);
        if (preg_match_all(self::ATTS_REGEX, $text, $match, PREG_SET_ORDER)) {
            foreach ($match as $m) {
                if (!empty($m[1])) {
                    $atts[strtolower($m[1])] = stripcslashes($m[2]);
                } elseif (!empty($m[3])) {
                    $atts[strtolower($m[3])] = stripcslashes($m[4]);
                } elseif (!empty($m[5])) {
                    $atts[strtolower($m[5])] = stripcslashes($m[6]);
                } elseif (!empty($m[7])) {
                    $atts[] = stripcslashes($m[7]);
                } elseif (!empty($m[8])) {
                    $atts[] = stripcslashes($m[8]);
                } elseif (isset($m[9])) {
                    $atts[] = stripcslashes($m[9]);
                }
            }

            // Reject any unclosed HTML elements.
            foreach ($atts as &$value) {
                if (str_contains($value, '<')) {
                    if ( 1 !== preg_match('/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $value)) {
                        $value = '';
                    }
                }
            }
        }
        return $atts;
    }
}
