<?php

namespace Compass\FileNode;

use BaseFrame\Server\ServerProvider;

require_once __DIR__ . "/../../../../../../start.php";

/**
 * Скрипт для удаления просроченных файлов
 */
class Delete_Expired_File
{
	// ключ для datastore
	private const _DATASTORE_KEY = "file_auto_deletion";

	/**
	 * Выполняем
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsHibernated
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\QueryFatalException
	 * @throws \cs_SocketRequestIsFailed
	 */
	public static function do(): void
	{

		$is_force = Type_Script_InputParser::getArgumentValue("--force", Type_Script_InputParser::TYPE_NONE, false, false);

		$auto_deletion_config     = getConfig("FILE_AUTO_DELETION");
		$is_auto_deletion_enabled = $auto_deletion_config["is_enabled"] ?? false;
		$check_interval           = $auto_deletion_config["check_interval"] ?? 0;

		// файлы нельзя удалять на проде сааса
		if ((ServerProvider::isSaas() && ServerProvider::isProduction()) || !$is_auto_deletion_enabled || $check_interval == 0) {
			exit(3);
		}

		// получаем время последней проверки и интервал проверки в секунда
		$last_check_at_arr   = Type_System_Datastore::get(self::_getKey(self::_DATASTORE_KEY));
		$last_check_at       = $last_check_at_arr["last_check_at"] ?? 0;
		$check_interval_time = $check_interval * 60 * 60 * 24;

		// если время проверки еще не пришло, то завершаем выполнение
		if ($last_check_at + $check_interval_time > time() && !$is_force) {
			exit(4);
		}

		// удаляем файлы
		Domain_File_Action_DeleteExpiredFiles::do();

		// обновляем datastore, устанавливая новое время последней проверки
		Type_System_Datastore::set(self::_getKey(self::_DATASTORE_KEY), ["last_check_at" => dayStart()]);
	}

	/**
	 * Получаем ключ настройки
	 */
	protected static function _getKey(string $key): string
	{

		return CODE_UNIQ_VERSION . "_" . $key;
	}
}
Delete_Expired_File::do();
