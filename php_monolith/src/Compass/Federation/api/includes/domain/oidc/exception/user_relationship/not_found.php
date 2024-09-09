<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/** связь «SSO аккаунт» – «Пользователь Compass» не найдена */
class Domain_Oidc_Exception_UserRelationship_NotFound extends DomainException {

	public function __construct(string $message = "user relationship not found") {

		parent::__construct($message);
	}
}