<?php declare(strict_types=1);

/**
 * Back office plugin
 * PHP version 7.4
 *
 * @category BulkGate Plugin
 * @package  BulkGate
 * @author   Lukáš Piják <pijak@bulkgate.com>
 * @license  GNU General Public License v3.0
 * @link     https://www.bulkgate.com/
 */

use BulkGate\WooSms\{Ajax\Authenticate, Ajax\PluginSettingsChange, Debug\Page, DI\Factory, Event\OrderForm, Utils\Escape, Utils\Logo, Utils\Meta};
use BulkGate\Plugin\{Debug\Logger, Debug\Requirements, Eshop, IO, Settings\Settings, Settings\Synchronizer, User, User\Sign, Utils\JsonResponse};

if (!defined('ABSPATH'))
{
    exit;
}

add_action('admin_menu', function (): void
{
	add_management_page(
		'BulkGate Debug',
		'BulkGate Debug',
		'manage_options',
		'bulkgate-debug',
		fn () => Page::print(Factory::get()->getByClass(Logger::class), Factory::get()->getByClass(Requirements::class))
	);

    add_menu_page('bulkgate', 'BulkGate SMS', 'manage_options', 'bulkgate', function (): void
    {
	    Factory::get()->getByClass(Eshop\EshopSynchronizer::class)->run();

	    Woosms_Print_admin();
        $di = Factory::get();
        $url = $di->getByClass(IO\Url::class);
        $logo = $url->get('/images/white-label/bulkgate/logo/short.svg');
        echo <<<HTML
            <style>
                @keyframes logo {
                    0% {
                        filter: grayscale(1) opacity(.2);
                        transform: scale(.6);
                    }
                    25% {
                        filter: none;
                        transform: scale(.65);
                    }
                    70% {
                        transform: none;
                    }
                }
                @keyframes heading {
                    0% {
                        opacity: .1;
                    }
                    50% {
                        opacity: .8;
                    }                 
                    100% {
                        opacity: 1;
                    }
                }
                @keyframes progress {
                100% {opacity: 1};
                }
                @keyframes progress-processing{
                0%{transform:translateX(-300px)}5%{transform:translateX(-240px)}15%{transform:translateX(-30px)}25%{transform:translateX(-30px)}30%{transform:translateX(-20px)}45%{transform:translateX(-20px)}50%{transform:translateX(-15px)}65%{transform:translateX(-15px)}70%{transform:translateX(-10px)}95%{transform:translateX(-10px)}100%{transform:translateX(-5px)}
                }
                #bulkgate-plugin {
                    position: relative;
                    z-index: 0;
                    margin-left: calc(var(--bulkgate-plugin-body-indent, 0) * -1);
                }
                #bulkgate-plugin #loading {
                    position: fixed;
                    contain: layout;
                    left: 0;
                    top: 0;
                    right: 0;
                    bottom: 0;
                    background: #fff;
                    z-index: 2999;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    text-align: center;
                }
                #bulkgate-plugin #loading img {
                    width: 160px;
                    animation: logo 1.5s .3s both;
                    margin: 24px 0;
                }              
                #bulkgate-plugin #loading h3 {
                    font-size: 32px;
                    color: #606469;
                    animation: heading .5s .675s both;
                }
                #bulkgate-plugin #progress {
                    animation: progress .5s 2.5s 1 both;
                    height: 4px;
                    width: 100%;
                    opacity: 0;
                    background: #ddd;
                    position: relative;
                    overflow: hidden;
                }
                #bulkgate-plugin #progress:before {
                    animation: progress-processing 20s 3s linear both;
                    background-color: var(--secondary);
                    content: '';
                    display: block;
                    height: 100%;
                    position: absolute;
                    transform: translateX(-300px);
                    width: 100%;
                }
                gate-ecommerce-plugin {
                    box-sizing: border-box; /* realne se tyka pouze web-componenty */
                }
            </style>
            <div id="bulkgate-plugin" style="--primary: #955a89; --secondary: #0094F0; --content: #f1f1f1;">
                <gate-ecommerce-plugin>
                    
                </gate-ecommerce-plugin>
                <div id="loading">
                    <div>
                        <img src="$logo" />
                        <div id="progress"></div>
                        <!--h3>BulkGate <span style="color: var(--secondary);">SMS</span> plugin</h3-->
                    </div>
                </div>
            </div>
        HTML;
    }, Logo::Menu, 58);
    add_filter('plugin_action_links', [Meta::class, 'settingsLink'], 10, 2);
    add_filter('plugin_row_meta', [Meta::class, 'links'], 10, 2);
});

