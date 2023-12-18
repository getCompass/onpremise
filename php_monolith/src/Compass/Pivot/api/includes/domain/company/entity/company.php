<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\ExceptionUtils;

/**
 * Класс для взаимодействия с компаниями
 */
class Domain_Company_Entity_Company {

	public const COMPANY_CREATE_USER_LIMIT = 24; // число доступных для создания компаний пользователем

	public const AVATAR_COLOR_GREEN_ID  = 1;
	public const AVATAR_COLOR_SEA_ID    = 2;
	public const AVATAR_COLOR_BLUE_ID   = 3;
	public const AVATAR_COLOR_YELLOW_ID = 4;
	public const AVATAR_COLOR_ORANGE_ID = 5;
	public const AVATAR_COLOR_RED_ID    = 6;
	public const AVATAR_COLOR_METAL_ID  = 7;
	public const AVATAR_COLOR_BLACK_ID  = 8;

	// список доступных цветов аватара
	public const ALLOW_AVATAR_COLOR_ID_LIST = [
		self::AVATAR_COLOR_GREEN_ID,
		self::AVATAR_COLOR_SEA_ID,
		self::AVATAR_COLOR_BLUE_ID,
		self::AVATAR_COLOR_YELLOW_ID,
		self::AVATAR_COLOR_ORANGE_ID,
		self::AVATAR_COLOR_RED_ID,
		self::AVATAR_COLOR_METAL_ID,
		self::AVATAR_COLOR_BLACK_ID,
	];

	public const COMPANY_STATUS_CREATING   = 0; // статус компании - создается
	public const COMPANY_STATUS_VACANT     = 1; // статус компании - свободна
	public const COMPANY_STATUS_ACTIVE     = 2; // статус компании - активная
	public const COMPANY_STATUS_HIBERNATED = 10; // компания в гибернации
	public const COMPANY_STATUS_RELOCATING = 40; // компания переезжает
	public const COMPANY_STATUS_DELETED    = 50; // компания удалена
	public const COMPANY_STATUS_INVALID    = 99; // статус компании - недоступна

	// массив для преобразования внутреннего типа системного статуса компании во внешний
	public const SYSTEM_COMPANY_STATUS_SCHEMA = [
		Domain_Company_Entity_Company::COMPANY_STATUS_VACANT     => "vacant",
		Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE     => "active",
		Domain_Company_Entity_Company::COMPANY_STATUS_HIBERNATED => "hibernated",
		Domain_Company_Entity_Company::COMPANY_STATUS_RELOCATING => "migrating",
		Domain_Company_Entity_Company::COMPANY_STATUS_INVALID    => "invalid",
		Domain_Company_Entity_Company::COMPANY_STATUS_DELETED    => "deleted",
	];

