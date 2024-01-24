<?php

namespace Compass\Company;

/**
 * Класс для работы с Extra для member уведомлений
 *
 * @package Compass\Company
 */
class Domain_Member_Entity_Notification_Extra {

	private int    $_hiring_request_id;
	private string $_action_user_full_name;
	private string $_company_name;
	private string $_action_user_avatar_file_key;
	private string $_action_user_avatar_color;

	/**
	 * Domain_Member_Entity_Notification_Extra constructor.
	 *
	 * @param int    $hiring_request_id
	 * @param string $action_user_full_name
	 * @param string $company_name
	 * @param string $action_user_avatar_file_key
	 * @param string $action_user_avatar_color
	 */
	public function __construct(int $hiring_request_id, string $action_user_full_name, string $company_name, string $action_user_avatar_file_key, string $action_user_avatar_color) {

		$this->_hiring_request_id           = $hiring_request_id;
		$this->_action_user_full_name       = $action_user_full_name;
		$this->_action_user_avatar_file_key = $action_user_avatar_file_key;
		$this->_action_user_avatar_color    = $action_user_avatar_color;
		$this->_company_name                = $company_name;
	}

	/**
	 * получаем hiring_request_id
	 *
	 * @return int
	 */
	public function getHiringRequestId():int {

		return $this->_hiring_request_id;
	}

	/**
	 * задаем hiring_request_id
	 *
	 * @param int $hiring_request_id
	 *
	 * @return void
	 */
	public function setHiringRequestId(int $hiring_request_id):void {

		$this->_hiring_request_id = $hiring_request_id;
	}

	/**
	 * получаем action_user_full_name
	 *
	 * @return string
	 */
	public function getActionUserFullName():string {

		return $this->_action_user_full_name;
	}

	/**
	 * задаем action_user_full_name
	 *
	 * @param string $action_user_full_name
	 *
	 * @return void
	 */
	public function setActionUserFullName(string $action_user_full_name):void {

		$this->_action_user_full_name = $action_user_full_name;
	}

	/**
	 * получаем company_name
	 *
	 * @return string
	 */
	public function getCompanyName():string {

		return $this->_company_name;
	}

	/**
	 * задаем company_name
	 *
	 * @param string $company_name
	 *
	 * @return void
	 */
	public function setCompanyName(string $company_name):void {

		$this->_company_name = $company_name;
	}

	/**
	 * получаем action_user_avatar_file_key
	 *
	 * @return string
	 */
	public function getActionUserAvatarFileKey():string {

		return $this->_action_user_avatar_file_key;
	}

	/**
	 * получаем action_user_avatar_color
	 *
	 * @return string
	 */
	public function getActionUserAvatarColor():string {

		return $this->_action_user_avatar_color;
	}
}
