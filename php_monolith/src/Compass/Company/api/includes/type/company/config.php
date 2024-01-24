<?php

namespace Compass\Company;

/**
 * Класс для управления конфигами
 */
class Type_Company_Config {

	protected const _MCACHE_KEY = CURRENT_MODULE . "_configuration";

	protected array $configuration = [];

	/**
	 * инициализирует Singleton
	 */
	public static function init():Type_Company_Config {

		return new self();
	}

	/**
	 * устанавливает конфиг
	 *
	 * @param string $key
	 * @param array  $set
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public function set(string $key, array $set):void {

		Gateway_Db_CompanyData_CompanyConfig::insertOrUpdate($key, $set);

		$config                    = Gateway_Db_CompanyData_CompanyConfig::get($key);
		$this->configuration       = ShardingGateway::cache()->get(self::_MCACHE_KEY) ?: [];

		$this->configuration[$key] = $config;
		ShardingGateway::cache()->set(self::_MCACHE_KEY, $this->configuration, 7200);
	}

	/**
	 * сбрасываем кэш
	 */
	public function resetCache():void {

		ShardingGateway::cache()->delete(self::_MCACHE_KEY);
		$this->configuration = [];
	}

	/**
	 * получает конфиг
	 */
	public function get(string $key):array {

		// если мы нашли в классе то отдаем
		if (isset($this->configuration[$key])) {
			return $this->configuration[$key];
		}

		// если нашли в кэше то отдаем
		$this->configuration = ShardingGateway::cache()->get(self::_MCACHE_KEY) ?: [];
		if (isset($this->configuration[$key])) {
			return $this->configuration[$key];
		}

		// если нашли в базе пишем в кэш и отдаем
		$config = Gateway_Db_CompanyData_CompanyConfig::get($key);
		if (count($config) < 1) {
			return $config;
		}

		$this->configuration[$key] = $config;
		ShardingGateway::cache()->set(self::_MCACHE_KEY, $this->configuration, 7200);

		return $this->configuration[$key];
	}

	/**
	 * Получить список конфигов
	 *
	 * @param array $key_list
	 *
	 * @return array
	 */
	public function getList(array $key_list):array {

		$output                    = [];
		$not_found_config_key_list = [];

		foreach ($key_list as $key) {

			// если мы нашли в классе то отдаем
			if (isset($this->configuration[$key])) {

				$output[$key] = $this->configuration[$key];
				continue;
			}

			$not_found_config_key_list[] = $key;
		}

		if (count($not_found_config_key_list) < 1) {
			return $output;
		}

		// пробуем получить список из мемкеша
		[$config_list, $not_found_config_key_list] = $this->_getListFromMemcache($not_found_config_key_list);

		$output = array_merge($output, $config_list);

		// если каким-то чудом нашли все в mcache
		if (count($not_found_config_key_list) < 1) {
			return $output;
		}

		// если нашли в базе пишем в кэш и отдаем
		$config_list = Gateway_Db_CompanyData_CompanyConfig::getList($not_found_config_key_list);

		foreach ($not_found_config_key_list as $config_key) {

			if (!isset($config_list[$config_key])) {
				$config_list[$config_key] = [];
			}
		}

		return array_merge($output, $config_list);
	}

	/**
	 * Получить значения из мемкеша
	 *
	 * @param array $key_list
	 *
	 * @return array
	 */
	protected function _getListFromMemcache(array $key_list):array {

		$not_found_key_list = [];
		$output             = [];

		// берем значения из memcache
		$this->configuration = ShardingGateway::cache()->get(self::_MCACHE_KEY) ?: [];

		// для каждого ненайденного элемента ищем конфиг в memcache
		foreach ($key_list as $config_key) {

			if (isset($this->configuration[$config_key])) {

				$output[$config_key] = $this->configuration[$config_key];
			}

			$not_found_key_list[] = $config_key;
		}

		return [$output, $not_found_key_list];
	}
}
