<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * Сценарии компании для API
 */
class Domain_Company_Scenario_Api {

	/**
	 * Создает новую компанию для пользователя.
	 *
	 * @long
	 */
	public static function create(int $user_id, int $avatar_color_id, string $name, string $client_company_id, string|false $avatar_file_key):Struct_User_Company {

		$name = Domain_Company_Entity_Sanitizer::sanitizeCompanyName($name);

		// проверяем параметры
		Domain_Company_Entity_Validator::assertIncorrectAvatarColorId($avatar_color_id);
		Domain_Company_Entity_Validator::assertIncorrectName($name);
		Domain_Company_Entity_Validator::assertIncorrectClientCompanyId($client_company_id);

		try {
			Gateway_Bus_PivotCache::getUserInfo($user_id);
		} catch (cs_UserNotFound) {
			throw new \ParamException("user not found {$user_id}");
		}

		// если это onpremise сервер, то проверяем пользователя является ли тот рутом
		if (ServerProvider::isOnPremise() && $user_id !== Domain_User_Entity_OnpremiseRoot::getUserId()) {
			throw new \ParamException("action is not allowed on this environment for user_id: {$user_id}");
		}

		$avatar_file_map = "";
		if ($avatar_file_key !== false) {
			$avatar_file_map = Type_Pack_Main::replaceKeyWithMap("file_key", $avatar_file_key);
		}

		// получаем все компании, где пользователь активный участник
		$company_list = Gateway_Db_PivotUser_CompanyList::getCompanyList($user_id);

		// проверям количество созданных компаний пользователем
		Domain_Company_Entity_Company::checkCountCompanyCreatedByUserId($user_id, $company_list);

		// если компания уже есть в кэше, возвращаем её
		$client_company_cache_id = Domain_Company_Entity_Company::getCompanyInCache($user_id, $client_company_id);
		if ($client_company_cache_id !== false) {
			throw new cs_CompanyIncorrectClientCompanyId();
		}

		// записываем компанию в кэш
		try {
			Domain_Company_Entity_Company::setCompanyInCache($user_id, $client_company_id);
		} catch (\cs_MemcacheRowIfExist) {
			throw new cs_CompanyIncorrectClientCompanyId();
		}

		// первое ли это пространство у пользователя
		$is_user_first_company = count($company_list) < 1;

		// выполняем действия по добавлению компании
		/** @var Struct_Db_PivotCompany_Company $company */
		$is_need_create_intercom_conversation = $is_user_first_company; // если это первое пространство пользователя, то нужно создать диалог в intercom
		[$company, $user_company] = Domain_Company_Action_Take::do($user_id, $avatar_color_id, $name, $client_company_id, $avatar_file_map, $is_need_create_intercom_conversation);

		// получаем данные связи пользователь-компания
		$user_company_api = Struct_User_Company::createFromCompanyStruct($company, Struct_User_Company::ACTIVE_STATUS, $user_company->order);

		// шлем создателю эвент о создании компании
		Gateway_Bus_SenderBalancer::companyCreated($user_id, Apiv1_Format::userCompany($user_company_api));

		if (!ServerProvider::isOnPremise()) {

			// отправляем в партнерку событие о создании пространства
			Domain_Partner_Entity_Event_UserCreateSpace::create($user_id, $user_company_api->company_id);
			Domain_Crm_Entity_Event_SpaceCreate::create($user_company_api->company_id);

		Type_User_ActionAnalytics::send($user_id, Type_User_ActionAnalytics::ADD_SPACE);
		Type_Space_ActionAnalytics::init($user_id)->send($user_company_api->company_id, Type_Space_ActionAnalytics::CREATED);
		Type_Phphooker_Main::sendUserAccountLog($user_id, Type_User_Analytics::ADD_SPACE);
		Type_Phphooker_Main::sendSpaceLog(Type_Space_Analytics::CREATED, $user_company_api->company_id);

			// обновляем в битриксе флаг "владелец пространства"
			Type_Phphooker_Main::sendBitrixOnUserChangedInfo($user_id, [Domain_Bitrix_Action_OnUserChangeData::CHANGED_SPACE_OWN_STATUS => 1]);
		}

		return $user_company_api;
	}

