<?php declare(strict_types = 1);

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Ключ сущности конфига
 */
class Domain_Config_Entity_Main {

	// ключ секретного ключа
	public const SECRET_KEY = "secret_key";

	// ключ создателя сервера
	public const SERVER_CREATOR = "server_creator";

	// ключ версии приложения
	public const ONPREMISE_APP_VERSION = "onpremise_app_version";

	// допустимые ключи для работы
	protected const _ALLOWED_KEY_LIST = [
		self::SECRET_KEY,
		self::SERVER_CREATOR,
		self::ONPREMISE_APP_VERSION,
	];

	/**
	 * Получить значение конфига
	 *
	 * @param string $key
	 *
	 * @return Struct_Db_PremiseData_Config
	 * @throws ParseFatalException
	 */
	public static function get(string $key):Struct_Db_PremiseData_Config {

		// если это неизвестный ключ - отказываемся его получать
		if (!in_array($key, self::_ALLOWED_KEY_LIST)) {
			throw new ParseFatalException("invalid config key");
		}

		try {
			$config = Gateway_Db_PremiseData_PremiseConfig::getOne($key);
		} catch (\cs_RowIsEmpty) {
			return new Struct_Db_PremiseData_Config($key, time(), 0, []);
		}

		return $config;
	}

	/**
	 * Установить значение конфига
	 *
	 * @param string $key
	 * @param array  $value
	 *
	 * @return Struct_Db_PremiseData_Config
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function set(string $key, array $value):Struct_Db_PremiseData_Config {

		// если это неизвестный ключ - отказываемся его устанавливать
		if (!in_array($key, self::_ALLOWED_KEY_LIST)) {
			throw new ParseFatalException("invalid config key");
		}

		try {
			$config = Gateway_Db_PremiseData_PremiseConfig::getOne($key);
		} catch (\cs_RowIsEmpty) {

			// если такого конфига нет - создаем и возвращаем объект
			$config = new Struct_Db_PremiseData_Config(
				$key,
				time(),
				0,
				$value
			);

			Gateway_Db_PremiseData_PremiseConfig::insert($config);
			return $config;
		}

		$updated_at = time();

		Gateway_Db_PremiseData_PremiseConfig::set($key, [
			"value"      => $value,
			"updated_at" => $updated_at,
		]);

		// возвращаем измененный объект
		return new Struct_Db_PremiseData_Config(
			$key,
			$config->created_at,
			$updated_at,
			$value,
		);
	}
}