add_action('wp_ajax_authenticate', fn () => Factory::get()->getByClass(Authenticate::class)->run(admin_url('admin.php?page=bulkgate#/sign/in')));

add_action('wp_ajax_login', fn () => JsonResponse::send(Factory::get()->getByClass(Sign::class)->in(
	sanitize_text_field((string) ($_POST['__bulkgate']['email'] ?? '')),
	sanitize_text_field((string) ($_POST['__bulkgate']['password'] ?? '')),
	admin_url('admin.php?page=bulkgate#/dashboard')
)));

add_action('wp_ajax_logout_module', fn () => JsonResponse::send(Factory::get()->getByClass(Sign::class)->out(admin_url('admin.php?page=bulkgate#/sign/in'))));
add_action('wp_ajax_save_module_settings', fn () => JsonResponse::send(Factory::get()->getByClass(PluginSettingsChange::class)->run($_POST['__bulkgate'] ?? [])));

add_action(
    'add_meta_boxes', function ($post_type) {

        if ($post_type === 'shop_order' && Factory::get()->getByClass(Settings::class)->load('static:application_token'))
		{
			add_meta_box('bulkgate_send_message', 'BulkGate SMS', function () {
                Woosms_Print_widget("widget/message/send", fn() => <<<JS
    function(widget) {
        widget.options.SendMessageProps = {
            message: "Muj injectnuty message",
            recipients: [{first_name: "John", last_name: "Doe", phone_mobile: 12345678}]
        };
    }
JS
);
                print("<gate-send-message></gate-send-message>");
            }, 'shop_order', 'side', 'high');
            /*add_meta_box(
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
            );*/
        }
    }
);


function Woosms_Print_widget(string $endpoint, callable $configuration): void
{
    $di = Factory::get();

    $url = $di->getByClass(IO\Url::class);
    $user = $di->getByClass(User\Sign::class);
    $jwt = $user->authenticate();

    $init_fn_name = 'init_' . str_replace("/", "_", $endpoint);
    $init_fn_content = $configuration($jwt);

    wp_print_inline_script_tag(<<<JS
    
    // this configuration function is called from bootstrap
    function $init_fn_name(widget){
        ($init_fn_content)(widget); //invoke javascript function
    }
JS
);
    wp_print_script_tag([
        'src' => Escape::url($url->get("$endpoint/$jwt?config=" . $init_fn_name)),
        'async' => true,
    ]);
}


