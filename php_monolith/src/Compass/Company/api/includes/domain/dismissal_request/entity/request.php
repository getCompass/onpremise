<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для взаимодействия с заявкой на увольнение
 */
class Domain_DismissalRequest_Entity_Request {

	public const STATUS_DELETED        = 1;  // статус заявки - удалена
	public const STATUS_WAITING        = 13; // статус заявки - ожидает
	public const STATUS_REJECTED       = 14; // статус заявки - отклонена
	public const STATUS_APPROVED       = 21; // статус заявки - одобрена
	public const STATUS_SELF_DISMISSAL = 31; // статус заявки - самоувольнение

	// список статусов заявки на увольнение при которых нельзя создать новую
	public const NOT_ALLOW_DISMISSAL_REQUEST_STATUS_LIST = [
		self::STATUS_WAITING,
	];

	// массив для преобразования числового type заявки в строковый
	public const DISMISSAL_REQUEST_TYPE_SCHEMA = [
		self::STATUS_WAITING        => "waiting",
		self::STATUS_APPROVED       => "approved",
		self::STATUS_REJECTED       => "rejected",
		self::STATUS_DELETED        => "approved", // удаленные заявки на увольнение всегда имеют статус одобренные
		self::STATUS_SELF_DISMISSAL => "self_dismissal",
	];

	// массив для преобразования числового type заявки в строковый
	public const DISMISSAL_REQUEST_INT_SCHEMA = [
		"waiting"        => self::STATUS_WAITING,
		"approved"       => self::STATUS_APPROVED,
		"rejected"       => self::STATUS_REJECTED,
		"self_dismissal" => self::STATUS_SELF_DISMISSAL,
	];

	// имя типа заявки при обращении в/из другие модули
	public const DISMISSAL_REQUEST_NAME_TYPE = "dismissal_request";

	/**
	 * Добавляем заявку
	 *
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function add(int $creator_user_id, int $dismissal_user_id):Struct_Db_CompanyData_DismissalRequest {

		$extra = self::initExtra();
		return Gateway_Db_CompanyData_DismissalRequest::insert(self::STATUS_WAITING,
			$creator_user_id,
			$dismissal_user_id,
			$extra
		);
	}

	/**
	 * Добавляем одобренную заявку
	 *
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function addApproved(int $creator_user_id, int $dismissal_user_id):Struct_Db_CompanyData_DismissalRequest {

		$extra = self::initExtra();
		return Gateway_Db_CompanyData_DismissalRequest::insert(self::STATUS_APPROVED,
			$creator_user_id,
			$dismissal_user_id,
			$extra
		);
	}

	/**
	 * Добавляем заявку на самоувольнение
	 *
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function addForSelf(int $user_id):Struct_Db_CompanyData_DismissalRequest {

		$extra = self::initExtra();
		return Gateway_Db_CompanyData_DismissalRequest::insert(self::STATUS_SELF_DISMISSAL,
			$user_id,
			$user_id,
			$extra
		);
	}

	/**
	 * Одобряем заявку
	 *
	 * @throws \parseException
	 */
	public static function approve(Struct_Db_CompanyData_DismissalRequest $dismissal_request):void {

		try {

			Gateway_Db_CompanyData_DismissalRequest::set($dismissal_request->dismissal_request_id, [
				"status"     => $dismissal_request->status,
				"updated_at" => $dismissal_request->updated_at,
			]);
		} catch (cs_RowNotUpdated) {
			throw new ParseFatalException("Dismissal request row not updated");
		}
	}

	/**
	 * Отклоняем заявку
	 *
	 * @throws \parseException
	 */
	public static function reject(Struct_Db_CompanyData_DismissalRequest $dismissal_request):void {

		try {

			Gateway_Db_CompanyData_DismissalRequest::set($dismissal_request->dismissal_request_id, [
				"status"     => $dismissal_request->status,
				"updated_at" => $dismissal_request->updated_at,
			]);
		} catch (cs_RowNotUpdated) {
			throw new ParseFatalException("Dismissal request row not updated");
		}
	}

	/**
	 * Получаем заявку по ее id
	 *
	 * @throws cs_DismissalRequestNotExist
	 */
	public static function get(int $dismissal_request_id):Struct_Db_CompanyData_DismissalRequest {

		try {
			$dismissal_request = Gateway_Db_CompanyData_DismissalRequest::getOne($dismissal_request_id);
		} catch (\cs_RowIsEmpty) {
			throw new cs_DismissalRequestNotExist();
		}

		return $dismissal_request;
	}

	/**
	 * Получаем массив заявок
	 */
	public static function getList(array $dismissal_request_id_list, int $limit = 50):array {

		return Gateway_Db_CompanyData_DismissalRequest::getList($dismissal_request_id_list, $limit);
	}

	/**
	 * Получаем заявку по id увольняемого пользователя
	 *
	 * @throws cs_DismissalRequestNotExist
	 */
	public static function getByDismissalUserId(int $dismissal_user_id):Struct_Db_CompanyData_DismissalRequest {

		try {
			$dismissal_request = Gateway_Db_CompanyData_DismissalRequest::getByDismissalUserId($dismissal_user_id, self::NOT_ALLOW_DISMISSAL_REQUEST_STATUS_LIST);
		} catch (\cs_RowIsEmpty) {
			throw new cs_DismissalRequestNotExist();
		}

		return $dismissal_request;
	}

