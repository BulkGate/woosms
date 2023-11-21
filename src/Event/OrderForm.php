<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\{Plugin\Settings\Settings, Plugin\Strict, WooSms\DI\Factory, WooSms\Utils\Escape};
use function woocommerce_form_field, substr;

class OrderForm
{
	use Strict;

	public const DefaultEnabled = false;

	private const Consent = [
		'en' => 'I consent to receiving marketing communications via SMS, Viber, RCS, WhatsApp, and other similar channels.',
		'cs' => 'Souhlasím se zasíláním marketingových sdělení prostřednictvím SMS, Viber, RCS, WhatsApp a dalších podobných kanálů.',
		'sk' => 'Súhlasím so zasielaním marketingových správ prostredníctvom SMS, Viber, RCS, WhatsApp a ďalších podobných kanálov.',
		'de' => 'Ich stimme dem Erhalt von Marketingkommunikation per SMS, Viber, RCS, WhatsApp und anderen ähnlichen Kanälen zu.',
		'fr' => 'J\'accepte de recevoir des communications marketing par SMS, Viber, RCS, WhatsApp et autres canaux similaires.',
		'it' => 'Acconsento a ricevere comunicazioni di marketing tramite SMS, Viber, RCS, WhatsApp e altri canali simili.',
		'es' => 'Consiento recibir comunicaciones de marketing a través de SMS, Viber, RCS, WhatsApp y otros canales similares.',
		'pl' => 'Wyrażam zgodę na otrzymywanie komunikacji marketingowej za pośrednictwem SMS, Viber, RCS, WhatsApp i innych podobnych kanałów.',
		'no' => 'Jeg samtykker i å motta markedsføringskommunikasjon via SMS, Viber, RCS, WhatsApp og andre lignende kanaler.',
		'dk' => 'Jeg giver samtykke til at modtage markedsføringskommunikation via SMS, Viber, RCS, WhatsApp og andre lignende kanaler.',
		'ru' => 'Я согласен получать маркетинговые сообщения по SMS, Viber, RCS, WhatsApp и другим подобным каналам.',
		'bg' => 'Съгласявам се да получавам маркетингови съобщения чрез SMS, Viber, RCS, WhatsApp и други подобни канали.',
		'ro' => 'Sunt de acord să primesc comunicări de marketing prin SMS, Viber, RCS, WhatsApp și alte canale similare.',
		'hu' => 'Hozzájárulok ahhoz, hogy marketingkommunikációt kapjak SMS, Viber, RCS, WhatsApp és más hasonló csatornákon keresztül.',
		'hr' => 'Slažem se da primam marketinške komunikacije putem SMS-a, Vibera, RCS-a, WhatsApp-a i drugih sličnih kanala.',
		'lt' => 'Sutinku gauti rinkodaros pranešimus per SMS, Viber, RCS, WhatsApp ir kitus panašius kanalus.',
		'lv' => 'Es piekrītu saņemt mārketinga komunikāciju caur SMS, Viber, RCS, WhatsApp un citiem līdzīgiem kanāliem.',
		'et' => 'Nõustun turundussõnumite saamisega SMS-i, Viberi, RCS-i, WhatsAppi ja muude sarnaste kanalite kaudu.',
		'fi' => 'Suostun vastaanottamaan markkinointiviestintää SMS: n, Viberin, RCS: n, WhatsAppin ja muiden vastaavien kanavien kautta.',
		'el' => 'Συμφωνώ να λαμβάνω μάρκετινγκ επικοινωνίας μέσω SMS, Viber, RCS, WhatsApp και άλλων παρόμοιων καναλιών.',
		'nl' => 'Ik geef toestemming om marketingcommunicatie te ontvangen via SMS, Viber, RCS, WhatsApp en andere vergelijkbare kanalen.',
		'pt' => 'Eu concordo em receber comunicações de marketing via SMS, Viber, RCS, WhatsApp e outros canais semelhantes.',
		'sl' => 'Strinjam se, da prejemam trženjska sporočila prek SMS, Viber, RCS, WhatsApp in drugih podobnih kanalov.',
		'sv' => 'Jag samtycker till att få marknadsföringskommunikation via SMS, Viber, RCS, WhatsApp och andra liknande kanaler.',
		'uk' => 'Я згоден отримувати маркетингові повідомлення за допомогою SMS, Viber, RCS, WhatsApp та інших подібних каналів.',
		'ja' => 'SMS、Viber、RCS、WhatsAppなどの類似したチャネルを介してマーケティングコミュニケーションを受け取ることに同意します。',
		'zh' => '我同意通过SMS，Viber，RCS，WhatsApp和其他类似渠道接收营销通信。',
		'ko' => 'SMS, Viber, RCS, WhatsApp 및 기타 유사한 채널을 통해 마케팅 커뮤니케이션을 수신하는 데 동의합니다.',
		'th' => 'ฉันยินยอมให้รับการสื่อสารทางการตลาดผ่าน SMS, Viber, RCS, WhatsApp และช่องทางที่คล้ายกัน',
		'vi' => 'Tôi đồng ý nhận thông tin tiếp thị qua SMS, Viber, RCS, WhatsApp và các kênh tương tự.',
		'ar' => 'أوافق على تلقي الاتصالات التسويقية عبر الرسائل القصيرة وفايبر و RCS و WhatsApp وغيرها من القنوات المماثلة.',
		'he' => 'אני מסכים לקבל תקשורת שיווקית באמצעות SMS, Viber, RCS, WhatsApp וערוצים דומים אחרים.',
		'fa' => 'من موافقم که از طریق پیامک، وایبر، RCS، WhatsApp و سایر کانال های مشابه ارتباطات بازاریابی دریافت کنم.',
		'hi' => 'मैं एसएमएस, वाइबर, आरसीएस, व्हाट्सएप और अन्य समान चैनलों के माध्यम से विपणन संचार प्राप्त करने के लिए सहमत हूं।',
		'mr' => 'मी एसएमएस, वायबर, आरसीएस, व्हाट्सएप आणि इतर सारख्या चॅनेल्सद्वारे मार्केटिंग संवाद प्राप्त करण्यास सहमत आहे.',
		'bn' => 'আমি এসএমএস, ভাইবার, আরসিএস, ওয়াটসঅ্যাপ এবং অন্যান্য অনুরূপ চ্যানেল দ্বারা মার্কেটিং যোগাযোগ পেতে সম্মত আছি।',
		'pa' => 'ਮੈਂ SMS, Viber, RCS, WhatsApp ਅਤੇ ਹੋਰ ਸਮਾਨ ਚੈਨਲਾਂ ਦੁਆਰਾ ਮਾਰਕੀਟਿੰਗ ਸੰਚਾਰ ਪ੍ਰਾਪਤ ਕਰਨ ਲਈ ਸਹਿਮਤ ਹਾਂ।',
		'gu' => 'હું SMS, Viber, RCS, WhatsApp અને અન્ય સમાન ચેનલ્સ દ્વારા માર્કેટિંગ સંદેશ પ્રાપ્ત કરવા માટે સંમત છું.',
		'ta' => 'எனது அனுபவத்தின் பகுதியாக SMS, Viber, RCS, WhatsApp மற்றும் போன்ற பொதுவான சேனல்களைப் பயன்படுத்தி சம்பந்தப்பட்ட விளம்பரப் பேச்சைப் பெறுவதற்கு என்ன நினைக்கிறீர்கள்.',
		'kn' => 'ನಾನು SMS, Viber, RCS, WhatsApp ಮತ್ತು ಇತರ ಸದೃಶ ಚಾನಲ್ಸ್ ಮೂಲಕ ಮಾರ್ಕೆಟಿಂಗ್ ಸಂಪರ್ಕವನ್ನು ಸ್ವೀಕರಿಸುತ್ತೇನೆ.',
		'te' => 'నేను SMS, Viber, RCS, WhatsApp మరియు ఇతర సాదరణ ఛానల్స్ ద్వారా మార్కెటింగ్ సంప్రదించడానికి సమ్మతిస్తున్నాను.',
		'ml' => 'ഞാൻ SMS, Viber, RCS, WhatsApp എന്നിവയിൽ പരസ്യപ്പെടുന്ന സമാന ചാനലുകളിൽ മാർക്കറ്റിംഗ് സന്ദേശങ്ങൾ ലഭിക്കുന്നതിന് സമ്മതിക്കുന്നു.',
	];

