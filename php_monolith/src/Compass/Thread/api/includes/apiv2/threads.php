<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * контроллер, отвечающий за работу тредов
 */
class Apiv2_Threads extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"getMetaAndMenuBatching",
	];

	############################	##############################
	# region диалоги
	##########################################################

	/**
	 * Метод для получения списка тредов
	 *
	 * @return array
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws BusFatalException
	 * @throws \parseException
	 * @long
	 */
	public function getMetaAndMenuBatching():array {

		$thread_key_list = $this->post(\Formatter::TYPE_ARRAY, "thread_key_list");

		// бросаем ошибку, если пришел некорректный массив тредов
		$this->_throwIfThreadListIsIncorrect($thread_key_list);

		// преобразуем все key в map
		$thread_map_list = $this->_tryDecryptThreadList($thread_key_list);

		[
			$frontend_thread_meta_list,
			$frontend_thread_menu_list,
			$action_user_id_list,
		] = Domain_Thread_Scenario_Apiv2::getMetaAndMenuBatching($this->user_id, $this->role, $thread_map_list);

		$this->action->users($action_user_id_list);

		return $this->ok([
			"thread_meta_list" => (array) $frontend_thread_meta_list,
			"thread_menu_list" => (array) $frontend_thread_menu_list,
		]);
	}

	// выбрасываем ошибку, если список тредов некорректный
	protected function _throwIfThreadListIsIncorrect(array $thread_list):void {

		// если пришел пустой массив файлов
		if (count($thread_list) < 1) {
			throw new ParamException("passed empty thread_list");
		}

		// если пришел слишком большой массив
		if (count($thread_list) > Domain_Thread_Entity_Validator::MAX_THREAD_MENU_COUNT) {
			throw new ParamException("passed thread_list biggest than max");
		}
	}

	// преобразуем пришедшие ключи в map
	protected function _tryDecryptThreadList(array $thread_list):array {

		$thread_map_list = [];
		foreach ($thread_list as $key) {

			// преобразуем key в map
			$thread_map = \CompassApp\Pack\Thread::tryDecrypt($key);

			// добавляем тред в массив
			$thread_map_list[] = $thread_map;
		}

		return $thread_map_list;
	}
}