	/**
	 * Сценарий получения списка компаний пользователя
	 *
	 * @param int  $user_id
	 * @param int  $limit
	 * @param int  $min_order
	 * @param bool $only_active
	 *
	 * @return array
	 * @throws cs_CompanyIncorrectLimit
	 * @throws cs_CompanyIncorrectMinOrder
	 */
	public static function getUserCompanyList(int $user_id, int $limit, int $min_order, bool $only_active):array {

		// проверяем
		Domain_Company_Entity_Validator::assertIncorrectLimit($limit);
		Domain_Company_Entity_Validator::assertIncorrectMinOrder($min_order);

		// фильтруем
		$limit = Domain_Company_Entity_Filter::filterUserCompanyListLimit($limit);

		// получаем список компаний
		return Domain_User_Action_GetOrderedCompanyList::do($user_id, $min_order, $limit, $only_active);
	}

	/**
	 * получаем пользовательский токен для сессии
	 *
	 * @throws cs_CompanyUserIsNotFound
	 * @throws \queryException
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public static function getUserCompanySessionToken(int $user_id, string $session_uniq, int $company_id):string {

		// проверяем, что company_id валиден
		Domain_Company_Entity_Validator::assertCorrectCompanyId($company_id);

		// проверяем что пользователь участник
		Domain_Company_Entity_User_Member::getCompanyUser($user_id, $company_id);

		return Domain_Company_Entity_UserCompanySessionToken::create($user_id, $session_uniq, $company_id);
	}

	/**
	 * Установить порядок компаний
	 *
	 * @param int   $user_id
	 * @param array $company_order_list Список с позициями компаний
	 *
	 * @throws cs_DuplicateCompanyId
	 * @throws cs_DuplicateOrder
	 * @throws cs_MissedValue
	 * @throws cs_WrongValue
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function setCompanyListOrder(int $user_id, array $company_order_list):void {

		// приводим элементы к порядку
		$company_order_list = array_values($company_order_list);

		Domain_User_Entity_Company_CompanyOrderList::assertValidInputData($company_order_list);

		Domain_Company_Entity_User_Order::set($user_id, $company_order_list);

		// приведем данные к Int для удобства работы frontend
		$company_order_list = array_map(function(array $company) {

			return ["company_id" => (int) $company["company_id"], "order" => (int) $company["order"]];
		}, $company_order_list);

		Gateway_Bus_SenderBalancer::companyListOrder($user_id, $company_order_list);
	}

	/**
	 * Метод для запроса инвайта по ссылке
	 *
	 * @param int    $user_id
	 * @param string $link
	 * @param string $session_uniq
	 *
	 * @return array
	 * @throws Domain_Company_Exception_IsHibernated
	 * @throws Domain_Company_Exception_IsRelocating
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @throws cs_IncorrectJoinLink
	 * @throws cs_JoinLinkIsNotActive
	 * @throws cs_JoinLinkIsUsed
	 * @throws cs_JoinLinkNotFound
	 * @throws cs_UserAlreadyInCompany
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 */
	public static function validateJoinLink(int $user_id, string $link, string $session_uniq):array {

		// валидируем ссылку-приглашение
		try {

			/** @var Struct_Db_PivotData_CompanyJoinLinkRel $invite_link_rel */
			/** @var Struct_Db_PivotUser_User $user_info */
			/** @var Struct_Db_PivotCompany_Company $company */
			/** @var bool $is_postmoderation */
			[
				$invite_link_rel, $company, $inviter_user_info, $entry_option, $is_postmoderation, $is_waiting_for_postmoderation, $is_exit_status_in_progress,
			] = Domain_Company_Action_JoinLink_ValidateLegacy::do($user_id, $link);

			// добавляем в историю валидацию ссылки
			Domain_Company_Entity_JoinLink_ValidateHistory::add($user_id, $invite_link_rel->join_link_uniq, $session_uniq, $link);
		} catch (\Exception $e) {

			// добавляем в историю ошибку валидации ссылки
			Domain_Company_Entity_JoinLink_ValidateHistory::add($user_id, "", $session_uniq, $link);
			throw $e;
		}

		// формирует ответ
		return [
			"invite_link_uniq"              => (string) $invite_link_rel->join_link_uniq,
			"company_id"                    => (int) $company->company_id,
			"company_name"                  => (string) $company->name,
			"company_avatar_color_id"       => (int) $company->avatar_color_id,
			"company_members_count"         => (int) Domain_Company_Entity_Company::getMemberCount($company->extra),
			"inviter_full_name"             => (string) $inviter_user_info->full_name,
			"inviter_avatar_file_key"       => (string) isEmptyString($inviter_user_info->avatar_file_map) ? "" : Type_Pack_File::doEncrypt($inviter_user_info->avatar_file_map),
			"is_postmoderation"             => (int) $is_postmoderation,
			"is_waiting_for_postmoderation" => (int) $is_waiting_for_postmoderation,
			"is_exit_status_in_progress"    => (int) $is_exit_status_in_progress,
			"company_avatar_file_key"       => (string) isEmptyString($company->avatar_file_map) ? "" : Type_Pack_File::doEncrypt($company->avatar_file_map),
		];
	}