	protected const _EXTRA_VERSION = 5; // версия упаковщика
	protected const _EXTRA_SCHEMA  = [  // схема extra

		1 => [
			"member_count"                         => 0,
			"client_company_id"                    => "",
			"is_invitations_were_sent_on_creation" => 0,
			"encrypt_passphrase"                   => "",
			"encrypt_iv"                           => "",
			"latest_public_key_version"            => 1,
			"latest_private_key_version"           => 1,

			// публичный ключ (для проверки запросов из компании в pivot)
			"public_key"                           => [
				1 => COMPANY_TO_PIVOT_PUBLIC_KEY,
			],

			// приватный ключ компании (для подписи запросов из pivot в компанию)
			"private_key"                          => [
				1 => PIVOT_TO_COMPANY_PRIVATE_KEY,
			],
		],

		2 => [
			"member_count"               => 0,
			"client_company_id"          => "",
			"encrypt_passphrase"         => "",
			"encrypt_iv"                 => "",
			"latest_public_key_version"  => 1,
			"latest_private_key_version" => 1,

			// публичный ключ (для проверки запросов из компании в pivot)
			"public_key"                 => [
				1 => COMPANY_TO_PIVOT_PUBLIC_KEY,
			],

			// приватный ключ компании (для подписи запросов из pivot в компанию)
			"private_key"                => [
				1 => PIVOT_TO_COMPANY_PRIVATE_KEY,
			],
		],

		3 => [
			"member_count"               => 0,
			"client_company_id"          => "",
			"latest_public_key_version"  => 1,
			"latest_private_key_version" => 1,

			// публичный ключ (для проверки запросов из компании в pivot)
			"public_key"                 => [
				1 => COMPANY_TO_PIVOT_PUBLIC_KEY,
			],

			// приватный ключ компании (для подписи запросов из pivot в компанию)
			"private_key"                => [
				1 => PIVOT_TO_COMPANY_PRIVATE_KEY,
			],
		],

		4 => [
			"member_count"               => 0,
			"client_company_id"          => "",
			"latest_public_key_version"  => 1,
			"latest_private_key_version" => 1,

			// публичный ключ (для проверки запросов из компании в pivot)
			"public_key"                 => [
				1 => COMPANY_TO_PIVOT_PUBLIC_KEY,
			],

			// приватный ключ компании (для подписи запросов из pivot в компанию)
			"private_key"                => [
				1 => PIVOT_TO_COMPANY_PRIVATE_KEY,
			],

			// список дополнительных промо для пользователей
			"premium_extra_promo_list"   => [],
		],

		5 => [
			"member_count"               => 0,
			"guest_count"                => 0,
			"client_company_id"          => "",
			"latest_public_key_version"  => 1,
			"latest_private_key_version" => 1,

			// публичный ключ (для проверки запросов из компании в pivot)
			"public_key"                 => [
				1 => COMPANY_TO_PIVOT_PUBLIC_KEY,
			],

			// приватный ключ компании (для подписи запросов из pivot в компанию)
			"private_key"                => [
				1 => PIVOT_TO_COMPANY_PRIVATE_KEY,
			],

			// список дополнительных промо для пользователей
			"premium_extra_promo_list"   => [],
		],
	];

	/**
	 * Создать новую структуру для extra
	 *
	 * @return array
	 */
	public static function initExtra():array {

		return [
			"version" => self::_EXTRA_VERSION,
			"extra"   => self::_EXTRA_SCHEMA[self::_EXTRA_VERSION],
		];
	}

	/**
	 * Добавляем количество участников в компании
	 *
	 * @param array $extra
	 * @param int   $member_count
	 *
	 * @return array
	 */
	public static function setMemberCount(array $extra, int $member_count):array {

		$extra = self::_getExtra($extra);

		$extra["extra"]["member_count"] = $member_count;

		return $extra;
	}

	/**
	 * Получаем количество участников в компании
	 *
	 * @param array $extra
	 *
	 * @return int
	 */
	public static function getMemberCount(array $extra):int {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["member_count"];
	}

	/**
	 * Добавляем количество гостей
	 *
	 * @param array $extra
	 * @param int   $guest_count
	 *
	 * @return array
	 */
	public static function setGuestCount(array $extra, int $guest_count):array {

		$extra = self::_getExtra($extra);

		$extra["extra"]["guest_count"] = $guest_count;

		return $extra;
	}

	/**
	 * Получаем количество гостей в компании
	 *
	 * @param array $extra
	 *
	 * @return int
	 */
	public static function getGuestCount(array $extra):int {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["guest_count"];
	}

	/**
	 * Получаем public_key
	 *
	 * @param array $extra
	 *
	 * @return string
	 */
	public static function getPublicKey(array $extra):string {

		$extra = self::_getExtra($extra);

		return array_pop($extra["extra"]["public_key"]);
	}

	/**
	 * Получаем private_key
	 *
	 * @param array $extra
	 *
	 * @return string
	 */
	public static function getPrivateKey(array $extra):string {

		$extra = self::_getExtra($extra);

		return array_pop($extra["extra"]["private_key"]);
	}

	/**
	 * Получаем passphrase
	 *
	 * @param array $extra
	 *
	 * @return array
	 */
	public static function getPassphrase(array $extra):array {

		$extra = self::_getExtra($extra);

		return [$extra["extra"]["encrypt_passphrase"], $extra["extra"]["encrypt_iv"]];
	}

	/**
	 *  Добавляем client_company_id в extra
	 *
	 * @param array  $extra
	 * @param string $client_company_id
	 *
	 * @return array
	 */
	public static function setClientCompanyId(array $extra, string $client_company_id):array {

		$extra = self::_getExtra($extra);

		$extra["extra"]["client_company_id"] = $client_company_id;

		return $extra;
	}

