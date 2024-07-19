<?php
class ShortCodeController extends ZenShortcode
{
    //- Concept provided by https://developer.wordpress.org/reference/functions/shortcode_parse_atts/
    protected const ATTS_REGEX = '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|\'([^\']*)\'(?:\s|$)|(\S+)(?:\s|$)/';

    private array $handlers = [];

    private string $classesDir;

    public function __construct($classes_dir)
    {
        $this->classesDir = $classes_dir;

        $handlers = glob($classes_dir . 'ShortCodes/*.php');
        if (is_dir(DIR_WS_CLASSES . 'ShortCodes/')) {
            $extra_handlers = glob(DIR_WS_CLASSES . 'ShortCodes/*.php');
            $handlers = array_merge($handlers, $extra_handlers);
        }
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
