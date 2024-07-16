<?php

namespace Compass\Jitsi;

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
	 * Просим подключиться для чтения блокирующих анонсов.
	 *
	 * @param string $token
	 *
	 * @return array
	 * @noinspection PhpUnused
	 */
	protected function _getAnnouncementStart(string $token):array {

		return [
			"initial_token" => $token,
			"url"           => PUBLIC_ENTRYPOINT_ANNOUNCEMENT,
		];
	}

	/**
	 * Просим подключиться для работы с анонсами.
	 *
	 * @param string $token
	 *
	 * @return array
	 * @noinspection PhpUnused
	 */
	protected function _getAnnouncementConnect(string $token):array {

		return [
			"authorization_token" => $token,
			"url"                 => PUBLIC_ENTRYPOINT_ANNOUNCEMENT,
		];
	}
}