	/**
	 * Получаем client_company_id в extra
	 *
	 * @param array $extra
	 *
	 * @return string
	 */
	public static function getClientCompanyId(array $extra):string {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["client_company_id"];
	}

	/**
	 * Добавляем особое промо в список особых промо компании.
	 */
	public static function addPremiumExtraPromo(array $extra, string $promo_label):array {

		$extra                                        = self::_getExtra($extra);
		$extra["extra"]["premium_extra_promo_list"][] = $promo_label;

		// убираем дубли
		$extra["extra"]["premium_extra_promo_list"] = array_unique($extra["extra"]["premium_extra_promo_list"]);

		return $extra;
	}

	/**
	 * Убираем особое промо из списка особых промо компании.
	 */
	public static function removePremiumExtraPromo(array $extra, string $promo_label):array {

		$extra = self::_getExtra($extra);

		foreach ($extra["extra"]["premium_extra_promo_list"] as $k => $label) {

			if ($promo_label === $label) {
				unset($extra["extra"]["premium_extra_promo_list"][$k]);
			}
		}

		return $extra;
	}

	/**
	 * Получаем список особых промо для компании.
	 *
	 * @param array $extra
	 *
	 * @return string[]
	 */
	public static function getPremiumExtraPromoList(array $extra):array {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["premium_extra_promo_list"];
	}

	/**
	 * Получаем дефолтные файлы.
	 *
	 * @return Struct_File_Default
	 */
	public static function getDefaultFileListStruct():Struct_File_Default {

		try {

			// выполняем действия при создании компании
			$hiring_file  = Gateway_Db_PivotSystem_DefaultFileList::get("hiring_conversation_avatar");
			$notes_file   = Gateway_Db_PivotSystem_DefaultFileList::get("notes_conversation_avatar");
			$support_file = Gateway_Db_PivotSystem_DefaultFileList::get("support_conversation_avatar");
			$respect_file = Gateway_Db_PivotSystem_DefaultFileList::get("respect_conversation_avatar");
		} catch (\cs_RowIsEmpty) {
			return new Struct_File_Default("", "", "", "");
		}

		return new Struct_File_Default($hiring_file->file_key, $notes_file->file_key, $support_file->file_key, $respect_file->file_key);
	}

	/**
	 * Получаем дефолтных ботов
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws cs_UserNotFound
	 */
	public static function getBotList():array {

		$bot_id_list   = [AUTH_BOT_USER_ID, REMIND_BOT_USER_ID, SUPPORT_BOT_USER_ID];
		$bot_info_list = Gateway_Bus_PivotCache::getUserListInfo($bot_id_list);

		$bot_list = [];
		foreach ($bot_info_list as $bot_info) {

			$bot_list[] = [
				"user_id"         => $bot_info->user_id,
				"full_name"       => $bot_info->full_name,
				"avatar_file_key" => mb_strlen($bot_info->avatar_file_map) > 0 ? Type_Pack_File::doEncrypt($bot_info->avatar_file_map) : "",
				"npc_type"        => $bot_info->npc_type,
			];
		}

		return $bot_list;
	}

	/**
	 * Получаем запись компании
	 *
	 * @param int $company_id
	 *
	 * @return Struct_Db_PivotCompany_Company
	 * @throws cs_CompanyNotExist
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public static function get(int $company_id):Struct_Db_PivotCompany_Company {

		try {
			$company = Gateway_Db_PivotCompany_CompanyList::getOne($company_id);
		} catch (\cs_RowIsEmpty) {
			throw new cs_CompanyNotExist();
		}

		return $company;
	}

	/**
	 * Получаем компанию из кэша
	 *
	 * @param int    $user_id
	 * @param string $client_company_id
	 *
	 * @return mixed
	 */
	public static function getCompanyInCache(int $user_id, string $client_company_id):mixed {

		return ShardingGateway::cache()->get(self::_getKeyForCompany($user_id, $client_company_id));
	}

	/**
	 * Делаем запись в кеше
	 *
	 * @throws \cs_MemcacheRowIfExist
	 */
	public static function setCompanyInCache(int $user_id, string $client_company_id):void {

		ShardingGateway::cache()->add(self::_getKeyForCompany($user_id, $client_company_id), $client_company_id);
	}

