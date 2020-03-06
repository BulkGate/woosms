<?php

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Extensions, BulkGate\WooSMS\Escape, BulkGate\WooSms\Post, BulkGate\Extensions\JsonResponse, BulkGate\WooSms;

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
    add_filter('plugin_row_meta', 'woosms_add_links_meta', 10, 2);
    wp_enqueue_style('device', $woo_sms_module->getUrl('/dist/css/devices.min.css'));
    wp_enqueue_style('woosms', $woo_sms_module->getUrl('/'.(defined('BULKGATE_DEV_MODE') ? 'dev' : 'dist').'/css/bulkgate-woosms.css'));
    wp_enqueue_style('woosms-icons', 'https://fonts.googleapis.com/icon?family=Material+Icons|Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i');
});


add_action('wp_ajax_authenticate', function ()
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    try
    {
        JsonResponse::send($woo_sms_di->getProxy()->authenticate());
    }
    catch (Extensions\IO\AuthenticateException $e)
    {
        JsonResponse::send(array('redirect' => admin_url('admin.php?page=woosms_sign_in')));
    }
});


add_action('wp_ajax_register', function ()
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    $response = $woo_sms_di->getProxy()->register(array_merge(array('name' => woosms_get_shop_name()), Post::get('__bulkgate')));

    if ($response instanceof Extensions\IO\Response)
    {
        JsonResponse::send($response);
    }
    JsonResponse::send(array('token' => $response, 'redirect' => admin_url('admin.php?page=woosms_dashboard_default')));
});


add_action('wp_ajax_login', function ()
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    $response =  $woo_sms_di->getProxy()->login(array_merge(array('name' => woosms_get_shop_name()), Post::get('__bulkgate')));

    if ($response instanceof Extensions\IO\Response)
    {
        JsonResponse::send($response);
    }
    JsonResponse::send(array('token' => $response, 'redirect' => admin_url('admin.php?page=woosms_dashboard_default')));
});


add_action('wp_ajax_load_module_data', function ()
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    JsonResponse::send($woo_sms_di->getProxy()->loadCustomersCount(
        Post::getFromArray('__bulkgate', 'application_id'),
        Post::getFromArray('__bulkgate', 'campaign_id')
    ));
});


add_action('wp_ajax_save_module_customers', function ()
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    JsonResponse::send($woo_sms_di->getProxy()->saveModuleCustomers(
        Post::getFromArray('__bulkgate', 'application_id'),
        Post::getFromArray('__bulkgate', 'campaign_id')
    ));
});


add_action('wp_ajax_add_module_filter', function ()
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    JsonResponse::send($woo_sms_di->getProxy()->loadCustomersCount(
        Post::getFromArray('__bulkgate', 'application_id'),
        Post::getFromArray('__bulkgate', 'campaign_id'),
        'addFilter',
        Post::get('__bulkgate')
    ));
});


add_action('wp_ajax_remove_module_filter', function ()
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    JsonResponse::send($woo_sms_di->getProxy()->loadCustomersCount(
        Post::getFromArray('__bulkgate', 'application_id'),
        Post::getFromArray('__bulkgate', 'campaign_id'),
        'removeFilter',
        Post::get('__bulkgate')
    ));
});


add_action('wp_ajax_save_customer_notifications', function ()
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    JsonResponse::send($woo_sms_di->getProxy()->saveCustomerNotifications(
        Post::get('__bulkgate', array(), array('template'))
    ));
});


add_action('wp_ajax_save_admin_notifications', function ()
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    JsonResponse::send($woo_sms_di->getProxy()->saveAdminNotifications(
        Post::get('__bulkgate', array(), array('template'))
    ));
});


add_action('wp_ajax_save_module_settings', function()
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    if (Post::get('__bulkgate', false))
    {
        $woo_sms_di->getProxy()->saveSettings(Post::get('__bulkgate'));
    }

    JsonResponse::send(array('redirect' => admin_url('admin.php?page=woosms_modulesettings_default')));
});


add_action('wp_ajax_logout_module', function()
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    $woo_sms_di->getProxy()->logout();

    JsonResponse::send(array('token' => 'guest', 'redirect' => admin_url('admin.php?page=woosms_sign_in')));
});


add_action('add_meta_boxes', function ($post_type)
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    if ($post_type === 'shop_order' && $woo_sms_di->getSettings()->load('static:application_token', false))
    {
        add_meta_box('send_sms', 'WooSMS', function($post) {
            ?><div id="woo-sms" style="margin:0; zoom: 0.85">
            <div id="react-snack-root" style="zoom: 0.8"></div>
            <div id="react-app-root">
                <?= Escape::html(woosms_translate('loading_content', 'Loading content')); ?>
            </div>
            <?php
            woosms_print_widget('ModuleComponents', 'sendSms', array('id' => get_post_meta($post->ID, '_billing_phone', 'true'), 'key' => strtolower(get_post_meta($post->ID, '_billing_country', 'true'))));
            ?></div><?php
        }, 'shop_order', 'side', 'high');
    }
});


