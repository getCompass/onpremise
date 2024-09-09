<?php

namespace Compass\Pivot;

use BaseFrame\Exception\DomainException;

/** связь «SSO аккаунт» – «Пользователь Compass» уже существует */
class Domain_User_Exception_AuthStory_Sso_UserRelationshipAlreadyExists extends DomainException {

	public function __construct(string $message = "user relationship already exists") {

		parent::__construct($message);
	}
}