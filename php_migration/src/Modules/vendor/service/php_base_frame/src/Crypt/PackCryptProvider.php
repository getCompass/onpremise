<?php

namespace BaseFrame\Crypt;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-обертка для работы с шифрованием
 */
class PackCryptProvider {

	public const CONVERSATION = "conversation";
	public const THREAD       = "thread";
	public const MESSAGE      = "message";
	public const FILE         = "file";
	public const CALL         = "call";
	public const INVITE       = "invite";
	public const PREVIEW      = "preview";
	public const COMPANY      = "company";
	public const WIKI         = "wiki";

	private static ?self $_instance       = null;
	protected array      $crypt_data_list = [];

	/**
	 * Инициализируем
	 *
	 * @return $this
	 */
	public static function init(array $crypt_data_list = []):static {

		if (is_null(static::$_instance)) {
			static::$_instance = new static();
		}

		static::$_instance->crypt_data_list = array_merge(static::$_instance->crypt_data_list, $crypt_data_list);
		return static::$_instance;
	}

	/**
	 * получаем шифрование диалогов
	 *
	 * @return PackCryptData
	 */
	public static function conversation():PackCryptData {

		return static::$_instance->_getDataByKey(static::CONVERSATION);
	}

	/**
	 * получаем шифрование тредов
	 *
	 * @return PackCryptData
	 */
	public static function thread():PackCryptData {

		return static::$_instance->_getDataByKey(static::THREAD);
	}

	/**
	 * получаем шифрование файлов
	 *
	 * @return PackCryptData
	 */
	public static function file():PackCryptData {

		return static::$_instance->_getDataByKey(static::FILE);
	}

	/**
	 * получаем шифрование звонков
	 *
	 * @return PackCryptData
	 */
	public static function call():PackCryptData {

		return static::$_instance->_getDataByKey(static::CALL);
	}

	/**
	 * получаем шифрование сообщений
	 *
	 * @return PackCryptData
	 */
	public static function message():PackCryptData {

		return static::$_instance->_getDataByKey(static::MESSAGE);
	}

	/**
	 * получаем шифрование инвайтов
	 *
	 * @return PackCryptData
	 */
	public static function invite():PackCryptData {

		return static::$_instance->_getDataByKey(static::INVITE);
	}

	/**
	 * получаем шифрование превью
	 *
	 * @return PackCryptData
	 */
	public static function preview():PackCryptData {

		return static::$_instance->_getDataByKey(static::PREVIEW);
	}

	/**
	 * получаем шифрование компаний
	 *
	 * @return PackCryptData
	 */
	public static function company():PackCryptData {

		return static::$_instance->_getDataByKey(static::COMPANY);
	}

	/**
	 * получаем шифрование вики
	 *
	 * @return PackCryptData
	 */
	public static function wiki():PackCryptData {

		return static::$_instance->_getDataByKey(static::WIKI);
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
	protected function _getDataByKey(string $key):PackCryptData {

		if (!isset(static::$_instance->crypt_data_list[$key])) {
			throw new ParseFatalException("not defined");
		}

		return static::$_instance->crypt_data_list[$key];
	}
}