function define_menu($capabilities = 'manage_options')
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $wp_version, $woo_sms_di;

    $woo_sms_settings = $woo_sms_di->getSettings();

    $menu = $woo_sms_settings->load('menu:');

    if (empty($menu))
    {
        woosms_synchronize(true);
        $menu = $woo_sms_settings->load('menu:', array(), true);
    }

    $application_token = $woo_sms_settings->load('static:application_token', false);

    add_menu_page('woosms_profile_page', 'WooSMS', $capabilities, $application_token ? 'woosms_dashboard_default' : 'woosms_sign_in', '', ((float)$wp_version) >= 3.8 ? 'dashicons-email-alt' : plugins_url(WOOSMS_DIR . '/img/logo.png'), '58');

    if ($application_token && is_array($menu))
    {
        foreach ($menu as $slug => $m)
        {
            add_submenu_page($application_token ? $m->parent : null, woosms_translate($m->title), woosms_translate($m->title), $capabilities, $slug, function () use ($m) {
                woosms_page($m->presenter, $m->action, woosms_translate($m->title), $m->box);
            });
        }

        add_submenu_page(null, woosms_translate('sign_in'), woosms_translate('sign_in'), $capabilities, 'woosms_sign_in', function () {
            woosms_page('ModuleSign', 'in', woosms_translate('sign_in'), false);
        });
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
    woosms_synchronize();

    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    $woo_sms_module = $woo_sms_di->getModule();

    ?>
        <div id="woo-sms">
            <nav>
                <div class="container-fluid">
                    <div class="nav-wrapper">
                        <div id="brand-logo">
                            <a class="brand-logo hide-on-med-and-down" href="<?= Escape::url(admin_url('admin.php?page=woosms_dashboard_default')); ?>">
                                <img alt="woosms" width="120" src="<?= Escape::url($woo_sms_module->getUrl('/images/products/ws.svg')); ?>" />
                            </a>
                        </div>
                        <ul class="controls">
                            <span id="react-app-panel-admin-buttons"></span>
                            <span id="react-app-info"></span>                              
                        </ul>
                        <div class="nav-h1">
                            <span class="h1-divider"></span>
                            <h1 class="truncate"><?= Escape::html($title) ?><span id="react-app-h1-sub"></span></h1>
                        </div>
                    </div>
                </div>
            </nav>
            <div id="profile-tab"></div>
            <div<?php if($box): ?> class="module-box"<?php endif; ?>>
                <div id="react-snack-root"></div>
                <div id="react-app-root">
                    <div class="loader loading">
                        <div class="spinner"></div>
                        <p><?= Escape::html(woosms_translate('loading_content', 'Loading content')); ?></p>
                    </div>
                </div>
                <?php
                    woosms_print_widget($presenter, $action, $params);
                ?>
            </div>
        </div>
    <?php
}


function woosms_print_widget($presenter, $action, $params = array())
{
    /** @var WooSms\DIContainer $woo_sms_di */
    global $woo_sms_di;

    $woo_sms_module = $woo_sms_di->getModule();
    $woo_sms_settings = $woo_sms_di->getSettings();

    ?>
        <div id="react-language-footer"></div>
        <script type="application/javascript">
            var _bg_client_config = {
                url: {
                    authenticationService : ajaxurl
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
        <script src="<?= Escape::url($woo_sms_module->getUrl('/'.(defined('BULKGATE_DEV_MODE') ? 'dev' : 'dist').'/widget-api/widget-api.js?v=2')); ?>"></script>
        <script type="application/javascript">
            _bg_client.registerMiddleware(function (data)
            {
                if (data.init._generic)
                {
                    data.init.env.homepage.logo_link = <?= Escape::js($woo_sms_module->getUrl('/images/products/ws.svg')); ?>;
                    data.init._generic.scope.module_info = <?= Escape::js($woo_sms_module->info()); ?>;
                }
            });

            var input = _bg_client.parseQuery(location.search);

            _bg_client.require(<?= Escape::js($woo_sms_settings->load('static:application_id', '')) ?>, {
                product: 'ws',
                language: <?= Escape::js($woo_sms_settings->load('main:language', 'en')) ?>,
                view : {
                    presenter: <?= Escape::js($presenter) ?>,
                    action: <?= Escape::js($action) ?>
                },
                params: {
                    id: <?php if(isset($params['id'])): echo Escape::js($params['id']); else: ?>input["id"]<?php  endif; ?>,
                    key: <?php if(isset($params['key'])): echo Escape::js($params['key']); else: ?>input["key"]<?php  endif; ?>,
                    type: <?php if(isset($params['type'])): echo Escape::js($params['type']); else: ?>input["type"]<?php  endif; ?>,
                },
                proxy: <?= Escape::js(woosms_get_proxy_links($presenter, $action)); ?>
            });
        </script>
    <?php
}


function woosms_get_proxy_links($presenter, $action)
{
    switch ($presenter.':'.$action)
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
	    break;
        case 'SmsCampaign:campaign':
	    	return array('campaign' => array(
                'loadModuleData' => array(
                    'url' => woosms_ajax_url(),
                    'params' => array('action' => 'load_module_data')
                ),
                'saveModuleCustomers' => array(
                    'url' => woosms_ajax_url(),
                    'params' => array('action' => 'save_module_customers')
                ),
                'addModuleFilter' => array(
                    'url' => woosms_ajax_url(),
                    'params' => array('action' => 'add_module_filter')
                ),
                'removeModuleFilter' => array(
                    'url' => woosms_ajax_url(),
                    'params' => array('action' => 'remove_module_filter')
                )
            ));
        break;
        default:
            return array();
    }
}
