<?php

namespace Compass\Pivot;

/**
 * Базовый класс для действия удаления компании из списка дэшборда
 */
class Domain_Company_Action_RemoveFromList {

	/**
	 * выполняем
	 *
	 * @throws \busException
	 * @throws cs_CompanyIsNotLobby
	 * @throws cs_HiringRequestNotPostmoderation
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \userAccessException
	 */
	public static function do(int $user_id, Struct_Db_PivotCompany_Company $company):void {

		// достаем компанию из предбанника
		try {
			$user_lobby = Domain_Company_Entity_User_Lobby::get($user_id, $company->company_id);
		} catch (\cs_RowIsEmpty) {
			throw new cs_CompanyIsNotLobby();
		}

		// если статус на постмодерации
		if (Domain_Company_Entity_User_Lobby::isStatusPostModeration($user_lobby->status)) {

			// помечаем заявку на наём отклоненной пользователем
			$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);
			$user_info   = Gateway_Bus_PivotCache::getUserInfo($user_id);
			Domain_Company_Entity_JoinLink_UserRel::setStatus(
				$user_lobby->entry_id, $user_lobby->user_id, $user_lobby->company_id, Domain_Company_Entity_JoinLink_UserRel::JOIN_LINK_REL_REVOKE
			);

			$user_info = Struct_User_Info::createStruct($user_info);

			try {
				Gateway_Socket_Company::revokeHiringRequest($user_id, $user_lobby->entry_id, $company->company_id, $company->domino_id, $private_key, $user_info);
			} catch (Gateway_Socket_Exception_CompanyIsNotServed|cs_CompanyIsHibernate) {
				// !!! если вдруг компания неактивна, то продолжаем дальнейшее выполнение на пивоте
			}
		}

		// удаляем компанию из предбанника
		Domain_Company_Entity_User_Lobby::delete($user_id, $company->company_id);

		// отправляем ws-событие
		Gateway_Bus_SenderBalancer::companyRemovedFromList($user_id, $company->company_id);
	}
}