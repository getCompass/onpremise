<?php

namespace Compass\Migration;

/**
 * класс для собирания actions в процессе исполнения запроса
 * action это сущность, директива, какое-то действие которое клиенту надо выполнить обязательно
 * заодно с основной целью его запроса
 * они добавлются в ответ в поле "actions" в ApiV1_Handler::
 */
class Type_Api_Action {

	protected array $_ar_need = []; // какие actions нужно отдать при ответе
	protected int   $_user_id;      // для кого пользователя работаем

	protected function __construct(int $user_id) {

		$this->_user_id = $user_id;
	}

	// инициализируем и кладем класс в $GLOBALS
	public static function init(int $user_id):self {

		if (isset($GLOBALS[__CLASS__][$user_id])) {

			return $GLOBALS[__CLASS__][$user_id];
		}

		$GLOBALS[__CLASS__][$user_id] = new self($user_id);

		return $GLOBALS[__CLASS__][$user_id];
	}

	// -------------------------------------------------------
	// ACTIONS
	// -------------------------------------------------------

	// обработать и отдать накопленные actions
	public function getActions():array {

		$output = [];

		// проходим каждый action, обрабатываем и добавляем к ответу
		foreach ($this->_ar_need as $k => $v) {

			$func = "_get" . $k;
			$data = $this->$func($v);

			$output[] = [
				"type" => (string) $k,
				"data" => (object) $data,
			];
		}

		return $output;
	}

	// просим клиент сходить на старт
	public function start():void {

		$this->_ar_need["start"] = [];
	}

	// просим клиент подгрузить пользователей
	public function users(array $user_list):void {

		// nothing
		if (count($user_list) < 1) {

			return;
		}

		if (!isset($this->_ar_need["users"])) {

			$this->_ar_need["users"] = [];
		}

		foreach ($user_list as $v) {

			$this->_ar_need["users"][$v] = null;
		}
	}

	// отдаем клиенту состояние авторизации
	public function profile():void {

		$this->_ar_need["profile"] = [];
	}

	// отдать команду клиенту для отображения попапа для работы с CMD
	public function cmd():void {

		$this->_ar_need["cmd"] = [];
	}

	// очистить все накопленные actions
	public function end():void {

		$this->_ar_need = [];
	}

	// отдаем клиенту анонс о каком-то событии
	public function announcement(array $announcement):void {

		// если анонс пустой
		if (count($announcement) < 1) {
			return;
		}

		$this->_ar_need["announcement"] = $announcement;
	}

	// -------------------------------------------------------
	// ACTIONS
	// -------------------------------------------------------

	// отдаем cmd
	protected function _getCmd():array {

		return [];
	}

	// отдаем анонс на клиент
	protected function _getAnnouncement(array $announcement):array {

		return $announcement;
	}
}