<?php

namespace Compass\Federation;

/**
 * абстрактный класс, описывающий контракт различных уровней блокировки аккаунтов
 * @package Compass\Federation
 */
abstract class Domain_Ldap_Entity_UserBlocker_Abstract {

	/**
	 * Запускаем блокировку пользователя
	 */
	final public function run(int $user_id):void {

		// выполняем основную логику блокировки пользователя
		static::_block($user_id);
	}

	/**
	 * функция для основной логики блокировки пользователя
	 */
	abstract protected static function _block(int $user_id):void;
}