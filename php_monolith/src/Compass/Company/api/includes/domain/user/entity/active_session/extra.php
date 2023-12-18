<?php

namespace Compass\Company;

/**
 * Класс для работы с экстра данными сессии.
 */
class Domain_User_Entity_ActiveSession_Extra {

	// -------------------------------------------------------
	// EXTRA SCHEMA
	// -------------------------------------------------------

	// версия упаковщика
	protected const _EXTRA_VERSION = 1;

	// схема extra по версиям
	protected const _EXTRA_SCHEMA = [

		1 => [
			"need_block_if_premium_inactive" => 0,
			"premium_active_till"            => 0,
		],
	];

	/**
	 * Создать новую структуру для extra.
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["version" => "int", "extra" => "int[]"])]
	public static function initExtra():array {

		return [
			"version" => static::_EXTRA_VERSION,
			"extra"   => static::_EXTRA_SCHEMA[static::_EXTRA_VERSION],
		];
	}

	/**
	 * Устанавливает данные о текущем статусе премиуме для пользователя.
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["version" => "int", "extra" => "int[]"])]
	public static function setPremiumInfo(array $extra, int $active_till, bool $need_block_if_inactive):array {

		$extra = self::_getExtra($extra);

		// обновляем данные премиума
		$extra["extra"]["need_block_if_premium_inactive"] = $need_block_if_inactive;
		$extra["extra"]["premium_active_till"]            = $active_till;

		return $extra;
	}

	/**
	 * Возвращает данные о текущем статусе премиума для пользователя.
	 */
	#[\JetBrains\PhpStorm\ArrayShape([0 => "bool", 1 => "int"])]
	public static function getPremiumInfo(array $extra):array {

		$extra = self::_getExtra($extra);

		return [
			(bool) $extra["extra"]["need_block_if_premium_inactive"],
			(int) $extra["extra"]["premium_active_till"],
		];
	}

	# region protected

	/**
	 * Получить актуальную структуру для extra
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["version" => "int", "extra" => "int[]"])]
	protected static function _getExtra(array $extra):array {

		// у сессий почему-то пустая экстра,
		// поэтому с нуля актуализируем, если версия не указана
		if (!isset($extra["version"])) {
			return static::initExtra();
		}

		// если версия не совпадает - дополняем её до текущей
		if ((int) $extra["version"] !== static::_EXTRA_VERSION) {

			$extra["extra"]   = array_merge(static::_EXTRA_SCHEMA[static::_EXTRA_VERSION], $extra["extra"]);
			$extra["version"] = static::_EXTRA_VERSION;
		}

		return $extra;
	}

	# endregion protected
}
