<?php

/**
 * Back office plugin
 * PHP version 7.3
 *
 * @category WooSMS
 * @package  BulkGate
 * @author   Lukáš Piják <pijak@bulkgate.com>
 * @license  GNU General Public License v3.0
 * @link     https://www.bulkgate.com/
 */

use BulkGate\WooSms\Escape, BulkGate\WooSms\Post;
use BulkGate\Plugin\{
    DI\Container as DIContainer,
    Eshop,
    IO,
    User,
    Utils\JsonResponse,
    Settings,
};

if (!defined('ABSPATH')) {
    exit;
}


add_action(
    'admin_menu', function ()
    {
        Woosms_Define_menu(defined('SMS_DEMO') ? 'read' : 'manage_options');
        add_filter('plugin_action_links', 'woosms_add_settings_link', 10, 2);
        add_filter('plugin_row_meta', 'woosms_add_links_meta', 10, 2);
    }
);


add_action(
    'wp_ajax_authenticate', function () {
        /**
         * DI Container
         *
         * @var DIContainer $woo_sms_di DI Container
         */
        global $woo_sms_di;
        $woo_sms_settings = $woo_sms_di->getByClass(Settings\Settings::class);

        if ($woo_sms_settings->load('static:application_token') === null)
        {
            JsonResponse::send(['redirect' => admin_url('admin.php?page=bulkgate#/sign/in')]);
        }
        else
        {
            JsonResponse::send(['token' => $woo_sms_di->getByClass(User\Sign::class)->authenticate()]);
        }
    }
);


add_action(
    'wp_ajax_login', function () {
        /**
         * DI Container
         *
         * @var DIContainer $woo_sms_di DI Container
         */
        global $woo_sms_di;

        ['email' => $email, 'password' => $password] = Post::get('__bulkgate');
        $response = $woo_sms_di->getByClass(User\Sign::class)->in($email, $password, woosms_get_shop_name(), admin_url('admin.php?page=bulkgate#/dashboard'));

        JsonResponse::send($response);
    }
);


add_action(
    'wp_ajax_save_module_settings', function () {
        /**
         * DI Container
         *
         * @var DIContainer $woo_sms_di DI Container
         */
        global $woo_sms_di;

        if (Post::get('__bulkgate', false)) {
            $woo_sms_di->getProxy()->saveSettings(Post::get('__bulkgate'));
        }

        JsonResponse::send(['redirect' => admin_url('admin.php?page=bulkgate#/module-settings')]);
    }
);


add_action(
    'wp_ajax_logout_module', function () {
        /**
         * DI Container
         *
         * @var DIContainer $woo_sms_di DI Container
         */
        global $woo_sms_di;

        $woo_sms_di->getByClass(User\Sign::class)->out();

        JsonResponse::send(['token' => 'guest', 'redirect' => admin_url('admin.php?page=bulkgate#/sign/in')]);
    }
);


/*add_action(
    'add_meta_boxes', function ($post_type) {
        /**
         * DI Container
         *
         * @var DIContainer $woo_sms_di DI Container
         *
        global $woo_sms_di;

        if ($post_type === 'shop_order' && $woo_sms_di->getSettings()->load('static:application_token', false)) {
            add_meta_box(
                'send_sms', 'BulkGate', function ($post) {
                    ?><div id="woo-sms" style="margin:0; zoom: 0.85">
            <div id="react-snack-root" style="zoom: 0.8"></div>
            <div id="react-app-root">
                    <?php echo Escape::html(woosms_translate('loading_content', 'Loading content')); ?>
            </div>
                    <?php
                    Woosms_Print_widget('ModuleComponents', 'sendSms', ['id' => get_post_meta($post->ID, '_billing_phone', 'true'), 'key' => strtolower(get_post_meta($post->ID, '_billing_country', 'true'))]);
                    ?></div><?php
                }, 'shop_order', 'side', 'high'
            );
        }
    }
);*/


/**
 * Defines structure of WooSMS menu
 *
 * @param string $capabilities Capabilities
 *
 * @return void
 */
function Woosms_Define_menu($capabilities = 'manage_options')
{
    /**
     * DI Container
     *
     * @var DIContainer $woo_sms_di DI Container
     */
    global $woo_sms_di;

    $woo_sms_settings = $woo_sms_di->getByClass(Settings\Settings::class);

    $application_token = $woo_sms_settings->load('static:application_token', false);

    add_menu_page('bulkgate', 'BulkGate SMS', $capabilities, 'bulkgate', fn () => Woosms_page('ModuleSign', 'in', woosms_translate('sign_in'), false), 'dashicons-email-alt', '58');
}