	/**
	 * сценарий подтверждения ссылки инвайта
	 *
	 * @param int    $user_id
	 * @param string $join_link_uniq
	 * @param string $comment
	 * @param string $session_uniq
	 *
	 * @long
	 * @return array
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
	public static function acceptJoinLink(int $user_id, string $join_link_uniq, string $comment, string $session_uniq):array {

		/**
		 * принимаем инвайт
		 *
		 * @var Struct_Db_PivotUser_User                         $user_info
		 * @var Struct_Dto_Socket_Company_AcceptJoinLinkResponse $accept_link_response
		 */
		[$company_id, $company, $accept_link_response, $user_info] = Domain_Company_Action_JoinLink_Accept::do(
			$user_id, $join_link_uniq, $comment, $session_uniq
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

			// отправляем в аналитику, что пользователь ждет одобрения заявки на вступление
			Domain_User_Entity_Attribution_JoinSpaceAnalytics::shouldCollectAnalytics($user_info) && Domain_User_Entity_Attribution_JoinSpaceAnalytics::onUserWaitingPostModerationJoinLink($user_id);
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

		// формирует сущность компании с нужным статусом и отдаем ее
		$frontend_company = Apiv1_Format::formatUserCompany(Struct_User_Company::createFromCompanyStruct(
			$company, $status, $user_company->order, $accept_link_response->inviter_user_id
		));
		Gateway_Bus_SenderBalancer::companyStatusChanged($user_id, $frontend_company);

		return [$frontend_company, $accept_link_response->entry_option];
	}

	/**
	 * Удаляем компанию из списка
	 *
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyIsNotLobby
	 * @throws cs_CompanyNotExist
	 * @throws cs_HiringRequestNotPostmoderation
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_UserAlreadyInCompany
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \userAccessException
	 */
	public static function removeFromList(int $user_id, int $company_id):void {

		Domain_Company_Entity_Validator::assertCorrectCompanyId($company_id);

		// проверяем что компания для пользователя неактивна
		Domain_Company_Entity_User_Member::assertUserIsNotMemberOfCompany($user_id, $company_id);

		$company = Domain_Company_Entity_Company::get($company_id);

		Domain_Company_Action_RemoveFromList::do($user_id, $company);
	}

