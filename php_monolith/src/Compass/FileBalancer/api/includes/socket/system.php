<?php

namespace Compass\FileBalancer;

/**
 * группа сокет методов предназначена для получения статуса воркера модуля php_server_type
 * управления его конфиг-файлом файловых нод
 */
class Socket_System extends \BaseFrame\Controller\Socket {

	// поддерживаемые методы. Регистр не имеет значение */
	public const ALLOW_METHODS = [
		"tryPing",
		"setConfig",
		"getConfig",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * метод в большей степени предназначен для получения статуса доступности воркера
	 * а так же получения информации о нем (название модуля, code_uniq идентификатора)
	 *
	 * @return array
	 */
	public function tryPing() {

		return $this->ok([
			"module_name" => (string) CURRENT_MODULE,
			"code_uniq"   => (int) CODE_UNIQ_VERSION,
		]);
	}

	// метод обновляет конфиг-файл

	/**
	 * метод обновляет конфиг-файл
	 *
	 * @return array
	 *
	 * @throws paramException
	 * @throws parseException
	 */
	public function setConfig() {

		$config = $this->post("?a", "config");

		// пройдемся по каждому элементу конфига
		foreach ($config as $v) {

			if (!Type_Node_Config::isConfigItemCorrect($v)) {

				Type_System_Admin::log("incorrect_admin_config", $config);
				throw new parseException("php_admin passed incorrect item of configuration");
			}
		}

		// актуализируем конфигурационный файл
		Type_Node_Config::updateConfig($config);

		return $this->ok();
	}

	// метод возвращает установленный конфиг-файл
	public function getConfig() {

		return $this->ok([
			"config" => (object) Type_Node_Config::getConfig(),
		]);
	}
}