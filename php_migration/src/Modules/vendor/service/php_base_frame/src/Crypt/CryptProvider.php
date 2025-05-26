<?php

namespace BaseFrame\Crypt;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-обертка для работы с шифрованием
 */
class CryptProvider {

	public const DEFAULT  = "default";
	public const EXTENDED = "extended";
	public const SESSION  = "session";

	private static ?self $_instance = null;

	protected array $crypt_data_list = [];

	/**
	 * Инициализируем
	 *
	 * @return $this
	 */
	public static function init(array $crypt_data_list):static {

		if (is_null(static::$_instance)) {
			static::$_instance = new static();
		}

		static::$_instance->crypt_data_list = array_merge(static::$_instance->crypt_data_list, $crypt_data_list);
		return static::$_instance;
	}

	/**
	 * Получаем дефолтное шифрование
	 *
	 * @return CryptData
	 * @throws ParseFatalException
	 */
	public static function default():CryptData {

		return static::$_instance->_getDataByKey(static::DEFAULT);
	}

	/**
	 * Получаем расширенное шифрование
	 *
	 * @return CryptData
	 * @throws ParseFatalException
	 */
	public static function extended():CryptData {

		return static::$_instance->_getDataByKey(static::EXTENDED);
	}

	/**
	 * Получаем шифрование сессий
	 *
	 * @return CryptData
	 * @throws ParseFatalException
	 */
	public static function session():CryptData {

		return static::$_instance->_getDataByKey(static::SESSION);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Получаем данные шифрования по ключу
	 *
	 * @return CryptData
	 * @throws ParseFatalException
	 */
	protected function _getDataByKey(string $key):CryptData {

		if (!isset(static::$_instance->crypt_data_list[$key])) {
			throw new ParseFatalException("not defined");
		}

		return static::$_instance->crypt_data_list[$key];
	}
}