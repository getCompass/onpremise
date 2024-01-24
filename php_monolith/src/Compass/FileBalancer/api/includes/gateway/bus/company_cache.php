<?php

namespace Compass\FileBalancer;

use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use CompassApp\Company\CompanyProvider;
use JetBrains\PhpStorm\ArrayShape;
use CompassApp\Domain\Member\Struct\Main;

/**
 * Класс для работы с go_company_cache
 */
class Gateway_Bus_CompanyCache {

	/**
	 * Получить информацию по сессии
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws apiAccessException
	 * @throws busException
	 * @throws \cs_SessionNotFound
	 * @throws returnException
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

			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		return [self::_doFormatMember($response->getMember()), $response->getExtra()];
	}

	/**
	 * Получить информацию по одному пользователю
	 *
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws apiAccessException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws returnException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getMember(int $user_id):Main {

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
	 * Возвращает статус микросервиса
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 */
	#[ArrayShape(["name" => "string", "goroutines" => "\int|string", "memory" => "\int|string", "memory_kb" => "string", "memory_mb" => "string", "uptime" => "int"])]
	public static function getStatus():array {

		$request = new \CompanyCacheGrpc\SystemStatusRequestStruct();
		[$response, $status] = self::_doCallGrpc("SystemStatus", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		return self::_formatSystemStatusResponse($response);
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
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 */
	protected static function _doCallGrpc(string $method_name, mixed $request):array {

		$connection = ShardingGateway::rpc("company_cache", \CompanyCacheGrpc\companyCacheClient::class);

		return $connection->callGrpc($method_name, $request);
	}

	/**
	 * Проверка, существует ли такая компания
	 *
	 * @throws apiAccessException
	 * @throws returnException
	 */
	protected static function _checkCompanyExists():void {

		if (!Gateway_Socket_Company::exists()) {
			throw new \apiAccessException("company does not exist, probably subdomain is wrong");
		}
	}

	/**
	 * Форматируем ответ system.status
	 */
	#[ArrayShape(["name" => "string", "goroutines" => "int|string", "memory" => "int|string", "memory_kb" => "string", "memory_mb" => "string", "uptime" => "int"])]
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
	 * Форматируем в структуру
	 */
	protected static function _doFormatMember(\CompanyCacheGrpc\MemberInfoStruct $member):Main {

		return new Main(
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
}
