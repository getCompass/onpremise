<?php

namespace Compass\Announcement;

/**
 * класс для собирания actions в процессе исполнения запроса
 * action это сущность, директива, какое-то действие которое клиенту надо выполнить обязательно
 * заодно с основной целью его запроса
 * они добавлются в ответ в поле "actions" в ApiV1_Handler::
 */
class Type_Api_Action extends \BaseFrame\Controller\Action {

	// просим клиент сходить на старт
	public function start():void {

		$this->_ar_need["start"] = [];
	}
}