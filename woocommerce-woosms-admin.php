<?php
use BulkGate\Extensions, BulkGate\WooSms, BulkGate\Extensions\Escape, BulkGate\Extensions\JsonResponse;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

if (!defined('ABSPATH'))
{
    exit;
}

add_action('admin_menu', function ()
{
    /** @var Extensions\IModule $woo_sms_module */
    global $woo_sms_module;

    define_menu(defined('SMS_DEMO') ? 'read' : 'manage_options');
    add_filter('plugin_action_links', 'woosms_add_settings_link', 10, 2);

    wp_enqueue_style('device', $woo_sms_module->getUrl('/dist/css/devices.min.css'));
    wp_enqueue_style('woosms', $woo_sms_module->getUrl('/dev/css/bulkgate-woosms.css'));
    wp_enqueue_style('woosms-icons', 'https://fonts.googleapis.com/icon?family=Material+Icons|Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i');
});

add_action('wp_ajax_authenticate', function ()
{
    /**
     * @var Extensions\IO\IConnection $woo_sms_connection
     * @var Extensions\IModule $woo_sms_module
     */
    global $woo_sms_connection, $woo_sms_module;

    JsonResponse::send(
        $woo_sms_connection->run(new BulkGate\Extensions\IO\Request($woo_sms_module->getUrl('/widget/authenticate')))
    );
});

add_action('wp_ajax_register', function ()
{
    /**
     * @var Extensions\IO\IConnection $woo_sms_connection
     * @var Extensions\IModule $woo_sms_module
     * @var Extensions\ISettings $woo_sms_settings
     */
    global $woo_sms_connection, $woo_sms_module, $woo_sms_settings;

    $response = $woo_sms_connection->run(new BulkGate\Extensions\IO\Request($woo_sms_module->getUrl('/module/sign/up'), array("name" => 'WooSMS') + $_POST['__bulkgate']));

    $register = (array) $response->get('::register');

    if(isset($register['application_id']) && isset($register['application_token']))
    {
        $woo_sms_settings->set('static:application_id', $register['application_id'], array('type' => 'int'));
        $woo_sms_settings->set('static:application_token', $register['application_token']);
        JsonResponse::send(array('token' => isset($register['application_token_temp']) ? $register['application_token_temp'] : 'guest', 'redirect' => admin_url("admin.php?page=woosms_dashboard_default")));
    }
    JsonResponse::send($response);
});

add_action('wp_ajax_login', function ()
{
    /**
     * @var Extensions\IO\IConnection $woo_sms_connection
     * @var Extensions\IModule $woo_sms_module
     * @var Extensions\ISettings $woo_sms_settings
     */
    global $woo_sms_connection, $woo_sms_module, $woo_sms_settings;

    $response = $woo_sms_connection->run(new BulkGate\Extensions\IO\Request($woo_sms_module->getUrl('/module/sign/in'), array("name" => 'WooSMS') + $_POST['__bulkgate']));

    $login = (array) $response->get('::login');

    if(isset($login['application_id']) && isset($login['application_token']))
    {
        $woo_sms_settings->set('static:application_id', $login['application_id'], array('type' => 'int'));
        $woo_sms_settings->set('static:application_token', $login['application_token']);
        JsonResponse::send(array('token' => isset($login['application_token_temp']) ? $login['application_token_temp'] : 'guest', 'redirect' => admin_url("admin.php?page=woosms_dashboard_default")));
    }
    JsonResponse::send($response);
});

add_action('wp_ajax_save_customer_notifications', function ()
{
    /**
     * @var Extensions\IO\IConnection $woo_sms_connection
     * @var Extensions\IModule $woo_sms_module
     * @var Extensions\Synchronize $woo_sms_synchronize
     */
    global $woo_sms_connection, $woo_sms_module, $woo_sms_synchronize;

    $response = $woo_sms_synchronize->synchronize(function($module_settings) use ($woo_sms_connection, $woo_sms_module)
    {
        return $woo_sms_connection->run(new BulkGate\Extensions\IO\Request($woo_sms_module->getUrl('/module/hook/customer'), array("__synchronize" => $module_settings) + $_POST['__bulkgate'], true));
    });

    JsonResponse::send($response);
});

add_action('wp_ajax_save_admin_notifications', function ()
{
    /**
     * @var Extensions\IO\IConnection $woo_sms_connection
     * @var Extensions\IModule $woo_sms_module
     * @var Extensions\Synchronize $woo_sms_synchronize
     */
    global $woo_sms_connection, $woo_sms_module, $woo_sms_synchronize;

    $response = $woo_sms_synchronize->synchronize(function($module_settings) use ($woo_sms_connection, $woo_sms_module)
    {
        return $woo_sms_connection->run(new BulkGate\Extensions\IO\Request($woo_sms_module->getUrl('/module/hook/admin'), array("__synchronize" => $module_settings) + $_POST['__bulkgate'], true));
    });

    JsonResponse::send($response);
});

