<?php

namespace BaseFrame\Crypt;

/**
 * Класс-хранилище для шифровальщиков..
 */
class CrypterProvider {

	/** @var \BaseFrame\Crypt\Crypter[] хранилище шифровальщиков */
	protected static array $_store = [];

	/**
	 * Добавляет новый шифровальщик в хранилище.
	 */
	public static function add(string $uniq_key, Crypter $crypter):void {

		if (isset(static::$_store[$uniq_key])) {
			throw new \RuntimeException("passed key $uniq_key already defined");
		}

		static::$_store[$uniq_key] = $crypter;
	}

	/**
	 * Достает из хранилища шифровальщик по ключу.
	 */
	public static function get(string $uniq_key):Crypter {

		if (!isset(static::$_store[$uniq_key])) {
			throw new \RuntimeException("crypter with uniq $uniq_key is not defined");
		}

		return static::$_store[$uniq_key]->init();
	}
}