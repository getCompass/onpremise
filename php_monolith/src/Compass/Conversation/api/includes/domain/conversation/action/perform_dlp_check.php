<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Request\InappropriateContentException;
use BaseFrame\Icap\Client\HttpRequestBuilder;
use BaseFrame\Icap\Client\IcapClient;
use BaseFrame\Icap\Client\IcapMockClient;
use BaseFrame\Url\UrlProvider;

/**
 * Действие проверки в dlp
 */
class Domain_Conversation_Action_PerformDlpCheck
{
	/**
	 * Проверить в DLP для списка сообщений
	 *
	 *
	 * @throws Domain_File_Exception_FileNotFound
	 */
	public static function doForMessageList(int $user_id, array $message_list): void
	{

		$request = [];

		foreach ($message_list as $message) {

			if (!isset($message["text"])) {
				continue;
			}

			$request["message_list"][] = $message["text"];
		}

		if ($request === []) {
			return;
		}

		self::_sendToDlp($user_id, $request);
	}

	/**
	 * Проверить в DLP для текста
	 */
	public static function doForText(int $user_id, string $text): void
	{

		$request = [
			"message_list" => [$text]
		];

		self::_sendToDlp($user_id, $request);
	}

	/**
	 * Отправить в DLP
	 */
	private static function _sendToDlp(int $user_id, array $request): void
	{

		$icap_config = Domain_Config_Entity_Icap::instance($user_id);

		// проверяем, включен ли клиент и контролируется ли текст
		if (!$icap_config->isEnabled() || !$icap_config->isTextControlled()) {
			return;
		}

		// формируем болванку http запроса для ICAP
		$http_request_builder = (new HttpRequestBuilder())
			->method("POST")
			->url("/" . str_replace(".", "/", get("api_method")))
			->addHeader("Host", UrlProvider::pivotDomain());

		$http_request_builder
			->bodyFromForm($request);

		// создаем ICAP клиент
		$icap_client = self::_getIcapClient($user_id);

		// отправляем REQMOD запрос
		try {
			$icap_response = $icap_client->reqmod($http_request_builder->build());
		} catch (\Throwable $t) {

			// ошибка в запросе до ICAP - ошибка контента
			throw new InappropriateContentException("icap request error: {$t->getMessage()}");
		}

		// если изменился запрос - значит отдаем ошибку
		if ($icap_response->isRequestModified($icap_client->getLastRequest())) {
			throw new InappropriateContentException("icap request changed");
		}
	}

	/**
	 * Получить ICAP клиент
	 */
	protected static function _getIcapClient(int $user_id): IcapClient
	{

		$icap_config = Domain_Config_Entity_Icap::instance($user_id);

		// если включен мок - активируем мок клиент
		if ($icap_config->isMock()) {
			return IcapMockClient::instance(ShardingGateway::class, DOMINO_ID . "_$user_id");
		}

		return new IcapClient(
			sprintf(
				"icap://%s:%d/%s",
				$icap_config->host(),
				$icap_config->port(),
				$icap_config->service(),
			)
		);
	}
}
