<?php

namespace Compass\Pivot;

/**
 * Действия принятия приглашения по ссылке-приглашению.
 */
class Domain_Link_Action_Accept {

	/**
	 * Выполняет принятия приглашения по ссылке-приглашению.
	 *
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \userAccessException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_ExitTaskInProgress
	 * @throws cs_JoinLinkIsNotActive
	 * @throws cs_Text_IsTooLong
	 * @throws cs_UserNotFound
	 */
	public static function run(int $user_id, array $invite_accept_info, string $session_uniq):array {

		/** @var Struct_Link_ValidationResult $validation_result */
		[$join_link_rel_row, $user, $validation_result] = $invite_accept_info;
		$company = $validation_result->company;

		/** @var Struct_Dto_Socket_Company_AcceptJoinLinkResponse $accept_link_response */
		[$accept_link_response, $user_info] = Domain_Company_Action_JoinLink_Join::run(
			$user, $join_link_rel_row, $company, $validation_result->user_invite_link_rel, $session_uniq, ""
		);

		$order = Domain_Company_Entity_User_Order::getMaxOrder($user_id);
		$order++;

		if ($accept_link_response->is_postmoderation) {

			// если ссылка с постмодерацией, добавляем в лобби
			$user_company = Domain_Company_Entity_User_Lobby::addPostModeratedUser(
				$user_id,
				$company->company_id,
				$order,
				$validation_result->inviter_user_info->user_id,
				$accept_link_response->entry_id
			);

			$status = Struct_User_Company::POSTMODERATED_STATUS;
		} else {

			// удаляем компанию из лобби, если вдруг имелась запись
			Domain_Company_Entity_User_Lobby::delete($user_id, $company->company_id);

			// добавляем пользователя в компанию
			/** @var Struct_Db_PivotCompany_Company $company */
			[$user_company, $company] = Domain_Company_Entity_User_Member::add(
				$user_id,
				$accept_link_response->user_space_role,
				$accept_link_response->user_space_permissions,
				$user_info->created_at,
				$company->company_id,
				$order,
				Type_User_Main::NPC_TYPE_HUMAN,
				$accept_link_response->company_push_token,
				$accept_link_response->entry_id
			);

			$status = Struct_User_Company::ACTIVE_STATUS;
		}

		// формирует сущность компании с нужным статусом и отдаем ее
		$frontend_company = Apiv1_Format::formatUserCompany(Struct_User_Company::createFromCompanyStruct(
			$company, $status, $user_company->order, $validation_result->inviter_user_info->user_id,
		));

		Gateway_Bus_SenderBalancer::companyStatusChanged($user_id, $frontend_company);
		return [$user_company, $accept_link_response];
	}
}