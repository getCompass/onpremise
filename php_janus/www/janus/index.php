<?php /** @noinspection PhpUnhandledExceptionInspection */

require_once __DIR__ . "/../../start.php";

// обрабатываем запрос
$result = \Application\Entrypoint\Janus::processRequest(strtolower($_SERVER["REQUEST_METHOD"]), $_POST);
showAjax($result["result"]);
