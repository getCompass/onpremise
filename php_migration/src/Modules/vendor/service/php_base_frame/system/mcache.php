<?php

use BaseFrame\Conf\ConfProvider;

/**
 * класс для работы с memCache
 */
class mCache {

	protected ?Memcache $memcache = null;

	protected const _TIMEOUT = 2;

	/**
	 * Возвращает экземпляр класс с указанным конфигом.
	 * @return static
	 */
	public static function configured(array $conf, string $key = "memcache_data"):static {

		if (!isset($GLOBALS[$key])) {
			$GLOBALS[$key] = new static($conf["host"], $conf["port"]);
		}

		return $GLOBALS[$key];
	}

	/**
	 * model singleton
	 *
	 * @return mCache|mixed
	 * @mixed
	 */
	public static function init() {

		if (isset($GLOBALS["memcache_data"])) {
			return $GLOBALS["memcache_data"];
		}

		// получаем sharding конфиг
		$conf = ConfProvider::shardingMcache();

		//
		$GLOBALS["memcache_data"] = new mCache($conf["host"], $conf["port"]);
		return $GLOBALS["memcache_data"];
	}

	/**
	 * закрываем соединение
	 */
	public static function end():void {

		if (isset($GLOBALS["memcache_data"]) && $GLOBALS["memcache_data"] instanceof mCache) {

			$GLOBALS["memcache_data"]->close();
		}

		$GLOBALS["memcache_data"] = null;
		unset($GLOBALS["memcache_data"]);
	}

	/**
	 * mCache constructor.
	 *
	 * @param string $host
	 * @param string $port
	 */
	function __construct(string $host, string $port) {

		$this->memcache = new Memcache();
		$this->memcache->connect($host, $port, self::_TIMEOUT);
	}

	/**
	 * добавить запись в mcache
	 *
	 * @param string $key
	 * @param        $value
	 * @param int    $expire
	 *
	 * @mixed
	 */
	function add(string $key, $value, int $expire = 3600):void {

		$key = self::_getKey($key);

		if ($expire > time()) {
			$expire = $expire - time();
		}

		if (is_int($value)) {
			$value = strval($value);
		}

		$output = $this->memcache->add($key, $value, MEMCACHE_COMPRESSED, $expire);
		if ($output == false) {
			throw new cs_MemcacheRowIfExist();
		}
	}

	/**
	 * получить запись из mcache
	 *
	 * @param string $key
	 * @param false  $default
	 *
	 * @return array|false|mixed|string
	 * @mixed
	 */
	function get(string $key, $default = false) {

		$key = self::_getKey($key);

		$output = $this->memcache->get($key);

		return $output == false ? $default : $output;
	}

	/**
	 * получить несколько записей из mcache
	 *
	 * @param array $key_list
	 *
	 * @return array|false|string
	 * @mixed
	 */
	function getList(array $key_list) {

		$output_key_list = [];
		foreach ($key_list as $key) {
			$output_key_list[] = self::_getKey($key);
		}

		return $this->memcache->get($output_key_list);
	}

	/**
	 * удалить запись из mcache
	 *
	 * @param string $key
	 */
	function delete(string $key):void {

		$key = self::_getKey($key);
		$this->memcache->delete($key);
	}

	/**
	 * очистить полностью кэш
	 */
	function flush():void {

		$this->memcache->flush();
	}

	/**
	 * обновить запись в mcache
	 *
	 * @param string $key
	 * @param        $value
	 * @param int    $expire
	 *
	 * @mixed
	 */
	function set(string $key, $value, int $expire = 3600):void {

		$key = self::_getKey($key);

		if ($expire > time()) {
			$expire = $expire - time();
		}

		if (is_int($value)) {
			$value = strval($value);
		}

		$this->memcache->set($key, $value, MEMCACHE_COMPRESSED, $expire);
	}

	/**
	 * закрываем соединение
	 */
	public function close():void {

		$this->memcache->close();
	}

	// -------------------------------------------------
	// PROTECTED
	// -------------------------------------------------

	/**
	 * формируем ключ
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	protected function _getKey(string $key):string {

		$prefix = static::_getPrefix();
		if (mb_strlen($prefix) > 0) {
			return md5($prefix . "_" . $key);
		}
		return md5($key);
	}

	// получаем очередь
	protected static function _getPrefix():string {

		return "";
	}
}