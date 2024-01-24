<?php

namespace Compass\Pivot;

/**
 * Action для добавления логов для тестового сервера
 */
class Domain_System_Action_TestLog {

	// Лог обновления порта
	// Нужен для понимания почему вакантная компания не имеет записи с активным портом в таблице port_registry
	public const UPDATE_PORT_LOG = "update_port_log";

	/**
	 * Выполняем
	 */
	public static function do(string $log_name, array $log_data):void {

		// если не тестовый, то пропускаем
		if (!isTestServer()) {
			return;
		}

		Type_System_Admin::log($log_name, $log_data);
	}
}
