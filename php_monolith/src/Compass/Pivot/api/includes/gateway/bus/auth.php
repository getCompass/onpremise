<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * класс для работы с go_auth
 */
class Gateway_Bus_Auth
{
	/**
	 * Создать апи ключ
	 *
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function create(int $user_id, int $expires_at, string $name, array $scope_list, int $template_id = 0): Struct_User_Apikey
	{

		$request = new \AuthGrpc\ApikeyCreateRequestStruct([
			"user_id"     => $user_id,
			"expires_at"  => $expires_at,
			"name"        => $name,
			"scope_list"  => $scope_list,
			"template_id" => $template_id,
		]);
		[$response, $status] = self::_doCallGrpc("ApikeyCreate", $request);

		self::_assertError($status->code);

		// формируем ответ
		return new Struct_User_Apikey(
			$response->getUserId(),
			$response->getApiKey(),
			$response->getExpiresAt(),
			$response->getName(),
			iterator_to_array($response->getScopeList()->getIterator()),
			$response->getTemplateId(),
		);
	}

	/**
	 * Пересоздать ключ
	 *
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function refresh(int $user_id, string $api_key): Struct_User_Apikey
	{

		$request = new \AuthGrpc\ApikeyRefreshRequestStruct([
			"user_id" => $user_id,
			"api_key" => $api_key,
		]);
		[$response, $status] = self::_doCallGrpc("ApikeyRefresh", $request);

		self::_assertError($status->code);

		// формируем ответ
		return new Struct_User_Apikey(
			$response->getUserId(),
			$response->getApiKey(),
			$response->getExpiresAt(),
			$response->getName(),
			iterator_to_array($response->getScopeList()->getIterator()),
			$response->getTemplateId(),
		);
	}

	/**
	 * Отредактировать ключ
	 *
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function edit(int $user_id, string $api_key, int $expires_at, string $name, array $scope_list): Struct_User_Apikey
	{

		$request = new \AuthGrpc\ApikeyEditRequestStruct([
			"user_id"    => $user_id,
			"api_key"    => $api_key,
			"expires_at" => $expires_at,
			"name"       => $name,
			"scope_list" => $scope_list,
		]);
		[$response, $status] = self::_doCallGrpc("ApikeyEdit", $request);

		self::_assertError($status->code);

		// формируем ответ
		return new Struct_User_Apikey(
			$response->getUserId(),
			$response->getApiKey(),
			$response->getExpiresAt(),
			$response->getName(),
			iterator_to_array($response->getScopeList()->getIterator()),
			$response->getTemplateId(),
		);
	}

	/**
	 * Удаляем ключ
	 *
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function remove(int $user_id, string $api_key): void
	{

		$request = new \AuthGrpc\ApikeyRemoveRequestStruct([
			"user_id" => $user_id,
			"api_key" => $api_key,
		]);
		[$_, $status] = self::_doCallGrpc("ApikeyRemove", $request);

		self::_assertError($status->code);
	}

	/**
	 * Удаляем все ключи пользователя
	 *
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function removeAll(int $user_id): void
	{

		$request = new \AuthGrpc\ApikeyRemoveAllRequestStruct([
			"user_id" => $user_id,
		]);
		[$_, $status] = self::_doCallGrpc("ApikeyRemoveAll", $request);

		self::_assertError($status->code);
	}

	/**
	 * Получаем массив ключей
	 *
	 * @return Struct_User_Apikey[]
	 *
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function getList(int $user_id): array
	{

		$request = new \AuthGrpc\ApikeyGetListRequestStruct([
			"user_id" => $user_id,
		]);
		[$response, $status] = self::_doCallGrpc("ApikeyGetList", $request);

		self::_assertError($status->code);

		// формируем ответ
		return self::_makeApikeyListStruct($response);
	}

	/**
	 * Получаем массив шаблонов
	 *
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 *
	 * @return Struct_User_ApikeyTemplate[]
	 */
	public static function getTemplateList(): array
	{

		$request             = new \AuthGrpc\ApikeyGetTemplateListRequestStruct([]);
		[$response, $status] = self::_doCallGrpc("ApikeyGetTemplateList", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		// формируем ответ
		// формируем ответ
		return self::_makeApiKeyTemplateListStruct($response);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Форматируем массив с ключами
	 *
	 * @return Struct_User_Apikey[]
	 */
	protected static function _makeApikeyListStruct(\AuthGrpc\ApiKeyListStruct $response): array
	{

		$apikey_list = [];
		foreach ($response->getApiKeyList() as $apikey) {

			$apikey_list[] = new Struct_User_Apikey(
				$apikey->getUserId(),
				$apikey->getApiKey(),
				$apikey->getExpiresAt(),
				$apikey->getName(),
				iterator_to_array($apikey->getScopeList()->getIterator()),
				$apikey->getTemplateId(),
			);
		}

		return $apikey_list;
	}

	/**
	 * Сформировать список темплейтов для API ключей
	 *
	 * @return Struct_User_ApikeyTemplate[]
	 */
	protected static function _makeApiKeyTemplateListStruct(\AuthGrpc\ApiKeyTemplateListStruct $response): array
	{

		$apikey_template_list = [];
		foreach ($response->getApiKeyTemplateList() as $template) {

			$apikey_template_list[] = new Struct_User_ApikeyTemplate(
				$template->getTemplateId(),
				$template->getOrder(),
				$template->getTitle(),
				$template->getUniqName(),
				$template->getDescription(),
				iterator_to_array($template->getScopeList()->getIterator()),
			);
		}

		return $apikey_template_list;
	}

	/**
	 * Проверить ответ на ошибки
	 */
	protected static function _assertError(int $status_code): void
	{

		if ($status_code === \Grpc\STATUS_OK) {
			return;
		}

		throw match ($status_code) {
			901, 902, 914 => new Domain_Apikey_Exception_ApikeyNotFound("api key not found"),
			910     => new Domain_Apikey_Exception_ApikeyIncorrectName("incorrect name for token"),
			911     => new Domain_Apikey_Exception_ApikeyIncorrectExpiresAt("incorrect expires_at"),
			912     => new Domain_Apikey_Exception_ApikeyCountExceeded("api key count exceeded"),
			913     => new Domain_Apikey_Exception_ApikeyIncorrect("cant decrypt api key"),
			915     => new Domain_Apikey_Exception_ApikeyIncorrectTemplateId("invalid template id"),
			916     => new Domain_Apikey_Exception_ApikeyIncorrectScopeList("incorrect scope list"),
			500     => new ReturnFatalException("auth microservice fatal error"),
			default => new ReturnFatalException("unknown error in auth microservice"),
		};
	}

	/**
	 * делаем grpc запрос к указанному методу с переданными данными
	 *
	 * @throws BusFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _doCallGrpc(string $method_name, \Google\Protobuf\Internal\Message $request): array
	{

		$connection = ShardingGateway::rpc("auth", \AuthGrpc\authClient::class);

		return $connection->callGrpc($method_name, $request);
	}
}
