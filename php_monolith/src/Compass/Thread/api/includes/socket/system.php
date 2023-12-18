<?php

namespace Compass\Thread;

/**
 * группа сокет методов предназначеная для получения статуса воркера модуля php_thread
 * управления его конфиг-файлом архивных серверов
 */
class Socket_System extends \BaseFrame\Controller\Socket {

	// поддерживаемые методы, регистр не имеет значение
	public const ALLOW_METHODS = [
		"tryPing",
		"setConfig",
		"getConfig",
		"execCompanyUpdateScript",
		"setCompanyStatus",
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
}