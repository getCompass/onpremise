<?php

namespace Compass\Federation;

/**
 * исключение связанное с ошибкой на уровне LDAP протокола
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_ProtocolError_AuthMethodNotSupported extends Domain_Ldap_Exception_ProtocolError {

	public function __construct(string $message = "auth method not supported") {

		parent::__construct(Domain_Ldap_Entity_Client_Default::ERROR_NUM_AUTH_METHOD_NOT_SUPPORTED, $message);
	}
}