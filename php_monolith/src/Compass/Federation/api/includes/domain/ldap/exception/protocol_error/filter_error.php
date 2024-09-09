<?php

namespace Compass\Federation;

/**
 * исключение связанное с ошибкой поискового фильтра
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_ProtocolError_FilterError extends Domain_Ldap_Exception_ProtocolError {

	public function __construct(string $message = "search filter error") {

		parent::__construct(Domain_Ldap_Entity_Client_Default::ERROR_NUM_FILTER_ERROR, $message);
	}
}