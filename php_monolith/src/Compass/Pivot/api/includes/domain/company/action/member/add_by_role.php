<?php

namespace Compass\Pivot;

/**
 * Базовый класс для действия добавления пользователя в компанию с указанной ролью
 */
class Domain_Company_Action_Member_AddByRole {

	/**
	 * Добавляем пользователя в компанию
	 *
	 * @param int                            $user_id
	 * @param int                            $user_role
	 * @param Struct_Db_PivotCompany_Company $company
	 * @param string                         $locale
	 * @param bool                           $is_need_create_intercom_conversation
	 *
	 * @return array
	 * @throws Domain_SpaceTariff_Exception_TimeLimitReached
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_ExitTaskInProgress
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_UserNotFound
	 * @throws \queryException
	 * @long
	 */
	public static function do(int $user_id, int $user_role, Struct_Db_PivotCompany_Company $company, string $locale):array {

		$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);
		try {

			$user_lobby = Domain_Company_Entity_User_Lobby::get($user_id, $company->company_id);
			$order      = $user_lobby->order;
		} catch (\cs_RowIsEmpty) {

			$order = Domain_Company_Entity_User_Order::getMaxOrder($user_id);
			$order++;
		}

		// увеличиваем лимит участников, если есть такая возможность
		[, $is_trial_activated] = Domain_SpaceTariff_Action_IncreaseMemberCountLimit::do($user_id, $company);

		// добавляем пользователя в компанию как участника
		$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);

		[$entry_id, $company_push_token, $role, $permissions] = Gateway_Socket_Company::addMemberByRole(
			$user_id,
			$user_role,
			$company->company_id,
			$user_info->npc_type,
			$company->domino_id,
			$private_key,
			$user_info->full_name,
			$user_info->avatar_file_map,
			Type_User_Main::getAvatarColorId($user_info->extra),
			$locale,
			$is_trial_activated,
			getIp(),
			\BaseFrame\System\UserAgent::getUserAgent(),
			Type_User_Main::getAvgScreenTime($user_info->extra),
			Type_User_Main::getTotalActionCount($user_info->extra),
			Type_User_Main::getAvgMessageAnswerTime($user_info->extra),
		);

		// удаляем пользователя из лобби
		Domain_Company_Entity_User_Lobby::delete($user_id, $company->company_id);

		// добавляем пользователя в компанию
		return Domain_Company_Entity_User_Member::add(
			$user_id, $role, $permissions, $user_info->created_at, $company->company_id, $order, $user_info->npc_type, $company_push_token, $entry_id
		);
	}
}