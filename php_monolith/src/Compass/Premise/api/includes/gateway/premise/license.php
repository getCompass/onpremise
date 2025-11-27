<?php

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * класс-интерфейс для работы с модулем CRM
 */
class Gateway_Premise_License extends Gateway_Premise_Default
{
	/**
	 * Получаем персональный код
	 *
	 * @throws Gateway_Premise_Exception_ServerNotFound
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 */
	public static function getPersonalCode(
		int $user_id,
		Struct_PersonalCode_Data $personal_code_data,
	): string {

		[$status, $response] = self::_call("user.getPersonalCode", [
			"user_id"   => $user_id,
			"user_data" => toJson($personal_code_data->toArray()),
		]);

		if ($status !== "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("unexpected response");
			}

			throw match ($response["error_code"]) {
				6801001 => new Gateway_Premise_Exception_ServerNotFound("server not found"),
				default => new ReturnFatalException("unexpected error code"),
			};
		}

		return $response["personal_code"];
	}

	/**
	 * Регистририуем сервер
	 *
	 * @throws Gateway_Premise_Exception_ServerAlreadyRegistered
	 * @throws Gateway_Premise_Exception_ServerCountExceeded
	 * @throws Gateway_Premise_Exception_ServerIsNotAvailable
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 */
	public static function register(string $endpoint_url, string $server_uid, string | false $yc_identity_document, string | false $yc_identity_document_base64_signature): string
	{

		$ar_post = [
			"endpoint_url" => $endpoint_url,
			"server_uid"   => $server_uid,
		];
		if ($yc_identity_document !== false && $yc_identity_document_base64_signature !== false) {

			$ar_post["yc_identity_document"]                  = $yc_identity_document;
			$ar_post["yc_identity_document_base64_signature"] = $yc_identity_document_base64_signature;
		}
		[$status, $response] = self::_call("public.register", $ar_post);

		if ($status !== "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("unexpected response");
			}

			throw match ($response["error_code"]) {
				6801005 => new Gateway_Premise_Exception_ServerAlreadyRegistered("server already registered"),
				6801004 => new Gateway_Premise_Exception_ServerCountExceeded("server count exceeded"),
				6801003 => new Gateway_Premise_Exception_ServerIsNotAvailable("server is not available"),
				default => new ReturnFatalException("unexpected error code"),
			};
		}

		return $response["secret_key"];
	}

	/**
	 * Обновить secret_key onpremise сервера
	 * !!! Только для тестового/стейдж сервера
	 *
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \ParseException
	 */
	public static function updateServerKey(string $endpoint_url, string $server_uid): string
	{

		if (!ServerProvider::isTest() && !ServerProvider::isStage()) {
			throw new \ParseException("called is not test or stage server");
		}

		[$status, $response] = self::_call("public.updateServerKey", [
			"endpoint_url" => $endpoint_url,
			"server_uid"   => $server_uid,
		]);

		if ($status !== "ok") {
			throw new ReturnFatalException("unexpected response");
		}

		return $response["secret_key"];
	}

	/**
	 * Получить токен аутентификации
	 *
	 * @throws Gateway_Premise_Exception_ServerNotFound
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 */
	public static function getAuthenticationToken(int $user_id): Struct_Premise_AuthToken
	{

		$ar_post = [
			"user_id" => $user_id,
		];

		[$status, $response] = self::_call("user.getAuthenticationToken", $ar_post);

		if ($status !== "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("unexpected response");
			}

			throw match ($response["error_code"]) {
				6801001 => new Gateway_Premise_Exception_ServerNotFound("server not found"),
				default => new ReturnFatalException("unexpected error code"),
			};
		}

		return new Struct_Premise_AuthToken(
			$response["authentication_token"],
			$response["need_refresh_at"]
		);
	}

	/**
	 * Отзываем токен аутентификации.
	 *
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 */
	public static function disableAuthenticationToken(int $user_id): void
	{

		$ar_post = [
			"user_id" => $user_id,
		];

		[$status, $response] = self::_call("user.disableAuthenticationToken", $ar_post);

		if ($status !== "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("unexpected response");
			}
		}
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Делаем вызов
	 *
	 * @throws ReturnFatalException
	 * @throws ParseFatalException
	 */
	protected static function _call(string $method, array $params): array
	{

		if (ServerProvider::isSaas()) {
			return ["", []];
		}

		// получаем url
		$url = self::_getUrl();

		if (mb_strlen($url) < 1) {
			return ["", []];
		}

		// совершаем запрос
		return self::_doCall($url, $method, $params);
	}

	/**
	 * Возвращаем url
	 */
	protected static function _getUrl(): string
	{

		$premise_url           = getConfig("PREMISE_URL");
		$premise_module_config = getConfig("PREMISE_MODULE");
		if (mb_strlen($premise_url["license"]) < 1) {
			return "";
		}

		return sprintf("%s%s", $premise_url["license"], $premise_module_config["license"]["path"]);
	}
}
