<?php

namespace Compass\Speaker;

/**
 * класс для собирания actions в процессе исполнения запроса
 * action это сущность, директива, какое-то действие которое клиенту надо выполнить обязательно
 * заодно с основной целью его запроса
 * они добавлются в ответ в поле "actions" в ApiV1_Handler::
 */
class Type_Api_Action {

	protected array $_ar_need = []; // какие actions нужно отдать при ответе
	protected int   $_user_id;      // для кого пользователя работаем

	// типы action
	protected const _ACTION_TYPE_USERS          = "users";
	protected const _ACTION_TYPE_CMD            = "cmd";
	protected const _ACTION_TYPE_THREAD_EFFECTS = "thread_effects";
	protected const _ACTION_NEED_PING           = "need_ping";

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

			$output[] = [
				"type" => (string) $k,
				"data" => (object) $this->_getActionData($k, $v),
			];
		}

		return $output;
	}

	// получаем data для любого action
	protected function _getActionData(string $action_name, array $action_data):array {

		switch ($action_name) {

			case self::_ACTION_TYPE_USERS:
				return $this->_getUsers($action_data);

			case self::_ACTION_TYPE_CMD:
				return $this->_getCmd();

			case self::_ACTION_TYPE_THREAD_EFFECTS:
				return $this->_getThreadEffects($action_data);

			case self::_ACTION_NEED_PING:
				return $this->_getNeedPing($action_data);

			default:
				throw new \parseException("Unsupported action `{$action_name}`");
		}
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
			$this->_ar_need[self::_ACTION_TYPE_USERS][$v] = false;
		}
	}

	// отдать команду клиенту для отображения попапа для работы с CMD
	public function cmd():void {

		$this->_ar_need[self::_ACTION_TYPE_CMD] = [];
	}

	// эффекты для тредов
	public function threadEffects(string $thread_effect_id):void {

		if (!isset($this->_ar_need[self::_ACTION_TYPE_THREAD_EFFECTS])) {
			$this->_ar_need[self::_ACTION_TYPE_THREAD_EFFECTS] = [];
		}

		$this->_ar_need[self::_ACTION_TYPE_THREAD_EFFECTS][$thread_effect_id] = false;
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

	/**
	 * просим клиент пропинговать ноды для звонка
	 *
	 */
	public function needPing(string $call_map, array $node_list):void {

		$this->_ar_need["need_ping"] = [
			"call_map"  => (string) $call_map,
			"node_list" => (array) $node_list,
		];
	}

	/**
	 * Получает подпись
	 *
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

	// отдаем cmd
	protected function _getCmd():array {

		return [];
	}

	// просим клиент подгрузить пользователей
	protected function _getUsers(array $action_data):array {

		$output      = [];
		$action_data = array_keys($action_data);

		// не забываем про форматирование, клиенты будут ругаться если сюда попадут строки
		foreach ($action_data as $v) {
			$output[] = intval($v);
		}

		return [
			"user_list" => (array) $output,
			"signature" => (string) self::getUsersSignature($output, time()),
		];
	}

	// отдаем thread_effects
	protected function _getThreadEffects(array $action_data):array {

		$output                = [];
		$thread_effect_id_list = array_keys($action_data);

		// собираем и форматируем массив для ответа
		foreach ($thread_effect_id_list as $v) {
			$output[] = (string) $v;
		}

		return [
			"thread_effect_id_list" => (array) $output,
		];
	}

	// просим клиент пропинговать ноды для звонка
	protected function _getNeedPing(array $data):array {

		return $data;
	}
}