<?php

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Extensions, BulkGate\WooSms;

function woosms_translate($key, $default = null)
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    return $woo_sms_di->getTranslator()->translate($key, $default);
}

function woosms_get_order_meta_array($id)
{
    $data = (array) get_post($id);

    $result = get_post_meta($id);

    if(is_array($data) && is_array($result))
    {
        foreach($result as $key => $value)
        {
            $data[$key] = $value[0];
        }
    }
    else
    {
        return  new BulkGate\Extensions\Buffer();
    }

    return new BulkGate\Extensions\Buffer($data);
}

function woosms_get_address_meta_array($id)
{
    $data = array();

    $result = (array) get_user_meta($id);

    if(is_array($result) && count($result))
    {
        foreach($result as $key => $value)
        {
            $data[$key] = $value[0];
        }
    }
    else
    {
        return  new BulkGate\Extensions\Buffer();
    }

    return new BulkGate\Extensions\Buffer($data);
}

function woosms_isset($array_object, $key, $default = null)
{
    if(is_array($array_object) && isset($array_object[$key]))
    {
        return $array_object[$key];
    }
    elseif($array_object instanceof stdClass && isset($array_object->$key))
    {
        return $array_object->$key;
    }
    elseif($array_object instanceof BulkGate\Extensions\Database\Result)
    {
        return $array_object->get($key);
    }
    return $default;
}

function woosms_run_hook($name, \BulkGate\Extensions\Hook\Variables $variables)
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    $hook = new Extensions\Hook\Hook(
        $woo_sms_di->getModule()->getUrl('/module/hook'),
        $variables->get('lang_id', woosms_get_lang_iso()),
        0 /* single store */,
        $woo_sms_di->getConnection(),
        $woo_sms_di->getSettings(),
        new WooSms\HookLoad($woo_sms_di->getDatabase())
    );

    try
    {
        $hook->run((string) $name, $variables);
        return true;
    }
    catch (Extensions\IO\InvalidResultException $e)
    {
        return false;
    }
}

function woosms_get_shop_name()
{
    return html_entity_decode(get_option('blogname', 'WooSMS Store'), ENT_QUOTES);
}

function woosms_get_lang_iso()
{
    /* WPML Plugin */
    if ((is_plugin_active('sitepress-multilingual-cms-master/sitepress.php') || is_plugin_active('sitepress-multilingual-cms/sitepress.php')) && defined('ICL_LANGUAGE_CODE'))
    {
        return ICL_LANGUAGE_CODE;
    }
    else
    {
        return get_locale();
    }
}

function woosms_get_post_lang($post_id)
{
    /* WPML Plugin */
    if (is_plugin_active('sitepress-multilingual-cms-master/sitepress.php') || is_plugin_active('sitepress-multilingual-cms/sitepress.php'))
    {
        return get_post_meta($post_id, 'wpml_language', true) ?: woosms_get_lang_iso();
    }
    else
    {
        return woosms_get_lang_iso();
    }
}

function woosms_load_languages()
{
    $output = array();

    /* WPML Plugin */
    if (is_plugin_active('sitepress-multilingual-cms-master/sitepress.php') || is_plugin_active('sitepress-multilingual-cms/sitepress.php'))
    {
        $languages = apply_filters('wpml_active_languages', null, 'orderby=id&order=desc');

        foreach ($languages as $lang => $item)
        {
            $output[$lang] = isset($item['native_name']) ? $item['native_name'] : $lang;
        }
    }
    else
    {
        $actual = (array) get_available_languages();

        require_once(ABSPATH.'wp-admin/includes/translation-install.php' );
        $translations = wp_get_available_translations();

        foreach ($actual as $lang)
        {
            $output[$lang] = (isset($translations[$lang]) && isset($translations[$lang]['native_name'])) ? $translations[$lang]['native_name'] : $lang;
        }
    }
    return count($output) === 0 ? array(woosms_get_lang_iso() => 'Default') : $output;
}

function woosms_ajax_url()
{
    return admin_url('/admin-ajax.php', is_ssl() ? 'https' : 'http');
}

function woosms_add_settings_link($links, $file)
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    if(basename(dirname($file)) === WOOSMS_DIR)
    {
        if($woo_sms_di->getSettings()->load('static:application_token', false))
        {
            $settings_link = '<a href="'.esc_url(admin_url("admin.php?page=woosms_modulesettings_default")).'">'.esc_html__('Settings').'</a>';
        }
        else
        {
            $settings_link = '<a href="'.esc_url(admin_url("admin.php?page=woosms_sign_in")).'">'.esc_html__('Log In').'</a>';
        }
        array_unshift($links, $settings_link);
    }

    return $links;
}

function woosms_add_links_meta( $links, $file )
{
    if(basename(dirname($file)) === WOOSMS_DIR)
    {
        $row_meta = array(
            'help_desk'    => '<a href="' . esc_url('https://help.bulkgate.com/en/') . '" aria-label="' . esc_attr( 'Help Desk' ) . '">' . esc_html( 'Help Desk' ) . '</a>',
            'price_list'    => '<a href="' . esc_url('https://www.bulkgate.com/en/sms-price/') . '" aria-label="' . esc_attr( 'Price List' ) . '">' . esc_html( 'Price List' ) . '</a>',
            'youtube_channel'    => '<a href="' . esc_url('https://www.youtube.com/channel/UCGD7ndC4z2NfuWUrS-DGELg') . '" aria-label="' . esc_attr( 'YouTube Channel' ) . '">' . esc_html( 'YouTube Channel' ) . '</a>',
            'contact_us'    => '<a href="' . esc_url('https://www.bulkgate.com/en/contact-us/') . '" aria-label="' . esc_attr( 'Contact us' ) . '">' . esc_html( 'Contact us' ) . '</a>',
            'api'    => '<a href="' . esc_url('https://www.bulkgate.com/en/developers/sms-api/') . '" aria-label="' . esc_attr( 'API' ) . '">' . esc_html( 'API' ) . '</a>',
        );

        return array_merge($links, $row_meta);
    }

    return (array) $links;
}