function define_menu($capabilities = 'manage_options')
{
    /** @var BulkGate\Extensions\ISettings $woo_sms_settings */
    global $wp_version, $woo_sms_settings;

    $menu = $woo_sms_settings->load("menu:");

    $application_id = $woo_sms_settings->load("static:application_id", false);

    add_menu_page('woosms_profile_page', "WooSMS", $capabilities, $application_id ? 'woosms_dashboard_default' : 'woosms_sign_in', '', ((float)$wp_version) >= 3.8 ? 'dashicons-email-alt' : plugins_url(WOOSMS_DIR . '/img/logo.png'));

    if(is_array($menu))
    {
        foreach($menu as $slug => $m)
        {
            /*if($m->parent === 'main')
            {
                add_menu_page( woosms_translate($m->title),  woosms_translate($m->title), $capabilities, $slug, function () use ($m) {
                    woosms_page($m->presenter, $m->action, woosms_translate($m->title), $m->box);
                }, ((float)$wp_version) >= 3.8 ? (isset($m->icon) ? $m->icon : 'dashicons-email-alt') : plugins_url(WOOSMS_DIR . '/img/logo.png'));

            }
            else
            {*/
                add_submenu_page($application_id ? $m->parent : null, woosms_translate($m->title), woosms_translate($m->title), $capabilities, $slug, function () use ($m) {
                    woosms_page($m->presenter, $m->action, woosms_translate($m->title), $m->box);
                });
            //}
        }
    }
    else
    {
        add_submenu_page('woosms_dashboard_default', "About", "About", $capabilities, 'woosms_dashboard_default', 'woosms_about_page');
    }

    add_submenu_page('woosms_dashboard_default', "About", "About", $capabilities, 'woosms_about', 'woosms_about_page');

    add_submenu_page($application_id ? null : 'woosms_dashboard_default', woosms_translate("login"), woosms_translate("login"), $capabilities, 'woosms_sign_in', function () {
        woosms_page("ModuleSign", "in", woosms_translate("login"), false);
    });
    add_submenu_page($application_id ? null : 'woosms_dashboard_default', woosms_translate("register"), woosms_translate("register"), $capabilities, 'woosms_sign_up', function () {
        woosms_page("Sign", "up", woosms_translate("register"), false);
    });


}

/*function woosms_register_settings()
{
    register_setting("WOOSMS_CFG_SETTINGS_GROUP", 'woosms_settings', 'woosms_settings_validate');
    add_settings_section('woosms_section', "WooSMS " . v_menu_setting, 'woosms_settings_description', "WOOSMS_CFG_SETTINGS_SECTION");

    add_settings_field('woosms_language', ucfirst(trim(v_customersms_langversion)) . ": ", 'woosms_language_field', "WOOSMS_CFG_SETTINGS_SECTION", 'woosms_section');
    add_settings_field('woosms_show_example_ui', ucfirst(trim(v_sendsms_timezone2)) . ":", 'woosms_timezone_field', "WOOSMS_CFG_SETTINGS_SECTION", 'woosms_section');
}

function woosms_settings_validate($input)
{
    return $input;
}

function woosms_settings_description()
{
    echo '<p>' . v_about_copyright . '111</p>';
}

function woosms_timezone_field()
{

    $options = get_option('woosms_settings');

    if (!isset($options['timezone']))
    {
        $date = new DateTime();
        $tz = $date->getTimezone();
        $timezone = $tz->getName();

        if ($timezone == NULL || empty($timezone))
        {
            $timezone = "Europe/Prague";
        }
    }
    else
    {
        $timezone = $options['timezone'];
    }

    $timezoneArray = DateTimeZone::listIdentifiers(2047);

    echo '<select name="woosms_settings[timezone]">';
    foreach ($timezoneArray as $value)
    {
        if ($timezone == $value)
        {
            $selected = " selected=\"selected\"";
        }
        else
        {
            $selected = "";
        }
        echo "<option value=\"" . $value . "\"" . $selected . ">" . $value . "</option>";
    }

    echo '</select>';
}

function woosms_language_field()
{

    global $array_langs;

    $options = get_option('woosms_settings');

    $lang = (isset($options['language'])) ? $options['language'] : "en";

    $array_lang_names = explode("|", array_lang_names);
    $array_langs = explode("|", array_langs);

    echo '<select name="woosms_settings[language]">';
    foreach ($array_langs as $key => $value)
    {
        if ($lang == $value)
        {
            $selected = " selected=\"selected\"";
        }
        else
        {
            $selected = "";
        }
        echo "<option value=\"" . $value . "\"" . $selected . ">" . $array_lang_names[$key] . "</option>";
    }

    echo '</select>';
}

function woosms_display_admin_menu()
{

    if (!current_user_can('manage_options'))
    {
        wp_die("You don't have permission to edit the settings.");
    }

    echo '<div class="wrap">';
    echo '<div id="icon-themes" class="icon32"><br></div>';
    echo '<h2>' . v_about_version_wo . SMS_VERSION . '</h2>';

    echo '<form method="post" action="options.php">';

    settings_fields("WOOSMS_CFG_SETTINGS_GROUP");
    do_settings_sections("WOOSMS_CFG_SETTINGS_SECTION");

    echo '<p class="submit">
    <input type="submit" class="button-primary" value="' . v_smswizard_savesettings . '" />
    </p>';

    echo '</form>';

    echo '</div><hr/>';
    echo woosms_about_page();
}*/

