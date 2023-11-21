<?php declare(strict_types=1);

namespace BulkGate\WooSms\Template;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use function time, date, admin_url, is_ssl, wp_print_inline_script_tag, wp_print_script_tag;
use BulkGate\{Plugin\Event\Dispatcher, Plugin\IO\Url, Plugin\Settings\Settings, Plugin\Settings\Synchronizer, Plugin\Strict, Plugin\DI\Container, Plugin\User\Sign, WooSms\Event\Helpers, WooSms\Utils\Escape, WooSms\Event\OrderForm, WooSms\Utils\Logo};

class Basic
{
	use Strict;

	public static function print(Container $di): void
	{
		$jwt = $di->getByClass(Sign::class)->authenticate(false, ['expire' => time() + 300]);
		$url = $di->getByClass(Url::class);
		$escape_js = [Escape::class, 'js'];

		$ajax_url = admin_url('/admin-ajax.php', is_ssl() ? 'https' : 'http');
        $csfr_token = wp_create_nonce();

		$proxy = [
			'PROXY_LOG_IN' => [
				'url' => $ajax_url,
				'params' => ['action' => 'login', Helpers::CrossSiteRequestForgerySecurityParameter => $csfr_token]
			],
			'PROXY_LOG_OUT' => [
				'url' => $ajax_url,
				'params' => ['action' => 'logout_module', Helpers::CrossSiteRequestForgerySecurityParameter => $csfr_token]
			],
			'PROXY_SAVE_MODULE_SETTINGS' => [
				'url' => $ajax_url,
				'params' => ['action' => 'save_module_settings', Helpers::CrossSiteRequestForgerySecurityParameter => $csfr_token]
			]
		];

		$settings = $di->getByClass(Settings::class);

		$application = [
			'last_sync' => date('c', $di->getByClass(Synchronizer::class)->getLastSync()),
		];

		$plugin_settings = [
			'dispatcher' => $settings->load('main:dispatcher') ?? Dispatcher::$default_dispatcher,
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

		wp_print_inline_script_tag(<<<JS
			function init_widget_eshop_load(widget) {
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
				            body: {$escape_js('action=authenticate&' . Helpers::CrossSiteRequestForgerySecurityParameter . "=$csfr_token")},
				        });
				        let {token, redirect} = await response.json();
				
				        if (redirect) {
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
				        links: {
				            homepage: "/homepage"
				        },
				        server: {
				            application: {$escape_js($application)},
				            application_settings: {$escape_js($plugin_settings)}
				        },
				        // static (dictionary) for frontend form
				        scope: {
				            application_settings: {
				                dispatcher: {cron: "dispatcher_cron", asset: "dispatcher_asset", direct: "dispatcher_direct"},
				                synchronization: {
				                    all: "synchronization_all",
				                    message: "synchronization_message",
				                    off: "synchronization_off"
				                },
				                address_preference: {
				                    delivery: "address_preference_delivery",
				                    invoice: "address_preference_invoice"
				                },
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
				        logoUrl: "images/white-label/bulkgate/logo/logo-title.svg",
				        logoStyle: {
				            height: "28px",
				            width: "192px",
				        }
				    }
				};
				
				widget.options.proxy = function (reducerName, requestData) {
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
				    } catch {
				    }
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
		JS);

		wp_print_script_tag([
			'src' => Escape::url($url->get("widget/eshop/load/$jwt?config=init_widget_eshop_load")),
			'async' => true,
		]);

        $logo = $url->get('/images/white-label/bulkgate/logo/short.svg');

		?>

        <style>
            <?php echo Logo::CssLoader; ?>
        </style>

		<div id="bulkgate-plugin" style="--primary: #955a89; --secondary: #0094F0;">
            <gate-ecommerce-plugin data-theme='{"palette": {"mode": "light", "background": {"default": "#f1f1f1"}}}'></gate-ecommerce-plugin>
			<div id="loading">
				<div>
					<img src="<?php echo Escape::htmlAttr($logo); ?>" alt="BulkGate" />
					<div id="progress"></div>
				</div>
			</div>
		</div>

		<?php
	}
}
