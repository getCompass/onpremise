<?php

namespace Compass\Conversation;

/**
 * группа сокет методов предназначена для получения статуса воркера модуля php_conversation
 * управления его конфиг-файлом архивных среверов
 */
class Socket_System extends \BaseFrame\Controller\Socket {

	// Поддерживаемые методы. Регистр не имеет значение */
	public const ALLOW_METHODS = [
		"tryPing",
		"execCompanyUpdateScript",
		"setCompanyStatus",
		"sendMessageWithFile"
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	// метод в большей степени предназначен для получения статуса доступности воркера
	// а так же получения информации о нем (название модуля, code_uniq идентификатора)
	public function tryPing():array {

		return $this->ok([
			"module_name" => (string) CURRENT_MODULE,
			"code_uniq"   => (int) CODE_UNIQ_VERSION,
		]);
	}

	/**
	 * Вызывает выполнения скрипта в компании.
	 * Используется для фикса данных в бд при обновлении.
	 */
	public function execCompanyUpdateScript():array {

		$script_name = $this->post(\Formatter::TYPE_STRING, "script_name");
		$script_data = $this->post(\Formatter::TYPE_ARRAY, "script_data");
		$flag_mask   = $this->post(\Formatter::TYPE_INT, "flag_mask");

		try {
			[$script_log, $error_log] = Type_Script_Handler::exec($script_name, $script_data, $flag_mask);
		} catch (\Exception $e) {
			return $this->error($e->getCode(), $e->getMessage());
		}

		return $this->ok([
			"script_log" => (string) $script_log,
			"error_log"  => (string) $error_log,
		]);
	}

	// оптравляем сообщение с файлом от пользователя
	public function sendMessageWithFile():array {

		$sender_id        = $this->post(\Formatter::TYPE_STRING, "sender_id");
		$file_key         = $this->post(\Formatter::TYPE_STRING, "file_key");
		$conversation_map = $this->post(\Formatter::TYPE_STRING, "conversation_map");
		$file_map         = \CompassApp\Pack\File::doDecrypt($file_key);

		// формируем сообщение
		$message = Type_Conversation_Message_Main::getLastVersionHandler()::makeFile($sender_id, "", generateUUID(), $file_map);

		$meta_row = Type_Conversation_Meta::get($conversation_map);

		try {

			Helper_Conversations::addMessage(
				$conversation_map,
				$message, $meta_row["users"],
				$meta_row["type"],
				$meta_row["conversation_name"],
				$meta_row["extra"]
			);
		} catch (cs_ConversationIsLocked) {
			return $this->error(10018, "Conversation is locked");
		}

		return $this->ok();
	}

}