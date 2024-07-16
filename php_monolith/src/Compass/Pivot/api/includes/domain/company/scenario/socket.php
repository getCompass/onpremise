<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\CompanyNotServedException;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;

/**
 * Сценарии компании для API
 */
class Domain_Company_Scenario_Socket {

	/**
	 * Сценарий для получения информации авторизации в компании.
	 * На вход берет токен авторизации и возвращает информацию для создании сессии компании.
	 *
	 * @param int    $user_id
	 * @param int    $company_id
	 * @param string $user_company_session_token
	 *
	 * @throws \blockException
	 * @throws cs_InvalidUserCompanySessionToken
	 * @throws \parseException
	 */
	public static function checkUserCompanySessionToken(int $user_id, int $company_id, string $user_company_session_token):void {

		// проверяем блокировку
		Type_Antispam_Company::check($company_id, Type_Antispam_Company::USER_COMPANY_SESSION_TOKEN_LIMIT);

		try {

			Domain_Company_Entity_UserCompanySessionToken::assert($user_id, $company_id, $user_company_session_token);
		} catch (cs_InvalidUserCompanySessionToken $e) {

			Type_Antispam_Company::checkAndIncrementBlock($company_id, Type_Antispam_Company::USER_COMPANY_SESSION_TOKEN_LIMIT);
			throw $e;
		}
	}

	/**
	 * сценарий для обновления статуса_алиаса ссылки-инвайта
	 *
	 * @param string $join_link_uniq
	 * @param int    $status_alias
	 *
	 * @throws \parseException
	 */
	public static function updateJoinLinkStatus(string $join_link_uniq, int $status_alias):void {

		Domain_Company_Action_JoinLink_UpdateStatus::do($join_link_uniq, $status_alias);
	}

	/**
	 * сценарий для изменения имени компании
	 *
	 * @param int    $company_id
	 * @param string $name
	 *
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function setName(int $company_id, string $name):void {

		Domain_Company_Action_SetName::do($company_id, $name);
	}

	/**
	 * сценарий для изменения цвета аватарки компании
	 *
	 * @param int $company_id
	 * @param int $avatar_color_id
	 *
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function setAvatar(int $company_id, int $avatar_color_id):void {

		Domain_Company_Action_SetAvatar::do($company_id, $avatar_color_id);
	}

	/**
	 * сценарий для изменения основных данных профиля компании
	 *
	 * @param int          $company_id
	 * @param string|false $name
	 * @param int|false    $avatar_color_id
	 *
	 * @return array
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function setBaseInfo(int $company_id, string|false $name, int|false $avatar_color_id):array {

		Domain_Company_Action_SetBaseInfo::do($company_id, $name, $avatar_color_id);

		$company = Domain_Company_Entity_Company::get($company_id);

		return [$company->name, $company->avatar_color_id];
	}

	/**
	 * сценарий для изменения основных данных профиля компании
	 */
	public static function clearAvatar(int $company_id):void {

		Domain_Company_Action_ClearAvatar::do($company_id);
	}