/**
 * Prints WooSMS Page
 *
 * @param string $presenter Presenter
 * @param string $action    Action
 * @param string $title     Title
 * @param bool   $box       Box
 * @param array  $params    Parameters
 *
 * @return void
 */
function Woosms_page($presenter, $action, $title, $box, array $params = [])
{
    Woosms_synchronize();
    Woosms_Print_widget($presenter, $action, $params);

    echo "<ecommerce-module></ecommerce-module>";
}


/**
 * Prints Widget from Widget API
 *
 * @param string $presenter Presenter
 * @param string $action    Action
 * @param array  $params    Parameters
 *
 * @return void
 */
function Woosms_Print_widget($presenter, $action, array $params = [])
{
    /**
     * DI Container
     *
     * @var DIContainer $woo_sms_di DI Container
     */
    global $woo_sms_di;

    $url = $woo_sms_di->getByClass(IO\Url::class);
    $configuration = $woo_sms_di->getByClass(Eshop\Configuration::class);
    $user = $woo_sms_di->getByClass(User\Sign::class);
    $jwt = $user->authenticate();

    $escape_js = [Escape::class, 'js'];

    wp_print_inline_script_tag(
        <<<JS
            function initWidget_ecommerce_module(widget) {
                function getHeaders(token) {
                    return function () {
                        return {
                            Authorization: "Bearer " + token
                        }
                    }
                }
                widget.initialize({
                    _generic: {
                        scope: {$escape_js($configuration->info())}
                    }
                });
                widget.authenticator = {
                    getHeaders: getHeaders({$escape_js($jwt)}),
                    authenticate: async () => {
                        let response = await fetch(ajaxurl, {
                            method: "POST",
                            headers: {
                                'Content-Type': "application/x-www-form-urlencoded"
                            },
                            body: "action=authenticate",
                        });
                        let {token, redirect} = await response.json();
                        
                        if (redirect){
                            return {redirect};
                        }
                        if (token) {
                            widget.authenticator.getHeaders = getHeaders(token);
                        }
                        
                        return {};
                    }
                };
                //todo: nastavovat externe v ramci typu aplikace? 
                widget.options.main = {
                    showLanguagePanel: false,
                    showPermanentLogin: false,
                    logo: "images/white-label/bulkgate/logo/logo-title.svg",
                    logo_dark: "images/white-label/bulkgate/logo/logo-white.svg",
                    background: "images/products/backgrounds/ws.svg"
                };
                widget.options.layout = {
                    appBar: {
                        showLogOut: false,
                        logoUrl: "images/products/bg.svg",
                        logoStyle: {
                            height: "40px",
                            width: "100px",
                            filter: "brightness(0) invert(1)",
                        }
                    },
                    navBar: {
                        hidden: true,
                    }
                };
                widget.options.proxyFactory = function(store) {
                    let proxyData = {$escape_js(Woosms_Get_Proxy_links())};
                    
                    return function proxy(reducerName, requestData) {
                        let {activeRoute} = store.getState().routing.server;
                        let data = (proxyData[activeRoute] || {})[reducerName] || {}
                        let {url, params} = data[requestData.url] || {};
    
                        if (url){
                            requestData.contentType = "application/x-www-form-urlencoded";
                            requestData.url = url;
                            requestData.data = {__bulkgate: requestData.data, ...params};
                            return true;
                        }
                        
                        try {
                            // relative -> absolute url conversion. In modules context, relative urls are not suitable. This covers routing (soft redirects change route) and signals (actions). What about redirect??
                            let baseUrl = new URL({$escape_js($url->get(''))}); // bulkgate's app url
                            url = new URL(requestData.url, baseUrl);
                            requestData.url = url.toString();
                            return true;
                        } catch {}
                    }
                
                console.log("configuration called", widget);
            }
        }
    JS);

    wp_print_script_tag([
        'src' => Escape::url($url->get("web-components/ecommerce/default/$jwt?config=initWidget_ecommerce_module")),
        'async' => true,
    ]);
}


/**
 * Proxy structure generate
 *
 * @param string $presenter Presenter
 * @param string $action    Action
 *
 * @return array|array[][]
 */
function Woosms_Get_Proxy_links()
{
    return [
        'Sign:in' => [
            '_generic' => [
                'login' => [
                    'url' => woosms_ajax_url(),
                    'params' => ['action' => 'login']
                ]
            ]
        ],
        'ModuleSettings:default' => [
            '_generic' => [
                'save' => [
                    'url' => woosms_ajax_url(),
                    'params' => ['action' => 'save_module_settings']
                ],
                'logout' => [
                    'url' => woosms_ajax_url(),
                    'params' => ['action' => 'logout_module']
                ]
            ]
        ],
    ];
}