	/**
	 * Проверить, что компания жива
	 *
	 * @return void
	 * @throws Domain_Company_Exception_ConfigNotExist
	 * @throws Domain_Company_Exception_IsNotHibernated
	 */
	public static function assertCompanyIsAwaken(Struct_Db_PivotCompany_Company $company):void {

		if (Domain_Domino_Entity_Config::get($company)->status != Domain_Company_Entity_Company::COMPANY_STATUS_HIBERNATED) {
			throw new Domain_Company_Exception_IsNotHibernated("company isnt hibernated, cant wake up");
		}
	}

	/**
	 * Разослать во все компании событие обновления
	 *
	 * @long
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_SocketRequestIsFailed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 */
	public static function updateUserCompanyInfo(Struct_Db_PivotUser_User $user_info, string $client_launch_uuid):void {

		$company_list = Gateway_Db_PivotUser_CompanyList::getCompanyList($user_info->user_id);
		$is_deleted   = Type_User_Main::isDisabledProfile($user_info->extra) ? 1 : 0;
		$disabled_at  = Type_User_Main::getProfileDisabledAt($user_info->extra);

		if (count($company_list) < 1) {
			return;
		}

		$company_id_list     = array_column($company_list, "company_id");
		$company_list        = Gateway_Db_PivotCompany_CompanyList::getList($company_id_list);
		$formatted_user_info = Struct_User_Info::createStruct($user_info);

		foreach ($company_list as $company) {

			$private_key = self::getPrivateKey($company->extra);

			// пропускаем если компания неактивная
			if (!self::isCompanyActive($company)) {
				continue;
			}

			try {

				// делаем запрос в компанию
				Gateway_Socket_Company::updateUserInfo(
					$formatted_user_info,
					$user_info->created_at,
					$client_launch_uuid,
					$is_deleted,
					$disabled_at,
					$company->company_id,
					$company->domino_id,
					$private_key
				);
			} catch (Gateway_Socket_Exception_CompanyIsNotServed|cs_CompanyIsHibernate) {

				Type_System_Admin::log("update_user_info_in_company_error",
					"Не смогли обновить данные пользователя {$user_info->user_id} в компании {$company->company_id}");
			} catch (\cs_SocketRequestIsFailed $e) {

				// пишем лог в файл
				$exception_message = ExceptionUtils::makeMessage($e, HTTP_CODE_500);
				ExceptionUtils::writeExceptionToLogs($e, $exception_message);
			}
		}
	}

	/**
	 * Разослать во все компании событие разлогина
	 *
	 * @param int                                           $user_id
	 * @param Struct_Db_PivotUser_UserCompanySessionToken[] $user_company_session_token_list
	 *
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function logoutUserSessionList(int $user_id, array $user_company_session_token_list):void {

		$token_grouped_by_company_id = [];
		foreach ($user_company_session_token_list as $v) {
			$token_grouped_by_company_id[$v->company_id][] = $v->user_company_session_token;
		}

		$company_list = Gateway_Db_PivotCompany_CompanyList::getList(array_keys($token_grouped_by_company_id));
		foreach ($company_list as $company) {

			$private_key = self::getPrivateKey($company->extra);

			// пропускаем если компания неактивная
			if (!Domain_Company_Entity_Company::isCompanyActive($company)) {
				continue;
			}

			try {

				// делаем запрос в компанию
				Gateway_Socket_Company::logoutUserSessionList(
					$user_id, $token_grouped_by_company_id[$company->company_id], $company->company_id, $company->domino_id, $private_key
				);
			} catch (Gateway_Socket_Exception_CompanyIsNotServed|cs_CompanyIsHibernate) {
				// !!! если вдруг компания неактивна, то не останавливаемся и пытаемся разлогинить в других компаниях
			} catch (ReturnFatalException|ParseFatalException $e) {

				// пишем лог в файл
				$exception_message = ExceptionUtils::makeMessage($e, HTTP_CODE_500);
				ExceptionUtils::writeExceptionToLogs($e, $exception_message);
			}
		}
	}

	/**
	 * Проверяем, является ли пользователь создателем компании
	 *
	 * @param Struct_Db_PivotCompany_Company $company
	 * @param int                            $user_id
	 *
	 * @throws cs_UserIsNotCreatorOfCompany
	 */
	public static function assertUserIsCreator(Struct_Db_PivotCompany_Company $company, int $user_id):void {

		if ($company->created_by_user_id !== $user_id) {
			throw new cs_UserIsNotCreatorOfCompany();
		}
	}

