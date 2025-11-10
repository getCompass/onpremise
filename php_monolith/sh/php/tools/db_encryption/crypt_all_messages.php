<?php

$company_id = readline("company_id: ");
putenv("COMPANY_ID=$company_id");
$_POST["company_id"] = $company_id;

try {
	require_once __DIR__ . "/../../../../start.php";
} catch (TypeError|BaseFrame\Exception\Domain\ReturnFatalException) {

	console(redText("Передан некорректный id компании"));
	exit(1);
} catch (BaseFrame\Exception\Request\CompanyConfigNotFoundException) {

	console(redText("Компания с id {$company_id} не существует"));
	exit(1);
}

if ($company_id < 1) {

	console(redText("Передан некорректный id компании"));
	exit(1);
}

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

try {
	putenv("DATABASE_CRYPT_SECRET_KEY=" . file_get_contents("/run/secrets/compass_database_encryption_secret_key"));
} catch (Error) {

	console(redText("Для работы скрипта необходимо включить шифрование базы данных в read_write режим: https://doc-onpremise.getcompass.ru/quick-start.html#page-label-quick-start-encrypt-configuration"));
	exit(1);
}

if (DATABASE_ENCRYPTION_MODE !== "read_write") {

	console(redText("Для работы скрипта необходимо включить шифрование базы данных в read_write режим: https://doc-onpremise.getcompass.ru/quick-start.html#page-label-quick-start-encrypt-configuration"));
	exit(1);
}

/**
 * Вспомогательный класс для работы с диалогами
 * @since 14.10.25, безопасен для повторного использования.
 */
class CryptAllConversationMessages extends \Compass\Conversation\Gateway_Db_CompanyConversation_Main
{
	protected const _MESSAGE_BLOCK_TABLE_KEY = "message_block";
	protected const _LEFT_MENU_TABLE_KEY     = "user_left_menu";

	/**
	 * Шифрует все сообщения из шарда
	 */
	public static function updateShard(int $table_id): void
	{

		$limit_count = 10;
		$offset      = 0;
		while (true) {

			Compass\Conversation\Gateway_Db_CompanyConversation_MessageBlock::beginTransaction();

			$query       = "SELECT * FROM `?p` WHERE TRUE ORDER BY `block_id` ASC LIMIT ?i OFFSET ?i";
			$block_list  = static::_connect(static::_getDbKey())->getAll($query, self::_getMessageBlockTable($table_id), $limit_count, $offset);
			$block_count = count($block_list);
			$offset += $block_count;

			// обновляем
			foreach ($block_list as $block_row) {

				static::_connect(self::_getDbKey())->update(
					"UPDATE `?p` SET ?u WHERE conversation_map = ?s AND `block_id` = ?i LIMIT ?i",
					self::_getMessageBlockTable($table_id),
					["data" => fromJson($block_row["data"])],
					$block_row["conversation_map"],
					$block_row["block_id"],
					1
				);
			}

			Compass\Conversation\Gateway_Db_CompanyConversation_MessageBlock::commitTransaction();

			if ($block_count < $limit_count) {
				break;
			}
		}
	}

	/**
	 * Шифрует все сообщения из левого меню
	 */
	public static function updateLeftMenu(): void
	{

		$limit_count = 10;
		$offset      = 0;
		while (true) {

			Compass\Conversation\Gateway_Db_CompanyConversation_MessageBlock::beginTransaction();

			$query           = "SELECT * FROM `?p` WHERE TRUE ORDER BY `created_at` ASC LIMIT ?i OFFSET ?i";
			$left_menu_list  = static::_connect(static::_getDbKey())->getAll($query, self::_getLeftMenuTable(), $limit_count, $offset);
			$left_menu_count = count($left_menu_list);
			$offset += $left_menu_count;

			// обновляем
			foreach ($left_menu_list as $left_menu_row) {

				static::_connect(self::_getDbKey())->update(
					"UPDATE `?p` SET ?u WHERE user_id = ?i AND `conversation_map` = ?s LIMIT ?i",
					self::_getLeftMenuTable(),
					["last_message" => fromJson($left_menu_row["last_message"])],
					$left_menu_row["user_id"],
					$left_menu_row["conversation_map"],
					1
				);
			}

			Compass\Conversation\Gateway_Db_CompanyConversation_MessageBlock::commitTransaction();

			if ($left_menu_count < $limit_count) {
				break;
			}
		}
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// получаем таблицу
	protected static function _getMessageBlockTable(int $table_id): string
	{

		return self::_MESSAGE_BLOCK_TABLE_KEY . "_" . $table_id;
	}

	// получаем таблицу
	protected static function _getLeftMenuTable(): string
	{

		return self::_LEFT_MENU_TABLE_KEY;
	}
}

/**
 * Вспомогательный класс для работы с тредами
 * @since 14.10.25, безопасен для повторного использования.
 */
class CryptAllThreadMessages extends \Compass\Thread\Gateway_Db_CompanyThread_Main
{
	protected const _TABLE_KEY = "message_block";

	/**
	 * Шифрует все сообщения из шарда
	 */
	public static function updateShard(int $table_id): void
	{

		$limit_count = 10;
		$offset      = 0;
		while (true) {

			\Compass\Thread\Gateway_Db_CompanyThread_MessageBlock::beginTransaction();

			$query       = "SELECT * FROM `?p` WHERE TRUE ORDER BY `block_id` ASC LIMIT ?i OFFSET ?i";
			$block_list  = static::_connect(static::_getDbKey())->getAll($query, self::_getTable($table_id), $limit_count, $offset);
			$block_count = count($block_list);
			$offset += $block_count;

			// обновляем
			foreach ($block_list as $block_row) {

				static::_connect(self::_getDbKey())->update(
					"UPDATE `?p` SET ?u WHERE thread_map = ?s AND `block_id` = ?i LIMIT ?i",
					self::_getTable($table_id),
					["data" => fromJson($block_row["data"])],
					$block_row["thread_map"],
					$block_row["block_id"],
					1
				);
			}

			\Compass\Thread\Gateway_Db_CompanyThread_MessageBlock::commitTransaction();

			if ($block_count < $limit_count) {
				break;
			}
		}
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// получаем таблицу
	protected static function _getTable(int $table_id): string
	{

		return self::_TABLE_KEY . "_" . $table_id;
	}
}

// инициализируем все необходимые подключения
Compass\Company\ShardingGateway::instance();
Compass\Company\ShardingGateway::cache();

// шифруем все сообщения
foreach (range(1, 12) as $table_id) {
	CryptAllConversationMessages::updateShard($table_id);
	CryptAllThreadMessages::updateShard($table_id);
}

// шифруем левое меню
CryptAllConversationMessages::updateLeftMenu();