	public static function init(string $locale): void
	{
		add_action('woocommerce_review_order_before_submit', function () use ($locale): void
		{
			$settings = Factory::get()->getByClass(Settings::class);

			if ($settings->load('main:marketing_message_opt_in_enabled') ?? self::DefaultEnabled)
			{
				$text = $settings->load('main:marketing_message_opt_in_label');

				if (empty($text))
				{
					$text = self::Consent[self::getLocale($locale)] ?? self::Consent['en'];
				}

				$url = $settings->load('main:marketing_message_opt_in_url');

				woocommerce_form_field('bulkgate_marketing_message_opt_in', [
					'type' => 'checkbox',
					'class' => ['form-row mycheckbox'],
					'label_class' => ['woocommerce-form__label woocommerce-form__label-for-checkbox checkbox'],
					'input_class' => ['woocommerce-form__input woocommerce-form__input-checkbox input-checkbox'],
					'required' => false,
					'default' => $settings->load('main:marketing_message_opt_in_default') ?? false,
					'description' => $url ? '<br><a href="' . Escape::htmlAttr($url) . '" target="_blank">' . Escape::html($url) . '</a>' : '',
					'label' => Escape::html($text),
				]);
			}
		});
	}


	private static function getLocale(string $locale): string
	{
		return substr($locale, 0, 2) ?: 'en';
	}
}
