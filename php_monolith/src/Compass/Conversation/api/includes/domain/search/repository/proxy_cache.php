<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-загрузчик сущностей поиска.
 */
class Domain_Search_Repository_ProxyCache {

	// работает через singleton
	protected static ?self $_instance = null;

	protected array $_cache_store  = []; // кэш ранее загруженных элементов
	protected array $_loader_store = []; // список функций-загрузчиков

	protected float $_total_spent_time           = 0;  // сколько времени было потрачено на работу кэша за все время жизни
	protected array $_total_spent_time_by_loader = []; // сколько времени было потрачено на работу кэша за все время жизни

	/**
	 * Возвращает экземпляр класса.
	 */
	public static function instance():static {

		if (is_null(static::$_instance)) {
			static::$_instance = new static();
		}

		return self::$_instance;
	}

	/**
	 * Добавляет загрузчик.
	 *
	 * Логика работы такая:
	 *    — $filter_fn пытается ранее уже загруженные значения из запрошенного списка
	 *    — $load_fn загружаем недостающие элементы
	 *    — $load_fn кэшируем новые загруженные элементы
	 *
	 * @param string   $unique_key уникальный ключ для загрузчика
	 * @param callable $filter_fn  функция для фильтрации ранее загруженных данных, получает на вход текущий кэш
	 * @param callable $load_fn    функция загрузчик, вызывает при загрузка данных, получает на вход результат выполнения $filter_fn
	 * @param callable $cache_fn   функция для кэширования данных, получает на вход результат выполнения $load_fn и текущий кэш
	 * @param callable $pick_fn    функция для возврата данных, получает на вход текущий кэш (после обновления с использоваием $cache_fn)
	 *
	 * @return Domain_Search_Repository_ProxyCache
	 */
	public function register(string $unique_key, callable $filter_fn, callable $load_fn, callable $cache_fn, callable $pick_fn):static {

		if ($this->isRegistered($unique_key)) {
			return $this;
		}

		// добавляем загрузчик и кэш для него
		$this->_loader_store[$unique_key] = [$filter_fn, $load_fn, $cache_fn, $pick_fn];
		$this->_cache_store[$unique_key]  = [];

		return $this;
	}

	/**
	 * Проверяет наличие загрузчика.
	 */
	public function isRegistered(string $unique_key):bool {

		return isset($this->_cache_store[$unique_key]);
	}

	/**
	 * Выполняет загрузку укзанных данных.
	 */
	public function load(string $unique_key, mixed ...$args):mixed {

		if (!isset($this->_cache_store[$unique_key])) {
			throw new ParseFatalException("call to unknown loader $unique_key");
		}

		// фиксируем время начала работы для кэша
		// чтобы понять, сколько времени и на что мы потратили
		$start_at = microtime(true);

		[$filter_fn, $load_fn, $cache_fn, $pick_fn] = $this->_loader_store[$unique_key];

		$filtered                        = $filter_fn($this->_cache_store[$unique_key], ...$args);
		$loaded                          = $load_fn($filtered, ...$args);
		$this->_cache_store[$unique_key] = $cache_fn($loaded, $this->_cache_store[$unique_key], ...$args);

		// записываем в общее время работы и отдельно по каждому кэшу
		$this->_setSpentTime($unique_key, $start_at, microtime(true));

		return $pick_fn($this->_cache_store[$unique_key], ...$args);
	}

	/**
	 * Фиксирует время исполнения для прокси-кэша.
	 */
	protected function _setSpentTime(string $unique_key, float $start_at, float $done_at):void {

		if (!isset($this->_total_spent_time_by_loader[$unique_key])) {
			$this->_total_spent_time_by_loader[$unique_key] = 0;
		}

		$this->_total_spent_time                        += $done_at - $start_at;
		$this->_total_spent_time_by_loader[$unique_key] += $done_at - $start_at;
	}

	/**
	 * Записывает метрики времени исполнения.
	 */
	public function writeMetrics():void {

		$this->_writeMetric("total", $this->_total_spent_time);
		$this->_total_spent_time = 0;

		foreach ($this->_total_spent_time_by_loader as $unique_key => $value) {

			$this->_writeMetric($unique_key, $value);
			unset($this->_total_spent_time_by_loader[$unique_key]);
		}
	}

	/**
	 * Записывает одну метрику.
	 */
	protected function _writeMetric(string $unique_key, float $value):void {

		if ($value === 0.0) {
			return;
		}

		$unique_key = strtolower(str_replace(["-", " "], "_", $unique_key));
		\BaseFrame\Monitor\Core::metric("proxy_cache_{$unique_key}_work_time_ms", (int) ($value * 1000))->seal();
	}
}