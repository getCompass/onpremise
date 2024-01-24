<?php declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Загружает связи сущность-поиск по идентификаторам сущностей.
 */
class Domain_Search_Repository_ProxyCache_SearchIdEntity {

	public const HIT_TYPE_LABEL = "search_id_entity";

	/**
	 * Выполняет загрузку участников-администраторов.
	 *
	 * @param int[] $search_id_list
	 *
	 * @return Struct_Db_SpaceSearch_EntitySearchIdRel[];
	 */
	public static function load(array $search_id_list):array {

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

		return Domain_Search_Repository_ProxyCache::instance()->load(static::HIT_TYPE_LABEL, $search_id_list);
	}

	/**
	 * Функция фильтр для прокси-кэша.
	 *
	 * @param int $search_id_list
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _filterFn(array $cached, array $search_id_list, mixed ...$_):array {

		return array_diff($search_id_list, array_keys($cached));
	}

	/**
	 * Функция-загрузчик для прокси-кэша.
	 *
	 * @param int $search_id_list
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _loadFn(array $search_id_list, mixed ...$_):array {

		if (count($search_id_list) === 0) {
			return [];
		}

		return Gateway_Db_SpaceSearch_EntitySearchIdRel::getSearched($search_id_list);
	}

	/**
	 * Функция-кэш для прокси-кэша.
	 *
	 * @param Struct_Db_SpaceSearch_EntitySearchIdRel[] $loaded
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _cacheFn(array $loaded, array $cached, mixed ...$_):array {

		foreach ($loaded as $loaded_rel) {
			$cached[$loaded_rel->search_id] = $loaded_rel;
		}

		return $cached;
	}

	/**
	 * Функция возврата найденных элементов для прокси-кэша.
	 * Возвращает записи, сгруппированные по типам сущностей.
	 *
	 * @param int[] $search_id_list
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _pickFn(array $cached, array $search_id_list, mixed ...$_):array {

		return array_intersect_key($cached, array_flip($search_id_list));
	}
}