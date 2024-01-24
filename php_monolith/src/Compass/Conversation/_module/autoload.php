<?php /** @noinspection DuplicatedCode */

declare(strict_types = 1);

/**
 * Файл модуля.
 * Регистрирует загрузчик классов для модуля.
 *
 * @package Compass\Conversation
 */

namespace Compass\Conversation;

/** @noinspection PhpIncludeInspection */
include_once CONVERSATION_MODULE_ROOT . "private/custom.php";

include_once CONVERSATION_MODULE_ROOT . "api/includes/custom_define.php";
include_once CONVERSATION_MODULE_ROOT . "api/includes/custom_exception.php";

// регистрируем загрузчик классов
spl_autoload_register(function(string $class_name):bool {

	$exploded   = explode("\\", $class_name);
	$class_name = array_pop($exploded);

	// проверяем, что пространство имен принадлежит модулю
	if (!str_contains(implode("\\", $exploded), __NAMESPACE__)) {
		return false;
	}

	// разбиваем на лексемы класса, чтобы конвертировать camel case
	$exploded = explode("_", $class_name);

	foreach ($exploded as $index => $word) {
		$exploded[$index] = strtolower(preg_replace("/\B([A-Z])/", "_$1", $word));
	}

	/* автоподгрузка */
	$file = implode("/", $exploded);
	$path = CONVERSATION_MODULE_ROOT . "api/includes/" . $file . ".php";

	// убеждаемся, что такой файл есть
	if (!file_exists($path)) {
		return false;
	}

	/** @noinspection PhpIncludeInspection */
	include_once $path;
	return true;
});