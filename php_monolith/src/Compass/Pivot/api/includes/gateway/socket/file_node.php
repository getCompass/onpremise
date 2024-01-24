<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для работы с модулей file_node
 */
class Gateway_Socket_FileNode extends Gateway_Socket_Default {

	/**
	 * Загружаем дефолтный файл
	 *
	 * @throws \parseException
	 * @throws \returnException
	 * @long
	 */
	public static function uploadDefaultFile(string $node_url, string $file_path, int $file_source):string {

		$params      = [
			"file_source" => $file_source,
		];
		$json_params = toJson($params);

		// получаем url и подпись
		$signature = Type_Socket_Auth_Handler::getSignature(Type_Socket_Auth_Handler::AUTH_TYPE_KEY, SOCKET_KEY_PIVOT, $json_params);

		$ar_post = [
			"method"        => "nodes.uploadDefaultFile",
			"company_id"    => 0,
			"user_id"       => 0,
			"sender_module" => CURRENT_MODULE,
			"json_params"   => $json_params,
			"signature"     => $signature,
		];

		$url = $node_url . "api/socket/";

		// загружаем файл на ноду через curl
		$curl     = new \Curl();
		$response = $curl->uploadFile($url, $ar_post, $file_path);
		$response = fromJson($response);

		if ($response["status"] !== "ok") {
			throw new ReturnFatalException("unexpected response");
		}

		if (!isset($response["response"]["file_key"])) {
			throw new ParseFatalException("unexpected response");
		}

		return $response["response"]["file_key"];
	}

	/**
	 * Загружаем счет
	 *
	 * @throws \returnException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \parseException
	 */
	public static function uploadInvoice(string $node_url, string $file_path, string $mime_type, string $posted_filename, int $file_source):string {

		$params      = [
			"file_source" => $file_source,
		];
		$json_params = toJson($params);

		// получаем url и подпись
		$signature = Type_Socket_Auth_Handler::getSignature(Type_Socket_Auth_Handler::AUTH_TYPE_KEY, SOCKET_KEY_PIVOT, $json_params);

		$ar_post = [
			"method"        => "nodes.uploadInvoice",
			"user_id"       => 0,
			"sender_module" => CURRENT_MODULE,
			"json_params"   => $json_params,
			"signature"     => $signature,
		];

		$url = $node_url . "api/socket/";

		// загружаем файл на ноду через curl
		$curl     = new \Curl();
		$response = $curl->uploadFile($url, $ar_post, $file_path, [], $mime_type, $posted_filename);
		$response = fromJson($response);

		if ($response["status"] !== "ok") {
			throw new ReturnFatalException("unexpected response");
		}

		if (!isset($response["response"]["file_key"])) {
			throw new ParseFatalException("unexpected response");
		}

		return $response["response"]["file_key"];
	}

	/**
	 * заменяем аватарку пользовательского бота
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function replaceUserbotAvatar(string $node_url, string $file_key, string $file_path):void {

		$params      = [
			"file_key" => $file_key,
		];
		$json_params = toJson($params);

		// получаем url и подпись
		$signature = Type_Socket_Auth_Handler::getSignature(
			Type_Socket_Auth_Handler::AUTH_TYPE_KEY, SOCKET_KEY_PIVOT, $json_params
		);

		$ar_post = [
			"method"        => "nodes.replaceUserbotAvatar",
			"company_id"    => 0,
			"user_id"       => 0,
			"sender_module" => CURRENT_MODULE,
			"json_params"   => $json_params,
			"signature"     => $signature,
		];

		$url = $node_url . "api/socket/";

		// загружаем файл на ноду через curl
		$curl     = new \Curl();
		$response = $curl->uploadFile($url, $ar_post, $file_path);
		$response = fromJson($response);

		if ($response["status"] !== "ok") {
			throw new ReturnFatalException("unexpected response");
		}
	}

	/**
	 * Заменяем превью у видео-онбординга
	 */
	public static function replacePreviewForWelcomeVideo(string $node_url, string $welcome_video_file_key, string $replace_preview_file_key):void {

		$params      = [
			"welcome_video_file_key"   => $welcome_video_file_key,
			"replace_preview_file_key" => $replace_preview_file_key,
		];
		$json_params = toJson($params);

		$ar_post = [
			"method"        => "nodes.replacePreviewForWelcomeVideo",
			"company_id"    => 0,
			"user_id"       => 0,
			"sender_module" => CURRENT_MODULE,
			"json_params"   => $json_params,
			"signature"     => Type_Socket_Auth_Handler::getSignature(Type_Socket_Auth_Handler::AUTH_TYPE_KEY, SOCKET_KEY_PIVOT, $json_params),
		];

		// загружаем файл на ноду через curl
		$curl     = new \Curl();
		$response = $curl->post($node_url . "api/socket/", $ar_post);
		$response = fromJson($response);

		if ($response["status"] !== "ok") {
			throw new ReturnFatalException("unexpected response");
		}
	}

	/**
	 * Загружаем файл по url
	 *
	 * @throws \parseException
	 * @throws \returnException|\cs_CurlError
	 * @long
	 */
	public static function uploadFile(string $node_url, string $file_url, string $file_name, int $company_id, string $company_url):string {

		$params      = [
			"file_url"    => $file_url,
			"file_name"   => $file_name,
			"company_id"  => $company_id,
			"company_url" => $company_url,
		];
		$json_params = toJson($params);

		$ar_post = [
			"method"        => "system.doUploadFile",
			"company_id"    => 0,
			"user_id"       => 0,
			"sender_module" => CURRENT_MODULE,
			"json_params"   => $json_params,
			"signature"     => Type_Socket_Auth_Handler::getSignature(Type_Socket_Auth_Handler::AUTH_TYPE_KEY, SOCKET_KEY_PIVOT, $json_params),
		];

		// загружаем файл на ноду через curl
		$curl     = new \Curl();
		$response = $curl->post($node_url . "api/socket/", $ar_post);
		$response = fromJson($response);

		if ($response["status"] !== "ok") {
			throw new ReturnFatalException("unexpected response");
		}

		if (!isset($response["response"]["file_key"])) {
			throw new ParseFatalException("unexpected response");
		}

		return $response["response"]["file_key"];
	}
}
