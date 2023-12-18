<?php

namespace Compass\FileBalancer;

// класс для работы с memCache
// настройки подключения лежат в /private/main.php
class mCache {

	protected $obj    = null;
	protected $_local = [];

	// model singleton
	// @mixed
	public static function init() {

		if (isset($GLOBALS["memcache_data"])) {
			return $GLOBALS["memcache_data"];
		}

		//
		$GLOBALS["memcache_data"] = new mCache(MCACHE_HOST, MCACHE_PORT);
		return $GLOBALS["memcache_data"];
	}

	public static function end():void {

		$class = __CLASS__;
		if (isset($GLOBALS["memcache_data"]) && $GLOBALS["memcache_data"] instanceof $class) {
			$GLOBALS["memcache_data"]->close();
		}

		$GLOBALS["memcache_data"] = null;
		unset($GLOBALS["memcache_data"]);
	}

	// конструктор
	function __construct(string $host, string $port) {

		$this->obj = new Memcache();
		$this->obj->connect($host, $port);
	}

	// получить запись из mcache
	// @mixed
	function get(string $key, $default = false) {

		$key = $this->_getKey($key);

		$local = $this->_getLocal($key);
		if ($local !== false) {
			return $local;
		}

		$output = $this->obj->get($key);
		$this->_setLocal($key, $output);

		return $output === false ? $default : $output;
	}

	// удалить запись из mcache
	function delete(string $key):void {

		$key = $this->_getKey($key);
		$this->obj->set($key, false, MEMCACHE_COMPRESSED, 1);
		$this->_local[$key] = false;
	}

	// обновить запись в mcache
	// @mixed
	function set(string $key, $value, int $expire = 3600):void {

		$key = $this->_getKey($key);

		if ($expire > time()) {
			$expire -= time();
		}

		if (is_int($value)) {
			$value = strval($value);
		}

		$this->obj->set($key, $value, MEMCACHE_COMPRESSED, $expire);
		$this->_setLocal($key, $value);
	}

	// закрываем соединение
	public function close():void {

		$this->obj->close();
	}

	// -------------------------------------------------
	// PROTECTED
	// -------------------------------------------------

	// получаем запись из mcache, если она не просрочилась
	// @mixed
	protected function _getLocal(string $key) {

		$info = $this->_local[$key] ?? false;

		if ($info === false) {
			return false;
		}

		if (isset($info["expire"]) && $info["expire"] > time()) {
			return $info["data"];
		}

		return false;
	}

	// обновляем запись в mcache
	// @mixed
	protected function _setLocal(string $key, $value):void {

		$info = [
			"expire" => time() + 5,
			"data"   => $value,
		];

		$this->_local[$key] = $info;
	}

	// формируем ключ
	protected function _getKey(string $key):string {

		return md5(PATH_ROOT . CODE_UNIQ_VERSION . $key);
	}
}