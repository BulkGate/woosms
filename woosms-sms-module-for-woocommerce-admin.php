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
    AuthenticateException,
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
    'admin_menu', function () {
        /**
         * DI Container
         *
         * @var DIContainer $woo_sms_di DI Container
         */
        global $woo_sms_di;

        $url = $woo_sms_di->getByClass(IO\Url::class);

        Woosms_Define_menu(defined('SMS_DEMO') ? 'read' : 'manage_options');
        add_filter('plugin_action_links', 'woosms_add_settings_link', 10, 2);
        add_filter('plugin_row_meta', 'woosms_add_links_meta', 10, 2);
        //wp_enqueue_style('woosms', $url->get((defined('BULKGATE_DEV_MODE') ? 'dev' : 'dist').'/css/bulkgate-woosms.css?v=2.2'));
        //wp_enqueue_style('woosms-icons', 'https://fonts.googleapis.com/icon?family=Material+Icons|Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i');
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

        try
        {
            JsonResponse::send($woo_sms_di->getByClass(User\Sign::class)->authenticate());
        }
        catch (AuthenticateException $e)
        {
            JsonResponse::send(['redirect' => admin_url('admin.php?page=woosms_sign_in')]);
        }
    }
);


add_action(
    'wp_ajax_register', function () {
        /**
         * DI Container
         *
         * @var DIContainer $woo_sms_di DI Container
         */
        global $woo_sms_di;

        $response = $woo_sms_di->getProxy()->register(array_merge(['name' => woosms_get_shop_name()], Post::get('__bulkgate')));

        if ($response instanceof Extensions\IO\Response) {
            JsonResponse::send($response);
        }
        JsonResponse::send(['token' => $response, 'redirect' => admin_url('admin.php?page=woosms_dashboard_default')]);
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
        $response = $woo_sms_di->getByClass(User\Sign::class)->in($email, $password, woosms_get_shop_name(), admin_url('admin.php?page=woosms_dashboard_default'));

        JsonResponse::send($response);
    }
);


add_action(
    'wp_ajax_load_module_data', function () {
        /**
         * DI Container
         *
         * @var DIContainer $woo_sms_di DI Container
         */
        global $woo_sms_di;

        JsonResponse::send(
            $woo_sms_di->getProxy()->loadCustomersCount(
                Post::getFromArray('__bulkgate', 'application_id'),
                Post::getFromArray('__bulkgate', 'campaign_id')
            )
        );
    }
);


add_action(
    'wp_ajax_save_module_customers', function () {
        /**
         * DI Container
         *
         * @var DIContainer $woo_sms_di DI Container
         */
        global $woo_sms_di;

        JsonResponse::send(
            $woo_sms_di->getProxy()->saveModuleCustomers(
                Post::getFromArray('__bulkgate', 'application_id'),
                Post::getFromArray('__bulkgate', 'campaign_id')
            )
        );
    }
);


add_action(
    'wp_ajax_add_module_filter', function () {
        /**
         * DI Container
         *
         * @var DIContainer $woo_sms_di DI Container
         */
        global $woo_sms_di;

        JsonResponse::send(
            $woo_sms_di->getProxy()->loadCustomersCount(
                Post::getFromArray('__bulkgate', 'application_id'),
                Post::getFromArray('__bulkgate', 'campaign_id'),
                'addFilter',
                Post::get('__bulkgate')
            )
        );
    }
);


add_action(
    'wp_ajax_remove_module_filter', function () {
        /**
         * DI Container
         *
         * @var DIContainer $woo_sms_di DI Container
         */
        global $woo_sms_di;

        JsonResponse::send(
            $woo_sms_di->getProxy()->loadCustomersCount(
                Post::getFromArray('__bulkgate', 'application_id'),
                Post::getFromArray('__bulkgate', 'campaign_id'),
                'removeFilter',
                Post::get('__bulkgate')
            )
        );
    }
);


