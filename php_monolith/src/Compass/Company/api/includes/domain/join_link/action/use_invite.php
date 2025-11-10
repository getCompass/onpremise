<?php

namespace Compass\Company;

/**
 * Класс для использования ссылки-инвайта
 */
class Domain_JoinLink_Action_UseInvite {

	/**
	 * выполняем
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws \returnException
	 * @long
	 */
	public static function do(string $invite_link_uniq):int {

		Gateway_Db_CompanyData_JoinLinkList::beginTransaction();

		try {
			$invite_link = Gateway_Db_CompanyData_JoinLinkList::getForUpdate($invite_link_uniq);
		} catch (\cs_RowIsEmpty $exception) { // невозможная ситуация

			Gateway_Db_CompanyData_JoinLinkList::rollback();
			throw $exception;
		}

		try {

			// если ссылку нельзя использовать, то бросаем исключение
			Domain_JoinLink_Entity_Main::assertCanUse($invite_link);
		} catch (\Exception $exception) { // здесь так чтобы если добавили новые exception в assert то функция не сломалась)

			Gateway_Db_CompanyData_JoinLinkList::rollback();
			throw $exception;
		}

		$invite_link_status = $invite_link->status;

		// если ссылка имеет ограничение по количеству использований
		if (!Domain_JoinLink_Entity_Main::isLinkWithoutCanUseLimit($invite_link)) {

			// уменьшаем количество использований
			$invite_link->can_use_count = $invite_link->can_use_count - 1;

			// если те закончились, помечаем ссылку использованной
			if ($invite_link->can_use_count < 1) {

				$invite_link_status = Domain_JoinLink_Entity_Main::STATUS_USED;

				// если в этом кейсе ссылка была без ограничений по времени
				// то помечаем время истечения текущим временем
				if ($invite_link->expires_at == 0) {
					$invite_link->expires_at = time();
				}
			}
		}

		$set = [
			"updated_at"    => time(),
			"can_use_count" => $invite_link->can_use_count,
			"status"        => $invite_link_status,
			"expires_at"    => $invite_link->expires_at,
		];

		Gateway_Db_CompanyData_JoinLinkList::set($invite_link_uniq, $set);
		Gateway_Db_CompanyData_JoinLinkList::commitTransaction();

		return $set["status"];
	}
}
