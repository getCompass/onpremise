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

	public const ERROR_NUM_AUTH_METHOD_NOT_SUPPORTED = 7;
	public const ERROR_NUM_STRONG_AUTH_REQUIRED      = 8;
	public const ERROR_NUM_INVALID_DN_SYNTAX         = 34;
	public const ERROR_NUM_INVALID_CREDENTIALS       = 49;
	public const ERROR_NUM_UNWILLING_TO_PERFORM      = 53;

	public function __construct(string $host, int $port) {

		$this->ldap_connection = ldap_connect(sprintf("ldap://%s", $host), $port);
		if (!$this->ldap_connection) {
			throw new ParseFatalException(sprintf("could not connect to ldap server [%s]", ldap_error($this->ldap_connection)));
		}
		ldap_set_option($this->ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
	}

	public function bind(string $dn, string $password):bool {

		try {
			return ldap_bind($this->ldap_connection, $dn, $password);
		} catch (\Exception|\Error $e) {

			$error_num = ldap_errno($this->ldap_connection);
			return match ($error_num) {
				self::ERROR_NUM_AUTH_METHOD_NOT_SUPPORTED => throw new Domain_Ldap_Exception_ProtocolError_AuthMethodNotSupported(),
				self::ERROR_NUM_STRONG_AUTH_REQUIRED      => throw new Domain_Ldap_Exception_ProtocolError_StrongAuthRequired(),
				self::ERROR_NUM_INVALID_DN_SYNTAX         => throw new Domain_Ldap_Exception_ProtocolError_InvalidDnSyntax(),
				self::ERROR_NUM_INVALID_CREDENTIALS       => throw new Domain_Ldap_Exception_ProtocolError_InvalidCredentials(),
				self::ERROR_NUM_UNWILLING_TO_PERFORM      => throw new Domain_Ldap_Exception_ProtocolError_UnwillingToPerform(),
				default                                   => throw new Domain_Ldap_Exception_ProtocolError($error_num, $e->getMessage()),
			};
		}
	}

	public function unbind():void {

		ldap_unbind($this->ldap_connection);
	}

	public function searchEntries(string $base, string $filter, array $attribute_list = []):array {

		$search_result = ldap_search($this->ldap_connection, $base, $filter, $attribute_list);
		if (!$search_result) {
			return [];
		}

		$entry_list = ldap_get_entries($this->ldap_connection, $search_result);

		$count = 0;
		if (isset($entry_list["count"])) {

			$count = $entry_list["count"];
			unset($entry_list["count"]);
		}

		return [$count, $entry_list];
	}
}