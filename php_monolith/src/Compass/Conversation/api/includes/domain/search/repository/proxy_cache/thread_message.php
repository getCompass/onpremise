<?php declare(strict_types=1);

namespace Compass\Conversation;

use Compass\Thread\Gateway_Db_CompanyThread_MessageBlock;

/**
 * Прокси кэш для загрузки сообщений из тредов.
 */
class Domain_Search_Repository_ProxyCache_ThreadMessage {

	public const HIT_TYPE_LABEL = "thread_message";

	/**
	 * Выполняет загрузку сообщений.
	 */
	public static function load(array $message_map_list):array {

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

		return Domain_Search_Repository_ProxyCache::instance()->load(static::HIT_TYPE_LABEL, $message_map_list);
	}

	/**
	 * Функция фильтр для прокси-кэша.
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _filterFn(array $cached, array $message_map_list, mixed ...$_):array {

		$to_fetch_block_list = [];

		foreach ($message_map_list as $message_map) {

			// получаем map треда и идентификатор блока сообщения
			$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map);
			$block_id   = \CompassApp\Pack\Message\Thread::getBlockId($message_map);

			// если блок уже закэширован и сообщение в нем есть
			if (isset($cached[$thread_map][$block_id][$message_map])) {
				continue;
			}

			$to_fetch_block_list[$thread_map][] = $block_id;
		}

		foreach ($to_fetch_block_list as $index => $block_id_list) {
			$to_fetch_block_list[$index] = array_values(array_unique($block_id_list));
		}

		return $to_fetch_block_list;
	}

	/**
	 * Функция-загрузчик для прокси-кэша.
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _loadFn(array $filtered, mixed ...$_):array {

		if (count($filtered) === 0) {
			return [];
		}

		return Gateway_Db_CompanyThread_MessageBlock::getSpecifiedList($filtered);
	}

	/**
	 * Функция-кэш для прокси-кэша.
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _cacheFn(array $loaded, array $cached, mixed ...$_):array {

		foreach ($loaded as $message_block) {
			$cached[$message_block["thread_map"]][$message_block["block_id"]] = $message_block;
		}

		return $cached;
	}

	/**
	 * Функция возврата найденных элементов для прокси-кэша.
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _pickFn(array $cached, array $message_map_list, mixed ...$_):array {

		$output = [];

		foreach ($message_map_list as $message_map) {

			// получаем map треда и идентификатор блока сообщения
			$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map);
			$block_id   = \CompassApp\Pack\Message\Thread::getBlockId($message_map);

			if (!isset($cached[$thread_map][$block_id])) {
				continue;
			}

			$output[$message_map] = $cached[$thread_map][$block_id]["data"][$message_map];;
		}

		return $output;
	}
}