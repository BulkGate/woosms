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
		$settings->shouldReceive('load')->with('main:marketing_message_opt_in_url')->once()->andReturnNull();
		$url->shouldReceive('get')->withNoArgs()->once()->andReturn('https://www.example.com/');
		$url->shouldReceive('get')->with('widget/eshop/load/jwt.token.451?config=init_widget_eshop_load')->once()->andReturn('https://www.example.com/widget/message/send/jwt.token.451?config=init_widget_message_send');
		$url->shouldReceive('get')->with('/images/white-label/bulkgate/logo/short.svg')->once()->andReturn('https://www.example.com/images/white-label/bulkgate/logo/short.svg');

		ob_start();
		Basic::print($container);

		$content = trim(str_replace(["\r", "\n"], ["\n"], ob_get_contents()));

		self::Generate && file_put_contents(__DIR__ . '/basic.html', $content);

		Assert::same(file_get_contents(__DIR__ . '/basic.html'), $content);

		Assert::same([
			"	function init_widget_eshop_load(widget) {\r\n		function getHeaders(token) {\r\n		    return function () {\r\n		        return {\r\n		            Authorization: \"Bearer \" + token\r\n		        }\r\n		    }\r\n		}\r\n		\r\n		widget.authenticator = {\r\n		    getHeaders: getHeaders(\"jwt.token.451\"),\r\n		    setToken: (token) => {\r\n		        widget.authenticator.getHeaders = getHeaders(token);\r\n		    },\r\n		    authenticate: async () => {\r\n		        let response = await fetch(ajaxurl, {\r\n		            method: \"POST\",\r\n		            headers: {\r\n		                'Content-Type': \"application/x-www-form-urlencoded\"\r\n		            },\r\n		            body: \"action=authenticate\",\r\n		        });\r\n		        let {token, redirect} = await response.json();\r\n		\r\n		        if (redirect) {\r\n		            return {redirect};\r\n		        }\r\n		        if (token) {\r\n		            widget.authenticator.getHeaders = getHeaders(token);\r\n		        }\r\n		\r\n		        return {};\r\n		    }\r\n		};\r\n		\r\n		widget.merge({\r\n		    layout: {\r\n		        server: {\r\n		            application: {\"last_sync\":\"1970-01-01T01:07:31+01:00\"},\r\n		            application_settings: {\"dispatcher\":\"asset\",\"synchronization\":\"all\",\"language\":\"cs\",\"language_mutation\":false,\"delete_db\":false,\"address_preference\":\"delivery\",\"marketing_message_opt_in_enabled\":false,\"marketing_message_opt_in_label\":\"label\",\"marketing_message_opt_in_default\":false,\"marketing_message_opt_in_url\":\"\"}\r\n		        },\r\n		        // static (dictionary) for frontend form\r\n		        scope: {\r\n		            application_settings: {\r\n		                dispatcher: {cron: \"dispatcher_cron\", asset: \"dispatcher_asset\", direct: \"dispatcher_direct\"},\r\n		                synchronization: {\r\n		                    all: \"synchronization_all\",\r\n		                    message: \"synchronization_message\",\r\n		                    off: \"synchronization_off\"\r\n		                },\r\n		                address_preference: {\r\n		                    delivery: \"address_preference_delivery\",\r\n		                    invoice: \"address_preference_invoice\"\r\n		                },\r\n		            }\r\n		        }\r\n		    }\r\n		});\r\n		\r\n		widget.events.onComputeHostLayout = (compute) => {\r\n		    let hostAppBar = document.getElementById(\"wpadminbar\");\r\n		    let hostNavBar = document.getElementById(\"adminmenuback\");\r\n		    let hostRootWrap = document.getElementById(\"bulkgate-plugin\");\r\n		\r\n		    compute({appBar: hostAppBar, navBar: hostNavBar});\r\n		\r\n		    if (hostRootWrap.parentElement.id === \"wpbody-content\") { // woosms-module page, otherwise eg. send-sms widget\r\n		        let style = getComputedStyle(document.getElementById(\"wpcontent\"));\r\n		        hostRootWrap.style.setProperty(\"--bulkgate-plugin-body-indent\", style.getPropertyValue(\"padding-left\"));\r\n		    }\r\n		};\r\n		\r\n		widget.options.theme = {\r\n		    components: {\r\n		        BulkGateSignInView: {\r\n		            defaultProps: {\r\n		                showLanguagePanel: false,\r\n		                showPermanentLogin: false,\r\n		                logo: \"images/white-label/bulkgate/logo/logo-title.svg\",\r\n		                logo_dark: \"images/white-label/bulkgate/logo/logo-white.svg\",\r\n		                background: \"images/products/backgrounds/ws.svg\"\r\n		            }\r\n		        },\r\n		        BulkGateSignUpView: {\r\n		            defaultProps: {\r\n		                showLanguagePanel: false,\r\n		                logo: \"images/white-label/bulkgate/logo/logo-title.svg\",\r\n		                logo_dark: \"images/white-label/bulkgate/logo/logo-white.svg\",\r\n		                background: \"images/products/backgrounds/ws.svg\"\r\n		            }\r\n		        }\r\n		    }\r\n		};\r\n		\r\n		widget.options.layout = {\r\n		    appBar: {\r\n		        showLogOut: false,\r\n		        logoUrl: \"images/products/bg.svg\",\r\n		        logoStyle: {\r\n		            height: \"40px\",\r\n		            width: \"100px\",\r\n		        }\r\n		    }\r\n		};\r\n		\r\n		widget.options.proxy = function (reducerName, requestData) {\r\n		    let proxyData = {\"PROXY_LOG_IN\":{\"url\":\"http:\\/\\/localhost\\/wp-admin\\/\",\"params\":{\"action\":\"login\"}},\"PROXY_LOG_OUT\":{\"url\":\"http:\\/\\/localhost\\/wp-admin\\/\",\"params\":{\"action\":\"logout_module\"}},\"PROXY_SAVE_MODULE_SETTINGS\":{\"url\":\"http:\\/\\/localhost\\/wp-admin\\/\",\"params\":{\"action\":\"save_module_settings\"}}};\r\n		    let {url, params} = proxyData[requestData.actionType] || {};\r\n		\r\n		    if (url) {\r\n		        requestData.contentType = \"application/x-www-form-urlencoded\";\r\n		        requestData.url = url;\r\n		        requestData.data = {__bulkgate: requestData.data, ...params};\r\n		        return true;\r\n		    }\r\n		\r\n		    try {\r\n		        // relative -> absolute url conversion. In modules context, relative urls are not suitable. This covers routing (soft redirects change route) and signals (actions).\r\n		        let baseUrl = new URL(\"https:\\/\\/www.example.com\\/\"); // bulkgate's app url\r\n		        url = new URL(requestData.url, baseUrl);\r\n		        requestData.url = url.toString();\r\n		        return true;\r\n		    } catch {\r\n		    }\r\n		};\r\n		\r\n		// loading management\r\n		function handleViewRender(ev) {\r\n		    const loading = document.querySelector(\"#bulkgate-plugin #loading\");\r\n		    loading.style.display = \"none\";\r\n		\r\n		    //remove handler after hide loader\r\n		    window.removeEventListener(\"gate-view-render\", handleViewRender);\r\n		}\r\n		\r\n		window.addEventListener(\"gate-view-render\", handleViewRender);\r\n	}",
			[
				'src' => '|https://www.example.com/widget/message/send/jwt.token.451?config=init_widget_message_send|',
				'async' => true,
			],
		], $GLOBALS['script']);
	}
}

(new BasicTest())->run();
