<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/** связь «LDAP аккаунт» – «Пользователь Compass» не найдена */
class Domain_Ldap_Exception_UserRelationship_NotFound extends DomainException {

	public function __construct(string $message = "user relationship not found") {

		parent::__construct($message);
	}
}