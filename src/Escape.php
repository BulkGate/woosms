<?php

namespace BulkGate\WooSms;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

class Escape
{
    public static function html($s)
    {
        return esc_html($s);
    }


    public static function js($s)
    {
        return str_replace(
            array("\xe2\x80\xa8", "\xe2\x80\xa9", ']]>', '<!'),
            array('\u2028', '\u2029', ']]\x3E', '\x3C!'),
            json_encode($s, JSON_UNESCAPED_UNICODE)
        );
    }


    public static function url($s)
    {
        return esc_url($s);
    }


    public static function htmlAttr($s)
    {
        return esc_attr($s);
    }
}
