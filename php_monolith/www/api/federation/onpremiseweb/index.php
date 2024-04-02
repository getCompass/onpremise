<?php

// main
require_once __DIR__ . "/../../../../start.php";

// начинаем работу
showAjax(Application\Entrypoint\OnPremiseWeb::processRequest("Federation", get("api_method"), $_POST));