	/**
	 * Удалить компанию
	 *
	 * @param int          $user_id
	 * @param int          $company_id
	 * @param string|false $two_fa_key
	 *
	 * @return Struct_Db_PivotCompany_Company
	 * @throws Domain_Company_Exception_IsHibernated
	 * @throws Domain_Company_Exception_IsNotServed
	 * @throws Domain_Company_Exception_IsRelocating
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \busException
	 * @throws cs_AnswerCommand
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @throws \cs_CompanyUserIsNotOwner
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_TwoFaInvalidCompany
	 * @throws cs_TwoFaInvalidUser
	 * @throws cs_TwoFaIsExpired
	 * @throws cs_TwoFaIsFinished
	 * @throws cs_TwoFaIsNotActive
	 * @throws cs_TwoFaTypeIsInvalid
	 * @throws cs_UnknownKeyType
	 * @throws \cs_UnpackHasFailed
	 * @throws cs_UserNotInCompany
	 * @throws cs_WrongTwoFaKey
	 * @throws cs_blockException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function delete(int $user_id, int $company_id, string|false $two_fa_key):void {

		// ! частично дублирует socket-сценарий удаления компании

		// проверяем, что company_id валиден
		Domain_Company_Entity_Validator::assertCorrectCompanyId($company_id);

		// проверяем 2fa
		Domain_User_Entity_TwoFa_TwoFa::handle($user_id, Domain_User_Entity_TwoFa_TwoFa::TWO_FA_DELETE_COMPANY, $two_fa_key, $company_id);

		// проверяем, что компания активна
		$company = Domain_Company_Entity_Company::get($company_id);
		Domain_Company_Entity_Company::assertCompanyActive($company);

		// проверяем что пользователь состоит в компании
		Domain_Company_Entity_User_Member::assertUserIsMemberOfCompany($user_id, $company_id);

		// инкрементим блокировку успешного удаления компании
		Type_Antispam_User::throwIfBlocked($user_id, Type_Antispam_User::COMPANY_DELETE);

		// удаляем компанию
		Domain_Company_Action_Delete::do($user_id, $company);

		Type_Phphooker_Main::sendSpaceLog(Type_Space_Analytics::DELETED, $company_id);
		Type_User_ActionAnalytics::send($user_id, Type_User_ActionAnalytics::DELETE_SPACE);
		Type_Space_ActionAnalytics::init($user_id)->send($company_id, Type_Space_ActionAnalytics::DELETED);
	}

	/**
	 * Разбудить компанию
	 *
	 * @param int $user_id
	 * @param int $company_id
	 *
	 * @return int
	 * @throws Domain_Company_Exception_ConfigNotExist
	 * @throws Domain_Company_Exception_IsNotHibernated
	 * @throws Domain_System_Exception_IsNotAllowedServiceTask
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @throws cs_UserNotInCompany
	 * @throws \queryException
	 */
	public static function wakeup(int $user_id, int $company_id):int {

		// проверяем, что company_id валиден и компания существует
		Domain_Company_Entity_Validator::assertCorrectCompanyId($company_id);
		$company_row = Domain_Company_Entity_Company::get($company_id);

		// проверяем что пользователь состоит в компании
		Domain_Company_Entity_User_Member::assertUserIsMemberOfCompany($user_id, $company_id);
		Domain_Company_Entity_Company::assertCompanyIsAwaken($company_row);

		// когда нужно проверить компанию
		$need_check_at = time() + 5;

		// добавляем задачу на пробуждение
		Domain_Company_Entity_ServiceTask::schedule(
			Domain_Company_Entity_ServiceTask::TASK_TYPE_AWAKE,
			0,
			$company_id);

		// сохраним аналитику по пробуждению
		Type_System_Analytic::save($company_id, $company_row->domino_id, Type_System_Analytic::TYPE_AWAKE);

		return $need_check_at;
	}

	/**
	 * Получить статус компании
	 *
	 * @param int $user_id
	 * @param int $company_id
	 *
	 * @return int
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @throws cs_PlatformNotFound
	 * @throws cs_UserNotInCompany
	 */
	public static function checkStatus(int $user_id, int $company_id):int {

		// проверяем, что company_id валиден и компания существует
		Domain_Company_Entity_Validator::assertCorrectCompanyId($company_id);
		$company_row = Domain_Company_Entity_Company::get($company_id);
		$domino      = Gateway_Db_PivotCompanyService_DominoRegistry::getOne($company_row->domino_id);

		// отправим аналитику о вызове метода
		self::_sendAnalyticByPlatform($company_id, $company_row->domino_id);

		// проверяем что пользователь состоит в компании
		Domain_Company_Entity_User_Member::assertUserIsMemberOfCompany($user_id, $company_id);

		return Gateway_Socket_Company::getCompanyConfigStatus($company_row, $domino);
	}

