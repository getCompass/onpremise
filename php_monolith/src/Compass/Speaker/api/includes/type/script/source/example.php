<?php

namespace Compass\Speaker;

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

			$this->_log("speaker: dry run reporting");
			return;
		}

		if (isset($data["commands"])) {

			foreach (explode(" ", $data["commands"]) as $command) {
				$this->_log("performing command {$command}");
			}
		}

		$this->_log("speaker: executing something interesting");
		$this->_error("speaker: something bad happens :(");
		$this->_error("speaker: something bad happens again :(");
		$this->_log("speaker: executing something interesting again");
		$this->_log("speaker: executing something interesting again and again");
		$this->_error("speaker: something bad happens again and again :(");
	}
}