	/**
	 * Сценарий исключения пользователя из компании
	 *
	 * @param int    $company_id
	 * @param int    $user_id
	 * @param int    $user_role
	 * @param bool   $need_add_user_lobby
	 * @param string $reason
	 *
	 * @return bool
	 * @throws Domain_User_Exception_Onboarding_NotAllowedStatus
	 * @throws Domain_User_Exception_Onboarding_NotAllowedStatusStep
	 * @throws Domain_User_Exception_Onboarding_NotAllowedType
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws BusFatalException
	 * @throws EndpointAccessDeniedException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @throws cs_UserNotFound
	 */
	public static function kickMember(int $company_id, int $user_id, int $user_role, bool $need_add_user_lobby, string $reason):bool {

		// получаем запись пользователя в компании
		try {
			$user_company = Domain_Company_Entity_User_Member::getUserCompany($user_id, $company_id);
		} catch (cs_CompanyUserIsNotFound) {

			// достаем запись из лобби; если уже помечен как уволенный, то дальше не идём
			$user_company = Domain_Company_Entity_User_Lobby::get($user_id, $company_id);
			if (Domain_Company_Entity_User_Lobby::isStatusFired($user_company->status)) {
				return true;
			}
		}

		// если необходимо - добавляем пользователя в лобби как покинувшего компанию
		if ($need_add_user_lobby) {
			Domain_Company_Entity_User_Lobby::addFiredUser($user_id, $company_id, $user_company->order, $user_company->entry_id);
		}

		// удаляем компанию пользователя из списка активных компаний
		Gateway_Db_PivotCompany_CompanyUserList::delete($user_id, $company_id);
		Gateway_Db_PivotUser_CompanyList::delete($user_id, $company_id);
		Type_Phphooker_Main::sendUserAccountLog($user_id, Type_User_Analytics::LEFT_SPACE);

		// пытаемся прыгнуть на бесплатный тарифный план
		$space = Domain_Company_Entity_Company::get($company_id);
		Domain_SpaceTariff_Action_TrySwitchToFreeMemberCount::run($space);

		// пересчитываем счетчик участников/гостей пространства
		$space = Domain_Company_Entity_Company::incCounterByRole($company_id, Type_User_Main::NPC_TYPE_HUMAN, $user_role, -1);

		// если участников стало 1 - сбрасываем онбординг у создателя компании
		if ((Domain_Company_Entity_Company::getMemberCount($space->extra) + Domain_Company_Entity_Company::getGuestCount($space->extra)) === 1) {
			Domain_User_Action_Onboarding_Clear::do($space->created_by_user_id, Domain_User_Entity_Onboarding::TYPE_SPACE_CREATOR);
		}

		// если аккаунт пользователя был удален
		$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);
		if (!Type_User_Main::isDisabledProfile($user_info->extra)) {

			// отправляем пользователю WS событие, что его уволили
			Gateway_Bus_SenderBalancer::userFired($user_id, $company_id, $reason);
		}

		// пушим событие в партнерку и crm
		Domain_Partner_Entity_Event_UserLeftSpace::create($user_id, $company_id, $user_role);
		Domain_Crm_Entity_Event_SpaceLeaveMember::create($company_id, $user_id, $user_role);

		// если пользователь покинул команду после пробывания в ней не больше суток, то проверим
		if (Domain_User_Entity_Attribution_JoinSpaceAnalytics::isUserLeftSpaceEarly($user_company->created_at, time())) {
			Type_Phphooker_Main::onUserLeftSpaceEarly($user_id, $company_id, $user_company->entry_id);
		}

