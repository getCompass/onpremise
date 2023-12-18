<?php

$dir = dirname(__FILE__);

// подключаем прямыми ссылками,
// чтобы не сканировать лишний раз директорию
return include_once $dir . "/FileNode/_module_request.php";
