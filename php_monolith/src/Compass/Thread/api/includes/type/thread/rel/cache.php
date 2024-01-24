<?php

namespace Compass\Thread;

// класс для работы с кэшом родительского сообщения треда
class Type_Thread_Rel_Cache {

	protected const _CACHE_EXPIRE_TIME = 60; // время протухания кэша

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// сохранить родительское сообщение в кэш
	public static function set(string $parent_message_map, array $value):void {

		// получаем ключ
		$key = self::_getKey($parent_message_map);

		// записываем
		ShardingGateway::cache()->set($key, $value, self::_CACHE_EXPIRE_TIME);
	}

	// получить родительское сообщение из кэша
	// @mixed - может быть false
	public static function get(string $parent_message_map) {

		// получаем ключ
		$key = self::_getKey($parent_message_map);

		return ShardingGateway::cache()->get($key);
	}

	// очистить кэш (например при изменении родительского сообщения)
	public static function clear(string $parent_message_map):void {

		// получаем ключ
		$key = self::_getKey($parent_message_map);

		// очищаем
		ShardingGateway::cache()->delete($key);
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// получить ключ для обращения в кэш
	protected static function _getKey(string $parent_message_map):string {

		return "Apiv1_Threads::getMenuItemBatching." . $parent_message_map;
	}
}