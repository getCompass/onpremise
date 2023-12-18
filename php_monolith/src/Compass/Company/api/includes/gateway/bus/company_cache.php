<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Module\ModuleProvider;

/**
 * Класс для работы с go_company_cache
 */
class Gateway_Bus_CompanyCache extends \CompassApp\Gateway\Bus\CompanyCache {

	/**
	 * Получить информацию по сессии
	 *
	 * @throws ControllerMethodNotFoundException
	 * @throws ReturnFatalException
	 * @throws \cs_SessionNotFound
	 * @throws \returnException
	 * @throws BusFatalException
	 */
	#[\JetBrains\PhpStorm\ArrayShape([0 => \CompassApp\Domain\Member\Struct\Main::class, 1 => "array"])]
	public static function getSessionInfo(string $session_uniq):array {

		$request = new \CompanyCacheGrpc\SessionGetInfoRequestStruct([
			"session_uniq" => $session_uniq,
			"company_id"   => COMPANY_ID,
		]);

		/** @var \CompanyCacheGrpc\SessionGetInfoResponseStruct $response */
		[$response, $status] = self::_doCallGrpc("SessionGetInfo", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			// если go_session не смог получить данные из БД
			if ($status->code === 500) {
				throw new BusFatalException("database error in go_session");
			}

			// если сессия не найдена
			if ($status->code === 902) {
				throw new \cs_SessionNotFound();
			}

			if ($status->code === 14) {
				self::_checkCompanyExists(ModuleProvider::current());
			}
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code . " company_id: " . COMPANY_ID);
		}

		return [self::_doFormatMember($response->getMember()), fromJson($response->getExtra())];
	}

	/**
	 * Очистить кэш с сессиями
	 *
	 * @throws BusFatalException
	 * @throws ControllerMethodNotFoundException
	 * @throws ReturnFatalException
	 * @throws \returnException
	 */
	public static function clearSessionCache():void {

		$request = new \CompanyCacheGrpc\SessionClearCacheRequestStruct([
			"company_id" => COMPANY_ID,
		]);

		/** @var \CompanyCacheGrpc\MemberGetOneResponseStruct $response */
		[, $status] = self::_doCallGrpc("SessionClearCache", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			if ($status->code === 14) {
				self::_checkCompanyExists(ModuleProvider::current());
			}

			// если go_company_cache не смог получить данные из БД
			if ($status->code === 500) {
				throw new BusFatalException("database error in go_company_cache");
			}
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	/**
	 * Очистить кэш с пользователями
	 *
	 * @throws BusFatalException
	 * @throws ControllerMethodNotFoundException
	 * @throws ReturnFatalException
	 * @throws \returnException
	 */
	public static function clearMemberCache():void {

		$request = new \CompanyCacheGrpc\MemberClearCacheRequestStruct([
			"company_id" => COMPANY_ID,
		]);

		/** @var \CompanyCacheGrpc\MemberClearCacheResponseStruct $response */
		[, $status] = self::_doCallGrpc("MemberClearCache", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			if ($status->code === 14) {
				self::_checkCompanyExists(ModuleProvider::current());
			}

			// если go_company_cache не смог получить данные из БД
			if ($status->code === 500) {
				throw new BusFatalException("database error in go_company_cache");
			}
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	/**
	 * Очистить кэш с конфигом
	 *
	 * @throws BusFatalException
	 * @throws ControllerMethodNotFoundException
	 * @throws ReturnFatalException
	 * @throws \returnException
	 */
	public static function clearConfigCache():void {

		$request = new \CompanyCacheGrpc\ConfigClearCacheRequestStruct([
			"company_id" => COMPANY_ID,
		]);

		/** @var \CompanyCacheGrpc\ConfigClearCacheResponseStruct $response */
		[, $status] = self::_doCallGrpc("ConfigClearCache", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			if ($status->code === 14) {
				self::_checkCompanyExists(ModuleProvider::current());
			}

			// если go_company_cache не смог получить данные из БД
			if ($status->code === 500) {
				throw new BusFatalException("database error in go_session");
			}
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	/**
	 * очищаем member-cache по user_id_list
	 *
	 * @param array $user_id_list
	 *
	 * @throws BusFatalException
	 */
	public static function clearMemberCacheByUserIdList(array $user_id_list):void {

		$request = new \CompanyCacheGrpc\MemberDeleteFromCacheByUserIdListRequestStruct([
			"user_id_list" => $user_id_list,
			"company_id"   => COMPANY_ID,
		]);
		[, $status] = self::_doCallGrpc("MemberDeleteFromCacheByUserIdList", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// делаем grpc запрос к указанному методу с переданными данными
	protected static function _doCallGrpc(string $method_name, mixed $request):array {

		return ShardingGateway::rpc("company_cache", \CompanyCacheGrpc\companyCacheClient::class)->callGrpc($method_name, $request);
	}

	/**
	 * Проверка, существует ли такая компания
	 *
	 * @throws ReturnFatalException
	 * @throws \returnException
	 * @throws ControllerMethodNotFoundException
	 */
	protected static function _checkCompanyExists(string $current_module):void {

		if (!self::_exists($current_module)) {
			throw new ControllerMethodNotFoundException("company does not exist, probably subdomain is wrong");
		}
	}

}
