<?php

namespace Compass\FileNode;

/**
 * Класс для собирания actions в процессе исполнения запроса
 * action это сущность, директива, какое-то действие которое клиенту надо выполнить обязательно
 * заодно с основной целью его запроса
 * они добавлются в ответ в поле "actions" в Apiv1_Handler::
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
	 * Просим клиента получить блокирующие анонсы без авторизации.
	 *
	 * @param string $initial_token
	 */
	public function announcementStart(string $initial_token):void {

		$this->_ar_need["announcement_start"] = $initial_token;
	}

	/**
	 * Просим подключится к анонсам как авторизованного пользователя.
	 *
	 * @param string $authorization_token
	 */
	public function announcementConnect(string $authorization_token):void {

		$this->_ar_need["announcement_connect"] = $authorization_token;
	}

	/**
	 * Отдать команду клиенту для отображения попапа для работы с CMD
	 */
	public function cmd():void {

		$this->_ar_need["cmd"] = [];
	}

	/**
	 * Очистить все накопленные actions
	 */
	public function end():void {

		$this->_ar_need = [];
	}

	// -------------------------------------------------------
	// ACTIONS
	// -------------------------------------------------------

	/**
	 * Отдаем клиенту состояние авторизации
	 *
	 * @param int $user_id
	 *
	 * @return int[]
	 * @throws busException
	 * @throws cs_UserNotFound
	 * @throws parseException
	 * @throws userAccessException
	 */
	protected function _getProfile(int $user_id = 0):array {

		// инициализируем массив, который вернем в ответе
		$output = [
			"logged_in" => (int) 0,
		];

		if ($this->_user_id > 0) {

			$user_id = $this->_user_id;
		}

		// если пользователь авторизован
		if ($user_id > 0) {

			// получаем информацию о пользователе
			$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);

			$output["logged_in"] = (int) 1;
			$output["user_id"]   = (int) $user_id;
			$output["user"]      = (object) Apiv1_Pivot_Format::user(Struct_User_Info::createStruct($user_info));
		}

		return $output;
	}

	/**
	 * Просим подключиться для чтения блокирующих анонсов.
	 *
	 * @param string $token
	 *
	 * @return array
	 */
	protected function _getAnnouncementStart(string $token):array {

		return [
			"initial_token" => $token,
			"url"           => ANNOUNCEMENT_PROTOCOL . "://" . ANNOUNCEMENT_DOMAIN,
		];
	}

	/**
	 * Просим подключиться для работы с анонсами.
	 *
	 * @param string $token
	 *
	 * @return array
	 */
	protected function _getAnnouncementConnect(string $token):array {

		return [
			"authorization_token" => $token,
			"url"                 => ANNOUNCEMENT_PROTOCOL . "://" . ANNOUNCEMENT_DOMAIN,
		];
	}
}