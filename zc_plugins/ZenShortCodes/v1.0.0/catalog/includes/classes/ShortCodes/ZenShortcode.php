<?php
// -----
// Part of the "Zen Cart Shortcodes" plugin for Zen Cart v1.5.8 or later
//
// Copyright (c) 2024, Vinos de Frutas Tropicales (lat9)
// Portions Copyright 2003-2018 Zen Cart Development Team
//
abstract class ZenShortcode
{
    public final static function getAllShortcodes(): array
    {
        return ZenShortcode::getRegisteredShortcodes() ?? [];
    }

    abstract public function get(array $parameters): string;

    protected final function register(&$shortcode_handler): void
    {
        $registered_shortcodes = &ZenShortcode::getRegisteredShortcodes();
        if (!is_array($registered_shortcodes)) {
            $registered_shortcodes = [];
        }
        $registered_shortcodes[get_class($shortcode_handler)] = &$shortcode_handler;
    }

    private static function &getRegisteredShortcodes()
    {
        static $registered_shortcodes;

        return $registered_shortcodes;
    }
}
