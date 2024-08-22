<?php

use Compass\Pivot\Type_Api_Platform;
use Compass\Pivot\Type_Captcha_YandexCloud;

require_once __DIR__ . "/../../../start.php";

$captcha_class = new Type_Captcha_YandexCloud();
$captcha_key   = $captcha_class->getPublicCaptchaKey(Type_Api_Platform::PLATFORM_ELECTRON);
?>

<html>
<head>
	<script type="text/javascript">

		// подключаем ipcrender электрон сборки
		const IPCRENDERER = require('electron').ipcRenderer;

		// отправка события с ответом токена после проверки
		var verifyCallback = function (response) {
			IPCRENDERER.send('captcha-entered', response);
		};

		// рендерим капчу в div, указывая публичный ключ электрона
		var onloadFunction = function () {

			if (!window.smartCaptcha) {
				return;
			}

			window.smartCaptcha.render('path_to_captcha_container', {
				sitekey: '<?php echo $captcha_key; ?>',
				invisible: false, // Сделать капчу невидимой
				'callback': verifyCallback,
			});
		};
	</script>
</head>
<body>

<form id="path_to_captcha_form" style="display: flex; justify-content: center;">
	<div id="path_to_captcha_container"></div>
</form>

<script
	src="https://smartcaptcha.yandexcloud.net/captcha.js?render=onload&onload=onloadFunction"
	async defer>
</script>

</body>
</html>