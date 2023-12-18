<?php

namespace Compass\Pivot;

/**
 * Пользователи уволены или удалены
 */
class Domain_User_Exception_NotAllowedUserList extends \BaseFrame\Exception\DomainException {

	protected array $_account_deleted_user_id_list;
	protected array $_kicked_user_id_list;

	public function __construct(string $message, array $account_deleted_user_id_list = [], array $kicked_user_id_list = []) {

		$this->message                       = $message;
		$this->_account_deleted_user_id_list = array_values($account_deleted_user_id_list);
		$this->_kicked_user_id_list          = array_values($kicked_user_id_list);
		parent::__construct($message);
	}

	/**
	 * вернуть $_account_deleted_user_id_list
	 */
	public function getAccountDeletedUserIdList():array {

		return $this->_account_deleted_user_id_list;
	}

	/**
	 * вернуть $_kicked_user_id_list
	 */
	public function getKickedUserIdList():array {

		return $this->_kicked_user_id_list;
	}
}