	/**
	 * Получаем заявку с блокировкой
	 *
	 * @throws cs_HireRequestNotExist
	 */
	public static function getForUpdate(int $dismissal_request_id):Struct_Db_CompanyData_DismissalRequest {

		try {
			$hiring_request = Gateway_Db_CompanyData_DismissalRequest::getForUpdate($dismissal_request_id);
		} catch (\cs_RowIsEmpty) {
			throw new cs_HireRequestNotExist();
		}

		return $hiring_request;
	}

	/**
	 * сменился статус заявки увольнения
	 *
	 * @throws \parseException
	 */
	public static function sendDismissalRequestStatusChangedEvent(array $dismissal_request):void {

		$user_list         = Gateway_Socket_Conversation::getHiringConversationUserIdList();
		$talking_user_list = $user_list["talking_user_list"];

		// получаем id создателя заявки
		$creator_user_id = $dismissal_request["creator_user_id"];

		// докидываем создателя в массив на отправку эвента если необходимо
		if (!self::_isUserAlreadyInTalkingUserList($talking_user_list, $creator_user_id)) {
			$talking_user_list[] = Gateway_Bus_Sender::makeTalkingUserItem($creator_user_id, false);
		}

		// отправляем эвент
		Gateway_Bus_Sender::dismissalRequestChanged($dismissal_request, $talking_user_list);
	}

	/**
	 * Добавляет user_id заявки на увольнение в кэш
	 *
	 * @param int $user_id идентификатор пользователя
	 *
	 * @throws cs_DismissalRequestIsAlreadyExist
	 */
	public static function addDismissalRequestUserIdInCache(int $user_id):void {

		try {
			\Compass\Company\ShardingGateway::cache()->add(self::_getKeyForDismissalRequestUserId($user_id), true);
		} catch (\cs_MemcacheRowIfExist) {
			throw new cs_DismissalRequestIsAlreadyExist();
		}
	}

	/**
	 * Удаляем user_id заявки на увольнение из кэша
	 *
	 * @param int $user_id идентификатор пользователя
	 */
	public static function deleteDismissalRequestUserIdFromCache(int $user_id):void {

		\Compass\Company\ShardingGateway::cache()->delete(self::_getKeyForDismissalRequestUserId($user_id));
	}

	/**
	 * проверяем что создатель заявки уже есть в массиве пользователей для отправки эвента об изменении ее статуса
	 */
	protected static function _isUserAlreadyInTalkingUserList(array $talking_user_list, int $creator_user_id):bool {

		// проходимся по всем пользователям состоящим в чате
		foreach ($talking_user_list as $v) {

			if ($v["user_id"] == $creator_user_id) {
				return true;
			}
		}

		return false;
	}

	// -------------------------------------------------------
	// EXTRA SCHEMA
	// -------------------------------------------------------

	protected const _EXTRA_VERSION = 3; // версия упаковщика
	protected const _EXTRA_SCHEMA  = [  // схема extra

		1 => [
			"thread_map" => "",
		],
		2 => [
			"thread_map"         => "",
			"is_company_creator" => 0,
		],
		3 => [
			"thread_map"         => "",
			"message_map"        => "",
			"is_company_creator" => 0,
		],
	];

	/**
	 * Создать новую структуру для extra
	 */
	public static function initExtra():array {

		return [
			"version" => self::_EXTRA_VERSION,
			"extra"   => self::_EXTRA_SCHEMA[self::_EXTRA_VERSION],
		];
	}

	/**
	 * устанавливаем thread_map
	 */
	public static function setThreadMap(array $extra, string $thread_map):array {

		$extra                        = self::_getExtra($extra);
		$extra["extra"]["thread_map"] = $thread_map;

		return $extra;
	}

	/**
	 * получаем thread_map
	 */
	public static function getThreadMap(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["thread_map"];
	}

	/**
	 * имеется ли thread_map
	 */
	public static function isExistThreadMap(array $extra):string {

		$extra = self::_getExtra($extra);
		return mb_strlen($extra["extra"]["thread_map"]) > 0;
	}

	/**
	 * получаем message_map
	 */
	public static function getMessageMap(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["message_map"];
	}

	/**
	 * устанавливаем message_map
	 */
	public static function setMessageMap(array $extra, string $message_map):array {

		$extra                         = self::_getExtra($extra);
		$extra["extra"]["message_map"] = $message_map;

		return $extra;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Получить актуальную структуру для extra
	 */
	protected static function _getExtra(array $extra):array {

		// если версия не совпадает - дополняем её до текущей
		if ($extra["version"] != self::_EXTRA_VERSION) {

			$extra["extra"]   = array_merge(self::_EXTRA_SCHEMA[self::_EXTRA_VERSION], $extra["extra"]);
			$extra["version"] = self::_EXTRA_VERSION;
		}

		return $extra;
	}

	/**
	 * Метод для получения ключа mCache для заявки на увольнение
	 *
	 * @param int $user_id идентификатор пользователя
	 */
	protected static function _getKeyForDismissalRequestUserId(int $user_id):string {

		return "dismissal_request_duplicate_protection_" . $user_id;
	}
}
