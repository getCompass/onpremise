<?php

namespace Compass\Pivot;

/**
 * Действие, исключающее пользователя из компании.
 * Третий этап в блокировке пользователя.
 */
class Domain_User_Action_KickUserFromCompany {

	/**
	 * Исключает пользователя из компании.
	 *
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws cs_CompanyIncorrectCompanyId
	 * @long
	 */
	public static function do(int $user_id, int $user_role, int $company_id):void {

		$company = Gateway_Db_PivotCompany_CompanyList::getOne($company_id);

		// получаем запись пользователя в компании
		try {
			$user_company = Domain_Company_Entity_User_Member::getUserCompany($user_id, $company_id);
		} catch (cs_CompanyUserIsNotFound) {

			// значит пользователя уже нет в компании
			return;
		}
		// добавляем пользователя в лооби как покинувший компани
		Domain_Company_Entity_User_Lobby::addFiredUser($user_id, $company_id, $user_company->order, 0);

		// удаляем компанию пользователя из списка активных компаний
		Gateway_Db_PivotCompany_CompanyUserList::delete($user_id, $company_id);
		Gateway_Db_PivotUser_CompanyList::delete($user_id, $company_id);
		Type_Phphooker_Main::sendUserAccountLog($user_id, Type_User_Analytics::LEFT_SPACE);

		// декрементим количество участников компании
		Domain_Company_Entity_Company::incCounterByRole($company_id, Type_User_Main::NPC_TYPE_HUMAN, $user_role, -1);

		// пропускаем если компания неактивная
		if (!Domain_Company_Entity_Company::isCompanyActive($company)) {
			return;
		}

		try {

			// шлем запрос в нужную компанию
			Type_System_Admin::log("user-kicker", "send kick user {$user_id} command to company {$company_id}");
			Gateway_Socket_Company::kickUser($user_id, $company_id, $company->domino_id, Domain_Company_Entity_Company::getPrivateKey($company->extra));

			// удаляем у пользователя все постоянные комнаты
			Gateway_Socket_Jitsi::removeAllPermanentConference($user_id, $company_id);
		} catch (cs_CompanyIsHibernate $e) { // кетчим если компания в гибернации чтобы отловить один кейс

			// если это тестовый сервер - значит компанию усыпили и она уже не проснется(физически удаляется база компании), ничего не делаем
			// на паблике и стейдже такой кейс невозможен, т.к увольнение кого-либо считается проявлением активности и компания не сможет уснуть еще минимум 7 дней
			if (isTestServer()) {
				return;
			}

			// если же на паблике или стейдже такое произошло - кидаем exception дальше
			Type_System_Admin::log("user-kicker", "command kick user {$user_id} from company {$company_id} failed, company in hibernate");
			throw $e;
		} catch (\Exception $e) {

			Type_System_Admin::log("user-kicker", "command kick user {$user_id} from company {$company_id} failed, reason: {$e->getMessage()}");
			throw $e;
		}
	}
}