	/**
	 * Проверяет, можно ли выполнить очистку компании.
	 *
	 * @param Struct_Db_PivotCompany_Company $company
	 *
	 * @return bool
	 */
	public static function isAllowedForPurge(Struct_Db_PivotCompany_Company $company):bool {

		return !in_array($company->status, [
			self::COMPANY_STATUS_HIBERNATED,
			self::COMPANY_STATUS_RELOCATING,
			self::COMPANY_STATUS_DELETED,
			self::COMPANY_STATUS_INVALID,
		]);
	}

	/**
	 * Получаем количество созданных компаний пользователем
	 *
	 * @param int   $user_id
	 * @param array $company_list
	 *
	 * @throws cs_CompanyCreateExceededLimit
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public static function getCountCompanyCreatedByUserId(int $user_id, array $company_list):int {

		// получаем записи компаний
		$company_list = Gateway_Db_PivotCompany_CompanyList::getList(array_column($company_list, "company_id"));

		// считаем количество компаний, где пользователь создатель
		$created_company_count = 0;
		foreach ($company_list as $company) {

			if ($company->created_by_user_id === $user_id) {
				$created_company_count++;
			}
		}

		return $created_company_count;
	}

	/**
	 * Проверяем количество созданных компаний пользователем
	 *
	 * @param int   $user_id
	 * @param array $company_list
	 *
	 * @throws cs_CompanyCreateExceededLimit
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public static function checkCountCompanyCreatedByUserId(int $user_id, array $company_list):void {

		// получаем количество созданных компаний пользователем
		$created_company_count = self::getCountCompanyCreatedByUserId($user_id, $company_list);

		// проверяем количество созданных компаний
		if ($created_company_count >= self::COMPANY_CREATE_USER_LIMIT) {
			throw new cs_CompanyCreateExceededLimit();
		}
	}

	/**
	 * Пересчитываем счетчик участников/гостей пространства
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public static function incCounterByRole(int $space_id, int $npc_type, int $user_role, int $increment_value):Struct_Db_PivotCompany_Company {

		// если пространство покидает полноценный участник пространства
		if (in_array($user_role, Domain_Company_Entity_User_Member::SPACE_RESIDENT_ROLE_LIST)) {

			// декрементим количество участников компании
			return Domain_Company_Entity_Company::incMemberCount($space_id, $npc_type, $increment_value);
		}

		// если пространство покидает гость
		if ($user_role === Domain_Company_Entity_User_Member::ROLE_GUEST) {

			// декрементим количество гостей компании
			return Domain_Company_Entity_Company::incGuestCount($space_id, $npc_type, $increment_value);
		}

		throw new ParseFatalException("unexpected behaviour");
	}

	/**
	 * функция которая увеличивает количество участников в компании
	 *
	 * @param int $company_id
	 * @param int $npc_type
	 * @param int $value
	 *
	 * @return Struct_Db_PivotCompany_Company
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public static function incMemberCount(int $company_id, int $npc_type, int $value = 1):Struct_Db_PivotCompany_Company {

		Gateway_Db_PivotCompany_CompanyList::beginTransaction($company_id);
		try {

			$company = Gateway_Db_PivotCompany_CompanyList::getForUpdate($company_id);
		} catch (\cs_RowIsEmpty) {

			Gateway_Db_PivotCompany_CompanyList::rollback($company_id);
			throw new ReturnFatalException("row not found");
		}

		// если добавляем не настоящего пользователя, то количество сотрудников не трогаем
		if (!Type_User_Main::isHuman($npc_type)) {

			Gateway_Db_PivotCompany_CompanyList::rollback($company_id);
			return $company;
		}

		// инкрементим количество сотрудников в компании
		$member_count = Domain_Company_Entity_Company::getMemberCount($company->extra);

		$member_count += $value;

		$company->extra      = Domain_Company_Entity_Company::setMemberCount($company->extra, $member_count);
		$company->updated_at = time();
		Gateway_Db_PivotCompany_CompanyList::set($company->company_id, [
			"updated_at" => $company->updated_at,
			"extra"      => $company->extra,
		]);

		Gateway_Db_PivotCompany_CompanyList::commitTransaction($company_id);

		return $company;
	}

	/**
	 * функция которая увеличивает количество гостей в компании
	 *
	 * @param int $company_id
	 * @param int $npc_type
	 * @param int $value
	 *
	 * @return Struct_Db_PivotCompany_Company
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public static function incGuestCount(int $company_id, int $npc_type, int $value = 1):Struct_Db_PivotCompany_Company {

		Gateway_Db_PivotCompany_CompanyList::beginTransaction($company_id);
		try {

			$company = Gateway_Db_PivotCompany_CompanyList::getForUpdate($company_id);
		} catch (\cs_RowIsEmpty) {

			Gateway_Db_PivotCompany_CompanyList::rollback($company_id);
			throw new ReturnFatalException("row not found");
		}

		// если добавляем не настоящего пользователя, то количество сотрудников не трогаем
		if (!Type_User_Main::isHuman($npc_type)) {

			Gateway_Db_PivotCompany_CompanyList::rollback($company_id);
			return $company;
		}

		// инкрементим количество гостей в компании
		$guest_count = Domain_Company_Entity_Company::getGuestCount($company->extra);

		$guest_count += $value;

		$company->extra      = Domain_Company_Entity_Company::setGuestCount($company->extra, $guest_count);
		$company->updated_at = time();
		Gateway_Db_PivotCompany_CompanyList::set($company->company_id, [
			"updated_at" => $company->updated_at,
			"extra"      => $company->extra,
		]);

		Gateway_Db_PivotCompany_CompanyList::commitTransaction($company_id);

		return $company;
	}

	/**
	 * Проверяет, можно ли занять компанию.
	 */
	public static function isAllowedToTake(Struct_Db_PivotCompany_Company $company):bool {

		return $company->status === static::COMPANY_STATUS_VACANT;
	}

