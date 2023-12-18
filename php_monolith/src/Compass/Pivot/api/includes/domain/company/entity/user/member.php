<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;
use BaseFrame\Server\ServerProvider;

/**
 * Класс для взаимодействия с участниками
 */
class Domain_Company_Entity_User_Member {

	public const ACTIVE_USER_COMPANY_STATUS     = 1;  // участник компании
	public const FIRED_USER_COMPANY_STATUS      = 11; // уволен из компании
	public const NOT_MEMBER_USER_COMPANY_STATUS = 12; // не участник компании (находится на постмодерации или отклонил заявку)
	public const DELETED_COMPANY_STATUS         = 13; // компания удалена

	/**
	 * alias с пакета company_utils, файл src/Domain/Member/Entity/Member.php
	 */
	public const ROLE_MEMBER              = 1; // простой участник
	public const ROLE_ADMINISTRATOR       = 2; // администратор
	public const ROLE_USERBOT             = 3; // роль пользовательского бота
	public const ROLE_GUEST               = 4; // гость
	public const SPACE_RESIDENT_ROLE_LIST = [
		self::ROLE_MEMBER, self::ROLE_ADMINISTRATOR,
	];

	/**
	 * Получаем статус пользователя в компании
	 *
	 * @throws cs_CompanyUserIsNotFound
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public static function getCompanyUser(int $user_id, int $company_id):Struct_Db_PivotCompany_CompanyUser {

		try {
			$company_user_row = Gateway_Db_PivotCompany_CompanyUserList::getOne($company_id, $user_id);
		} catch (\cs_RowIsEmpty) {
			throw new cs_CompanyUserIsNotFound();
		}

		return $company_user_row;
	}

	/**
	 * Получаем статус пользователя в компании
	 *
	 * @throws cs_CompanyUserIsNotFound
	 */
	public static function getUserCompany(int $user_id, int $company_id):Struct_Db_PivotUser_Company {

		try {
			$company_user_row = Gateway_Db_PivotUser_CompanyList::getOne($user_id, $company_id);
		} catch (\cs_RowIsEmpty) {
			throw new cs_CompanyUserIsNotFound();
		}

		return $company_user_row;
	}

	/**
	 * Получаем список компаний созданных пользователем, в которых он попрежнему является активным участником
	 *
	 */
	public static function getCompanyListCreatedByUser(int $user_id):array {

		// получаем все компании пользователя
		$user_company_list = Gateway_Db_PivotUser_CompanyList::getCompanyList($user_id);
		$company_list      = Gateway_Db_PivotCompany_CompanyList::getList(array_column($user_company_list, "company_id"));

		// оставляем только те компании, где пользователь является создателем
		$output = [];
		foreach ($company_list as $company) {

			if ($company->created_by_user_id === $user_id) {
				$output[] = $company;
			}
		}

		return $output;
	}

	/**
	 * Проверяем, что пользователя нет в компании
	 *
	 * @throws cs_UserAlreadyInCompany
	 */
	public static function assertUserIsNotMemberOfCompany(int $user_id, int $company_id):void {

		try {
			$user = Gateway_Db_PivotUser_CompanyList::getOne($user_id, $company_id);
		} catch (\cs_RowIsEmpty) {

			// всё ок
			return;
		}

		throw new cs_UserAlreadyInCompany($user->user_id);
	}

	/**
	 * Проверяем, что пользователя состоит в компании
	 *
	 * @throws cs_UserNotInCompany
	 */
	public static function assertUserIsMemberOfCompany(int $user_id, int $company_id):void {

		!static::isMember($user_id, $company_id) && throw new cs_UserNotInCompany();
	}

	/**
	 * Проверяем, является ли пользователь участником пространства.
	 */
	public static function isMember(int $user_id, int $space_id):bool {

		try {
			Gateway_Db_PivotUser_CompanyList::getOne($user_id, $space_id);
		} catch (\cs_RowIsEmpty) {
			return false;
		}

		return true;
	}

	/**
	 * Проверяем статус пользователя в лобби
	 */
	public static function isWaitingForPostmoderation(int $user_id, int $company_id):bool {

		try {

			// проверяем, находится ли пользователь в предбаннике
			$user_lobby = Domain_Company_Entity_User_Lobby::get($user_id, $company_id);

			// проверяем статус пользователя в предбаннике
			Domain_Company_Entity_User_Lobby::assertUserNotPostModeration($user_lobby->status);
			Domain_Company_Entity_User_Lobby::assertUserFiredOrRevoked($user_lobby->status);
		} catch (cs_UserAlreadyInPostModeration) {
			return true;
		} catch (cs_UserAlreadyInCompany) {
			return false;
		} catch (\cs_RowIsEmpty) {
			// если нет в предбаннике, значит всё оки
		}

		return false;
	}

	/**
	 * Функция нужна для очистики компании
	 */
	public static function deleteByCompany(int $company_id):void {

		// очищаем связи пользователь-компания
		Gateway_Db_PivotCompany_CompanyUserList::deleteByCompany($company_id);

		// очищаем связи компания пользователь
		Gateway_Db_PivotUser_CompanyList::deleteByCompany($company_id);
		Gateway_Db_PivotUser_CompanyLobbyList::deleteByCompany($company_id);
	}

