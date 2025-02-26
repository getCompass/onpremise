<?php

namespace Compass\Pivot;

/**
 * Онлайн пользователей
 */
class Apiv2_User_Online extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"get",
		"getList",
	];

	/**
	 * получаем онлайн пользователя
	 *
	 * @return array
	 */
	public function get():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		$last_online_at = Domain_User_Scenario_Api::getOnline($user_id);

		return $this->ok(Apiv2_Format::getOnline($last_online_at));
	}

	/**
	 * получаем список онлайна пользователей
	 *
	 * @return array
	 */
	public function getList():array {

		$user_id_list = $this->post(\Formatter::TYPE_ARRAY_INT, "user_id_list");

		$online_list = Domain_User_Scenario_Api::getOnlineList($user_id_list);

		return $this->ok(Apiv2_Format::getOnlineList($online_list));
	}
}