<?php

namespace Compass\Thread;

// класс для собирания actions в процессе исполнения запроса
// action это сущность, директива, какое-то действие которое клиенту надо выполнить обязательно
// заодно с основной целью его запроса они добавлются в ответ в поле "actions" в ApiV1_Handler::
class Type_Api_Action {

	// время, в течении которого валидна подпись для action users
	protected const _USERS_SIGNATURE_EXPIRE = 60 * 2;

	protected $_ar_need = []; // какие actions нужно отдать при ответе
	protected $_user_id;      // для кого пользователя работаем

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

	/**
	 * Получает подпись
	 *
	 * @param array $user_list
	 * @param int   $time
	 *
	 * @return string
	 */
	public static function getUsersSignature(array $user_list, int $time):string {

		// делаем int каждого элемента
		$temp = [];
		foreach ($user_list as $v) {
			$temp[] = (int) $v;
		}
		$user_list = $temp;

		$user_list[] = $time;
		sort($user_list);

		$json = toJson($user_list);

		// зашифровываем данные
		$iv_length   = openssl_cipher_iv_length(ENCRYPT_CIPHER_METHOD);
		$iv          = substr(ENCRYPT_IV_ACTION, 0, $iv_length);
		$binary_data = openssl_encrypt($json, ENCRYPT_CIPHER_METHOD, ENCRYPT_PASSPHRASE_ACTION, 0, $iv);

		return md5($binary_data) . "_" . $time;
	}

	// -------------------------------------------------------
	// ACTIONS
	// -------------------------------------------------------

	// просим клиент сходить на старт
	protected function _getStart():array {

		return [
			"url" => (string) Type_System_Url::getStartUrl(),
		];
	}

	// просим клиент подгрузить пользователей
	protected function _getUsers(array $user_list):array {

		$output    = [];
		$user_list = array_keys($user_list);

		// не забываем про форматирование, клиенты будут ругаться если сюда попадут строки
		foreach ($user_list as $v) {
			$output[] = intval($v);
		}

		return [
			"user_list" => (array) $output,
			"signature" => (string) self::getUsersSignature($output, time()),
		];
	}

	// отдаем cmd
	protected function _getCmd():array {

		return [];
	}
}