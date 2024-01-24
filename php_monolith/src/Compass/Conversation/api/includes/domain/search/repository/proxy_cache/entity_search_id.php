<?php declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Загружает связи сущность-поиск по идентификаторам сущностей.
 */
class Domain_Search_Repository_ProxyCache_EntitySearchId {

	public const HIT_TYPE_LABEL = "entity_search_id";

	/**
	 * Выполняет загрузку участников-администраторов.
	 *
	 * @param Struct_Domain_Search_AppEntity[] $entity_list
	 *
	 * @return int[]
	 */
	public static function load(array $entity_list):array {

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

		return Domain_Search_Repository_ProxyCache::instance()->load(static::HIT_TYPE_LABEL, $entity_list);
	}

	/**
	 * Функция фильтр для прокси-кэша.
	 *
	 * @param Struct_Domain_Search_AppEntity[] $entity_list
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _filterFn(array $cached, array $entity_list, mixed ...$_):array {

		$output = [];

		foreach ($entity_list as $entity) {

			// получаем entity_id
			$entity_id = Gateway_Db_SpaceSearch_EntitySearchIdRel::getEntityId($entity->entity_map);

			// если сущность уже кэширована, то пропускаем
			if (isset($cached[$entity_id])) {
				continue;
			}

			$output[] = $entity;
		}

		return $output;
	}

	/**
	 * Функция-загрузчик для прокси-кэша.
	 *
	 * @param Struct_Domain_Search_AppEntity[] $filtered
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _loadFn(array $filtered, mixed ...$_):array {

		if (count($filtered) === 0) {
			return [];
		}

		return Gateway_Db_SpaceSearch_EntitySearchIdRel::getIndexed(array_column($filtered, "entity_map"));
	}

	/**
	 * Функция-кэш для прокси-кэша.
	 *
	 * @param Struct_Db_SpaceSearch_EntitySearchIdRel[] $loaded
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _cacheFn(array $loaded, array $cached, mixed ...$_):array {

		foreach ($loaded as $entity_id => $search_id) {
			$cached[$entity_id] = (int) $search_id;
		}

		return $cached;
	}

	/**
	 * Функция возврата найденных элементов для прокси-кэша.
	 * Возвращает записи, сгруппированные по типам сущностей.
	 *
	 * @param Struct_Domain_Search_AppEntity[] $entity_list
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _pickFn(array $cached, array $entity_list, mixed ...$_):array {

		$output = [];

		foreach ($entity_list as $entity) {

			// получаем entity_id
			$entity_id = Gateway_Db_SpaceSearch_EntitySearchIdRel::getEntityId($entity->entity_map);

			if (!isset($cached[$entity_id])) {
				continue;
			}

			$output[$entity->entity_map] = (int) $cached[$entity_id];
		}

		return $output;
	}
}