	/**
	 * получаем статус пользователя в компании
	 *
	 * @param int $user_id
	 * @param int $company_id
	 *
	 * @return int
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @long
	 */
	public static function getUserStatus(int $user_id, int $company_id):int {

		// проверяем, что company_id валиден
		Domain_Company_Entity_Validator::assertCorrectCompanyId($company_id);

		// достаем запись компании
		$company = Domain_Company_Entity_Company::get($company_id);

		try {

			// пробуем получить компанию для пользователя
			Domain_Company_Entity_User_Member::getUserCompany($user_id, $company_id);

			// если компания удалена, то отдаем статус что компания удалена
			if ($company->is_deleted) {
				return Domain_Company_Entity_User_Member::DELETED_COMPANY_STATUS;
			}

			return Domain_Company_Entity_User_Member::ACTIVE_USER_COMPANY_STATUS;
		} catch (cs_CompanyUserIsNotFound) {
		}

		// проверяем статус компании в лобби
		return self::_checkUserLobbyStatus($user_id, $company_id, $company->is_deleted == 1);
	}

	/**
	 * проверяем статус компании в лобби
	 */
	protected static function _checkUserLobbyStatus(int $user_id, int $company_id, bool $is_company_deleted):int {

		// пробуем достать компанию из лобби
		try {
			$user_company = Domain_Company_Entity_User_Lobby::get($user_id, $company_id);
		} catch (\cs_RowIsEmpty) {

			// если не нашли в лобби, то возвращаем статус что не участник
			return Domain_Company_Entity_User_Member::NOT_MEMBER_USER_COMPANY_STATUS;
		}

		// если компания в лобби сообщает что уволен из компании
		if (Domain_Company_Entity_User_Lobby::isStatusFired($user_company->status)) {
			return Domain_Company_Entity_User_Member::FIRED_USER_COMPANY_STATUS;
		}

		// если компания в лобби сообщает что компания удалена
		if (Domain_Company_Entity_User_Lobby::isStatusCompanyDeleted($user_company->status)) {
			return Domain_Company_Entity_User_Member::DELETED_COMPANY_STATUS;
		}

		// удалена ли компания
		if ($is_company_deleted) {
			return Domain_Company_Entity_User_Member::DELETED_COMPANY_STATUS;
		}

		// в остальных случаях - пользователь не является участником компании
		return Domain_Company_Entity_User_Member::NOT_MEMBER_USER_COMPANY_STATUS;
	}

	/**
	 * Отправим аналитику по платформе
	 * @throws cs_PlatformNotFound
	 */
	protected static function _sendAnalyticByPlatform(int $company_id, string $domino_id):void {

		$platform = Type_Api_Platform::getPlatform(getUa());

		switch ($platform) {

			case Type_Api_Platform::PLATFORM_ELECTRON:

				// сохраним аналитику по проверке статуса
				Type_System_Analytic::save($company_id, $domino_id, Type_System_Analytic::TYPE_CHECK_STATUS_ELECTRON);
				break;

			case Type_Api_Platform::PLATFORM_ANDROID:

				// сохраним аналитику по проверке статуса
				Type_System_Analytic::save($company_id, $domino_id, Type_System_Analytic::TYPE_CHECK_STATUS_ANDROID);
				break;

			case Type_Api_Platform::PLATFORM_IPAD:
			case Type_Api_Platform::PLATFORM_IOS:

				// сохраним аналитику по проверке статуса
				Type_System_Analytic::save($company_id, $domino_id, Type_System_Analytic::TYPE_CHECK_STATUS_IOS);
				break;
		}
	}

	/**
	 * Сценарий получения списка компаний пользователя по массиву id
	 */
	public static function getBatching(int $user_id, array $company_id_list, bool $only_active):array {

		Domain_Company_Entity_Validator::assertIncorrectCompanyIdList($company_id_list);
		return Domain_User_Action_GetBatchingCompanyList::do($user_id, $company_id_list, $only_active);
	}
}
