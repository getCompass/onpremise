<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Request\InappropriateContentException;
use BaseFrame\Icap\Client\HttpRequestBuilder;
use BaseFrame\Icap\Client\IcapClient;
use BaseFrame\Icap\Client\IcapMockClient;
use BaseFrame\Url\UrlProvider;

/**
 * Действие проверки в dlp
 */
class Domain_Jitsi_Action_Conference_PerformDlpCheck
{
	/**
	 * Отправить в DLP
	 */
	public static function do(array $request): void
	{

		// в jitsi не имеем доступа к user_id из коробки - не надо усложнять логику
		$user_id = 0;

		$icap_config = Domain_Config_Entity_Icap::instance($user_id);

		// проверяем, включен ли клиент и контролируется ли текст
		if (!$icap_config->isEnabled() || !$icap_config->isTextControlled()) {
			return;
		}

		// формируем болванку http запроса для ICAP
		$http_request_builder = (new HttpRequestBuilder())
			->method("POST")
			->url("/conference/addMessage")
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
			return IcapMockClient::instance(ShardingGateway::class, CURRENT_MODULE . "_$user_id");
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