		return true;
	}

	/**
	 * Метод для активации пользователя
	 *
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @throws cs_CompanyUserIsNotFound
	 * @throws \cs_RowIsEmpty
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 */
	public static function doConfirmHiringRequest(int $user_id, int $company_id, int $user_space_role, int $user_space_permissions, string $user_company_token, int $inviter_user_id, int $approved_by_user_id):void {

		$user_info  = Gateway_Bus_PivotCache::getUserInfo($user_id);
		$user_lobby = Domain_Company_Entity_User_Lobby::get($user_id, $company_id);

		// удаляем пользователя из лобби
		Domain_Company_Entity_User_Lobby::delete($user_id, $company_id);

		Domain_Company_Entity_User_Member::add($user_id, $user_space_role, $user_space_permissions, $user_info->created_at, $company_id, $user_lobby->order,
			$user_info->npc_type, $user_company_token, $user_lobby->entry_id);

		// проверяем, является ли пользователь участником компании
		$user_company = Domain_Company_Entity_User_Member::getUserCompany($user_id, $company_id);

		$company = Domain_Company_Entity_Company::get($company_id);

		$prepared_user_company = Struct_User_Company::createFromCompanyStruct(
			$company, Struct_User_Company::ACTIVE_STATUS, $user_company->order, $inviter_user_id
		);
		$prepared_user_company = Struct_User_Company::addApprovedUserInfo($prepared_user_company, $approved_by_user_id);
		$frontend_company      = Apiv1_Format::formatUserCompany($prepared_user_company);
		Domain_Company_Entity_JoinLink_UserRel::setStatus($user_lobby->entry_id, $user_lobby->user_id, $user_lobby->company_id,
			Domain_Company_Entity_JoinLink_UserRel::JOIN_LINK_REL_ACCEPTED);

		$push_data = Domain_Company_Entity_Push::makeConfirmPushData($company_id, $inviter_user_id, $company->name);
		Gateway_Bus_SenderBalancer::companyStatusChanged($user_id, $frontend_company, $push_data);
	}

	/**
	 * Сценарий отзыва приглашения в компанию
	 *
	 * @param int $user_id
	 * @param int $inviter_user_id
	 * @param int $company_id
	 *
	 * @return void
	 * @throws \busException
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \userAccessException
	 */
	public static function doRejectHiringRequest(int $user_id, int $inviter_user_id, int $company_id):void {

		try {

			// проверяем, является ли пользователь участником компании
			$user_lobby = Domain_Company_Entity_User_Lobby::get($user_id, $company_id);

			// если заявка на пост модерации
			Domain_Company_Entity_User_Lobby::reject($user_id, $company_id, $user_lobby->order, $user_lobby->entry_id);
			Domain_Company_Entity_JoinLink_UserRel::setStatus($user_lobby->entry_id, $user_lobby->user_id, $user_lobby->company_id,
				Domain_Company_Entity_JoinLink_UserRel::JOIN_LINK_REL_REJECTED);
		} catch (\cs_RowIsEmpty) {
			// ничего не делаем
		}

		// получаем данные по тому кто пригласил
		$user_info = Gateway_Bus_PivotCache::getUserInfo($inviter_user_id);
		$user_info = Struct_User_Info::createStruct($user_info);
		$company   = Domain_Company_Entity_Company::get($company_id);
		$push_data = Domain_Company_Entity_Push::makeRejectedPushData($company_id, $company->name);
		Gateway_Bus_SenderBalancer::companyStatusRejected($user_id, $user_info, $company_id, $company->name, $push_data);

		// помечаем в аналитике join-space что заявка отменена
		// делаем для всех случаев тк это дешевле, чем делать доп. выборки из базы чтобы отфильтровать лишние кейсы
		// это действие ничему не навредит
		Domain_User_Entity_Attribution_JoinSpaceAnalytics::onUserPostModerationRequestCanceled($user_id);
	}

	/**
	 * создаем ссылку-инвайт
	 *
	 * @param int $company_id
	 * @param int $status_alias
	 *
	 * @return string
	 * @throws \queryException
	 */
	public static function createJoinLink(int $company_id, int $status_alias):string {

		return Domain_Company_Action_JoinLink_Create::do($company_id, $status_alias);
	}

	/**
	 * начинаем гибернацию
	 *
	 * @param int $company_id
	 *
	 * @throws Domain_System_Exception_IsNotAllowedServiceTask
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyIsNotActive
	 * @throws \cs_RowIsEmpty
	 * @throws \queryException
	 */
	public static function startHibernate(int $company_id):void {

		$company_row = Gateway_Db_PivotCompany_CompanyList::getOne($company_id);

		// работает только с активными компаниями
		if ($company_row->status !== Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE) {
			throw new cs_CompanyIsNotActive();
		}

		// сохраним аналитику по гибернации
		Type_System_Analytic::save($company_id, $company_row->domino_id, Type_System_Analytic::TYPE_HIBERNATE);

		// добавляем задачу в хукер для гибернации компании
		Domain_Company_Entity_ServiceTask::schedule(Domain_Company_Entity_ServiceTask::TASK_TYPE_HIBERNATION_STEP_ONE, 0, $company_id);
	}

	/**
	 * Залочить компанию для миграции
	 *
	 * @param string $domino_id
	 * @param int    $company_id
	 * @param bool   $need_port
	 *
	 * @return void
	 * @throws Domain_Company_Exception_IsBusy
	 * @throws Domain_Domino_Exception_PortBindingIsNotAllowed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \busException
	 * @throws \returnException
	 */
	public static function lockBeforeMigration(string $domino_id, int $company_id, bool $need_port):void {

		Gateway_Db_PivotCompanyService_Main::beginTransaction();
		$company_registry_row = Gateway_Db_PivotCompanyService_CompanyRegistry::getForUpdate($domino_id, $company_id);

		if ($company_registry_row->is_busy) {

			Gateway_Db_PivotCompanyService_Main::rollback();
			throw new Domain_Company_Exception_IsBusy("company is busy already");
		}

		Gateway_Db_PivotCompanyService_CompanyRegistry::set($domino_id, $company_id, [
			"is_busy" => 1,
		]);

		Gateway_Db_PivotCompanyService_Main::commitTransaction();
		if ($need_port) {

			$port_registry_row   = Gateway_Db_PivotCompanyService_PortRegistry::getServiceVoidPortForUpdate($domino_id);
			$domino_registry_row = Gateway_Db_PivotCompanyService_DominoRegistry::getOne($domino_id);
			Domain_Domino_Action_Port_Bind::run($domino_registry_row, $port_registry_row, $company_id, Domain_Domino_Action_Port_Bind::POLICY_MIGRATING);
		}
	}

	/**
	 * Разлочить компанию после миграции
	 *
	 * @param string $domino_id
	 * @param int    $company_id
	 * @param bool   $is_service_port
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \busException
	 * @throws \returnException
	 */
	public static function unlockAfterMigration(string $domino_id, int $company_id, bool $is_service_port):void {

		Gateway_Db_PivotCompanyService_CompanyRegistry::set($domino_id, $company_id, [
			"is_busy" => 0,
		]);

		if ($is_service_port) {

			$port_registry_row   = Gateway_Db_PivotCompanyService_PortRegistry::getServiceVoidPortForUpdate($domino_id);
			$domino_registry_row = Gateway_Db_PivotCompanyService_DominoRegistry::getOne($domino_id);
			Domain_Domino_Action_Port_Unbind::run($domino_registry_row, $port_registry_row, "unlockAfterMigration");
		}
	}

	/**
	 * @param string $domino_id
	 *
	 * @return bool[]
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getDominoMigrationOptions(string $domino_id):array {

		$domino_registry_row = Gateway_Db_PivotCompanyService_DominoRegistry::getOne($domino_id);

		// определяем, какому тир надо делать бэкап
		[$need_backup, $need_wakeup] = match ($domino_registry_row->tier) {

			Domain_Domino_Entity_Registry_Main::TIER_FREE, Domain_Domino_Entity_Registry_Main::TIER_PREPAYING => [true, false],
			Domain_Domino_Entity_Registry_Main::TIER_PAYING                                                   => [true, true],
			default                                                                                           => [false, false],
		};

		return [$need_backup, $need_wakeup];
	}

	/**
	 * сценарий для изменения основных данных компании
	 */
	public static function changeInfo(int $company_id, string|false $name, string|false $avatar_file_key):array {

		$avatar_file_map = false;
		if ($avatar_file_key !== false) {
			$avatar_file_map = Type_Pack_Main::replaceKeyWithMap("file_key", $avatar_file_key);
		}

		Domain_Company_Action_ChangeInfo::do($company_id, $name, $avatar_file_map);

		$company = Domain_Company_Entity_Company::get($company_id);

		return [$company->name, isEmptyString($company->avatar_file_map) ? "" : Type_Pack_File::doEncrypt($company->avatar_file_map)];
	}

	/**
	 * Получаем идентификаторы компаний находящихся в статусе COMPANY_STATUS_ACTIVE
	 *
	 * @return array
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \parseException
	 */
	public static function getActiveCompanyIdList():array {

		// сюда сложим список всех идентификаторов
		$output = [];

		$count  = 1000;
		$offset = 0;
		do {

			// получаем порцию компаний в статусе COMPANY_STATUS_ACTIVE
			$company_list = Gateway_Db_PivotCompany_CompanyList::getByStatusList([Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE], $count, $offset);

			// складываем идентификаторы в ответ
			$output = array_merge($output, array_column($company_list, "company_id"));

			// если получили запрашиваемое кол-во компаний, то делаем еще одну итерацию
			$has_next = count($company_list) == $count;
		} while ($has_next);

		return $output;
	}

	/**
	 * сценарий для получения списка компаний готовых к переезду
	 */
	public static function getTierList(int $limit, int $offset):array {

		if ($limit < 1 || $offset < 0) {
			throw new \BaseFrame\Exception\Request\ParamException("passed incorrect offset or limit");
		}

		// получаем список компаний
		$ready_to_relocation_list = Gateway_Db_PivotCompany_CompanyTierObserve::getReadyToRelocationList($limit, $offset);
		$company_id_list          = array_column($ready_to_relocation_list, "company_id");

		// получаем инфу по компаниям
		$assoc_company_info_list = Gateway_Db_PivotCompany_CompanyList::getList($company_id_list, true);
		$avatar_url_list         = self::_getCompanyAvatarUrlList($assoc_company_info_list);

		$company_list = [];
		foreach ($ready_to_relocation_list as $item) {

			// убираем из ответа те, которые уже в процессе релокации
			if (Domain_Company_Entity_Tier::getIsRelocating($item->extra) == 1) {
				continue;
			}

			$temp                 = (array) $item;
			$temp["company_name"] = $assoc_company_info_list[$item->company_id]->name;
			$temp["avatar_url"]   = $avatar_url_list[$item->company_id] ?? "";
			$company_list[]       = $temp;
		}

		return $company_list;
	}

	/**
	 * начинаем процесс переезда компании на другое домино
	 */
	public static function relocateToAnotherDomino(int $company_id, int $expected_domino_tier, int $relocate_at):string {

		// проверяем что компания существует
		try {
			$company_tier = Gateway_Db_PivotCompany_CompanyTierObserve::get($company_id);
			$company      = Gateway_Db_PivotCompany_CompanyList::getOne($company_id);
		} catch (\cs_RowIsEmpty|\BaseFrame\Exception\Gateway\RowNotFoundException) {
			throw new Domain_Company_Exception_NotExist("company not exist");
		}

		// если попытались переместить на домино не подходящее по рангу
		if ($company_tier->expected_domino_tier != $expected_domino_tier) {
			throw new \BaseFrame\Exception\Request\ParamException("passed incorrect params");
		}

		// проверяем что компания живая
		if (!in_array($company->status, [Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE, Domain_Company_Entity_Company::COMPANY_STATUS_HIBERNATED])) {
			throw new Domain_Company_Exception_IsNotServed("company not served");
		}

		// проверяем что домино существует и получаем для релокации
		try {
			$domino = Gateway_Db_PivotCompanyService_DominoRegistry::getOneForRelocateByTier($company->domino_id, $expected_domino_tier);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {
			throw new Domain_Domino_Exception_DominoNotFound("domino not found");
		}

		// добавляем задачу на релокацию
		$task_delay = ($relocate_at - time()) < 0 ? 0 : ($relocate_at - time());
		$task_id    = Domain_Company_Entity_ServiceTask_Relocation::schedule($company, $domino, $task_delay, 60 * 10, 60);

		// помечаем, что начался процесс переезда
		Domain_Company_Entity_Tier::markRelocatingStarted($company_tier, $task_id);
		return $domino->domino_id;
	}

	/**
	 * сценарий для получения списка компаний в процессе переезда
	 */
	public static function getRelocationList(int $limit, int $offset):array {

		if ($limit < 1 || $offset < 0) {
			throw new \BaseFrame\Exception\Request\ParamException("passed incorrect offset or limit");
		}

		// получаем список компаний
		$ready_to_relocation_list = Gateway_Db_PivotCompany_CompanyTierObserve::getReadyToRelocationList($limit, $offset);

		$relocation_list = [];
		$task_id_list    = [];
		foreach ($ready_to_relocation_list as $company) {

			if (Domain_Company_Entity_Tier::getIsRelocating($company->extra) == 1) {

				$relocation_list[]                  = $company;
				$task_id_list[$company->company_id] = Domain_Company_Entity_Tier::getRelocatingTaskId($company->extra);
			}
		}
		if ($relocation_list < 1) {
			return [];
		}

		// получаем инфу по компаниям
		$company_id_list         = array_column($relocation_list, "company_id");
		$assoc_company_info_list = Gateway_Db_PivotCompany_CompanyList::getList($company_id_list, true);
		$avatar_url_list         = self::_getCompanyAvatarUrlList($assoc_company_info_list);
		$relocation_step_list    = self::_getRelocationStepList($task_id_list);

		return self::_makeRelocationCompanyList($relocation_list, $assoc_company_info_list, $avatar_url_list, $relocation_step_list);
	}

	/**
	 * формируем массив company_list
	 *
	 * @param array                                              $relocation_list
	 * @param array                                              $assoc_company_info_list
	 * @param array                                              $avatar_url_list
	 * @param Struct_Db_PivotCompanyService_CompanyServiceTask[] $relocation_step_list
	 *
	 * @return array
	 */
	protected static function _makeRelocationCompanyList(array $relocation_list, array $assoc_company_info_list, array $avatar_url_list, array $relocation_step_list):array {

		$company_list = [];
		foreach ($relocation_list as $item) {

			$temp                    = (array) $item;
			$temp["company_name"]    = $assoc_company_info_list[$item->company_id]->name;
			$temp["avatar_url"]      = $avatar_url_list[$item->company_id] ?? "";
			$temp["relocation_step"] = $relocation_step_list[$item->company_id]->type;
			$temp["started_at"]      = $relocation_step_list[$item->company_id]->started_at;
			$temp["need_work"]       = $relocation_step_list[$item->company_id]->need_work;
			$temp["is_failed"]       = $relocation_step_list[$item->company_id]->is_failed;
			$temp["finished_at"]     = $relocation_step_list[$item->company_id]->finished_at;
			$company_list[]          = $temp;
		}

		return $company_list;
	}

	/**
	 * получаем шаг релокации компаний
	 *
	 * @return Struct_Db_PivotCompanyService_CompanyServiceTask[]
	 */
	protected static function _getRelocationStepList(array $task_id_list):array {

		$list = Gateway_Db_PivotCompanyService_CompanyServiceTask::getList($task_id_list);

		$relocation_list = [];
		foreach ($list as $item) {
			$relocation_list[$item->company_id] = $item;
		}

		return $relocation_list;
	}

	/**
	 * удаляем компанию
	 *
	 * @throws Domain_Company_Exception_IsHibernated
	 * @throws Domain_Company_Exception_IsNotServed
	 * @throws Domain_Company_Exception_IsRelocating
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @throws \cs_CompanyUserIsNotOwner
	 * @throws cs_UserNotInCompany
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function delete(int $user_id, int $company_id):void {

		// !! частично дублирует api-сценарий удаления компании

		// проверяем, что company_id валиден
		Domain_Company_Entity_Validator::assertCorrectCompanyId($company_id);

		// проверяем, что компания активна
		$company = Domain_Company_Entity_Company::get($company_id);
		Domain_Company_Entity_Company::assertCompanyActive($company);

		// проверяем, что пользователь состоит в компании
		Domain_Company_Entity_User_Member::assertUserIsMemberOfCompany($user_id, $company_id);

		// удаляем компанию
		Domain_Company_Action_Delete::do($user_id, $company);
	}

	/**
	 * получаем данные для аналитики по компании
	 *
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 */
	public static function getAnalyticsInfo(int $company_id):array {

		$company = Domain_Company_Entity_Company::get($company_id);

		$tariff_rows = Gateway_Db_PivotCompany_TariffPlan::getBySpace($company_id);
		$tariff      = Domain_SpaceTariff_Tariff::load($tariff_rows);

		// получаем лимит для пользователей в пространстве
		$max_member_count = $tariff->memberCount()->getLimit();

		// получаем всех, кто платил за пространство
		$payment_info_list  = array_column($tariff_rows, "payment_info");
		$user_id_payer_list = [];
		foreach ($payment_info_list as $payment_info) {
			$user_id_payer_list[] = $payment_info["data"]["customer_user_id"];
		}

		// получаем текущий статус тарифа в пространстве
		$tariff_status = Type_Space_Analytics::getAnalyticTariffStatus($tariff->memberCount());

		return [$company->created_by_user_id, $tariff_status, $max_member_count, $user_id_payer_list, $company->deleted_at];
	}

	/**
	 * При переводе гостя в роль участника пространства
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public static function onUpgradeGuest(int $company_id):void {

		// пересчитываем счетчики:
		// уменьшаем кол-во гостей
		Domain_Company_Entity_Company::incGuestCount($company_id, Type_User_Main::NPC_TYPE_HUMAN, -1);

		// увеличиваем кол-во участников
		Domain_Company_Entity_Company::incMemberCount($company_id, Type_User_Main::NPC_TYPE_HUMAN);
	}

	/**
	 * Проверяет что указанный пользователь может начать
	 * видеоконференцию в указанном пространстве.
	 *
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_CompanyIsHibernate
	 */
	#[\JetBrains\PhpStorm\ArrayShape([0 => 'bool', 1 => "int"])]
	public static function isMediaConferenceCreatingAllowed(int $user_id, int $company_id):array {

		try {

			$company_row = Domain_Company_Entity_Company::get($company_id);
		} catch (cs_CompanyNotExist|cs_CompanyIncorrectCompanyId) {
			return [false, -1];
		}

		// вернем ответ как есть, в текущей реализации нет смысле менять коды ошибок
		// или как-то еще обрабатывать результат, пивот работает как прокси по сути
		return Gateway_Socket_Company::isMediaConferenceCreatingAllowed($company_row, $user_id);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получаем аватарки компаний
	 */
	protected static function _getCompanyAvatarUrlList(array $assoc_company_info_list):array {

		// формируем массив file_map для получения аватарок
		$file_key_list = [];
		foreach ($assoc_company_info_list as $item) {

			//
			if (!isEmptyString($item->avatar_file_map)) {
				$file_key_list[$item->company_id] = (string) Type_Pack_File::doEncrypt($item->avatar_file_map);
			}
		}

		$avatar_url_list = [];
		if (count($file_key_list) > 0) {

			$file_list = Domain_Partner_Scenario_Socket::getFileByKeyList($file_key_list);
			$temp_list = [];
			foreach ($file_list as $item) {

				// формируем массив вида file_key => avatar_url
				$temp_list[$item["file_key"]] = $item["data"]["image_version_list"][0]["url"];
			}

			// формируем output
			// не можем сделать это выше, в кейсе когда у нескольких компаний один file_key
			foreach ($file_key_list as $company_id => $file_key) {

				// формируем массив вида company_id => avatar_url
				$avatar_url_list[$company_id] = $temp_list[$file_key];
			}
		}

		return $avatar_url_list;
	}
}
