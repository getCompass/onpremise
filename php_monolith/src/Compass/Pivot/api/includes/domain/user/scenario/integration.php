<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\InvalidMail;
use BaseFrame\Exception\Domain\InvalidPhoneNumber;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\ExceptionUtils;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\CompanyNotServedException;

/**
 * Сценарии пользователя для Integration
 *
 * Class Domain_User_Scenario_Integration
 */
class Domain_User_Scenario_Integration {

	/**
	 * Сценарий получения user_id по номеру телефона
	 *
	 * @param string $phone_number
	 *
	 * @return int
	 * @throws InvalidPhoneNumber
	 * @throws cs_UserNotFound
	 */
	public static function getUserIdByPhoneNumber(string $phone_number):int {

		Domain_User_Entity_Validator::assertValidPhoneNumber($phone_number);

		try {
			$user_id = Domain_User_Entity_Phone::getUserIdByPhone($phone_number);
		} catch (cs_PhoneNumberNotFound) {
			throw new cs_UserNotFound();
		}

		return $user_id;
	}

	/**
	 * Сценарий получения user_id по почте
	 *
	 * @param string $mail
	 *
	 * @return int
	 * @throws InvalidMail
	 * @throws cs_UserNotFound
	 */
	public static function getUserIdByMail(string $mail):int {

		$mail_obj = new \BaseFrame\System\Mail($mail);

		try {
			$user_id = Domain_User_Entity_Mail::getUserIdByMail($mail_obj->mail());
		} catch (Domain_User_Exception_Mail_NotFound) {
			throw new cs_UserNotFound();
		}

		return $user_id;
	}

	/**
	 * Сценарий обновления профиля
	 *
	 * @param int          $user_id
	 * @param string|false $name
	 * @param string|false $avatar_file_map
	 * @param string|false $description
	 * @param string|false $status
	 *
	 * @return Struct_User_Info
	 * @throws Domain_User_Exception_AvatarIsDeleted
	 * @throws ReturnFatalException
	 * @throws ParseFatalException
	 * @throws BusFatalException
	 * @throws CompanyNotServedException
	 * @throws \busException
	 * @throws \cs_InvalidProfileName
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_FileIsNotImage
	 * @throws cs_InvalidAvatarFileMap
	 */
	public static function setProfile(int $user_id, string|false $name, string|false $avatar_file_map, string|false $description, string|false $status):Struct_User_Info {

		if ($name !== false) {

			$name = Domain_User_Entity_Sanitizer::sanitizeProfileName($name);
			Domain_User_Entity_Validator::assertValidProfileName($name);
		}

		if ($avatar_file_map !== false) {

			Domain_User_Entity_Validator::assertValidAvatarFileMap($avatar_file_map);
			$avatar_file_key = Type_Pack_File::doEncrypt($avatar_file_map);

			// получаем аватар, чтобы убедиться, что он не удален
			$is_deleted = Gateway_Socket_PivotFileBalancer::checkIsDeleted($avatar_file_key);

			if ($is_deleted) {
				throw new Domain_User_Exception_AvatarIsDeleted("avatar is deleted");
			}
		}

		/** @noinspection PhpUnusedLocalVariableInspection */
		[$is_profile_was_empty, $user_info] = Domain_User_Action_UpdateProfile::do($user_id, $name, $avatar_file_map);

		if ($description !== false || $status !== false) {
			self::_updateCompanyMemberInfo($user_info->user_id, $description, $status);
		}

		return $user_info;
	}

	/**
	 * Обновляем данные участника компании
	 *
	 * @param int          $user_id
	 * @param string|false $description
	 * @param string|false $status
	 *
	 * @return void
	 * @throws CompanyNotServedException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	protected static function _updateCompanyMemberInfo(int $user_id, string|false $description, string|false $status):void {

		$user_company_list = Gateway_Db_PivotUser_CompanyList::getCompanyList($user_id);
		if (count($user_company_list) < 1) {
			return;
		}

		$company_id_list = array_column($user_company_list, "company_id");
		$company_list    = Gateway_Db_PivotCompany_CompanyList::getList($company_id_list);
		foreach ($company_list as $company) {

			$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);

			// пропускаем если компания неактивная
			if (!Domain_Company_Entity_Company::isCompanyActive($company)) {
				continue;
			}

			try {

				Gateway_Socket_Company::updateMemberInfo(
					$company->domino_id, $company->company_id, $private_key,
					$user_id, $description, $status, false, false,
				);
			} catch (Gateway_Socket_Exception_CompanyIsNotServed|cs_CompanyIsHibernate) {

				Type_System_Admin::log("update_employee_info_in_company_error",
					"Не смогли обновить данные пользователя {$user_id} в компании {$company->company_id}");
			} catch (\cs_SocketRequestIsFailed $e) {

				// пишем лог в файл
				$exception_message = ExceptionUtils::makeMessage($e, HTTP_CODE_500);
				ExceptionUtils::writeExceptionToLogs($e, $exception_message);
			}
		}
	}

	/**
	 * Сценарий очистки аватарки
	 *
	 * @param int $user_id
	 *
	 * @return void
	 * @throws \parseException
	 */
	public static function doClearAvatar(int $user_id):void {

		Domain_User_Action_ClearAvatar::do($user_id);
	}

