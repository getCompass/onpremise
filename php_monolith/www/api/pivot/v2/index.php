<?php

// main
require_once __DIR__ . "/../../../../start.php";

// начинаем работу
showAjax(Application\Entrypoint\ApiV2::processRequest("Pivot", get("api_method"), $_POST));
	
