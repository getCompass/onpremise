<?php

namespace Compass\Pivot;

/**
 * Класс для взаимодействия с лобби
 */
class Domain_Company_Entity_User_Lobby {

	protected const _POSTMODERATED = 1;  // сотрудник находится на постмодерации
	protected const _FIRED         = 11; // сотрудник уволен
	protected const _REJECTED      = 12; // заявка отклонена
	protected const _DELETED       = 13; // компания удалена

	public const TEXT_STATUS_SCHEMA = [
		self::_POSTMODERATED => Struct_User_Company::POSTMODERATED_STATUS,
		self::_FIRED         => Struct_User_Company::FIRED_STATUS,
		self::_REJECTED      => Struct_User_Company::REJECTED_STATUS,
		self::_DELETED       => Struct_User_Company::DELETED_STATUS,
	];

	/**
	 * получаем компанию из предбанника
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function get(int $user_id, int $company_id):Struct_Db_PivotUser_CompanyLobby {

		return Gateway_Db_PivotUser_CompanyLobbyList::getOne($user_id, $company_id);
	}

	// добавляем пользователя на постмодерацию
	public static function addPostModeratedUser(int $user_id, int $company_id, int $order, int $inviter_user_id, int $entry_id):Struct_Db_PivotUser_CompanyLobby {

		$extra = self::init($inviter_user_id);

		return self::_addUser($user_id, $company_id, $order, self::_POSTMODERATED, $entry_id, $extra);
	}

	// добавляем уволенного пользователя
	public static function addFiredUser(int $user_id, int $company_id, int $order, int $entry_id):Struct_Db_PivotUser_CompanyLobby {

		return self::_addUser($user_id, $company_id, $order, self::_FIRED, $entry_id);
	}

	// добавляем пользователя из удаленной компании
	public static function addDeletedCompanyUser(int $user_id, int $company_id, int $order, int $entry_id):Struct_Db_PivotUser_CompanyLobby {

		return self::_addUser($user_id, $company_id, $order, self::_DELETED, $entry_id);
	}

	// добавляем список пользователей из удаленной компании
	public static function addDeletedCompanyUserList(array $user_company_list):void {

		$user_lobby_list = [];

		/** @var Struct_Db_PivotUser_Company[] $user_company_list */
		foreach ($user_company_list as $user_company) {

			$user_lobby_list[$user_company->user_id] = new Struct_Db_PivotUser_CompanyLobby(
				$user_company->user_id, $user_company->company_id, $user_company->order, self::_DELETED, $user_company->entry_id, time(), time(), []);
		}

		Gateway_Db_PivotUser_CompanyLobbyList::insertArray($user_lobby_list);
	}

	// добавляем отклоненного пользователя
	public static function reject(int $user_id, int $company_id, int $order, int $entry_id):Struct_Db_PivotUser_CompanyLobby {

		return self::_addUser($user_id, $company_id, $order, self::_REJECTED, $entry_id);
	}

	// удаляем запись
	public static function delete(int $user_id, int $company_id):void {

		Gateway_Db_PivotUser_CompanyLobbyList::delete($user_id, [$company_id]);
	}

	/**
	 * удаляем несколько записей
	 */
	public static function deleteList(int $user_id, array $company_id_list):void {

		Gateway_Db_PivotUser_CompanyLobbyList::delete($user_id, $company_id_list);
	}

	/**
	 * проверяем, что пользователь не находится на постмодерации
	 *
	 * @throws cs_UserAlreadyInPostModeration
	 */
	public static function assertUserNotPostModeration(int $status):void {

		if (self::isStatusPostModeration($status)) {
			throw new cs_UserAlreadyInPostModeration();
		}
	}

	/**
	 * проверяем, что пользователь уволен или заявка на него отклонена
	 *
	 * @throws cs_UserAlreadyInCompany
	 */
	public static function assertUserFiredOrRevoked(int $status):void {

		if ($status != self::_FIRED && $status != self::_REJECTED) {
			throw new cs_UserAlreadyInCompany();
		}
	}

	/**
	 * статус - на постмодерации?
	 */
	public static function isStatusPostModeration(int $status):bool {

		return $status == self::_POSTMODERATED;
	}

	/**
	 * статус - отклонил заявку в компанию?
	 */
	public static function isStatusRejected(int $status):bool {

		return $status == self::_REJECTED;
	}

	/**
	 * статус - компания удалена?
	 */
	public static function isStatusCompanyDeleted(int $status):bool {

		return $status == self::_DELETED;
	}

	/**
	 * статус - уволен?
	 */
	public static function isStatusFired(int $status):bool {

		return $status == self::_FIRED;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Добавляем пользователя в лобби компании
	 */
	protected static function _addUser(int $user_id, int $company_id, int $order, int $status, int $entry_id, array $extra = []):Struct_Db_PivotUser_CompanyLobby {

		// создаем запись в таблице отношения компании к пользователю
		$user_lobby = new Struct_Db_PivotUser_CompanyLobby($user_id, $company_id, $order, $status, $entry_id, time(), time(), $extra);
		Gateway_Db_PivotUser_CompanyLobbyList::insertOrUpdate($user_lobby);

		return $user_lobby;
	}

	# region LOBBY_EXTRA

	// текущая версия extra
	protected const _EXTRA_LOBBY_COMPANY_VERSION = 1;

	protected const _EXTRA_LOBBY_COMPANY_SCHEMA = [
		1 => [
			"inviter_user_id" => 0,
		],
	];

	/**
	 * возвращает текущую структуру extra с default значениями
	 */
	public static function init(int $inviter_user_id = 0):array {

		$extra                             = [
			"handler_version" => self::_EXTRA_LOBBY_COMPANY_VERSION,
			"extra"           => self::_EXTRA_LOBBY_COMPANY_SCHEMA[self::_EXTRA_LOBBY_COMPANY_VERSION],
		];
		$extra["extra"]["inviter_user_id"] = $inviter_user_id;
		return $extra;
	}

	/**
	 * Получаем inviter_user_id
	 */
	public static function getInviterUserId(array $extra):int {

		// получаем актуальное extra
		$extra = self::_get($extra);

		return $extra["extra"]["inviter_user_id"];
	}

	/**
	 * Устанавливаем inviter_user_id
	 */
	public static function setInviterUserId(array $extra, int $inviter_user_id):array {

		// получаем актуальное extra
		$extra = self::_get($extra);

		$extra["extra"]["inviter_user_id"] = $inviter_user_id;
		return $extra;
	}

	/**
	 * получить extra
	 */
	protected static function _get(array $extra):array {

		if (!isset($extra["handler_version"])) {
			return self::init();
		}

		// сравниваем версию пришедшей extra с текущей
		if ($extra["handler_version"] != self::_EXTRA_LOBBY_COMPANY_VERSION) {

			// сливаем текущую версию extra и ту, что пришла
			$extra["extra"]           = array_merge(self::_EXTRA_LOBBY_COMPANY_SCHEMA[self::_EXTRA_LOBBY_COMPANY_VERSION], $extra["extra"]);
			$extra["handler_version"] = self::_EXTRA_LOBBY_COMPANY_VERSION;
		}

		return $extra;
	}

	# endregion LOBBY_EXTRA
}