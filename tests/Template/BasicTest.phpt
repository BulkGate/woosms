<?php declare(strict_types=1);

namespace BulkGate\WooSms\Template\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Mockery;
use Tester\{Assert, Expect, TestCase};
use BulkGate\{Plugin\DI\Container, Plugin\IO\Url, Plugin\Settings\Settings, Plugin\Settings\Synchronizer, Plugin\User\Sign, WooSms\Template\Basic};
use function file_put_contents, file_get_contents, ob_get_contents, ob_start, trim, str_replace;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/.mock-basic.php';

/**
 * @testCase
 */
class BasicTest extends TestCase
{
	private const Generate = false;

	public function testInit(): void
	{
		$container = Mockery::mock(Container::class);
		$container->shouldReceive('getByClass')->with(Sign::class)->once()->andReturn($sign = Mockery::mock(Sign::class));
		$sign->shouldReceive('authenticate')->with(false, Mockery::on(function (array $parameters): bool
		{
			Assert::equal(['expire' => Expect::type('int')], $parameters);
			return true;
		}))->once()->andReturn('jwt.token.451');
		$container->shouldReceive('getByClass')->with(Url::class)->once()->andReturn($url = Mockery::mock(Url::class));
		$container->shouldReceive('getByClass')->with(Settings::class)->once()->andReturn($settings = Mockery::mock(Settings::class));
		$container->shouldReceive('getByClass')->with(Synchronizer::class)->once()->andReturn($synchronizer = Mockery::mock(Synchronizer::class));
		$synchronizer->shouldReceive('getLastSync')->withNoArgs()->once()->andReturn(451);
		$settings->shouldReceive('load')->with('main:dispatcher')->once()->andReturn('asset');
		$settings->shouldReceive('load')->with('main:synchronization')->once()->andReturn('all');
		$settings->shouldReceive('load')->with('main:language')->once()->andReturn('cs');
		$settings->shouldReceive('load')->with('main:language_mutation')->once()->andReturnFalse();
		$settings->shouldReceive('load')->with('main:delete_db')->once()->andReturnFalse();
		$settings->shouldReceive('load')->with('main:address_preference')->once()->andReturn('delivery');
		$settings->shouldReceive('load')->with('main:marketing_message_opt_in_enabled')->once()->andReturnFalse();
		$settings->shouldReceive('load')->with('main:marketing_message_opt_in_label')->once()->andReturn('label');
		$settings->shouldReceive('load')->with('main:marketing_message_opt_in_default')->once()->andReturnFalse();
		$settings->shouldReceive('load')->with('main:marketing_message_opt_in_url')->once()->andReturn('');
		$url->shouldReceive('get')->withNoArgs()->once()->andReturn('https://www.example.com/');
		$url->shouldReceive('get')->with('widget/eshop/load/jwt.token.451?config=init_widget_eshop_load')->once()->andReturn('https://www.example.com/widget/message/send/jwt.token.451?config=init_widget_message_send');
		$url->shouldReceive('get')->with('/images/white-label/bulkgate/logo/short.svg')->once()->andReturn('https://www.example.com/images/white-label/bulkgate/logo/short.svg');

		ob_start();
		Basic::print($container);

		$content = ob_get_contents();

		self::Generate && file_put_contents(__DIR__ . '/basic.html', $content);

		Assert::same(file_get_contents(__DIR__ . '/basic.html'), $content);

		Assert::same(
			'	function init_widget_eshop_load(widget) {		function getHeaders(token) {		    return function () {		        return {		            Authorization: "Bearer " + token		        }		    }		}				widget.authenticator = {		    getHeaders: getHeaders("jwt.token.451"),		    setToken: (token) => {		        widget.authenticator.getHeaders = getHeaders(token);		    },		    authenticate: async () => {		        let response = await fetch(ajaxurl, {		            method: "POST",		            headers: {		                \'Content-Type\': "application/x-www-form-urlencoded"		            },		            body: "action=authenticate&security=nonce_token",		        });		        let {token, redirect} = await response.json();				        if (redirect) {		            return {redirect};		        }		        if (token) {		            widget.authenticator.getHeaders = getHeaders(token);		        }				        return {};		    }		};				widget.merge({		    layout: {		        links: {		            homepage: "/homepage"		        },		        server: {		            application: {"last_sync":"1970-01-01T01:07:31+01:00"},		            application_settings: {"dispatcher":"asset","synchronization":"all","language":"cs","language_mutation":false,"delete_db":false,"address_preference":"delivery","marketing_message_opt_in_enabled":false,"marketing_message_opt_in_label":"label","marketing_message_opt_in_default":false,"marketing_message_opt_in_url":""}		        },		        // static (dictionary) for frontend form		        scope: {		            application_settings: {		                dispatcher: {cron: "dispatcher_cron", asset: "dispatcher_asset", direct: "dispatcher_direct"},		                synchronization: {		                    all: "synchronization_all",		                    message: "synchronization_message",		                    off: "synchronization_off"		                },		                address_preference: {		                    delivery: "address_preference_delivery",		                    invoice: "address_preference_invoice"		                },		            }		        }		    }		});				widget.events.onComputeHostLayout = (compute) => {		    let hostAppBar = document.getElementById("wpadminbar");		    let hostNavBar = document.getElementById("adminmenuback");		    let hostRootWrap = document.getElementById("bulkgate-plugin");				    compute({appBar: hostAppBar, navBar: hostNavBar});				    if (hostRootWrap.parentElement.id === "wpbody-content") { // woosms-module page, otherwise eg. send-sms widget		        let style = getComputedStyle(document.getElementById("wpcontent"));		        hostRootWrap.style.setProperty("--bulkgate-plugin-body-indent", style.getPropertyValue("padding-left"));		    }		};				widget.options.theme = {		    components: {		        BulkGateSignInView: {		            defaultProps: {		                showLanguagePanel: false,		                showPermanentLogin: false,		                logo: "images/white-label/bulkgate/logo/logo-title.svg",		                logo_dark: "images/white-label/bulkgate/logo/logo-white.svg",		                background: "images/products/backgrounds/ws.svg"		            }		        },		        BulkGateSignUpView: {		            defaultProps: {		                showLanguagePanel: false,		                logo: "images/white-label/bulkgate/logo/logo-title.svg",		                logo_dark: "images/white-label/bulkgate/logo/logo-white.svg",		                background: "images/products/backgrounds/ws.svg"		            }		        }		    }		};				widget.options.layout = {		    appBar: {		        showLogOut: false,		        logoUrl: "images/white-label/bulkgate/logo/logo-title.svg",		        logoStyle: {		            height: "28px",		            width: "192px",		        }		    }		};				widget.options.proxy = function (reducerName, requestData) {		    let proxyData = {"PROXY_LOG_IN":{"url":"http:\/\/localhost\/wp-admin\/","params":{"action":"login","security":"nonce_token"}},"PROXY_LOG_OUT":{"url":"http:\/\/localhost\/wp-admin\/","params":{"action":"logout_module","security":"nonce_token"}},"PROXY_SAVE_MODULE_SETTINGS":{"url":"http:\/\/localhost\/wp-admin\/","params":{"action":"save_module_settings","security":"nonce_token"}}};		    let {url, params} = proxyData[requestData.actionType] || {};				    if (url) {		        requestData.contentType = "application/x-www-form-urlencoded";		        requestData.url = url;		        requestData.data = {__bulkgate: requestData.data, ...params};		        return true;		    }				    try {		        // relative -> absolute url conversion. In modules context, relative urls are not suitable. This covers routing (soft redirects change route) and signals (actions).		        let baseUrl = new URL("https:\/\/www.example.com\/"); // bulkgate\'s app url		        url = new URL(requestData.url, baseUrl);		        requestData.url = url.toString();		        return true;		    } catch {		    }		};				// loading management		function handleViewRender(ev) {		    const loading = document.querySelector("#bulkgate-plugin #loading");		    loading.style.display = "none";				    //remove handler after hide loader		    window.removeEventListener("gate-view-render", handleViewRender);		}				window.addEventListener("gate-view-render", handleViewRender);	}',
			str_replace(["\r", "\n"], [''], $GLOBALS['script'][0])
		);

		Assert::same([
			'src' => '|https://www.example.com/widget/message/send/jwt.token.451?config=init_widget_message_send|',
			'async' => true,
		], $GLOBALS['script'][1]);

		Mockery::close();
	}
}

(new BasicTest())->run();
