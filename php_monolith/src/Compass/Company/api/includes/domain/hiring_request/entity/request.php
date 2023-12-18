<?php

namespace Compass\Company;

use JetBrains\PhpStorm\ArrayShape;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для взаимодействия с заявкой на найм
 */
class Domain_HiringRequest_Entity_Request {

	public const STATUS_NEED_POSTMODERATION      = 1;  // на постмодерации
	public const STATUS_CONFIRMED                = 11; // принята, пользователь вступил в компанию
	public const STATUS_NEED_CONFIRM             = 12; // пользователь вступил в компанию, но заявка остается на подтверждении у собственника
	public const STATUS_CONFIRMED_POSTMODERATION = 13; // принята, пользователь вступил в компанию после модерации
	public const STATUS_DISMISSED                = 21; // пользователя уволили
	public const STATUS_REJECTED                 = 22; // отклонена собственником
	public const STATUS_REVOKED                  = 23; // отклонена приглашаемым
	public const STATUS_SYSTEM_DELETED           = 24; // отклонена системой

	public const HIRING_REQUEST_APPROVED_ELEMENT_STATUS = 1; // подтвержденный элемет из списка в заявке
	public const HIRING_REQUEST_REJECTED_ELEMENT_STATUS = 0; // отклоненный элемет из списка в заявке

	// список доступных ролей, на которую можно пустить пользователя по заявке в момент одобрения заявки
	public const CONFIRM_ENTRY_ROLE_GUEST  = "guest";
	public const CONFIRM_ENTRY_ROLE_MEMBER = "member";

	// статус заявки найма для получения данные пользователя из pivot
	public const ALLOW_HIRING_GET_USER_INFO_LIST = [
		self::STATUS_NEED_POSTMODERATION,
	];

	// статус заявки найма для получения данные пользователя из company
	public const ALLOW_HIRING_GET_COMPANY_USER_INFO_LIST = [
		self::STATUS_CONFIRMED,
		self::STATUS_NEED_CONFIRM,
		self::STATUS_CONFIRMED_POSTMODERATION,
		self::STATUS_DISMISSED,
	];

	// массив для преобразования числового type заявки в строковый
	public const HIRING_REQUEST_TYPE_SCHEMA = [
		self::STATUS_NEED_POSTMODERATION      => "need_postmoderation",
		self::STATUS_NEED_CONFIRM             => "need_confirm",
		self::STATUS_REJECTED                 => "rejected",
		self::STATUS_CONFIRMED                => "confirmed",
		self::STATUS_CONFIRMED_POSTMODERATION => "confirmed_postmoderation",
		self::STATUS_REVOKED                  => "revoked",
		self::STATUS_DISMISSED                => "dismissed",
	];

	// массив для преобразования числового type заявки в строковый
	public const HIRING_REQUEST_INT_SCHEMA = [
		"need_postmoderation"      => self::STATUS_NEED_POSTMODERATION,
		"need_confirm"             => self::STATUS_NEED_CONFIRM,
		"rejected"                 => self::STATUS_REJECTED,
		"confirmed"                => self::STATUS_CONFIRMED,
		"confirmed_postmoderation" => self::STATUS_CONFIRMED_POSTMODERATION,
		"revoked"                  => self::STATUS_REVOKED,
		"dismissed"                => self::STATUS_DISMISSED,
	];

	// имя типа заявки при обращении в/из другие модули
	public const HIRING_REQUEST_NAME_TYPE = "hiring_request";

	/**
	 * Подтверждаем заявку, новая логика
	 *
	 * @throws cs_HiringRequestAlreadyConfirmed
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @long
	 */
	public static function confirm(Struct_Db_CompanyData_HiringRequest $hiring_request):array {

		Domain_Company_Entity_Dynamic::decHiringByStatus($hiring_request->status);

		$is_need_user_add = false;
		switch ($hiring_request->status) {

			case Domain_HiringRequest_Entity_Request::STATUS_NEED_CONFIRM:

				$new_status = Domain_HiringRequest_Entity_Request::STATUS_CONFIRMED;
				break;

			case Domain_HiringRequest_Entity_Request::STATUS_NEED_POSTMODERATION:

				$new_status       = Domain_HiringRequest_Entity_Request::STATUS_CONFIRMED_POSTMODERATION;
				$is_need_user_add = true;
				break;
			default:
				throw new cs_HiringRequestAlreadyConfirmed();
		}

		$hiring_request->updated_at = time();

		try {

			Gateway_Db_CompanyData_HiringRequest::setWhereStatus($hiring_request->hiring_request_id, [
				"status"     => $new_status,
				"updated_at" => $hiring_request->updated_at,
				"extra"      => $hiring_request->extra,
			], $hiring_request->status);

			// инкрементим число подтвержденных заявок
			Domain_Company_Entity_Dynamic::inc(Domain_Company_Entity_Dynamic::HIRING_REQUEST_CONFIRMED);
		} catch (cs_RowNotUpdated) {
			throw new cs_HiringRequestAlreadyConfirmed();
		}

		$hiring_request->status = $new_status;
		return [$hiring_request, $is_need_user_add];
	}

