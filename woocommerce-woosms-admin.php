<?php
use BulkGate\Extensions, BulkGate\Extensions\Escape, BulkGate\Extensions\JsonResponse, BulkGate\WooSms;

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
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    $woo_sms_module = $woo_sms_di->getModule();

    define_menu(defined('SMS_DEMO') ? 'read' : 'manage_options');
    add_filter('plugin_action_links', 'woosms_add_settings_link', 10, 2);

    wp_enqueue_style('device', $woo_sms_module->getUrl('/dist/css/devices.min.css'));
    wp_enqueue_style('woosms', $woo_sms_module->getUrl('/'.(defined('BULKGATE_DEV_MODE') ? 'dev' : 'dist').'/css/bulkgate-woosms.css'));
    wp_enqueue_style('woosms-icons', 'https://fonts.googleapis.com/icon?family=Material+Icons|Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i');
});

add_action('wp_ajax_authenticate', function ()
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    JsonResponse::send($woo_sms_di->getProxy()->authenticate());
});

add_action('wp_ajax_register', function ()
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    $response = $woo_sms_di->getProxy()->register(array_merge(array("name" => get_bloginfo('name')), $_POST['__bulkgate']));

    if($response instanceof Extensions\IO\Response)
    {
        JsonResponse::send($response);
    }
    JsonResponse::send(array('token' => $response, 'redirect' => admin_url("admin.php?page=woosms_dashboard_default")));
});

add_action('wp_ajax_login', function ()
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    $response =  $woo_sms_di->getProxy()->login(array_merge(array("name" => get_bloginfo('name')), $_POST['__bulkgate']));

    if($response instanceof Extensions\IO\Response)
    {
        JsonResponse::send($response);
    }
    JsonResponse::send(array('token' => $response, 'redirect' => admin_url("admin.php?page=woosms_dashboard_default")));
});

add_action('wp_ajax_save_customer_notifications', function ()
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    JsonResponse::send($woo_sms_di->getProxy()->saveCustomerNotifications($_POST['__bulkgate']));
});

add_action('wp_ajax_save_admin_notifications', function ()
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    JsonResponse::send($woo_sms_di->getProxy()->saveAdminNotifications($_POST['__bulkgate']));
});

add_action('wp_ajax_save_module_settings', function()
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    if(isset($_POST['__bulkgate']))
    {
        $woo_sms_di->getProxy()->saveSettings($_POST['__bulkgate']);
    }

    JsonResponse::send(array('redirect' => admin_url("admin.php?page=woosms_modulesettings_default")));
});

add_action('wp_ajax_logout_module', function()
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    $woo_sms_di->getProxy()->logout();

    JsonResponse::send(array('token' => 'guest', 'redirect' => admin_url("admin.php?page=woosms_sign_in")));
});

function define_menu($capabilities = 'manage_options')
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $wp_version, $woo_sms_di;

    $woo_sms_settings = $woo_sms_di->getSettings();

    $menu = $woo_sms_settings->load("menu:");

    $application_token = $woo_sms_settings->load("static:application_token", false);

    add_menu_page('woosms_profile_page', "WooSMS", $capabilities, $application_token ? 'woosms_dashboard_default' : 'woosms_sign_in', '', ((float)$wp_version) >= 3.8 ? 'dashicons-email-alt' : plugins_url(WOOSMS_DIR . '/img/logo.png'));

    if($application_token && is_array($menu))
    {
        foreach($menu as $slug => $m)
        {
            add_submenu_page($application_token ? $m->parent : null, woosms_translate($m->title), woosms_translate($m->title), $capabilities, $slug, function () use ($m) {
                woosms_page($m->presenter, $m->action, woosms_translate($m->title), $m->box);
            });
        }
    }
    else
    {
        add_submenu_page('woosms_sign_in', woosms_translate('sign_in'), woosms_translate('sign_in'), $capabilities, 'woosms_sign_in', function () {
            woosms_page('ModuleSign', 'in', woosms_translate('sign_in'), false);
        });
        add_submenu_page('woosms_sign_in', woosms_translate('sign_up'), woosms_translate('sign_up'), $capabilities, 'woosms_sign_up', function () {
            woosms_page('Sign', 'up', woosms_translate('sign_up'), false);
        });
    }
    add_submenu_page($application_token ? 'woosms_dashboard_default' : 'woosms_sign_in', woosms_translate('about_module'), woosms_translate('about_module'), $capabilities, 'woosms_about_default', function () {
        woosms_page('ModuleAbout', 'default', woosms_translate('about_module'), false);
    });
}

function woosms_page($presenter, $action, $title, $box, $params = array())
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    $woo_sms_module = $woo_sms_di->getModule();
    $woo_sms_settings = $woo_sms_di->getSettings();

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
                <script src="'.Escape::url($woo_sms_module->getUrl('/'.(defined('BULKGATE_DEV_MODE') ? 'dev' : 'dist').'/widget-api/widget-api.js')).'"></script>
                <script type="application/javascript">
                    _bg_client.registerMiddleware(function (data)
                    {
                        if(data.init._generic)
                        {
                            data.init.env.homepage.logo_link = '.Escape::js($woo_sms_module->getUrl('/images/products/ws.png')).';
                            data.init._generic.scope.module_info = '.Escape::js($woo_sms_module->info()).'
                        }
                    });
                                      
                    var input = _bg_client.parseQuery(location.search);
                    
                    _bg_client.require('.Escape::js($woo_sms_settings->load('static:application_id', '')).', {
                        product: "ws",
                        language: '.Escape::js($woo_sms_settings->load('main:language', 'en')).',
                        view : {
                            presenter : ' . Escape::js($presenter) . ',
                            action : ' . Escape::js($action) . ',
                        },
                        params : {
                            id : ' . ((isset($params['id'])) ? (Escape::js($params['id'])) : ('input["id"]')) . ',
                            key : ' . ((isset($params['key'])) ? (Escape::js($params['key'])) : ('input["key"]')) . ',
                            type : ' . ((isset($params['type'])) ? (Escape::js($params['type'])) : ('input["type"]')) . ',
                            profile_id : ' . ((isset($params['profile_id'])) ? (Escape::js($params['profile_id'])) : ('input["profile_id"]')) . ',
                        },
                        proxy: '.Extensions\Json::encode(woosms_get_proxy_links($presenter, $action)).',                    
                    });
                </script>
            </div>
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
        case 'ModuleSettings:default':
            return array('_generic' => array(
                'save' => array(
                    'url' => woosms_ajax_url(),
                    'params' => array('action' => 'save_module_settings')
                ),
                'logout' => array(
                    'url' => woosms_ajax_url(),
                    'params' => array('action' => 'logout_module')
            )));
        default:
            return array();
    }
}

