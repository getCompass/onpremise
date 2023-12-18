<?php

namespace Compass\Pivot;

/**
 * Класс для взаимодействия с историей принятия ссылок приглашений в компанию
 */
class Domain_Company_Entity_JoinLink_AcceptedHistory {

	/**
	 * Добавляем историю принятия ссылки-инвайта
	 *
	 * @throws \queryException
	 */
	public static function add(string $join_link_uniq, int $user_id, int $company_id, int $entry_id, string $session_uniq):void {

		$extra = self::_initExtra();
		Gateway_Db_PivotHistoryLogs_JoinLinkAcceptedHistory::insert($join_link_uniq, $user_id, $company_id, $entry_id, $session_uniq, $extra);
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