	/**
	 * Отклоняем заявку
	 *
	 * @throws \parseException
	 */
	public static function reject(Struct_Db_CompanyData_HiringRequest $hiring_request):void {

		try {

			Gateway_Db_CompanyData_HiringRequest::set($hiring_request->hiring_request_id, [
				"status"     => self::STATUS_REJECTED,
				"updated_at" => $hiring_request->updated_at,
				"extra"      => $hiring_request->extra,
			]);
		} catch (cs_RowNotUpdated) {
			throw new ParseFatalException("Hiring request row not updated");
		}
	}

	/**
	 * Отклоняем список заявок по причине отклоненных приглашений
	 *
	 * @throws \parseException
	 */
	public static function decline(Struct_Db_CompanyData_HiringRequest $hiring_request, string $candidate_full_name, string $candidate_avatar_file_key, int $candidate_avatar_color_id):Struct_Db_CompanyData_HiringRequest {

		$hiring_request->status     = Domain_HiringRequest_Entity_Request::STATUS_REVOKED;
		$hiring_request->updated_at = time();
		$hiring_request->extra      = Domain_HiringRequest_Entity_Request::setCandidateUserInfo(
			$hiring_request->extra, $candidate_full_name, $candidate_avatar_file_key, $candidate_avatar_color_id
		);

		try {

			Gateway_Db_CompanyData_HiringRequest::set($hiring_request->hiring_request_id, [
				"status"     => $hiring_request->status,
				"updated_at" => $hiring_request->updated_at,
				"extra"      => $hiring_request->extra,
			]);
		} catch (cs_RowNotUpdated) {
			throw new ParseFatalException("Hiring request row not updated");
		}

		return $hiring_request;
	}

	/**
	 * Получаем заявку
	 *
	 * @throws cs_HireRequestNotExist
	 */
	public static function get(int $hiring_request_id):Struct_Db_CompanyData_HiringRequest {

		try {
			$hiring_request = Gateway_Db_CompanyData_HiringRequest::getOne($hiring_request_id);
		} catch (\cs_RowIsEmpty) {
			throw new cs_HireRequestNotExist();
		}

		return $hiring_request;
	}

	/**
	 * Получаем заявку с блокировкой
	 *
	 * @throws cs_HireRequestNotExist
	 */
	public static function getForUpdate(int $hiring_request_id):Struct_Db_CompanyData_HiringRequest {

		try {
			$hiring_request = Gateway_Db_CompanyData_HiringRequest::getForUpdate($hiring_request_id);
		} catch (\cs_RowIsEmpty) {
			throw new cs_HireRequestNotExist();
		}

		return $hiring_request;
	}

	/**
	 * Получаем заявку по entry_id
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getByEntryId(int $entry_id):Struct_Db_CompanyData_HiringRequest {

		return Gateway_Db_CompanyData_HiringRequest::getByEntryId($entry_id);
	}

	/**
	 * Получаем массив заявок по id нанимаемых пользователей
	 */
	public static function getByCandidateUserIdList(array $candidate_user_id_list, int $limit = 50):array {

		return Gateway_Db_CompanyData_HiringRequest::getByCandidateUserIdList($candidate_user_id_list,
			[self::STATUS_CONFIRMED, self::STATUS_NEED_CONFIRM, self::STATUS_CONFIRMED_POSTMODERATION, self::STATUS_NEED_POSTMODERATION], $limit);
	}

	/**
	 * Получаем массив заявок
	 *
	 * @return Struct_Db_CompanyData_HiringRequest[]
	 */
	public static function getList(array $hiring_request_id_list, int $limit = 50):array {

		return Gateway_Db_CompanyData_HiringRequest::getList($hiring_request_id_list, $limit);
	}

