<?php

namespace Compass\Federation;

/**
 * исключение связанное с ошибкой на уровне LDAP протокола
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_ProtocolError_InvalidDnSyntax extends Domain_Ldap_Exception_ProtocolError {

	public function __construct(string $message = "invalid dn syntax") {

		parent::__construct(Domain_Ldap_Entity_Client_Default::ERROR_NUM_INVALID_DN_SYNTAX, $message);
	}
}