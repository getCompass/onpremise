<?php

require_once __DIR__ . "/../../start.php";
require_once __DIR__ . "/../../private/public.php";

?>

<html>
<head>
	<title>Captcha</title>
	<script src="https://www.google.com/recaptcha/api.js" defer=""></script>
</head>
<body>
<div id="path_to_captcha" style="display: flex; justify-content: center;"></div>

<script>

	// ждем когда окно загрузится чтобы отрисовать капчу
	window.addEventListener('load', function () {
		grecaptcha.render('path_to_captcha', parameter_list);
	});

	// для отправки сообщения в главный процесс
	const IPCRENDERER = require('electron').ipcRenderer;

	// параметры для капчи гугл
	// тут лучше var чтобы не вышло так let попыталась переопределиться
	var parameter_list = {
		sitekey : '<?php echo PUBLIC_CAPTCHA_SITE ?>',
		callback: function (data) {

			IPCRENDERER.send('captcha-entered', data);
		}
	};

</script>
</body>
</html>