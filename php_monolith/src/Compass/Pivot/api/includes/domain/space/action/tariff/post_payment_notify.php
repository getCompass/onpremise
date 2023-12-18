<?php

namespace Compass\Pivot;

/**
 * Уведомить о постоплатном периоде
 */
class Domain_Space_Action_Tariff_PostPaymentNotify {

	protected const _RESEND_REPEAT_TIME = HOUR12;

	/**
	 * Выполняем действие
	 *
	 * @param Struct_Db_PivotCompany_Company $company
	 * @param \BaseFrame\System\Log          $log
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(Struct_Db_PivotCompany_Company $company, \BaseFrame\System\Log $log):array {

		$tariff = Domain_SpaceTariff_Repository_Tariff::get($company->company_id);

		// если время не пришло публиковать анонс - завершаем выполнение
		if ($tariff->memberCount()->isActive(time()) && $tariff->memberCount()->getActiveTill() > time()) {
			return [true, $log];
		}

		$announcement_type  = \Service\AnnouncementTemplate\AnnouncementType::SPACE_TARIFF_EXPIRED;
		$resend_repeat_time = isBackendTest() || isLocalServer() ? 1 : self::_RESEND_REPEAT_TIME;

		$data = [
			"resend_repeat_time"     => $resend_repeat_time, // для CI сразу переотправляем анонс
			"expires_at"             => $tariff->memberCount()->getRestrictedAccessFrom(),
			"expired_plan_list"      => ["member_count"],
			"restricted_access_from" => $tariff->memberCount()->getRestrictedAccessFrom(),
		];

		try {
			Gateway_Socket_Space_Tariff::publishAnnouncement($announcement_type, $data, $company);
		} catch (Gateway_Socket_Exception_CompanyIsNotServed|cs_CompanyIsHibernate) {

			// при удаленном пространстве - оно в следующий тик удалится в обсервере, при гибернации - обсервер возьмет компанию на следующий тик
			return [true, $log];
		}

		// отправляем в интерком сообщение о том, что действие тарифа истекло - пространство скоро будет заблокировано
		$private_key    = Domain_Company_Entity_Company::getPrivateKey($company->extra);
		$user_role_list = Gateway_Socket_Company::getUserRoleList(
			[Domain_Company_Entity_User_Member::ROLE_ADMINISTRATOR], $company->company_id, $company->domino_id, $private_key);
		$owner_user_id  = $user_role_list[Domain_Company_Entity_User_Member::ROLE_ADMINISTRATOR][0];

		Gateway_Socket_Intercom::onSpaceTariffExpired($owner_user_id, $company->company_id);

		$log = $log->addText("Опубликовали анонс типа $announcement_type");

		return [true, $log];
	}
}