add_action(
    'wp_ajax_save_customer_notifications', function () {
        /**
         * DI Container
         *
         * @var DIContainer $woo_sms_di DI Container
         */
        global $woo_sms_di;

        JsonResponse::send(
            $woo_sms_di->getProxy()->saveCustomerNotifications(
                Post::get('__bulkgate', [], ['template'])
            )
        );
    }
);


add_action(
    'wp_ajax_save_admin_notifications', function () {
        /**
         * DI Container
         *
         * @var DIContainer $woo_sms_di DI Container
         */
        global $woo_sms_di;

        JsonResponse::send(
            $woo_sms_di->getProxy()->saveAdminNotifications(
                Post::get('__bulkgate', [], ['template'])
            )
        );
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

        JsonResponse::send(['redirect' => admin_url('admin.php?page=woosms_modulesettings_default')]);
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

        JsonResponse::send(['token' => 'guest', 'redirect' => admin_url('admin.php?page=woosms_sign_in')]);
    }
);


add_action(
    'add_meta_boxes', function ($post_type) {
        /**
         * DI Container
         *
         * @var DIContainer $woo_sms_di DI Container
         */
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
);


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

    $menu = $woo_sms_settings->load('menu:');

    if (empty($menu)) {
        Woosms_synchronize(true);
        $menu = $woo_sms_settings->load('menu:', true) ?? [];
    }

    $application_token = $woo_sms_settings->load('static:application_token', false);

    add_menu_page('woosms_profile_page', 'BulkGate SMS', $capabilities, $application_token ? 'woosms_dashboard_default' : 'woosms_sign_in', '', 'dashicons-email-alt', '58');

    if ($application_token && is_array($menu)) {
        foreach ($menu as $slug => $m) {
            $value = $m->value;

            add_submenu_page(
                $value['parent'], woosms_translate($value['title']), woosms_translate($value['title']), $capabilities, $slug, fn () => Woosms_page($value['presenter'], $value['action'], woosms_translate($value['title']), $value['box'])
            );
        }

        add_submenu_page(
            null, woosms_translate('sign_in'), woosms_translate('sign_in'), $capabilities, 'woosms_sign_in', function () {
                Woosms_page('ModuleSign', 'in', woosms_translate('sign_in'), false);
            }
        );
    } else {
        add_submenu_page(
            'woosms_sign_in', woosms_translate('sign_in'), woosms_translate('sign_in'), $capabilities, 'woosms_sign_in', function () {
                Woosms_page('ModuleSign', 'in', woosms_translate('sign_in'), false);
            }
        );
        add_submenu_page(
            'woosms_sign_in', woosms_translate('sign_up'), woosms_translate('sign_up'), $capabilities, 'woosms_sign_up', function () {
                Woosms_page('Sign', 'up', woosms_translate('sign_up'), false);
            }
        );
    }
    add_submenu_page(
        $application_token ? 'woosms_dashboard_default' : 'woosms_sign_in', woosms_translate('about_module'), woosms_translate('about_module'), $capabilities, 'woosms_about_default', function () {
            Woosms_page('ModuleAbout', 'default', woosms_translate('about_module'), false);
        }
    );
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

    /**
     * DI Container
     *
     * @var DIContainer $woo_sms_di DI Container
     */
    global $woo_sms_di;

    $url = $woo_sms_di->getByClass(IO\Url::class);
    Woosms_Print_widget($presenter, $action, $params);


    ?>
        <div id="woo-sms">
            <ecommerce-module></ecommerce-module>
        </div>
    <?php

    /*<div id="woo-sms">
            <nav>
                <div class="container-fluid">
                    <div class="nav-wrapper">
                        <div id="brand-logo">
                            <a class="brand-logo hide-on-med-and-down" href="<?php echo Escape::url(admin_url('admin.php?page=woosms_dashboard_default')); ?>">
                                <img alt="woosms" width="120" src="<?php echo Escape::url($url->get('images/products/ws.svg')); ?>" />
                            </a>
                        </div>
                        <ul class="controls">
                            <span id="react-app-panel-admin-buttons"></span>
                            <span id="react-app-info"></span>                              
                        </ul>
                        <div class="nav-h1">
                            <span class="h1-divider"></span>
                            <h1 class="truncate"><?php echo Escape::html($title) ?><span id="react-app-h1-sub"></span></h1>
                        </div>
                    </div>
                </div>
            </nav>
            <div id="profile-tab"></div>
            <div<?php if($box) : ?> class="module-box"<?php 
           endif; ?>>
                <div id="react-snack-root"></div>
                <div id="react-app-root">
                    <div class="loader loading">
                        <div class="spinner"></div>
                        <p><?php echo Escape::html(woosms_translate('loading_content', 'Loading content')); ?></p>
                    </div>
                </div>
                <?php
                    Woosms_Print_widget($presenter, $action, $params);
                ?>
            </div>
        </div>*/
        ?>
    <?php

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
    $jwt = $user->authenticate()['token'];

    $escape_js = [Escape::class, 'js'];

    ?>
        <div id="react-language-footer"></div>
    <?php

    wp_print_inline_script_tag(
        <<<JS
            function initWidget_ecommerce_module(widget) {
                widget.initialize({
                    _generic: {
                        scope: {$escape_js($configuration->info())}
                    }
                });
                widget.authenticator = {
                    getHeaders: () => {
                        return {
                            Authorization: "Bearer $jwt"
                        }
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
JS
    );

    wp_print_script_tag(
        [
            'src' => Escape::url($url->get('web-components/ecommerce/default/'.$jwt.'?config=initWidget_ecommerce_module')),
            'async' => true,
        ]
    );
    /*
    wp_print_inline_script_tag(
        <<<JS
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

            _bg_client.registerMiddleware(function (data)
            {
                if (data.init._generic)
                {
                    data.init.env.homepage.logo_link = {$escape_js($url->get('images/products/ws.svg'))};
                    data.init._generic.scope.module_info = {$escape_js($configuration->info())};
                }
            });

            var input = _bg_client.parseQuery(location.search);            

            _bg_client.require({$escape_js($woo_sms_settings->load('static:application_id'))}, {
                product: 'ws',
                language: {$escape_js($woo_sms_settings->load('main:language') ?? 'en')},
                view : {
                    presenter: {$escape_js($presenter)},
                    action: {$escape_js($action)}
                },
                params: Object.assign(input, {$escape_js($params)}),
                proxy: {$escape_js(Woosms_Get_Proxy_links($presenter, $action))}
            });
JS
    );*/
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
        'ModuleNotifications:customer' => [
            '_generic' => [
                'save' => [
                    'url' => woosms_ajax_url(),
                    'params' => ['action' => 'save_customer_notifications']
                ]
            ]
        ],
        'ModuleNotifications:admin' => [
            '_generic' => [
                'save' => [
                    'url' => woosms_ajax_url(),
                    'params' => ['action' => 'save_admin_notifications']
                ]
            ]
        ],
        'Sign:up' => [
            '_generic' => [
                'register' => [
                    'url' => woosms_ajax_url(),
                    'params' => ['action' => 'register']
                ]
            ]
        ],
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
        'SmsCampaign:campaign' => [
            'campaign' => [
                'loadModuleData' => [
                    'url' => woosms_ajax_url(),
                    'params' => ['action' => 'load_module_data']
                ],
                'saveModuleCustomers' => [
                    'url' => woosms_ajax_url(),
                    'params' => ['action' => 'save_module_customers']
                ],
                'addModuleFilter' => [
                    'url' => woosms_ajax_url(),
                    'params' => ['action' => 'add_module_filter']
                ],
                'removeModuleFilter' => [
                    'url' => woosms_ajax_url(),
                    'params' => ['action' => 'remove_module_filter']
                ]
            ]
        ]
    ];
}
