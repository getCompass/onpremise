<?php declare(strict_types=1);

namespace Compass\Conversation;

/**
 * Загружает данные левого меню пользователя.
 */
class Domain_Search_Repository_ProxyCache_LeftMenuItem {

	public const HIT_TYPE_LABEL = "left_menu_item";

	/**
	 * Выполняет данные левого меню пользователя.
	 */
	public static function load(int $user_id, array $conversation_map_list):array {

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

		return Domain_Search_Repository_ProxyCache::instance()->load(static::HIT_TYPE_LABEL, $conversation_map_list, $user_id);
	}

	/**
	 * Функция фильтр для прокси-кэша.
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _filterFn(array $cached, array $conversation_map_list, mixed ...$_):array {

		return array_diff($conversation_map_list, array_keys($cached));
	}

	/**
	 * Функция-загрузчик для прокси-кэша.
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _loadFn(array $filtered, array $conversation_map_list, int $user_id, mixed ...$_):array {

		if (count($filtered) === 0) {
			return [];
		}

		return Gateway_Db_CompanyConversation_UserLeftMenu::getAllowedListByConversationMap($user_id, $filtered);
	}

	/**
	 * Функция-кэш для прокси-кэша.
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _cacheFn(array $loaded, array $cached, mixed ...$_):array {

		foreach ($loaded as $loaded_meta) {
			$cached[$loaded_meta["conversation_map"]] = $loaded_meta;
		}

		return $cached;
	}

	/**
	 * Функция возврата найденных элементов для прокси-кэша.
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _pickFn(array $cached, array $conversation_map_list, mixed ...$_):array {

		$output = [];

		foreach ($conversation_map_list as $thread_map) {

			if (!isset($cached[$thread_map])) {
				continue;
			}

			$output[$thread_map] = $cached[$thread_map];
		}

		return $output;
	}
}