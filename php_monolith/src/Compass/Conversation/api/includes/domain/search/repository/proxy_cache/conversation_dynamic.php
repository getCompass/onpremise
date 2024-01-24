<?php declare(strict_types=1);

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Загружает динамических данных диалогов.
 */
class Domain_Search_Repository_ProxyCache_ConversationDynamic {

	public const HIT_TYPE_LABEL = "conversation_dynamic";

	/**
	 * Выполняет загрузку динамических данных диалогов.
	 * @return Struct_Db_CompanyConversation_ConversationDynamic[]
	 * @throws ParseFatalException
	 */
	public static function load(array $conversation_map_list):array {

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

		return Domain_Search_Repository_ProxyCache::instance()->load(static::HIT_TYPE_LABEL, $conversation_map_list);
	}

	/**
	 * Функция фильтр для прокси-кэша.
	 *
	 * @param Struct_Db_CompanyConversation_ConversationDynamic[] $cached
	 * @param string[]                                            $conversation_map_list
	 *
	 * @return string[]
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _filterFn(array $cached, array $conversation_map_list, mixed ...$_):array {

		return array_diff($conversation_map_list, array_keys($cached));
	}

	/**
	 * Функция-загрузчик для прокси-кэша.
	 *
	 * @param string[] $filtered
	 * @return Struct_Db_CompanyConversation_ConversationDynamic[]
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _loadFn(array $filtered, mixed ...$_):array {

		if (count($filtered) === 0) {
			return [];
		}

		return Gateway_Db_CompanyConversation_ConversationDynamic::getAll($filtered);
	}

	/**
	 * Функция-кэш для прокси-кэша.
	 *
	 * @param Struct_Db_CompanyConversation_ConversationDynamic[] $loaded
	 * @param Struct_Db_CompanyConversation_ConversationDynamic[] $cached
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _cacheFn(array $loaded, array $cached, mixed ...$_):array {

		foreach ($loaded as $loaded_dynamic) {
			$cached[$loaded_dynamic->conversation_map] = $loaded_dynamic;
		}

		return $cached;
	}

	/**
	 * Функция возврата найденных элементов для прокси-кэша.
	 * @return Struct_Db_CompanyConversation_ConversationDynamic[]
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _pickFn(array $cached, array $conversation_map_list, mixed ...$_):array {

		$output = [];

		foreach ($conversation_map_list as $conversation_map) {

			if (!isset($cached[$conversation_map])) {
				continue;
			}

			$output[$conversation_map] = $cached[$conversation_map];
		}

		return $output;
	}
}