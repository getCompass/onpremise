<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;
use LDAP\Connection;

/**
 * класс используемый по умолчанию для работы с LDAP
 * @package Compass\Federation
 */
class Domain_Ldap_Entity_Client_Default implements Domain_Ldap_Entity_Client_Interface {

	protected Connection $ldap_connection;

	public const ERROR_NUM_SUCCESS                   = 0;
	public const ERROR_NUM_AUTH_METHOD_NOT_SUPPORTED = 7;
	public const ERROR_NUM_STRONG_AUTH_REQUIRED      = 8;
	public const ERROR_NUM_INVALID_DN_SYNTAX         = 34;
	public const ERROR_NUM_INVALID_CREDENTIALS       = 49;
	public const ERROR_NUM_UNWILLING_TO_PERFORM      = 53;
	public const ERROR_NUM_FILTER_ERROR              = -7;
	public const ERROR_NUM_TIMEOUT_EXCEEDED          = 85;

	/** атрибуты LDAP для получения */
	protected const _GET_LDAP_ATTRIBUTES = [
		"objectclass",
		"samaccountname",
		"samaccounttype",
		"uid",
		"thumbnailphoto",
		"cn",
		"sn",
		"title",
		"description",
		"telephonenumber",
		"givenname",
		"distinguishedname",
		"distinguishedname",
		"whencreated",
		"whenchanged",
		"displayname",
		"memberof",
		"department",
		"company",
		"proxyaddresses",
		"name",
		"countrycode",
		"homedirectory",
		"homedrive",
		"badpasswordtime",
		"lastlogoff",
		"lastlogon",
		"primarygroupid",
		"userparameters",
		"admincount",
		"accountexpires",
		"showinaddressbook",
		"managedobjects",
		"userprincipalname",
		"lockouttime",
		"objectcategory",
		"mail",
		"manager",
		"mobile",
		"otherTelephone",
		"homePhone",
		"co",
		"l",
		"facsimileTelephoneNumber",
		"profilePath",
		"scriptPath",
		"physicalDeliveryOfficeName",
		"userClass",
		"employeeNumber",
		"employeeType",
		"ou",
		"extensionAttribute",
		"extensionAttribute1",
		"extensionAttribute2",
		"extensionAttribute3",
		"extensionAttribute4",
		"extensionAttribute5",
		"extensionAttribute6",
		"extensionAttribute7",
		"extensionAttribute8",
		"extensionAttribute9",
		"extensionAttribute10",
		"extensionAttribute11",
		"extensionAttribute12",
		"extensionAttribute13",
		"extensionAttribute14",
		"extensionAttribute15",
		"st",
		"postalCode",
		"postOfficeBox",
		"initials",
		"info",
		"otherPager",
		"streetAddress",
		"otherHomePhone",
		"wWWHomePage",
		"otherFacsimileTelephoneNumber",
		"otherMobile",
		"ipPhone",
		"otherIpPhone",
		"url",
		"pager",
	];

