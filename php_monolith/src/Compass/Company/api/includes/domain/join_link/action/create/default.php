<?php

namespace Compass\Company;

/**
 * Дефолтный класс для создания ссылок-инвайтов
 */
class Domain_JoinLink_Action_Create_Default {

	/**
	 * выполняем
	 *
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	protected static function _do(int $creator_user_id, int $type, int $live_time, int $can_use_count, int $entry_option):Struct_Db_CompanyData_JoinLink {

		// если имеется время жизни, то получаем expires_at
		// если значение 0, значим ссылка не имеет ограничения по времени
		if ($live_time > 0) {
			$expires_at = time() + $live_time;
		} else {
			$expires_at = 0;
		}

		// генерим новую ссылку-инвайт
		$join_link_uniq = Gateway_Socket_Pivot::createJoinLink($creator_user_id, Domain_JoinLink_Entity_Main::STATUS_ACTIVE);

		// добавляем запись в базу
		return Gateway_Db_CompanyData_JoinLinkList::insert(
			$join_link_uniq,
			$entry_option,
			Domain_JoinLink_Entity_Main::STATUS_ACTIVE,
			$type,
			$expires_at,
			$can_use_count,
			$creator_user_id,
			time(),
			time(),
		);
	}
}
