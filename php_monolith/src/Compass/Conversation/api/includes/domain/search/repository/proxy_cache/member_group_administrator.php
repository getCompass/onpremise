<?php declare(strict_types=1);

namespace Compass\Conversation;

use CompassApp\Domain\Member\Entity\Permission;

/**
 * Загружает участников-администраторов.
 * Да простят меня за такое решение, но лучше ничего не придумал.
 */
class Domain_Search_Repository_ProxyCache_MemberGroupAdministrator {

	public const HIT_TYPE_LABEL = "member_group_administrator";

	/**
	 * Выполняет загрузку участников-администраторов.
	 */
	public static function load():array {

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

		return Domain_Search_Repository_ProxyCache::instance()->load(static::HIT_TYPE_LABEL);
	}

	/**
	 * Функция фильтр для прокси-кэша.
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _filterFn(array $cached, mixed ...$_):array {

		if (isset($cached["is_load"])) {
			return [];
		}

		return [true];
	}

	/**
	 * Функция-загрузчик для прокси-кэша.
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _loadFn(array $filtered, mixed ...$_):array {

		if (reset($filtered) === false) {
			return [];
		}

		return Gateway_Db_CompanyData_MemberList::getByPermissionMask(Permission::GROUP_ADMINISTRATOR);
	}

	/**
	 * Функция-кэш для прокси-кэша.
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _cacheFn(array $loaded, array $cached, mixed ...$_):array {

		$cached["is_load"] = true;

		foreach ($loaded as $user_id => $user) {
			$cached["data"][$user_id] = $user;
		}

		return $cached;
	}

	/**
	 * Функция возврата найденных элементов для прокси-кэша.
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected static function _pickFn(array $cached, mixed ...$_):array {

		return $cached["data"] ?? [];
	}
}