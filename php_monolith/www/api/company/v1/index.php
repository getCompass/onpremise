<?php

// main
require_once __DIR__ . "/../../../../start.php";

// начинаем работу
showAjax(Application\Entrypoint\ApiV1::processRequest("Company", get("api_method"), $_POST));
	
