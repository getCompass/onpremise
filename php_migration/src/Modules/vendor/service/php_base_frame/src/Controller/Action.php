<?php

namespace BaseFrame\Controller;

/**
 * базовый класс обработки actions
 */
abstract class Action {

	protected int   $_user_id;
	protected array $_ar_need = []; // какие actions нужно отдать при ответе

	public function __construct(int $user_id) {

		$this->_user_id = $user_id;
	}

	/**
	 * Обработать и отдать накопленные actions
	 *
	 * @return array
	 */
	public function getActions():array {

		$output = [];

		// проходим каждый action, обрабатываем и добавляем к ответу
		foreach ($this->_ar_need as $k => $v) {

			$func = "_get" . str_replace("_", "", $k);
			$data = $this->$func($v);

			$output[] = [
				"type" => (string) $k,
				"data" => (object) $data,
			];
		}

		return $output;
	}
}