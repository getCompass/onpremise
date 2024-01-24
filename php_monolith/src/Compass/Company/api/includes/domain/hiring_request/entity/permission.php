<?php

namespace Compass\Company;

use JetBrains\PhpStorm\ArrayShape;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Класс для работы с логикой разрешений на действия найма
 */
class Domain_HiringRequest_Entity_Permission {

	// список возможных ошибок при попытке создать сингл диалог с пользователем при заявке на найм
	protected const _USER_HIRING_CREATE_SINGLE_ERRORS_LIST = [
		1002 => [
			"error_code" => 1002,
			"message"    => "User from single list left company",
			"user_id"    => "",
		],
	];

	/**
	 * Проверяет список сингл диалогов, которые нужно создать, и список групп, в которые нужно добавить, на возможность этого
	 *
	 * @param int   $user_id
	 * @param array $conversation_key_list_to_join
	 * @param array $single_list_to_create
	 *
	 * @return array[]
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws cs_IncorrectConversationKeyListToJoin
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	#[ArrayShape(["user_ok_list" => "array", "group_ok_list" => "array", "user_error_list" => "array", "group_error_list" => "array"])]
	public static function makeUsersAndGroupsOkErrorList(int $user_id, array $conversation_key_list_to_join, array $single_list_to_create):array {

		try {

			// проверяем что пользователь может отправить приглашения в группы из списка
			$group_ok_error_list = self::_isUserCanSendInvitesInGroups($conversation_key_list_to_join, $user_id);

			// проверяем что возможно создать сингл диалоги с пользователями из списка
			$user_ok_error_list = self::_isUsersFromSingleListToCreateExistInCompany($single_list_to_create);
		} catch (ParamException $e) {

			throw new ParamException($e->getMessage());
		} catch (ParseFatalException $e) {
			throw new ParseFatalException($e->getMessage());
		}

		$output["user_ok_list"]     = isset($user_ok_error_list["list_ok"]) ? array_column($user_ok_error_list["list_ok"], "user_id") : [];
		$output["group_ok_list"]    = isset($group_ok_error_list["list_ok"]) ? array_column($group_ok_error_list["list_ok"], "conversation_key") : [];
		$output["user_error_list"]  = $user_ok_error_list["list_error"] ?? [];
		$output["group_error_list"] = $group_ok_error_list["list_error"] ?? [];

		return $output;
	}

	/**
	 * Проверяем что переданные пользователи состоят в компании
	 *
	 * @param array $conversation_key_list_to_join
	 * @param int   $user_id
	 *
	 * @return array
	 * @throws \parseException
	 * @throws cs_IncorrectConversationKeyListToJoin
	 */
	public static function _isUserCanSendInvitesInGroups(array $conversation_key_list_to_join, int $user_id):array {

		return Gateway_Socket_Conversation::isUserCanSendInvitesInGroups($conversation_key_list_to_join, $user_id);
	}

	/**
	 * Проверяем что переданные пользователи состоят в компании
	 *
	 * @param array $single_list_to_create
	 *
	 * @return array
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \returnException
	 */
	#[ArrayShape(["list_ok" => "array", "list_error" => "array"])]
	public static function _isUsersFromSingleListToCreateExistInCompany(array $single_list_to_create):array {

		$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList($single_list_to_create);

		// выполняем проверку
		[$ok_list, $error_list] = self::_checkUsersFromSingleList($user_info_list, $single_list_to_create);

		return [
			"list_ok"    => $ok_list,
			"list_error" => $error_list,
		];
	}

	/**
	 * Проверяет возможность создать сингл диалог с пользователями из списка
	 *
	 * @param array $company_user_info_list
	 * @param array $single_list_to_create
	 *
	 * @return array
	 * @throws \parseException
	 */
	protected static function _checkUsersFromSingleList(array $company_user_info_list, array $single_list_to_create):array {

		$ok_list    = [];
		$error_list = [];

		foreach ($company_user_info_list as $user) {

			// если пользователь кикнут из компании
			if (\CompassApp\Domain\Member\Entity\Member::isDisabledProfile($user->role)) {
				$error_list[] = self::_makeCheckUsersFromSingleListError(1002, $user->user_id);
			} else {

				$ok_list[] = [
					"user_id" => $user->user_id,
				];
			}
		}

		// формируем ошибки для тех, кто не состоит в компании
		$user_not_in_company_error_list = self::_makeUserIdNotInCompanyList($company_user_info_list, $single_list_to_create);
		$error_list                     = array_merge($error_list, $user_not_in_company_error_list);

		return [$ok_list, $error_list];
	}

