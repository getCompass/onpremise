<?php

namespace Compass\Premise;

/**
 * класс для собирания actions в процессе исполнения запроса
 * action это сущность, директива, какое-то действие которое клиенту надо выполнить обязательно
 * заодно с основной целью его запроса
 * они добавляются в ответ в поле "actions" в ApiV1_Handler::
 */
class Type_Api_Action extends \BaseFrame\Controller\Action {

	/**
	 * Просим клиент сходить на старт
	 */
	public function start():void {

		$this->_ar_need["start"] = [];
	}

	/**
	 * Отдаем клиенту состояние авторизации
	 *
	 * @param int $user_id user_id для переопределения залогиненного user_id
	 */
	public function profile(int $user_id = 0):void {

		if ($this->_user_id > 0) {

			$user_id = $this->_user_id;
		}
		$this->_ar_need["profile"] = $user_id;
	}

	/**
	 * Просим клиент подгрузить пользователей
	 *
	 * @param array $user_list
	 */
	public function premiseUsers(array $user_list):void {

		if (count($user_list) < 1) {
			return;
		}

		if (!isset($this->_ar_need["premise_users"])) {
			$this->_ar_need["premise_users"] = [];
		}

		foreach ($user_list as $v) {
			$this->_ar_need["premise_users"][$v] = null;
		}
	}

	/**
	 * Отдаем клиенту состояние профиля онпремайза с правами
	 *
	 */
	public function premiseProfile(int $user_id, array $permission_list):void {

		$this->_ar_need["premise_profile"] = [
			"premise_user_id"         => $user_id,
			"premise_permission_list" => $permission_list,
		];
	}

	// -------------------------------------------------------
	// ACTIONS
	// -------------------------------------------------------

	/**
	 * Отдаем клиенту состояние профиля онпремайза с правами
	 */
	public static function _getPremiseProfile(array $premise_profile):array {

		// приводим права к формату для клиента
		$output_permission_list = [];
		foreach ($premise_profile["premise_permission_list"] as $permission) {
			$output_permission_list[] = Domain_User_Entity_Permissions::PERMISSIONS_OUTPUT_SCHEMA[$permission];
		}

		return [
			"premise_user_id"         => $premise_profile["premise_user_id"],
			"premise_permission_list" => $output_permission_list,
		];
	}

	/**
	 * Просим клиент подгрузить пользователей
	 *
	 * @param array $user_list
	 *
	 * @return array
	 */
	protected function _getPremiseUsers(array $user_list):array {

		$output    = [];
		$user_list = array_keys($user_list);

		// не забываем про форматирование, клиенты будут ругаться если сюда попадут строки
		foreach ($user_list as $v) {
			$output[] = intval($v);
		}

		return [
			"premise_user_list" => (array) $output,
			"signature"         => (string) \CompassApp\Controller\ApiAction::getUsersSignature($output, time()),
		];
	}
}