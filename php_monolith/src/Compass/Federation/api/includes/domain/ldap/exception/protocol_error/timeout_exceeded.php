<?php

namespace Compass\Federation;

/**
 * исключение связанное с ошибкой достижения таймаута
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_ProtocolError_TimeoutExceeded extends Domain_Ldap_Exception_ProtocolError {

	public function __construct(string $message = "timeout exceeded") {

		parent::__construct(Domain_Ldap_Entity_Client_Default::ERROR_NUM_TIMEOUT_EXCEEDED, $message);
	}
}