<?php

namespace Compass\Pivot;

/**
 * Родительский класс для конфигов с фичами
 */
abstract class Domain_App_Entity_Rule {

	protected const _RULE_VERSION = 0;

	/**
	 * Получить версию списка функционала
	 *
	 * @return int
	 */
	public function getRuleVersion():int {

		return static::_RULE_VERSION;
	}

	/**
	 * Инициализировать конфиг
	 *
	 * @param bool $need_force_update
	 *
	 * @return void
	 */
	abstract public function initializeConfig(bool $need_force_update = false):void;

	/**
	 * Получить ключ конфига для фичи
	 *
	 * @return mixed
	 */
	public static function getConfigKey():string {

		return mb_strtoupper("RULE_V" . static::_RULE_VERSION);
	}

	/**
	 * Получить список правил
	 *
	 * @return array
	 */
	abstract public function getConfig(bool $preserve_keys = true):array;

	/**
	 * Добавить правило
	 *
	 * @param string $rule_name
	 * @param int    $type
	 * @param int    $priority
	 * @param array  $restrictions
	 * @param array  $values
	 *
	 * @return array
	 */
	abstract public function add(string $rule_name, int $type, int $priority, array $restrictions, array $values):array;

	/**
	 * Изменить правило
	 *
	 * @param string $rule_name
	 * @param array  $restrictions
	 * @param array  $values
	 * @param array  $set
	 *
	 * @return array
	 */
	abstract public function edit(string $rule_name, array $restrictions, array $values, array $set):array;

	/**
	 * Удалить правило
	 *
	 * @param string $rule_name
	 *
	 * @return void
	 */
	abstract public function delete(string $rule_name):void;
}