<?php

namespace Compass\Speaker;

/**
 * Скрипт для обновления подписок событий в go_event.
 */
class Type_Script_Source_UpdateEventSubscriptions extends Type_Script_CompanyUpdateTemplate {

	/**
	 * Точка входа в скрипт.
	 *
	 * @param array $data
	 *
	 * @throws \parseException
	 */
	public function exec(array $data):void {

		if ($this->_isDry()) {
			$this->_log("planning to update generators and subscription\n");
		} else {

			// отправляем на go_event все свои подписки
			Domain_System_Action_Event_RefreshSubscriptions::do();
			$this->_log("generators and subscription update even was sent\n");
		}
	}
}