<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\CompanyNotServedException;

/**
 * Выполняет принятие приглашения по ссылке.
 */
class Domain_Company_Action_JoinLink_Join {

	/**
	 * Принимаем ссылку
	 *
	 * @long - try ... catch
	 *
	 * @throws Domain_User_Exception_Onboarding_NotAllowedStatus
	 * @throws Domain_User_Exception_Onboarding_NotAllowedType
	 * @throws CompanyNotServedException
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_ExitTaskInProgress
	 * @throws cs_JoinLinkIsNotActive
	 * @throws cs_Text_IsTooLong
	 * @throws cs_UserNotFound
	 */
	public static function run(
		Struct_Db_PivotUser_User                         $user_info,
		Struct_Db_PivotData_CompanyJoinLinkRel           $invite_link_rel_row,
		Struct_Db_PivotCompany_Company                   $company,
		Struct_Db_PivotData_CompanyJoinLinkUserRel|false $user_join_link_rel,
		string                                           $session_uniq,
		string                                           $comment,
		bool                                             $force_postmoderation
	):array {

		$user_id        = $user_info->user_id;
		$join_link_uniq = $invite_link_rel_row->join_link_uniq;
		$company_id     = $invite_link_rel_row->company_id;

		// получаем ключ для общения с пространством
		$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);

		$response = Gateway_Socket_Company::acceptJoinLink(
			$user_id, $join_link_uniq, $comment, $company_id, $company->domino_id, $private_key, $user_info, $force_postmoderation
		);

		// создаем запись в истории принятия ссылки приглашения в компанию
		Domain_Company_Entity_JoinLink_AcceptedHistory::add($join_link_uniq, $user_id, $company_id, $response->entry_id, $session_uniq);

		// добавляем или обновляем связь пользователь-приглашение
		$is_need_insert = $user_join_link_rel === false;
		Domain_Company_Entity_JoinLink_UserRel::insertOrUpdate($join_link_uniq, $user_id, $company_id, $response->entry_id, $response->is_postmoderation, $is_need_insert);

		// обновляем статус если он изменился
		if ($invite_link_rel_row->status_alias != $response->status) {
			Domain_Company_Action_JoinLink_UpdateStatus::do($join_link_uniq, $response->status);
		}

		// если пользователь еще не переходил ни по чьим ссылкам
		// то в качестве пригласившего партнера – устанавливаем владельца ссылки, по которой перешел пользователь
		if ($user_info->invited_by_partner_id === 0) {

			$user_info = static::_acceptJoinLinkFirstTime($user_id, $response->inviter_user_id, $response->inviter_user_id);

			// проверим, быть может это приглашение мы показали с помощью атрибуции – тогда обновим результат в аналитике
			Type_Phphooker_Main::onUserEnteringFirstCompany($user_id, $company_id, $response->entry_id);
		}

		// начинаем онбординг
		static::_startOnboarding($user_id, $company_id, $response->user_space_role, $response->is_postmoderation);
		return [$response, $user_info];
	}

	/**
	 * Если пользователь еще не переходил ни по чьим ссылкам
	 * @long транзакции
	 */
	protected static function _acceptJoinLinkFirstTime(int $user_id, int $invited_by_partner_id, int $inviter_user_id):Struct_Db_PivotUser_User {

		/** начало транзакции **/
		Gateway_Db_PivotUser_UserList::beginTransaction($user_id);

		try {

			$user_info = Gateway_Db_PivotUser_UserList::getForUpdate($user_id);
		} catch (\Exception $ex) {

			Gateway_Db_PivotUser_UserList::rollback($user_id);
			throw $ex;
		}

		// проверяем еще раз ибо мало ли
		if ($user_info->invited_by_partner_id > 0) {

			Gateway_Db_PivotUser_UserList::rollback($user_id);
			return $user_info;
		}

		$user_info->invited_by_partner_id = $invited_by_partner_id;
		$user_info->invited_by_user_id    = $inviter_user_id;
		$user_info->updated_at            = time();
		$set                              = [
			"invited_by_partner_id" => $user_info->invited_by_partner_id,
			"invited_by_user_id"    => $user_info->invited_by_user_id,
			"updated_at"            => $user_info->updated_at,
		];

		// обновляем данные о блокировке учетной записи
		Gateway_Db_PivotUser_UserList::set($user_id, $set);
		Gateway_Db_PivotUser_UserList::commitTransaction($user_id);
		/** конец транзакции **/

		// обновляем последние регистрации
		Gateway_Db_PivotData_LastRegisteredUser::setPartnerId($user_id, $invited_by_partner_id);

		// не забываем сбросить кэш
		Gateway_Bus_PivotCache::clearUserCacheInfo($user_id);
		return $user_info;
	}

	/**
	 * Начинаем онбординг для пользователя.
	 *
	 * @throws Domain_User_Exception_Onboarding_NotAllowedStatus
	 * @throws Domain_User_Exception_Onboarding_NotAllowedType
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws cs_UserNotFound
	 */
	protected static function _startOnboarding(int $user_id, int $space_id, int $user_space_role, bool $is_postmoderation):void {

		$data = [
			"space_id" => $space_id,
		];

		if ($user_space_role === Domain_Company_Entity_User_Member::ROLE_GUEST) {

			Domain_User_Action_Onboarding_ActivateIfNotExist::do($user_id, Domain_User_Entity_Onboarding::TYPE_SPACE_GUEST, $data);
			return;
		}

		if ($is_postmoderation) {

			Domain_User_Action_Onboarding_ActivateIfNotExist::do($user_id, Domain_User_Entity_Onboarding::TYPE_SPACE_JOIN_REQUEST, $data);
			return;
		}

		Domain_User_Action_Onboarding_ActivateIfNotExist::do($user_id, Domain_User_Entity_Onboarding::TYPE_SPACE_MEMBER, $data);
	}
}
