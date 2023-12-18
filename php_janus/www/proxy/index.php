<?php /** @noinspection PhpUnhandledExceptionInspection */

require_once __DIR__ . "/../../start.php";

// обрабатываем запрос
showAjax(\Application\Entrypoint\Proxy::processRequest());