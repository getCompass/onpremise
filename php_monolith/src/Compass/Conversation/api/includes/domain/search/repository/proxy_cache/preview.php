<?php declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Прокси кэш для загрузки превью url.
 */
class Domain_Search_Repository_ProxyCache_Preview {

	public const HIT_TYPE_LABEL = "preview";

	/**
	 * Выполняет загрузку сообщений.
	 */
	public static function load(array $preview_map_list):array {

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

		return Domain_Search_Repository_ProxyCache::instance()->load(static::HIT_TYPE_LABEL, $preview_map_list);
	}

	/**
	 * Функция фильтр для прокси-кэша.
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _filterFn(array $cached, array $preview_map_list, mixed ...$_):array {

		return array_diff($preview_map_list, array_keys($cached));
	}

	/**
	 * Функция-загрузчик для прокси-кэша.
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _loadFn(array $filtered, mixed ...$_):array {

		if (count($filtered) === 0) {
			return [];
		}

		return Type_Preview_Main::getAll($filtered);
	}

	/**
	 * Функция-кэш для прокси-кэша.
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _cacheFn(array $loaded, array $cached, mixed ...$_):array {

		foreach ($loaded as $loaded_preview) {
			$cached[$loaded_preview["preview_map"]] = $loaded_preview;
		}

		return $cached;
	}

	/**
	 * Функция возврата найденных элементов для прокси-кэша.
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _pickFn(array $cached, array $preview_map_list, mixed ...$_):array {

		$output = [];

		foreach ($preview_map_list as $preview_map) {

			if (!isset($cached[$preview_map])) {
				continue;
			}

			$output[$preview_map] = $cached[$preview_map];
		}

		return $output;
	}
}