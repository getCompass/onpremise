<?php

namespace Compass\Speaker;

use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use CompassApp\Company\CompanyProvider;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

/**
 * класс для работы с go_company_cache
 */
class Gateway_Bus_CompanyCache {

	/**
	 * Возвращает информацию по сессии
	 *
	 * @param string $session_uniq
	 * @return array
	 *
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \cs_SessionNotFound
	 * @throws \returnException
	 */
	public static function getSessionInfo(string $session_uniq):array {

		$request = new \CompanyCacheGrpc\SessionGetInfoRequestStruct([
			"session_uniq" => $session_uniq,
			"company_id"   => COMPANY_ID,
		]);

		/** @var \CompanyCacheGrpc\SessionGetInfoResponseStruct $response */
		[$response, $status] = self::_doCallGrpc("SessionGetInfo", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			// если go_session не смог получить данные из БД
			if ($status->code == 500) {
				throw new \busException("database error in go_session");
			}

			// если сессия не найдена
			if ($status->code == 902) {
				throw new \cs_SessionNotFound();
			}

			if ($status->code == 14) {
				self::_checkCompanyExists();
			}
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code . " company_id: " . COMPANY_ID);
		}

		/** @var \CompanyCacheGrpc\MemberInfoStruct $member */
		return [self::_doFormatMember($response->getMember()), fromJson($response->getExtra())];
	}

	/**
	 * очищаем session-cache по user_id
	 *
	 * @param int $user_id
	 *
	 * @throws \busException
	 */
	public static function clearSessionCacheByUserId(int $user_id):void {

		$request = new \CompanyCacheGrpc\SessionDeleteByUserIdRequestStruct([
			"user_id"    => $user_id,
			"company_id" => COMPANY_ID,
		]);
		[, $status] = self::_doCallGrpc("SessionDeleteByUserId", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	/**
	 * Получить сессии пользователя
	 *
	 * @param int $user_id
	 *
	 * @return array
	 * @throws \busException
	 */
	public static function getSessionListByUserId(int $user_id):array {

		$session_uniq_list = [];
		$request           = new \CompanyCacheGrpc\SessionGetListByUserIdRequestStruct([
			"user_id"    => $user_id,
			"company_id" => COMPANY_ID,
		]);

		/** @var \CompanyCacheGrpc\SessionGetListByUserIdResponseStruct $response */
		[$response, $status] = self::_doCallGrpc("SessionGetListByUserId", $request);

		if ($status->code !== \Grpc\STATUS_OK) {

			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code . " company_id: " . COMPANY_ID);
		}

		foreach ($response->getSessionUniqList() as $session_uniq) {

			array_push($session_uniq_list, $session_uniq);
		}

		return $session_uniq_list;
	}

	/**
	 * очищаем member-cache по user_id
	 *
	 * @param int $user_id
	 *
	 * @throws \busException
	 */
	public static function clearMemberCacheByUserId(int $user_id):void {

		$request = new \CompanyCacheGrpc\MemberDeleteFromCacheByUserIdRequestStruct([
			"user_id"    => $user_id,
			"company_id" => COMPANY_ID,
		]);
		[, $status] = self::_doCallGrpc("MemberDeleteFromCacheByUserId", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	// возвращает статус микросервиса
	public static function getStatus():array {

		$request = new \CompanyCacheGrpc\SystemStatusRequestStruct();
		[$response, $status] = self::_doCallGrpc("SystemStatus", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			if ($status->code == 14) {
				self::_checkCompanyExists();
			}
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		return self::_formatSystemStatusResponse($response);
	}

	/**
	 * получить информацию по пользователям
	 *
	 * @param array $user_id_list
	 *
	 * @return \CompassApp\Domain\Member\Struct\Main[]
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \returnException
	 */
	public static function getMemberList(array $user_id_list):array {

		$request = new \CompanyCacheGrpc\MemberGetListRequestStruct([
			"user_id_list" => $user_id_list,
			"company_id"   => COMPANY_ID,
		]);

		/** @var \CompanyCacheGrpc\MemberGetListResponseStruct $response */
		[$response, $status] = self::_doCallGrpc("MemberGetList", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			if ($status->code == 14) {
				self::_checkCompanyExists();
			}

			// если go_session не смог получить данные из БД
			if ($status->code == 500) {
				throw new \busException("database error in go_session");
			}
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		$output = [];
		foreach ($response->getMemberList() as $member) {

			/** @var \CompanyCacheGrpc\MemberInfoStruct $member */
			$output[$member->getUserId()] = self::_doFormatMember($member);
		}
		return $output;
	}

	/**
	 * получить информацию по пользователям (краткую)
	 *
	 * @param array $user_id_list
	 * @param bool  $is_only_human
	 *
	 * @return \CompassApp\Domain\Member\Struct\Main[]
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \returnException
	 */
	public static function getShortMemberList(array $user_id_list, bool $is_only_human = true):array {

		$request = new \CompanyCacheGrpc\MemberGetShortListRequestStruct([
			"user_id_list" => $user_id_list,
			"company_id"   => COMPANY_ID,
		]);

		/** @var \CompanyCacheGrpc\MemberGetShortListResponseStruct $response */
		[$response, $status] = self::_doCallGrpc("MemberGetShortList", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			if ($status->code == 14) {
				self::_checkCompanyExists();
			}

			// если go_session не смог получить данные из БД
			if ($status->code == 500) {
				throw new \busException("database error in go_session");
			}
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		$output = [];
		/** @var \CompanyCacheGrpc\MemberShortInfoStruct $member */
		foreach ($response->getMemberList() as $member) {

			// если нужны только человеки и участник не человек (например, бот), то пропускаем
			if ($is_only_human && !Type_User_Main::isHuman($member->getNpcType())) {
				continue;
			}

			$output[$member->getUserId()] = self::_doFormatShortMember($member);
		}
		return $output;
	}

	/**
	 * получить информацию по одному пользователю
	 *
	 * @param int $user_id
	 *
	 * @return \CompassApp\Domain\Member\Struct\Main
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \returnException
	 */
	public static function getMember(int $user_id):\CompassApp\Domain\Member\Struct\Main {

		$request = new \CompanyCacheGrpc\MemberGetOneRequestStruct([
			"user_id"    => $user_id,
			"company_id" => COMPANY_ID,
		]);

		/** @var \CompanyCacheGrpc\MemberGetOneResponseStruct $response */
		[$response, $status] = self::_doCallGrpc("MemberGetOne", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			if ($status->code == 14) {
				self::_checkCompanyExists();
			}

			// если go_company_cache не смог получить данные из БД
			if ($status->code == 500) {
				throw new \busException("database error in go_company_cache");
			}
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		if (!$response->getExist()) {

			throw new \cs_RowIsEmpty();
		}

		/** @var \CompanyCacheGrpc\MemberInfoStruct $member */
		return self::_doFormatMember($response->getMember());
	}

	/**
	 * форматируем в структуру
	 *
	 * @param \CompanyCacheGrpc\MemberInfoStruct $member
	 *
	 * @return \CompassApp\Domain\Member\Struct\Main
	 */
	protected static function _doFormatMember(\CompanyCacheGrpc\MemberInfoStruct $member):\CompassApp\Domain\Member\Struct\Main {

		return new \CompassApp\Domain\Member\Struct\Main(
			$member->getUserId(),
			$member->getRole(),
			$member->getNpcType(),
			$member->getPermissions(),
			$member->getCreatedAt(),
			$member->getUpdatedAt(),
			$member->getCompanyJoinedAt(),
			$member->getLeftAt(),
			$member->getFullnameUpdatedAt(),
			$member->getFullname(),
			$member->getMbtiType(),
			$member->getShortDescription(),
			$member->getAvatarFileKey(),
			$member->getComment(),
			fromJson($member->getExtra())
		);
	}

	/**
	 * форматируем в структуру
	 *
	 * @param \CompanyCacheGrpc\MemberShortInfoStruct $member
	 *
	 * @return \CompassApp\Domain\Member\Struct\Short
	 */
	#[Pure]
	protected static function _doFormatShortMember(\CompanyCacheGrpc\MemberShortInfoStruct $member):\CompassApp\Domain\Member\Struct\Short {

		return new \CompassApp\Domain\Member\Struct\Short(
			$member->getUserId(),
			$member->getRole(),
			$member->getNpcType(),
			$member->getPermissions(),
		);
	}

	// форматируем ответ system.status
	#[Pure] #[ArrayShape(["name" => "string", "goroutines" => "int|string", "memory" => "int|string", "memory_kb" => "string", "memory_mb" => "string", "uptime" => "int"])]
	protected static function _formatSystemStatusResponse(\CompanyCacheGrpc\SystemStatusResponseStruct $response):array {

		// формируем ответ
		return [
			"name"       => $response->getName(),
			"goroutines" => $response->getGoroutines(),
			"memory"     => $response->getMemory(),
			"memory_kb"  => $response->getMemoryKb(),
			"memory_mb"  => $response->getMemoryMb(),
			"uptime"     => $response->getUptime(),
		];
	}

	/**
	 * Получаем значение конфига по одному ключу
	 *
	 * @throws ControllerMethodNotFoundException
	 * @throws \cs_RowIsEmpty
	 * @throws BusFatalException
	 */
	public static function getConfigKey(string $key):\CompassApp\Domain\Space\Struct\Config {

		$request = new \CompanyCacheGrpc\ConfigGetOneRequestStruct([
			"key"        => $key,
			"company_id" => CompanyProvider::id(),
		]);

		/** @var \CompanyCacheGrpc\ConfigGetOneResponseStruct $response */
		[$response, $status] = static::_doCallGrpc("ConfigGetOne", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			if ($status->code === 14) {
				self::_checkCompanyExists();
			}

			// если go_company_cache не смог получить данные из БД
			if ($status->code === 500) {
				throw new BusFatalException("database error in go_company_cache");
			}
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		if (!$response->getExist()) {
			throw new \cs_RowIsEmpty();
		}

		/** @var \CompanyCacheGrpc\MemberInfoStruct $member */
		return self::_doFormatConfig($response->getKey());
	}

	/**
	 * Форматируем в структуру
	 */
	protected static function _doFormatConfig(\CompanyCacheGrpc\KeyInfoStruct $key_info_struct):\CompassApp\Domain\Space\Struct\Config {

		return new \CompassApp\Domain\Space\Struct\Config(
			$key_info_struct->getKey(),
			$key_info_struct->getCreatedAt(),
			$key_info_struct->getUpdatedAt(),
			fromJson($key_info_struct->getValue())
		);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Делаем grpc запрос к указанному методу с переданными данными
	 *
	 * @throws BusFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _doCallGrpc(string $method_name, mixed $request):array {

		$connection = ShardingGateway::rpc("company_cache", \CompanyCacheGrpc\companyCacheClient::class);

		return $connection->callGrpc($method_name, $request);
	}

	/**
	 * Проверка, существует ли такая компания
	 *
	 * @throws \apiAccessException
	 * @throws \returnException
	 */
	protected static function _checkCompanyExists():void {

		if (!Gateway_Socket_Company::exists()) {

			throw new \apiAccessException("company does not exist, probably subdomain is wrong");
		}
	}
}
