<?php declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Прокси кэш для загрузки файлов.
 */
class Domain_Search_Repository_ProxyCache_File {

	public const HIT_TYPE_LABEL = "file";

	/**
	 * Выполняет загрузку сообщений.
	 */
	public static function load(array $file_map_list):array {

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

		return Domain_Search_Repository_ProxyCache::instance()->load(static::HIT_TYPE_LABEL, $file_map_list);
	}

	/**
	 * Функция фильтр для прокси-кэша.
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _filterFn(array $cached, array $file_map_list, mixed ...$_):array {

		return array_diff($file_map_list, array_keys($cached));
	}

	/**
	 * Функция-загрузчик для прокси-кэша.
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _loadFn(array $filtered, mixed ...$_):array {

		if (count($filtered) === 0) {
			return [];
		}

		return Gateway_Socket_FileBalancer::getFileWithContentList($filtered);
	}

	/**
	 * Функция-кэш для прокси-кэша.
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _cacheFn(array $loaded, array $cached, mixed ...$_):array {

		foreach ($loaded as $loaded_file) {
			$cached[$loaded_file["file_map"]] = $loaded_file;
		}

		return $cached;
	}

	/**
	 * Функция возврата найденных элементов для прокси-кэша.
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _pickFn(array $cached, array $file_map_list, mixed ...$_):array {

		$output = [];

		foreach ($file_map_list as $file_map) {

			if (!isset($cached[$file_map])) {
				continue;
			}

			$output[$file_map] = $cached[$file_map];
		}

		return $output;
	}
}