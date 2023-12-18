<?php

namespace Compass\Conversation;

/**
 * класс для управления конфигами
 */
class Type_Conversation_Config {

	protected const _MCACHE_KEY      = CURRENT_MODULE . "_configuration";
	protected const _CACHE_LIFE_TIME = 7200;
	protected array $configuration   = [];

	/**
	 * инициализирует Singleton
	 *
	 */
	public static function init():Type_Conversation_Config {

		return new Type_Conversation_Config();
	}

	/**
	 * устанавливает конфиг
	 *
	 * @throws \parseException
	 */
	public function set(string $key, array $set):void {

		Gateway_Db_CompanyData_CompanyConfig::set($key, $set);

		$config                    = Gateway_Db_CompanyData_CompanyConfig::get($key);
		$this->configuration       = ShardingGateway::cache()->get(self::_MCACHE_KEY) ?: [];
		$this->configuration[$key] = $config;
		ShardingGateway::cache()->set(self::_MCACHE_KEY, $this->configuration, self::_CACHE_LIFE_TIME);
	}

	/**
	 * сбрасываем кэш
	 */
	protected function _resetCache():void {

		ShardingGateway::cache()->delete(self::_MCACHE_KEY);
		$this->configuration = [];
	}

	/**
	 * получает конфиг
	 *
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
		ShardingGateway::cache()->set(self::_MCACHE_KEY, $this->configuration, self::_CACHE_LIFE_TIME);

		return $this->configuration[$key];
	}
}
