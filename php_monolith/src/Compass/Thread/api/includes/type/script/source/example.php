<?php

namespace Compass\Thread;

use CompassApp\Pack\Message\Conversation;

/**
 * Пример использования логики обновления скриптов.
 */
class Type_Script_Source_Example extends Type_Script_CompanyUpdateTemplate {

	/** @var array данные об исполнении */
	protected array $_log = [];

	/**
	 * Точка входа в скрипт.
	 *
	 * @param array $data
	 * @param int   $mask
	 */
	public function exec(array $data, int $mask = 0):void {

		if ($this->_isDry())  {

			$this->_log("thread: dry run reporting");
			return;
		}

		if (isset($data["commands"])) {

			foreach (explode(" ", $data["commands"]) as $command) {
				$this->_log("performing command {$command}");
			}
		}

		$this->_log("thread: executing something interesting");
		$this->_error("thread: something bad happens :(");
		$this->_error("thread: something bad happens again :(");
		$this->_log("thread: executing something interesting again");
		$this->_log("thread: executing something interesting again and again");
		$this->_error("thread: something bad happens again and again :(");
	}
}