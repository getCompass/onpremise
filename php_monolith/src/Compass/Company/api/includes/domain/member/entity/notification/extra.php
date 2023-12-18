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

	/**
	 * Domain_Member_Entity_Notification_Extra constructor.
	 *
	 * @param int    $hiring_request_id
	 * @param string $action_user_full_name
	 * @param string $company_name
	 */
	public function __construct(int $hiring_request_id, string $action_user_full_name, string $company_name) {

		$this->_hiring_request_id     = $hiring_request_id;
		$this->_action_user_full_name = $action_user_full_name;
		$this->_company_name          = $company_name;
	}

	public function getHiringRequestId():int {

		return $this->_hiring_request_id;
	}

	public function setHiringRequestId(int $hiring_request_id):void {

		$this->_hiring_request_id = $hiring_request_id;
	}

	public function getActionUserFullName():string {

		return $this->_action_user_full_name;
	}

	public function setActionUserFullName(string $action_user_full_name):void {

		$this->_action_user_full_name = $action_user_full_name;
	}

	public function getCompanyName():string {

		return $this->_company_name;
	}

	public function setCompanyName(string $company_name):void {

		$this->_company_name = $company_name;
	}
}