	public function __construct(string $host, int $port, bool $use_ssl, int $require_cert_strategy, int $timeout) {

		// устанавливаем глобальные параметры SSL до создания соединения
		ldap_set_option(null, LDAP_OPT_X_TLS_REQUIRE_CERT, $require_cert_strategy);

		// создаем соединение с правильным форматированием URL
		if ($use_ssl) {
			$uri                   = "ldaps://" . $host . ":" . $port;
			$this->ldap_connection = ldap_connect($uri);
		} else {
			$this->ldap_connection = ldap_connect($host, $port);
		}

		if (!$this->ldap_connection) {
			throw new ParseFatalException(sprintf("could not connect to ldap server [%s]", ldap_error($this->ldap_connection)));
		}

		// устанавливаем опции для конкретного соединения
		ldap_set_option($this->ldap_connection, LDAP_OPT_REFERRALS, false);
		ldap_set_option($this->ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($this->ldap_connection, LDAP_OPT_NETWORK_TIMEOUT, $timeout);

		// делаем настройку сертификата для текущего соединения
		ldap_set_option($this->ldap_connection, LDAP_OPT_X_TLS_REQUIRE_CERT, $require_cert_strategy);
	}

	public function bind(string $dn, string $password):bool {

		try {
			return ldap_bind($this->ldap_connection, $dn, $password);
		} catch (\Exception|\Error $e) {

			// выбрасываем исключение, если это ldap ошибка
			$error_num = ldap_errno($this->ldap_connection);
			$error_num !== self::ERROR_NUM_SUCCESS && $this->_throwOnErrorNumber($error_num, $e->getMessage());

			// иначе выбрасываем ту ошибку, что возникла
			throw $e;
		}
	}

	public function unbind():void {

		ldap_unbind($this->ldap_connection);
	}

	// @long
	public function searchEntries(string $base, string $filter, int $page_size, array $attribute_list = self::_GET_LDAP_ATTRIBUTES):array {

		// кука, с которой совершается запрос в LDAP провайдер
		$cookie = "";

		// сюда сложим все результаты и количество сущностей
		$output_result = [];
		$count         = 0;

		// переменные, куда в случае ошибки установится ее код и сообщение
		$error_code    = 0;
		$error_message = "";

		do {

			try {

				// результаты поиска за одну итерацию
				$search_result = ldap_search($this->ldap_connection, $base, $filter, $attribute_list, 0, 0, 0, LDAP_DEREF_NEVER,
					[["oid" => LDAP_CONTROL_PAGEDRESULTS, "value" => ["size" => $page_size, "cookie" => $cookie]]]);
			} catch (\Throwable $e) {

				// выбрасываем исключение, если это ldap ошибка
				$error_num = ldap_errno($this->ldap_connection);
				$error_num !== self::ERROR_NUM_SUCCESS && $this->_throwOnErrorNumber($error_num, $e->getMessage());

				// иначе выбрасываем ту ошибку, что возникла
				throw $e;
			}

			// парсим результаты
			ldap_parse_result($this->ldap_connection, $search_result, error_code: $error_code, error_message: $error_message, controls: $response_controls);

			// если есть ошибка, то выбрасываем исключение
			if ($error_code !== self::ERROR_NUM_SUCCESS) {
				$this->_throwOnErrorNumber($error_code, $error_message);
			}

			// достем сущности и сохраняем
			$entry_list = ldap_get_entries($this->ldap_connection, $search_result);
			if (isset($entry_list["count"])) {
				$count += $entry_list["count"];
				unset($entry_list["count"]);
			}
			$output_result = array_merge($output_result, $entry_list);

			// проверяем наличие cookie
			/** @noinspection PhpIllegalStringOffsetInspection */
			$cookie = $response_controls[LDAP_CONTROL_PAGEDRESULTS]["value"]["cookie"] ?? "";
		} while ($cookie !== "");

		return [$count, $output_result];
	}

	/** выбрасываем соответствующее кастомное исключение на LDAP ошибку */
	protected function _throwOnErrorNumber(int $error_num, string $error_message):void {

		match ($error_num) {
			self::ERROR_NUM_AUTH_METHOD_NOT_SUPPORTED => throw new Domain_Ldap_Exception_ProtocolError_AuthMethodNotSupported(),
			self::ERROR_NUM_STRONG_AUTH_REQUIRED      => throw new Domain_Ldap_Exception_ProtocolError_StrongAuthRequired(),
			self::ERROR_NUM_INVALID_DN_SYNTAX         => throw new Domain_Ldap_Exception_ProtocolError_InvalidDnSyntax(),
			self::ERROR_NUM_INVALID_CREDENTIALS       => throw new Domain_Ldap_Exception_ProtocolError_InvalidCredentials(),
			self::ERROR_NUM_UNWILLING_TO_PERFORM      => throw new Domain_Ldap_Exception_ProtocolError_UnwillingToPerform(),
			self::ERROR_NUM_FILTER_ERROR              => throw new Domain_Ldap_Exception_ProtocolError_FilterError(),
			self::ERROR_NUM_TIMEOUT_EXCEEDED          => throw new Domain_Ldap_Exception_ProtocolError_TimeoutExceeded(),
			default                                   => throw new Domain_Ldap_Exception_ProtocolError($error_num, $error_message),
		};
	}
}