function woosms_add_settings_link($links, $file)
{
    if (basename(dirname($file)) === WOOSMS_DIR)
    {
        $settings_link = '<a href="#TODO">'.Escape::html(esc_html__('Settings')).'</a>';
        array_unshift($links, $settings_link);
    }

    return $links;
}

function woosms_page($presenter, $action, $title, $box, $params = array())
{
    /**
     * @var Extensions\IModule $woo_sms_module
     * @var Extensions\ISettings $woo_sms_settings
     */
    global $woo_sms_module, $woo_sms_settings;

    echo '
        <div id="woo-sms">
            <nav>
                <div class="container-fluid">
                    <div class="nav-wrapper">
                        <div id="brand-logo">
                            <a class="brand-logo hide-on-med-and-down" href="' . Escape::url(admin_url("admin.php?page=woosms_dashboard_default")) . '">
                                <img alt="woosms" width="120" src="' . Escape::url($woo_sms_module->getUrl('/images/products/ws.png')) . '" />
                            </a>
                        </div>
                        <ul class="controls">
                            <span id="react-app-panel-admin-buttons"></span>
                            <span id="react-app-info"></span>                              
                        </ul>
                        <div class="nav-h1">
                            <span class="h1-divider"></span>
                            <h1 class="truncate">' . Escape::html($title) . '<span id="react-app-h1-sub"></span></h1>
                        </div>
                    </div>
                </div>
            </nav>
            <div id="profile-tab"></div>
            <div ' . ($box ? 'class="module-box"' : '') . '>
                <div id="react-snack-root"></div>
                <div id="react-app-root">
                    <div class="loader loading">
                        <div class="spinner"></div>
                        <p>'.woosms_translate('loading_content', 'Loading content').'</p>
                    </div>
                </div>      
                <div id="sms-campaign" class="active"></div>
                <div id="react-language-footer"></div>
                <script type="application/javascript">
                      var _bg_client_config = {
                            url: {
                              authenticationService : ajaxurl,
                            },
                            actions: {
                                authenticate: function () {
                                    return {
                                        data: {
                                            action: "authenticate",
                                            data: {}
                                        }
                                    }
                                }
                            }
                          };
                </script>
                <script src="'.Escape::url($woo_sms_module->getUrl('/dev/widget-api/widget-api.js')).'"></script>
                <script type="application/javascript">
                  
                _bg_client.registerMiddleware(function (data)
                {
                    if(data.init._generic)
                    {
                        data.init._generic.scope.homepage.logo_link = '.Escape::js($woo_sms_module->getUrl('/images/products/ws.png')).'
                    }
                });
                                  
                var input = _bg_client.parseQuery(location.search);
                
                _bg_client.require('.Escape::js($woo_sms_settings->load('static:application_id', '')).', "ws", {
                    view : {
                        presenter : ' . Escape::js($presenter) . ',
                        action : ' . Escape::js($action) . ',
                    },
                    params : {
                        id : ' . ((isset($params['id'])) ? (Escape::js($params['id'])) : ('input["id"]')) . ',
                        key : ' . ((isset($params['key'])) ? (Escape::js($params['key'])) : ('input["key"]')) . ',
                        type : ' . ((isset($params['type'])) ? (Escape::js($params['type'])) : ('input["type"]')) . '
                    },
                    proxy: '.Extensions\Json::encode(woosms_get_proxy_links($presenter, $action)).',                    
                });
                </script>
            </div>
            <!-- TODO: FOOTER -->
        </div>
    ';
}

function woosms_get_proxy_links($presenter, $action)
{
    switch($presenter.':'.$action)
    {
        case 'ModuleNotifications:customer':
            return array('_generic' => array('save' => array(
                'url' => woosms_ajax_url(),
                'params' => array('action' => 'save_customer_notifications')
            )));
        break;
        case 'ModuleNotifications:admin':
            return array('_generic' => array('save' => array(
                'url' => woosms_ajax_url(),
                'params' => array('action' => 'save_admin_notifications')
            )));
            break;
        case 'Sign:up':
        return array('_generic' => array('register' => array(
            'url' => woosms_ajax_url(),
            'params' => array('action' => 'register')
        )));
        break;
        case 'ModuleSign:in':
            return array('_generic' => array('login' => array(
                'url' => woosms_ajax_url(),
                'params' => array('action' => 'login')
            )));
            break;
        default:
            return array();
    }
}

