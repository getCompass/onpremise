<?php

use Compass\Pivot\Type_Api_Platform;
use Compass\Pivot\Type_Captcha_EnterpriseGoogle;

require_once __DIR__ . "/../../../start.php";

$captcha_class = new Type_Captcha_EnterpriseGoogle();
$captcha_key   = $captcha_class->getPublicCaptchaKey(Type_Api_Platform::PLATFORM_ELECTRON);
?>

<html>
<head>
	<title>Captcha</title>
	<script type="text/javascript">

		// подключаем ipcrender электрон сборки
		const IPCRENDERER = require('electron').ipcRenderer;

		// отправка события с ответом токена после проверки
		var verifyCallback = function (response) {
			IPCRENDERER.send('captcha-entered', response);
		};

		// рендерим капчу в div, указывая публичный ключ электрона
		var onloadCallback = function () {
			grecaptcha.enterprise.render('path_to_captcha', {
				'sitekey': '<?php echo $captcha_key ?>',
				'callback': verifyCallback,
			});
		};
	</script>
</head>
<body>
<div id="path_to_captcha" style="display: flex; justify-content: center;"></div>

<script src="https://www.google.com/recaptcha/enterprise.js?onload=onloadCallback&render=explicit"
	async defer>
</script>

</body>
</html>