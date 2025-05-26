<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\GatewayException;
use BaseFrame\Server\ServerProvider;

/**
 * класс дял работы с API проверки ip
 */
class Gateway_Ip_Checker {

	/**
	 * @var \Curl
	 */
	protected \Curl $_curl;

	/**
	 * curl timeout в секундах
	 */
	protected const _CURL_TIMEOUT = 10;

	/**
	 * поля которые вернутся в ответе
	 */
	protected const _FIELDS = "status,message,continent,continentCode,country,countryCode,region,regionName,city,district,zip,lat,lon,timezone,offset,currency,isp,org,as,asname,mobile,proxy,hosting,query";

	/**
	 * язык на котором вернутся поля в ответе
	 */
	protected const _LANGUAGE = "en";

	/**
	 *
	 * @throws ParseFatalException
	 */
	public function __construct() {

		if (ServerProvider::isOnPremise()) {
			throw new ParseFatalException("action is not allowed on this environment");
		}

		// создаем curl-объект
		$this->_curl = new \Curl();
		$this->_curl->setTimeout(self::_CURL_TIMEOUT);
		if (mb_strlen(VONAGE_PROXY) > 0) {
			$this->_curl->setOpt(CURLOPT_PROXY, VONAGE_PROXY);
		}
	}

	/**
	 * Совершить post-запрос
	 *
	 * @param string $url
	 * @param array  $ar_post
	 *
	 * @return array
	 * @throws GatewayException
	 * @throws \cs_CurlError
	 */
	public function post(string $url, array $ar_post):array {

		// делаем запрос
		$response = $this->_curl->post($url, toJson($ar_post));

		// если response code не 200, то выбрасываем исключение
		if ($this->_curl->getResponseCode() !== 200) {
			throw new GatewayException("api request was failed, response code: " . $this->_curl->getResponseCode());
		}

		// распаковывем json и возвращаем ответ
		return fromJson($response);
	}

	/**
	 * Создаем сделку
	 * @see https://ip-api.com/docs/api:batch#test
	 *
	 * @param array $subnet_list
	 *
	 * @return array
	 * @throws GatewayException
	 * @throws \cs_CurlError
	 */
	public function batchCheckSubnet24(array $subnet_list):array {

		$ar_post = [];
		foreach ($subnet_list as $subnet) {

			$ar_post[] = [
				"query"  => $subnet,
				"fields" => self::_FIELDS,
				"lang"   => self::_LANGUAGE,
			];
		}
		return $this->post($this->_getBatchRequestUrl(), $ar_post);
	}

	/**
	 * Получаем ссылку для batch запроса
	 *
	 * @return string
	 */
	protected function _getBatchRequestUrl():string {

		return SUBNET_CHECKER_BATCH_REQUEST_URL . "?key=" . SUBNET_CHECKER_API_KEY;
	}
}