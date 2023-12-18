<?php

namespace Compass\FileBalancer;

/**
 * класс для собирания actions в процессе исполнения запроса
 * action это сущность, директива, какое-то действие которое клиенту надо выполнить обязательно
 * заодно с основной целью его запроса
 * они добавлются в ответ в поле "actions" в ApiV1_Handler::
 */
class Type_Api_Action extends \BaseFrame\Controller\Action {

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

	// отдаем клиенту состояние авторизации
	public function profile():void {

		$this->_ar_need["profile"] = [];
	}

	// отдать команду клиенту для отображения попапа для работы с CMD
	public function cmd():void {

		$this->_ar_need["cmd"] = [];
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