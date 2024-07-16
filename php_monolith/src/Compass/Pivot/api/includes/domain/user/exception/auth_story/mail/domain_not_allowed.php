<?php

namespace Compass\Pivot;

use BaseFrame\Exception\DomainException;

/** домен почты не разрешен для регистрации */
class Domain_User_Exception_AuthStory_Mail_DomainNotAllowed extends DomainException {

	protected array $_allowed_domain_list;

	public function __construct(array $allowed_domain_list, string $message = "domain not allowed") {

		$this->_allowed_domain_list = $allowed_domain_list;

		parent::__construct($message);
	}

	/**
	 * получить allowed_domain_list
	 *
	 * @return array
	 */
	public function getAllowedDomainList():array {

		return $this->_allowed_domain_list;
	}
}