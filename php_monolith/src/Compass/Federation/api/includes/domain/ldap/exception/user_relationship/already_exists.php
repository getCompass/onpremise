<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/** попытка создать связь «LDAP аккаунт» – «Пользователь Compass», которая уже существует */
class Domain_Ldap_Exception_UserRelationship_AlreadyExists extends DomainException {

	public function __construct(string $message = "user relationship already exists") {

		parent::__construct($message);
	}
}