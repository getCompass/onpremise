<?php

namespace Compass\Company;

/**
 * исключение на случай если заявка на вступление уже была одобрена
 */
class Domain_MemberManagement_Exception_JoinRequestAlreadyConfirmed extends \DomainException {

	// роль участника, чью заявку уже одобрили
	protected int $_member_role;

	public function __construct(int $member_role, string $message = "join request already confirmed", int $code = 0, ?\Throwable $previous = null) {

		// запишем роль участника, чью заявку уже одобрили
		$this->_member_role = $member_role;

		parent::__construct($message, $code, $previous);
	}

	public function getMemberRole():int {

		return $this->_member_role;
	}
}