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

function woosms_get_lang_iso()
{
    return get_locale();
}

function woosms_run_hook($name, \BulkGate\Extensions\Hook\Variables $variables)
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    $hook = new Extensions\Hook\Hook(
        $woo_sms_di->getModule()->getUrl('/module/hook'),
        woosms_get_lang_iso(),
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

function woosms_load_languages()
{
    $output = array();

    $actual = (array) get_available_languages();

    require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
    $translations = wp_get_available_translations();

    foreach ($actual as $lang)
    {
        if(isset($translations[$lang]) && isset($translations[$lang]['native_name']))
        {
            $output[$lang] = $translations[$lang]['native_name'];
        }
        else
        {
            $output[$lang] = $lang;
        }
    }
    return $output;
}

function woosms_ajax_url()
{
    return network_home_url().'/wp-admin/admin-ajax.php';
}

function woosms_add_settings_link($links, $file)
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    if(basename(dirname($file)) === WOOSMS_DIR)
    {
        if($woo_sms_di->getSettings()->load('static:application_token', false))
        {
            $settings_link = '<a href="'.Extensions\Escape::url(admin_url("admin.php?page=woosms_modulesettings_default")).'">'.Extensions\Escape::html(esc_html__('Settings')).'</a>';
        }
        else
        {
            $settings_link = '<a href="'.Extensions\Escape::url(admin_url("admin.php?page=woosms_sign_in")).'">'.Extensions\Escape::html(esc_html__('Log In')).'</a>';
        }
        array_unshift($links, $settings_link);
    }

    return $links;
}

