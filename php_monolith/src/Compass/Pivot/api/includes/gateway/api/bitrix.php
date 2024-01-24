<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * класс дял работы с Bitrix API
 */
class Gateway_Api_Bitrix {

	/**
	 * @var string авторизованная ссылка для работы с Bitrix API
	 */
	protected string $_authorized_endpoint_url;

	/**
	 * @var \Curl
	 */
	protected \Curl $_curl;

	/**
	 * curl timeout в секундах
	 */
	protected const _CURL_TIMEOUT = 10;

	/**
	 * @param string $authorized_endpoint_url авторизованная ссылка для работы с Bitrix API
	 */
	public function __construct(
		string $authorized_endpoint_url,
	) {

		if (ServerProvider::isOnPremise()) {
			throw new ParseFatalException("action is not allowed on this environment");
		}

		// сохраняем ссылочку
		$this->_authorized_endpoint_url = $authorized_endpoint_url;

		// создаем curl-объект
		$this->_curl = new \Curl();
		$this->_curl->setTimeout(self::_CURL_TIMEOUT);
	}

	/**
	 * Совершить post-запрос
	 *
	 * @return array
	 */
	public function post(string $method, array $parameters):array {

		// формируем ссылочку
		$url = $this->_getFinallyLink($method);

		// делаем запрос
		$response = $this->_curl->post($url, $parameters);

		// если response code не 200, то выбрасываем исключение
		if ($this->_curl->getResponseCode() !== 200) {
			throw new \BaseFrame\Exception\GatewayException("api request was failed, response code: " . $this->_curl->getResponseCode());
		}

		// распаковывем json и возвращаем ответ
		return fromJson($response);
	}

	/**
	 * Совершить get-запрос
	 *
	 * @return array
	 */
	public function get(string $method, array $parameters):array {

		// формируем ссылочку
		$url = $this->_getFinallyLink($method);

		// делаем запрос
		$response = $this->_curl->get($url, $parameters);

		// если response code не 200, то выбрасываем исключение
		if ($this->_curl->getResponseCode() !== 200) {
			throw new \BaseFrame\Exception\GatewayException("api request was failed, response code: " . $this->_curl->getResponseCode());
		}

		// распаковывем json и возвращаем ответ
		return fromJson($response);
	}

	/**
	 * Создаем сделку
	 *
	 * @param array $fields @see https://dev.1c-bitrix.ru/rest_help/crm/cdeals/crm_deal_fields.php
	 * @param array $params @see https://dev.1c-bitrix.ru/rest_help/crm/cdeals/crm_deal_add.php
	 *
	 * @return array
	 */
	public function crmDealAdd(array $fields, array $params = []):array {

		$ar_post = [
			"fields" => $fields,
			"params" => $params,
		];
		return $this->post("crm.deal.add", $ar_post);
	}

	/**
	 * Создаем контакт
	 *
	 * @param array $fields @see https://dev.1c-bitrix.ru/rest_help/crm/cdeals/crm_contact_fields.php
	 * @param array $params @see https://dev.1c-bitrix.ru/rest_help/crm/contacts/crm_contact_add.php
	 *
	 * @return array
	 */
	public function crmContactAdd(array $fields, array $params = []):array {

		$ar_post = [
			"fields" => $fields,
			"params" => $params,
		];
		return $this->post("crm.contact.add", $ar_post);
	}

	/**
	 * Обновляем контакт
	 *
	 * @param int   $contact_id ID контакта
	 * @param array $fields     @see https://dev.1c-bitrix.ru/rest_help/crm/cdeals/crm_contact_fields.php
	 * @param array $params     @see https://dev.1c-bitrix.ru/rest_help/crm/contacts/crm_contact_update.php
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\GatewayException
	 */
	public function crmContactUpdate(int $contact_id, array $fields, array $params = []):array {

		$ar_post = [
			"id"     => $contact_id,
			"fields" => $fields,
			"params" => $params,
		];
		return $this->post("crm.contact.update", $ar_post);
	}

	/**
	 * Обновляем сделку
	 *
	 * @param int   $deal_id ID сделки
	 * @param array $fields  @see https://dev.1c-bitrix.ru/rest_help/crm/cdeals/crm_deal_fields.php
	 * @param array $params  @see https://dev.1c-bitrix.ru/rest_help/crm/contacts/crm_deal_update.php
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\GatewayException
	 */
	public function crmDealUpdate(int $deal_id, array $fields, array $params = []):array {

		$ar_post = [
			"id"     => $deal_id,
			"fields" => $fields,
			"params" => $params,
		];
		return $this->post("crm.deal.update", $ar_post);
	}

	/**
	 * Получаем финальную ссылку метода
	 *
	 * @return string
	 */
	protected function _getFinallyLink(string $method):string {

		return "$this->_authorized_endpoint_url$method.json";
	}
}