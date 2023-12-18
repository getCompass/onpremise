<?php

namespace Compass\Thread;

use JetBrains\PhpStorm\ArrayShape;

/**
 * класс для сущности Напоминания
 */
class Domain_Remind_Entity_Remind {

	public const CONVERSATION_MESSAGE_TYPE  = 1;
	public const THREAD_MESSAGE_TYPE        = 2;
	public const THREAD_PARENT_MESSAGE_TYPE = 3;

	// версия упаковщика
	protected const _DATA_VERSION = 1;

	// схема data для Напоминания
	protected const _DATA_SCHEMA = [

		1 => [
			"comment" => "",
		],
	];

	/**
	 * инициируем data для Напоминания
	 */
	#[ArrayShape(["version" => "int", "data" => "array"])]
	public static function initData(string $comment = ""):array {

		$data = [
			"version" => self::_DATA_VERSION,
			"data"    => self::_DATA_SCHEMA[self::_DATA_VERSION],
		];

		return self::setComment($comment, $data);
	}

	/**
	 * устанавливаем коммент для Напоминания
	 */
	public static function setComment(string $comment, array $data):array {

		$data                    = self::_getData($data);
		$data["data"]["comment"] = $comment;

		return $data;
	}

	/**
	 * получаем коммент Напоминания
	 */
	public static function getComment(array $data):string {

		$data = self::_getData($data);

		if (isset($data["data"]["comment"])) {
			return $data["data"]["comment"];
		}

		return "";
	}

	// получить актуальную структуру для extra
	protected static function _getData(array $data):array {

		// если версия не совпадает - дополняем её до текущей
		if ($data["version"] != self::_DATA_VERSION) {

			$data["extra"]   = array_merge(self::_DATA_SCHEMA[self::_DATA_VERSION], $data["data"]);
			$data["version"] = self::_DATA_VERSION;
		}

		return $data;
	}
}