<?php

// main
require_once __DIR__ . "/../../../../start.php";

showAjax(Application\Entrypoint\Userbot::processRequest("Thread", get("api_method"), $_POST));
