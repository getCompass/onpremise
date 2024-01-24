<?php

namespace Compass\Speaker;

/**
 * группа сокет методов предназначена для управления конфиг-файлом janus нод
 */
class Socket_System extends \BaseFrame\Controller\Socket {

	// поддерживаемые методы. Регистр не имеет значение */
	public const ALLOW_METHODS = [
		"tryPing",
		"setConfig",
		"execCompanyUpdateScript",
		"setCompanyStatus",
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

	/**
	 * устанавливаем конфиг
	 *
	 * @return array
	 *
	 * @throws \parseException
	 */
	public function setConfig() {

		$config = $this->post("?a", "config");

		// подводим под формат
		$config = $this->_prepareConfig($config);

		// актуализируем конфигурационный файл
		Type_Call_Config::updateConfig($config);

		return $this->ok();
	}

	// подводим под формат
	// @long
	protected function _prepareConfig(array $config):array {

		$prepared_config = [];
		foreach ($config as $server_type => $server_list) {

			switch ($server_type) {

				case "stun_server_list":

					$output = [];
					foreach ($server_list as $v) {

						if (!Type_Call_Config::isStunItemCorrect($v)) {
							throw new \parseException("php_admin passed incorrect item of stun server configuration");
						}

						$output[] = Type_Call_Config::prepareStunServer($v);
					}
					break;

				case "turn_server_list":

					$output = [];
					foreach ($server_list as $v) {

						if (!Type_Call_Config::isTurnItemCorrect($v)) {
							throw new \parseException("php_admin passed incorrect item turn server  of configuration");
						}

						$output[] = Type_Call_Config::prepareTurnServer($v);
					}
					break;

				case "janus_node_list":

					$output = [];
					foreach ($server_list as $v) {

						if (!Type_Call_Config::isJanusItemCorrect($v)) {
							throw new \parseException("php_admin passed incorrect item of janus node configuration");
						}

						$output[] = Type_Call_Config::prepareJanusNode($v);
					}
					break;

				default:

					throw new \parseException("unknown config item");
			}

			$prepared_config[$server_type] = $output;
		}

		return $prepared_config;
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

	/**
	 * Установить статус компании
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function setCompanyStatus():array {

		$status = $this->post(\Formatter::TYPE_INT, "status");

		setCompanyConfig("COMPANY_STATUS", $status);

		return $this->ok();
	}
}