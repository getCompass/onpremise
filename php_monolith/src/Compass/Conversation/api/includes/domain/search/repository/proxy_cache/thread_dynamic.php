<?php declare(strict_types=1);

namespace Compass\Conversation;

/**
 * Загружает динамических данных тредов.
 */
class Domain_Search_Repository_ProxyCache_ThreadDynamic {

	public const HIT_TYPE_LABEL = "thread_dynamic";

	/**
	 * Выполняет загрузку динамических данных тредов.
	 */
	public static function load(array $thread_map_list):array {

		// добавляем загрузчик, если еще не был добавлен
		if (!Domain_Search_Repository_ProxyCache::instance()->isRegistered(static::HIT_TYPE_LABEL)) {

			Domain_Search_Repository_ProxyCache::instance()->register(
				static::HIT_TYPE_LABEL,
				static fn(...$args) => static::_filterFn(...$args),
				static fn(...$args) => static::_loadFn(...$args),
				static fn(...$args) => static::_cacheFn(...$args),
				static fn(...$args) => static::_pickFn(...$args),
			);
		}

		return Domain_Search_Repository_ProxyCache::instance()->load(static::HIT_TYPE_LABEL, $thread_map_list);
	}

	/**
	 * Функция фильтр для прокси-кэша.
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _filterFn(array $cached, array $thread_map_list, mixed ...$_):array {

		return array_diff($thread_map_list, array_keys($cached));
	}

	/**
	 * Функция-загрузчик для прокси-кэша.
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _loadFn(array $filtered, mixed ...$_):array {

		if (count($filtered) === 0) {
			return [];
		}
		
		// получаем просто все меты для тредов,
		// доступы отфильтруем потом, это не задача прокси-кэша
		return \Compass\Thread\Gateway_Db_CompanyThread_ThreadDynamic::getAll($filtered);
	}

	/**
	 * Функция-кэш для прокси-кэша.
	 * @param \Compass\Thread\Struct_Db_CompanyThread_ThreadDynamic[] $loaded
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _cacheFn(array $loaded, array $cached, mixed ...$_):array {

		foreach ($loaded as $loaded_dynamic) {
			$cached[$loaded_dynamic->thread_map] = $loaded_dynamic;
		}

		return $cached;
	}

	/**
	 * Функция возврата найденных элементов для прокси-кэша.
	 * @return \Compass\Thread\Struct_Db_CompanyThread_ThreadDynamic[]
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _pickFn(array $cached, array $thread_map_list, mixed ...$_):array {

		$output = [];

		foreach ($thread_map_list as $thread_map) {

			if (!isset($cached[$thread_map])) {
				continue;
			}

			$output[$thread_map] = $cached[$thread_map];
		}

		return $output;
	}
}