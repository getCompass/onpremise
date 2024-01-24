<?php

namespace Compass\Conversation;

/**
 * Контроллер сокет-методов работы с файлами.
 */
class Socket_File extends \BaseFrame\Controller\Socket {

	public const ALLOW_METHODS = [
		"index",
		"reindex",
	];

	/**
	 * Запускает индексацию файла.
	 */
	public function index():array {

		$file_map = $this->post(\Formatter::TYPE_STRING, "file_map");

		// ставим задачу на индексацию
		Domain_Search_Entity_File_Task_Index::queueList([$file_map]);
		return $this->ok();
	}

	/**
	 * Запускает переиндексацию файла.
	 */
	public function reindex():array {

		$file_map = $this->post(\Formatter::TYPE_STRING, "file_map");

		// ставим задачу на индексацию
		Domain_Search_Entity_File_Task_Reindex::queueList([$file_map]);
		return $this->ok();
	}
}