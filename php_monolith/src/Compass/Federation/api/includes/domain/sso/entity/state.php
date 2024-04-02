<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для работы с параметром state
 * @package Compass\Federation
 */
class Domain_Sso_Entity_State {

	/** параметры, зашиваемые в state */
	protected const _PARAMETER_LIST           = [
		self::_PARAMETER_SSO_AUTH_TOKEN,
		self::_PARAMETER_REDIRECT_URL,
	];
	protected const _PARAMETER_SSO_AUTH_TOKEN = "sso_auth_token";
	protected const _PARAMETER_REDIRECT_URL   = "redirect_url";

	/** @var array декодированные параметры */
	protected array $_decoded_parameter_list;

	public function __construct(string $state) {

		$this->_decoded_parameter_list = self::_decode($state);
	}

	/**
	 * подготавливаем state строку
	 *
	 * @return string
	 */
	public static function prepare(string $sso_auth_token, string $redirect_url):string {

		$parameter_list = [
			"sso_auth_token" => $sso_auth_token,
			"redirect_url"   => $redirect_url,
		];
		return self::_encode($parameter_list);
	}

	/**
	 * парсим параметр state из ссылки для авторизации в SSO
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	public static function parseStateFromAuthorizationLink(string $link):string {

		$part_list = parse_url($link);
		if (!isset($part_list["query"])) {
			throw new ParseFatalException("unexpected behaviour");
		}

		parse_str($part_list["query"], $get_parameter_list);
		if (!isset($get_parameter_list["state"])) {
			throw new ParseFatalException("unexpected behaviour");
		}

		return $get_parameter_list["state"];
	}

	/**
	 * получаем sso_auth_token
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	public function getSsoAuthToken():string {

		return $this->_getParameter(self::_PARAMETER_SSO_AUTH_TOKEN);
	}

	/**
	 * получаем redirect_url
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	public function getRedirectUrl():string {

		return $this->_getParameter(self::_PARAMETER_REDIRECT_URL);
	}

	/**
	 * получаем параметр
	 *
	 * @return mixed
	 * @throws ParseFatalException
	 */
	protected function _getParameter(string $parameter_name):mixed {

		if (!in_array($parameter_name, self::_PARAMETER_LIST)) {
			throw new ParseFatalException("unexpected parameter");
		}

		if (!isset($this->_decoded_parameter_list[$parameter_name])) {
			throw new ParseFatalException("unexpected behaviour");
		}

		return $this->_decoded_parameter_list[$parameter_name];
	}

	/**
	 * кодируем
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	protected static function _encode(array $parameter_list):string {

		// если пытаемся закодировать параметры, список которых не совпадает с self::_PARAMETER_LIST
		if (count(array_diff(array_keys($parameter_list), self::_PARAMETER_LIST)) > 0 || count(self::_PARAMETER_LIST) !== count($parameter_list)) {
			throw new ParseFatalException("incorrect parameter list");
		}

		return base64_encode(toJson($parameter_list));
	}

	/**
	 * декодируем
	 *
	 * @return array
	 * @throws Domain_Sso_Exception_Auth_State_Invalid
	 */
	protected static function _decode(string $state):array {

		$parameter_list = fromJson(base64_decode($state));
		if (count(array_diff(array_keys($parameter_list), self::_PARAMETER_LIST)) > 0 || count(self::_PARAMETER_LIST) !== count($parameter_list)) {
			throw new Domain_Sso_Exception_Auth_State_Invalid();
		}

		return $parameter_list;
	}
}