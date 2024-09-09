<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/** попытка создать связь «SSO аккаунт» – «Пользователь Compass», которая уже существует */
class Domain_Oidc_Exception_UserRelationship_AlreadyExists extends DomainException {

	public function __construct(string $message = "user relationship already exists") {

		parent::__construct($message);
	}
}