	/**
	 * Добавляем пользователя в компанию
	 *
	 * @param int    $user_id
	 * @param int    $user_created_at
	 * @param int    $company_id
	 * @param int    $order
	 * @param int    $npc_type
	 * @param string $company_push_token
	 * @param int    $entry_id
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \queryException
	 */
	public static function add(int $user_id, int $user_space_role, int $user_space_permissions, int $user_created_at, int $company_id, int $order, int $npc_type = Type_User_Main::NPC_TYPE_HUMAN, string $company_push_token = "", int $entry_id = 0):array {

		// создаем запись в таблице отношения компании к пользователю
		$extra        = Domain_Company_Entity_User_Extra::init();
		$company_user = new Struct_Db_PivotCompany_CompanyUser($company_id, $user_id, time(), time(), $extra);
		Gateway_Db_PivotCompany_CompanyUserList::insert($company_user);

		// создаем запись в таблице отношения пользователя к компании
		$user_company = new Struct_Db_PivotUser_Company($user_id, $company_id, 0, $order, $entry_id, time(), time(), []);
		Gateway_Db_PivotUser_CompanyList::insert($user_company);

		// в зависимости от роли пользователя, который вступает в пространство, инкрементим тот или иной счетчик
		$company = match ($user_space_role) {

			// если полноценные участники пространства
			Domain_Company_Entity_User_Member::ROLE_MEMBER, Domain_Company_Entity_User_Member::ROLE_ADMINISTRATOR
			=> Domain_Company_Entity_Company::incMemberCount($company_id, $npc_type),

			// если гость
			Domain_Company_Entity_User_Member::ROLE_GUEST => Domain_Company_Entity_Company::incGuestCount($company_id, $npc_type),

			// если бот
			Domain_Company_Entity_User_Member::ROLE_USERBOT => Domain_Company_Entity_Company::get($company_id),

			// если пришла неизвестная роль
			default => throw new ParseFatalException("unexpected user role [$user_space_role]"),
		};

		// ставим пуш токен для компании
		Domain_User_Entity_UserCompanyPushToken::add($user_id, $npc_type, $company_id, $company_push_token);

		// отправляем информацию в анонсы
		Gateway_Announcement_Main::bindUserToCompany($user_id, $npc_type, $company_id);

		// если вступил человек
		if (Type_User_Main::isHuman($npc_type)) {

			// если участник занимает слов в пространстве
			if (in_array($user_space_role, self::SPACE_RESIDENT_ROLE_LIST)) {

				Type_Space_ActionAnalytics::send($company_id, $user_id, Type_Space_ActionAnalytics::NEW_MEMBER);
				Type_Space_NewUserJoinedSpace::send($user_id, $user_created_at, $company_id, $company->created_by_user_id);
				Domain_Partner_Entity_Event_UserJoinedSpace::create($user_id, $company_id, $user_created_at);
			}

			// для всех случаев – отправляем событие в crm
			Domain_Crm_Entity_Event_SpaceJoinMember::create($company_id, $user_id, $user_space_role, $user_space_permissions, $user_company->created_at);
		}

		// начинаем онбординг создателя команды
		self::_startSpaceCreatorOnboarding($company);

		return [$user_company, $company];
	}

	/**
	 * Начинаем онбординг создателя компании
	 *
	 * @param Struct_Db_PivotCompany_Company $company
	 *
	 * @return void
	 * @throws Domain_User_Exception_Onboarding_NotAllowedStatus
	 * @throws Domain_User_Exception_Onboarding_NotAllowedType
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws BusFatalException
	 * @throws EndpointAccessDeniedException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws cs_UserNotFound
	 */
	protected static function _startSpaceCreatorOnboarding(Struct_Db_PivotCompany_Company $company):void {

		// онбординг отключен для on premise
		if (ServerProvider::isOnPremise()) {
			return;
		}

		// проверяем, может ранее уже добавили онбординг для создателя пространства
		$user            = Gateway_Bus_PivotCache::getUserInfo($company->created_by_user_id);
		$onboarding_list = Type_User_Main::getOnboardingList($user->extra);
		$onboarding      = Domain_User_Entity_Onboarding::getFromOnboardingList(Domain_User_Entity_Onboarding::TYPE_SPACE_CREATOR, $onboarding_list);
		$lite_onboarding = Domain_User_Entity_Onboarding::getFromOnboardingList(Domain_User_Entity_Onboarding::TYPE_SPACE_CREATOR_LITE, $onboarding_list);

		// если уже запущен онбординг для создателя команды, то завершаем
		if ($onboarding !== false) {
			return;
		}

		$member_count = Domain_Company_Entity_Company::getMemberCount($company->extra);
		$guest_count  = Domain_Company_Entity_Company::getGuestCount($company->extra);

		if (($member_count + $guest_count) === 2) {

			// если уже запущен упрощённый онбординг, то чистим его и заменяем на онбординг создателя команды
			if ($lite_onboarding !== false) {
				Domain_User_Action_Onboarding_ClearByType::do($company->created_by_user_id, Domain_User_Entity_Onboarding::TYPE_SPACE_CREATOR_LITE);
			}

			$data = [
				"space_id" => $company->company_id,
			];

			Domain_User_Action_Onboarding_Activate::do($user, Domain_User_Entity_Onboarding::TYPE_SPACE_CREATOR, $data);
		}
	}
}