	/**
	 * сменился статус заявки найма
	 *
	 * @throws \parseException
	 */
	public static function sendHiringRequestStatusChangedEvent(array $hiring_request):void {

		$user_list         = Gateway_Socket_Conversation::getHiringConversationUserIdList();
		$talking_user_list = $user_list["talking_user_list"];

		// получаем id создателя заявки
		$creator_user_id = $hiring_request["hired_by_user_id"];
		$hiring_request  = \CompassApp\Pack\Main::replaceMapWithKeys($hiring_request);

		// докидываем создателя в массив на отправку эвента если необходимо
		if (!self::_isUserAlreadyInTalkingUserList($talking_user_list, $creator_user_id)) {
			$talking_user_list[] = Gateway_Bus_Sender::makeTalkingUserItem($creator_user_id, false);
		}
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

	/**
	 * формируем список чатов для вступления
	 */
	public static function doPrepareConversationKeyListToJoin(array $conversation_key_list_to_join, int $status):array {

		$prepared_conversation_key_list_to_join = [];
		foreach ($conversation_key_list_to_join as $conversation_key) {

			$prepared_conversation_key_list_to_join[] = [
				"conversation_key" => $conversation_key,
				"status"           => $status,
			];
		}

		return $prepared_conversation_key_list_to_join;
	}

	/**
	 * формируем список диалогов для создания
	 */
	public static function doPrepareSingleListToCreate(array $single_list_to_create, int $creator_user_id):array {

		$is_already_exist_dialog_with_creator = false;

		$prepared_single_list_to_create = [];
		foreach ($single_list_to_create as $user_id) {

			if ($user_id == $creator_user_id) {
				$is_already_exist_dialog_with_creator = true;
			}

			$prepared_single_list_to_create[] = [
				"user_id" => $user_id,
				"status"  => 1,
			];
		}

		// докидываем диалог с создателем заявки если его раньше не было
		if ($is_already_exist_dialog_with_creator == false) {

			$prepared_single_list_to_create[] = [
				"user_id" => $creator_user_id,
				"status"  => 1,
			];
		}

		return $prepared_single_list_to_create;
	}

	/**
	 * Получаем массив заявок по id нанимаемых пользователей
	 *
	 * @return Struct_Db_CompanyData_HiringRequest[]
	 */
	public static function getByCandidateUserId(int $candidate_user_id):array {

		return Gateway_Db_CompanyData_HiringRequest::getByCandidateUserId($candidate_user_id);
	}

	/**
	 * Получаем последнюю заявку по id пользователя
	 *
	 * @return Struct_Db_CompanyData_HiringRequest
	 */
	public static function getByCandidateUserIdLast(int $candidate_user_id):Struct_Db_CompanyData_HiringRequest {

		return Gateway_Db_CompanyData_HiringRequest::getByCandidateUserIdLast($candidate_user_id);
	}

	/**
	 * Пометим что у заявки уволился пользователь
	 *
	 * @throws \parseException
	 */
	public static function dismiss(Struct_Db_CompanyData_HiringRequest $hiring_request):Struct_Db_CompanyData_HiringRequest {

		try {

			$updated_at = time();

			Gateway_Db_CompanyData_HiringRequest::set($hiring_request->hiring_request_id, [
				"status"     => self::STATUS_DISMISSED,
				"updated_at" => $updated_at,
			]);

			$hiring_request->status     = self::STATUS_DISMISSED;
			$hiring_request->updated_at = $updated_at;
		} catch (cs_RowNotUpdated) {
			throw new ParseFatalException("Hiring request row not updated");
		}

		return $hiring_request;
	}

	/**
	 * получаем заявки для подтверждения
	 *
	 * @return Struct_Db_CompanyData_HiringRequest[]
	 * @throws ParseFatalException
	 *
	 */
	public static function getNeedPostmoderationList(int $limit, int $offset):array {

		return Gateway_Db_CompanyData_HiringRequest::getListByStatus(self::STATUS_NEED_POSTMODERATION, $limit, $offset);
	}

	/**
	 * получаем подтвержденные заявки и заявки уволенных
	 *
	 * @return Struct_Db_CompanyData_HiringRequest[]
	 * @throws ParseFatalException
	 */
	public static function getConfirmedAndDismissedList(int $limit, int $offset = 0):array {

		return Gateway_Db_CompanyData_HiringRequest::getListByStatusList(
			[self::STATUS_CONFIRMED, self::STATUS_DISMISSED],
			$limit,
			$offset);
	}

	/**
	 * Получаем role нового пользователя пространства по entry_role
	 *
	 * @return int
	 * @throws \parseException
	 */
	public static function resolveRoleByEntryRole(string $entry_role):int {

		return match ($entry_role) {
			self::CONFIRM_ENTRY_ROLE_GUEST  => \CompassApp\Domain\Member\Entity\Member::ROLE_GUEST,
			self::CONFIRM_ENTRY_ROLE_MEMBER => \CompassApp\Domain\Member\Entity\Member::ROLE_MEMBER,
			default                         => throw new \parseException("unexpected entry_role: $entry_role"),
		};
	}

	// -------------------------------------------------------
	// EXTRA SCHEMA
	// -------------------------------------------------------

	protected const _EXTRA_VERSION = 10; // версия упаковщика
	protected const _EXTRA_SCHEMA  = [  // схема extra

		1  => [
			"autojoin" => [
				"group_conversation_autojoin_item_list"  => [],
				"single_conversation_autojoin_item_list" => [],
			],
		],
		2  => [
			"autojoin"   => [
				"group_conversation_autojoin_item_list"  => [],
				"single_conversation_autojoin_item_list" => [],
			],
			"thread_map" => "",
		],
		3  => [
			"autojoin"     => [
				"group_conversation_autojoin_item_list"  => [],
				"single_conversation_autojoin_item_list" => [],
			],
			"thread_map"   => "",
			"country_code" => "",
		],
		4  => [
			"autojoin"           => [
				"group_conversation_autojoin_item_list"  => [],
				"single_conversation_autojoin_item_list" => [],
			],
			"thread_map"         => "",
			"country_code"       => "",
			"is_company_creator" => 0,
		],
		5  => [
			"autojoin"                   => [
				"group_conversation_autojoin_item_list"  => [],
				"single_conversation_autojoin_item_list" => [],
			],
			"thread_map"                 => "",
			"country_code"               => "",
			"is_company_creator"         => 0,
			"is_added_on_company_create" => 0,
		],
		6  => [
			"autojoin"                   => [
				"group_conversation_autojoin_item_list"  => [],
				"single_conversation_autojoin_item_list" => [],
			],
			"thread_map"                 => "",
			"is_company_creator"         => 0,
			"is_added_on_company_create" => 0,
			"comment"                    => "",
		],
		7  => [
			"autojoin"                   => [
				"group_conversation_autojoin_item_list"  => [],
				"single_conversation_autojoin_item_list" => [],
			],
			"thread_map"                 => "",
			"is_company_creator"         => 0,
			"is_added_on_company_create" => 0,
			"comment"                    => "",
			"candidate_user_info"        => [
				"full_name"       => "",
				"avatar_file_map" => "",
			],
		],
		8  => [
			"autojoin"                   => [
				"group_conversation_autojoin_item_list"  => [],
				"single_conversation_autojoin_item_list" => [],
			],
			"thread_map"                 => "",
			"message_map"                => "",
			"is_company_creator"         => 0,
			"is_added_on_company_create" => 0,
			"comment"                    => "",
			"candidate_user_info"        => [
				"full_name"       => "",
				"avatar_file_map" => "",
			],
		],
		9  => [
			"autojoin"                   => [
				"group_conversation_autojoin_item_list"  => [],
				"single_conversation_autojoin_item_list" => [],
			],
			"thread_map"                 => "",
			"message_map"                => "",
			"is_company_creator"         => 0,
			"is_added_on_company_create" => 0,
			"comment"                    => "",
			"locale"                     => "",
			"candidate_user_info"        => [
				"full_name"       => "",
				"avatar_file_map" => "",
			],
		],
		10 => [
			"autojoin"                   => [
				"group_conversation_autojoin_item_list"  => [],
				"single_conversation_autojoin_item_list" => [],
			],
			"thread_map"                 => "",
			"message_map"                => "",
			"is_company_creator"         => 0,
			"is_added_on_company_create" => 0,
			"comment"                    => "",
			"locale"                     => "",
			"candidate_user_info"        => [
				"full_name"       => "",
				"avatar_file_map" => "",
				"avatar_color_id" => 0,
			],
		],
	];

	/**
	 * Создать новую структуру для extra
	 *
	 * @return array
	 */
	#[ArrayShape(["version" => "int", "extra" => "mixed"])]
	public static function initExtra():array {

		return [
			"version" => self::_EXTRA_VERSION,
			"extra"   => self::_EXTRA_SCHEMA[self::_EXTRA_VERSION],
		];
	}

	/**
	 * получаем group_conversation_autojoin_item_list
	 */
	public static function getConversationKeyListToJoin(array $extra):array {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["autojoin"]["group_conversation_autojoin_item_list"];
	}

	/**
	 * сохраняем group_conversation_autojoin_item_list
	 */
	public static function setConversationKeyListToJoin(array $extra, array $conversation_key_list_to_join):array {

		$extra = self::_getExtra($extra);

		$extra["extra"]["autojoin"]["group_conversation_autojoin_item_list"] = $conversation_key_list_to_join;

		return $extra;
	}

	/**
	 * получаем комментарий
	 */
	public static function getComment(array $extra):string {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["comment"];
	}

	/**
	 * сохраняем комментарий
	 */
	public static function setComment(array $extra, string $comment):array {

		$extra = self::_getExtra($extra);

		$extra["extra"]["comment"] = $comment;

		return $extra;
	}

	/**
	 * получаем локаль создателя заявки
	 */
	public static function getLocale(array $extra):string {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["locale"];
	}

	/**
	 * сохраняем локаль создателя заявки
	 */
	public static function setLocale(array $extra, string $locale):array {

		$extra = self::_getExtra($extra);

		$extra["extra"]["locale"] = $locale;

		return $extra;
	}

	/**
	 * получаем $single_list_to_create
	 */
	public static function getSingleListToCreate(array $extra):array {

		$extra                    = self::_getExtra($extra);
		$single_list_conversation = $extra["extra"]["autojoin"]["single_conversation_autojoin_item_list"];

		// перевернем массив, чтобы отдавался первым пригласивший пользователь
		return array_reverse($single_list_conversation);
	}

	/**
	 * сохраняем $single_list_to_create
	 */
	public static function setSingleListToCreate(array $extra, array $single_list_to_create):array {

		$extra = self::_getExtra($extra);

		$extra["extra"]["autojoin"]["single_conversation_autojoin_item_list"] = $single_list_to_create;

		return $extra;
	}

	/**
	 * сохраняем country_code
	 */
	public static function setCountryCode(array $extra, string $country_code):array {

		$extra = self::_getExtra($extra);

		$extra["extra"]["country_code"] = $country_code;

		return $extra;
	}

	/**
	 * сохраняем роль создателя
	 */
	public static function setIsAddedOnCompanyCreate(array $extra, bool $value):array {

		$extra = self::_getExtra($extra);

		$extra["extra"]["is_added_on_company_create"] = (int) $value;

		return $extra;
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

	/**
	 * получаем country_code
	 */
	public static function getCountryCode(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["country_code"];
	}

	/**
	 * получаем информацию о пользователе из заявки
	 */
	public static function getCandidateUserInfo(array $extra, int $user_id):Struct_User_Info {

		$extra = self::_getExtra($extra);

		return new Struct_User_Info(
			$user_id,
			$extra["extra"]["candidate_user_info"]["full_name"],
			$extra["extra"]["candidate_user_info"]["avatar_file_map"],
			$extra["extra"]["candidate_user_info"]["avatar_color_id"]
		);
	}

	/**
	 * записываем информацию о пользователе в заявку
	 */
	public static function setCandidateUserInfo(array $extra, string $full_name, string $avatar_file_key, int $avatar_color_id):array {

		$extra = self::_getExtra($extra);

		$extra["extra"]["candidate_user_info"]["full_name"]       = $full_name;
		$extra["extra"]["candidate_user_info"]["avatar_file_map"] = $avatar_file_key;
		$extra["extra"]["candidate_user_info"]["avatar_color_id"] = $avatar_color_id;

		return $extra;
	}

	/**
	 * проверяем что пользователь создатель компании
	 */
	public static function isCompanyCreator(array $extra):bool {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["is_company_creator"];
	}

	/**
	 * есть ли информация о пользователе в заявке
	 */
	public static function isExistCandidateUserInfo(array $extra):bool {

		$extra = self::_getExtra($extra);
		return mb_strlen($extra["extra"]["candidate_user_info"]["full_name"]) > 0;
	}

	/**
	 * Устанавливаем extra
	 *
	 * @throws \parseException
	 */
	public static function setExtra(int $hiring_request_id, array $extra):void {

		try {

			Gateway_Db_CompanyData_HiringRequest::set($hiring_request_id, [
				"updated_at" => time(),
				"extra"      => $extra,
			]);
		} catch (cs_RowNotUpdated) {
			throw new ParseFatalException("Hiring request row not updated");
		}
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
}