	/**
	 * Создаёт из массива пользоваетелй с которыми нужно создать сингл диалог и массива пользователей компании, список тех, кто не состоит в компании
	 *
	 * @param array $company_user_info_list
	 * @param array $single_list_to_create
	 *
	 * @return array
	 * @throws \parseException
	 */
	public static function _makeUserIdNotInCompanyList(array $company_user_info_list, array $single_list_to_create):array {

		$company_user_id_list = array_column($company_user_info_list, "user_id");

		$user_not_in_company_id_list    = array_values(array_diff($single_list_to_create, $company_user_id_list));
		$user_not_in_company_error_list = [];

		foreach ($user_not_in_company_id_list as $user_not_in_company_id_list_item) {
			$user_not_in_company_error_list[] = self::_makeCheckUsersFromSingleListError(1002, $user_not_in_company_id_list_item);
		}

		return $user_not_in_company_error_list;
	}

	/**
	 * Выбрасываем исключение если заявка уже отклонена
	 *
	 * @param Struct_Db_CompanyData_HiringRequest $hiring_request
	 *
	 * @throws cs_HiringRequestAlreadyRejected
	 */
	public static function assertHiringRequestAlreadyRejected(Struct_Db_CompanyData_HiringRequest $hiring_request):void {

		if ($hiring_request->status == Domain_HiringRequest_Entity_Request::STATUS_REJECTED) {
			throw new cs_HiringRequestAlreadyRejected();
		}
	}

	/**
	 * Выбрасываем исключение если заявка уже отозвана приглашённым пользователем
	 *
	 * @param Struct_Db_CompanyData_HiringRequest $hiring_request
	 *
	 * @throws Domain_HiringRequest_Exception_AlreadyRevoked
	 */
	public static function assertHiringRequestAlreadyRevoked(Struct_Db_CompanyData_HiringRequest $hiring_request):void {

		if ($hiring_request->status == Domain_HiringRequest_Entity_Request::STATUS_REVOKED) {
			throw new Domain_HiringRequest_Exception_AlreadyRevoked("hiring request already revoked");
		}
	}

	/**
	 * Проверяем доступ пользователя к заявке найма
	 *
	 * @param int                                 $user_id
	 * @param Struct_Db_CompanyData_HiringRequest $hiring_request
	 *
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_UserHasNoRightsToHiring
	 * @throws \cs_UserIsNotMember
	 */
	public static function checkRequestAllowedForUser(int $user_id, Struct_Db_CompanyData_HiringRequest $hiring_request):void {

		try {
			$user = Domain_User_Action_Member_GetShort::do($user_id);
			\CompassApp\Domain\Member\Entity\Permission::assertCanInviteMember($user->role, $user->permissions);
		} catch (\cs_RowIsEmpty) {
			throw new \cs_UserIsNotMember();
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {

			// если пользователь обычный, проверяем кто создавал заявку
			if ($hiring_request->hired_by_user_id != $user_id) {
				throw new cs_UserHasNoRightsToHiring();
			}
		}
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Возвращает ошибку в нужном формате для случая, когда невозможно создать сингл диалог с пользователем
	 *
	 * @param int $error_code
	 * @param int $user_id
	 *
	 * @return array
	 * @throws \parseException
	 */
	protected static function _makeCheckUsersFromSingleListError(int $error_code, int $user_id):array {

		$error_list = self::_USER_HIRING_CREATE_SINGLE_ERRORS_LIST;

		$error = $error_list[$error_code] ?? false;

		if ($error === false) {
			throw new ParseFatalException("invalid error code " . $error_code . " when make check users from single list error");
		}

		$error["user_id"] = $user_id;

		return $error;
	}
}
