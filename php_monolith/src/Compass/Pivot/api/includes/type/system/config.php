<?php

namespace Compass\Pivot;

/**
 * класс для управления конфигами
 */
class Type_System_Config {

	protected const _MCACHE_KEY = CURRENT_MODULE . "_configuration";

	protected array   $configuration = [];
	protected \mCache $mCache;

	protected function __construct() {

		$this->mCache = ShardingGateway::cache();
	}

	/**
	 * инициализирует Singleton
	 */
	public static function init():Type_System_Config {

		if (isset($GLOBALS[__CLASS__])) {
			return $GLOBALS[__CLASS__];
		}

		// создаём объект, если еще не существует
		$GLOBALS[__CLASS__] = new Type_System_Config();
		return $GLOBALS[__CLASS__];
	}

	/**
	 * устанавливает конфиг
	 *
	 */
	public function set(string $key, array $value):void {

		Gateway_Db_PivotData_PivotConfig::set($key, $value);
		$this->_resetCache();
	}

	/**
	 * удаляем по ключу
	 *
	 */
	public function delete(string $key):void {

		// удаляем конфиг отовсюду
		Gateway_Db_PivotData_PivotConfig::delete($key);
		$this->_resetCache();
	}

	/**
	 * сбрасываем кэш
	 */
	protected function _resetCache():void {

		$this->mCache->delete(self::_MCACHE_KEY);
		$this->configuration = [];
	}

	/**
	 * получает конфиг
	 *
	 */
	public function getConf(string $key):array {

		// если мы нашли в классе то отдаем
		if (isset($this->configuration[$key]) && count($this->configuration[$key]) > 0) {
			return $this->configuration[$key];
		}

		// если нашли в кэше то отдаем
		$tmp = $this->mCache->get(self::_MCACHE_KEY);

		if ($tmp !== false) {
			$this->configuration = $tmp;
		}

		if (isset($this->configuration[$key]) && count($this->configuration[$key]) > 0) {
			return $this->configuration[$key];
		}

		// если нашли в базе пишем в кэш и отдаем
		$config = Gateway_Db_PivotData_PivotConfig::get($key);

		$this->configuration[$key] = $config;
		$this->mCache->set(self::_MCACHE_KEY, $this->configuration, 7200);

		return $this->configuration[$key];
	}
}