	/**
	 * Проверяем, что компания активная
	 *
	 * @param Struct_Db_PivotCompany_Company $company
	 *
	 * @return void
	 * @throws Domain_Company_Exception_IsHibernated
	 * @throws Domain_Company_Exception_IsNotServed
	 * @throws Domain_Company_Exception_IsRelocating
	 */
	public static function assertCompanyActive(Struct_Db_PivotCompany_Company $company):void {

		if ($company->status === self::COMPANY_STATUS_HIBERNATED) {
			throw new Domain_Company_Exception_IsHibernated("company is hibernated");
		}

		if ($company->status === self::COMPANY_STATUS_RELOCATING) {
			throw new Domain_Company_Exception_IsRelocating("company is relocating");
		}

		if (in_array($company->status, [self::COMPANY_STATUS_INVALID, self::COMPANY_STATUS_DELETED])) {
			throw new Domain_Company_Exception_IsNotServed("company is not served");
		}
	}

	/**
	 * Возвращаем true/false в зависимости от статуса компании
	 */
	public static function isCompanyActive(Struct_Db_PivotCompany_Company $company):bool {

		// идем проверять статус компании
		try {

			Domain_Company_Entity_Company::assertCompanyActive($company);
		} catch (Domain_Company_Exception_IsHibernated|Domain_Company_Exception_IsRelocating|Domain_Company_Exception_IsNotServed) {

			return false;
		}

		return true;
	}

	/**
	 * Проверяет, доступна ли компания для выполнения пользовательских действий.
	 */
	public static function isAllowedForUserActions(Struct_Db_PivotCompany_Company $company):bool {

		return $company->status === self::COMPANY_STATUS_ACTIVE;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Получить актуальную структуру для extra
	 *
	 * @param array $extra
	 *
	 * @return array
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
	 * Метод для получения ключа mCache для компании
	 *
	 * @param int    $user_id
	 * @param string $client_company_id
	 *
	 * @return string
	 */
	protected static function _getKeyForCompany(int $user_id, string $client_company_id):string {

		return __CLASS__ . "_" . $user_id . "_" . $client_company_id;
	}
}