	/**
	 * Сценарий удаления пользователя из компаний
	 *
	 * @param int $user_id
	 *
	 * @return void
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_CompanyNotExist
	 */
	public static function kickFromCompanies(int $user_id):void {

		// получаем все активные компании для пользователя
		$user_company_list = Gateway_Db_PivotUser_CompanyList::getCompanyList($user_id);
		if (count($user_company_list) < 1) {
			return;
		}

		foreach ($user_company_list as $user_company) {

			$company     = Domain_Company_Entity_Company::get($user_company->company_id);
			$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);

			try {

				/** @noinspection PhpUnusedLocalVariableInspection */
				[$description, $badge, $user_role] = Gateway_Socket_Company::getUserInfo($user_id, $company->company_id, $company->domino_id, $private_key);
			} catch (cs_UserNotFound) {
				continue;
			}

			// для каждой компании ставим задачу на исключение
			Type_System_Admin::log("user-kicker", "Ставлю задачу на исключение пользователя {$user_id} из компании {$user_company->company_id}");
			Type_Phphooker_Main::onKickUserFromCompanyRequested($user_id, $user_role, $user_company->company_id);
		}
	}

	/**
	 * сценарий подтверждения ссылки инвайта
	 *
	 * @param int    $user_id
	 * @param string $join_link_uniq
	 * @param string $comment
	 * @param string $session_uniq
	 *
	 * @throws Domain_Company_Exception_IsHibernated
	 * @throws Domain_Company_Exception_IsRelocating
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @throws cs_ExitTaskInProgress
	 * @throws cs_IncorrectJoinLink
	 * @throws cs_JoinLinkIsNotActive
	 * @throws cs_JoinLinkIsUsed
	 * @throws cs_Text_IsTooLong
	 * @throws cs_UserAlreadyInCompany
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 * @throws cs_RowDuplication
	 * @long
	 */
	public static function acceptJoinLink(int $user_id, string $join_link_uniq, string $comment, bool $force_postmoderation):void {

		/**
		 * принимаем инвайт
		 *
		 * @var Struct_Db_PivotUser_User                         $user_info
		 * @var Struct_Dto_Socket_Company_AcceptJoinLinkResponse $accept_link_response
		 */
		[$company_id, $company, $accept_link_response, $user_info] = Domain_Company_Action_JoinLink_Accept::do(
			$user_id, $join_link_uniq, $comment, "", $force_postmoderation
		);

		$order = Domain_Company_Entity_User_Order::getMaxOrder($user_id);
		$order++;

		// в зависмости от одобренности инвайта изменяем
		if ($accept_link_response->is_postmoderation) {

			// добавляем пользователя в компанию
			$user_company = Domain_Company_Entity_User_Lobby::addPostModeratedUser(
				$user_id,
				$company_id,
				$order,
				$accept_link_response->inviter_user_id,
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
				$company_id,
				$order,
				Type_User_Main::NPC_TYPE_HUMAN,
				$accept_link_response->company_push_token,
				$accept_link_response->entry_id
			);

			$status = Struct_User_Company::ACTIVE_STATUS;

			// логируем, что пользователь принял приглашение по ссылке без модерации
			Type_Phphooker_Main::sendUserAccountLog($user_id, Type_User_Analytics::JOINED_SPACE);
		}

		// формирует сущность компании с нужным статусом
		$frontend_company = Apiv1_Format::formatUserCompany(Struct_User_Company::createFromCompanyStruct(
			$company, $status, $user_company->order, $accept_link_response->inviter_user_id
		));
		Gateway_Bus_SenderBalancer::companyStatusChanged($user_id, $frontend_company);
	}

	/**
	 * получаем номер телефона, почту пользователя
	 *
	 * @return array
	 */
	public static function getUserPhoneNumberMail(int $user_id):array {

		// ответ по умолчанию
		$phone_number = "";
		$mail         = "";

		// получаем запись с такой информацией
		try {

			$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);
			$phone_number  = $user_security->phone_number;
			$mail          = $user_security->mail;
		} catch (\cs_RowIsEmpty) {
		}

		return [$phone_number, $mail];
	}

	/**
	 * получаем количество компаний пользователя
	 *
	 * @param int $user_id
	 *
	 * @return int
	 */
	public static function getUserCompanyCount(int $user_id):int {

		// получаем компании пользователя
		$user_company_list = Gateway_Db_PivotUser_CompanyList::getCompanyList($user_id);

		return count($user_company_list);
	}

	/**
	 * устанавливаем права участнику пространства
	 *
	 * @throws CompanyNotServedException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_CompanyIsNotActive
	 * @throws cs_UserNotFound
	 */
	public static function setUserSpacePermissions(int $user_id, int $space_id, array $permissions):void {

		// получаем информацию о пространстве
		$space = Gateway_Db_PivotCompany_CompanyList::getOne($space_id);

		// если пространство не активно
		if (!Domain_Company_Entity_Company::isCompanyActive($space)) {
			throw new cs_CompanyIsNotActive();
		}

		Gateway_Socket_Company::setMemberPermissions($space, $user_id, $permissions);
	}
}
