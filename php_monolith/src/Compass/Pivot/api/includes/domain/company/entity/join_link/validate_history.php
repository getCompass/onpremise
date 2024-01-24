<?php

namespace Compass\Pivot;

/**
 * Класс для взаимодействия с историей валидации ссылкой приглашений в компанию
 */
class Domain_Company_Entity_JoinLink_ValidateHistory {

	/**
	 * Добавляем историю валидации ссылки-инвайта
	 *
	 * @throws \queryException
	 */
	public static function add(int $user_id, string $join_link_uniq, string $session_uniq, string $link):void {

		$extra = self::_initExtra();
		Gateway_Db_PivotHistoryLogs_JoinLinkValidateHistory::insert($user_id, $join_link_uniq, $session_uniq, $link, $extra);
	}

	# region EXTRA

	protected const _EXTRA_VERSION = 1;
	protected const _EXTRA_SCHEMA  = [];

	/**
	 * Инициализируем новую extra
	 *
	 */
	protected static function _initExtra():array {

		return [];
	}

	# endregion EXTRA
}