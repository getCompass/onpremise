<?php

namespace Compass\Pivot;

/**
 * Класс для обновления статуса_алиаса ссылки-инвайта
 */
class Domain_Company_Action_JoinLink_UpdateStatus {

	/**
	 * Делаем
	 *
	 * @throws \parseException
	 */
	public static function do(string $join_link_uniq, int $status_alias):void {

		$set = [
			"status_alias" => $status_alias,
			"updated_at"   => time(),
		];

		Gateway_Db_PivotData_CompanyJoinLinkRel::set($join_link_uniq, $set);
	}
}