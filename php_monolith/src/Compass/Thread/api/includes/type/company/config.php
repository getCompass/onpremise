<?php

namespace Compass\Thread;

/**
 * класс для управления конфигами
 */
class Type_Company_Config {

	protected const _MCACHE_KEY = "company_configuration";

	protected array $configuration = [];

	/**
	 * инициализирует Singleton
	 *
	 */
	public static function init():Type_Company_Config {

		return new Type_Company_Config();
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
	 *
	 * @throws \returnException
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

		// ищем в самой компании и сохраняем в кэш
		$config = Gateway_Socket_Company::getConfigByKey($key);
		if (count($config) < 1) {
			return $config;
		}

		$this->configuration[$key] = $config;
		ShardingGateway::cache()->set(self::_MCACHE_KEY, $this->configuration, 7200);

		return $this->configuration[$key];
	}
}
