<?php

// main
require_once __DIR__ . "/../../../../start.php";

// начинаем работу
showAjax(ApiV1_Handler::doStart(get("api_method"), $_POST));
	