function Woosms_Print_admin(): void
{
	$di = Factory::get();

	$url = admin_url('/admin-ajax.php', is_ssl() ? 'https' : 'http');

	$proxy = [
		'PROXY_LOG_IN' => [
			'url' => $url,
			'params' => ['action' => 'login']
		],
		'PROXY_LOG_OUT' => [
			'url' => $url,
			'params' => ['action' => 'logout_module']
		],
		'PROXY_SAVE_MODULE_SETTINGS' => [
			'url' => $url,
			'params' => ['action' => 'save_module_settings']
		]
	];

	$settings = $di->getByClass(Settings::class);

	$application = [
		'last_sync' => date('c', $di->getByClass(Synchronizer::class)->getLastSync()),
	];

	$plugin_settings = [
		'dispatcher' => $settings->load('main:dispatcher') ?? 'cron',
		'synchronization' => $settings->load('main:synchronization') ?? 'all',
		'language' => $settings->load('main:language') ?? 'en',
		'language_mutation' => $settings->load('main:language_mutation') ?? false,
		'delete_db' => $settings->load('main:delete_db') ?? false,
		'address_preference' => $settings->load('main:address_preference') ?? 'delivery',
		'marketing_message_opt_in_enabled' => $settings->load('main:marketing_message_opt_in_enabled') ?? OrderForm::DefaultEnabled,
		'marketing_message_opt_in_label' => $settings->load('main:marketing_message_opt_in_label') ?? '',
		'marketing_message_opt_in_default' => $settings->load('main:marketing_message_opt_in_default') ?? false,
		'marketing_message_opt_in_url' => $settings->load('main:marketing_message_opt_in_url') ?? '',
	];

    $url = $di->getByClass(IO\Url::class);

    $escape_js = [Escape::class, 'js'];

    Woosms_Print_widget("widget/eshop/load", fn(string $jwt) => <<<JS
    function(widget) {
        function getHeaders(token) {
            return function () {
                return {
                    Authorization: "Bearer " + token
                }
            }
        }
        
        widget.authenticator = {
            getHeaders: getHeaders({$escape_js($jwt)}),
            setToken: (token) => {
                widget.authenticator.getHeaders = getHeaders(token);
            },
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

        widget.merge({
            layout: {
                server: {
                    application: {$escape_js($application)},
                    application_settings: {$escape_js($plugin_settings)}
                },
                // static (dictionary) for frontend form
                scope: {
                    application_settings: {
                        dispatcher: {cron: "dispatcher_cron", asset: "dispatcher_asset", direct: "dispatcher_direct"},
                        synchronization: {all: "synchronization_all", message: "synchronization_message", off: "synchronization_off"},
                        address_preference: {delivery: "address_preference_delivery", invoice: "address_preference_invoice"},
                    }
                }
            }
        });
        
        widget.events.onComputeHostLayout = (compute) => {
            let hostAppBar = document.getElementById("wpadminbar");
            let hostNavBar = document.getElementById("adminmenuback");
            let hostRootWrap = document.getElementById("bulkgate-plugin");
            
            compute({appBar: hostAppBar, navBar: hostNavBar});
            
            if (hostRootWrap.parentElement.id === "wpbody-content") { // woosms-module page, otherwise eg. send-sms widget
                let style = getComputedStyle(document.getElementById("wpcontent"));
                hostRootWrap.style.setProperty("--bulkgate-plugin-body-indent", style.getPropertyValue("padding-left"));
            }
        };
        
        widget.options.theme = {
            components: {
                BulkGateSignInView: {
                    defaultProps: {
                        showLanguagePanel: false,
                        showPermanentLogin: false,
                        logo: "images/white-label/bulkgate/logo/logo-title.svg",
                        logo_dark: "images/white-label/bulkgate/logo/logo-white.svg",
                        background: "images/products/backgrounds/ws.svg"
                    }
                },
                BulkGateSignUpView: {
                    defaultProps: {
                        showLanguagePanel: false,
                        logo: "images/white-label/bulkgate/logo/logo-title.svg",
                        logo_dark: "images/white-label/bulkgate/logo/logo-white.svg",
                        background: "images/products/backgrounds/ws.svg"
                    }
                }
            }
        };
        
        widget.options.layout = {
            appBar: {
                showLogOut: false,
                logoUrl: "images/products/bg.svg",
                logoStyle: {
                    height: "40px",
                    width: "100px",
                }
            }
        };
        
        widget.options.proxy = function(reducerName, requestData) {
            let proxyData = {$escape_js($proxy)};
            let {url, params} = proxyData[requestData.actionType] || {};
        
            if (url) {
                requestData.contentType = "application/x-www-form-urlencoded";
                requestData.url = url;
                requestData.data = {__bulkgate: requestData.data, ...params};
                return true;
            }
            
            try {
                // relative -> absolute url conversion. In modules context, relative urls are not suitable. This covers routing (soft redirects change route) and signals (actions).
                let baseUrl = new URL({$escape_js($url->get())}); // bulkgate's app url
                url = new URL(requestData.url, baseUrl);
                requestData.url = url.toString();
                return true;
            } catch {}
        };
        
        // loading management
        function handleViewRender(ev) {
            const loading = document.querySelector("#bulkgate-plugin #loading");
            loading.style.display = "none";
            
            //remove handler after hide loader
            window.removeEventListener("gate-view-render", handleViewRender);
        }
        
        window.addEventListener("gate-view-render", handleViewRender);
    }
JS
    );
}
