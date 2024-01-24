<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Класс скрипта для отправки текста смс в бота
 */
class Script_Send_Sms_In_Bot {

	/**
	 * Определим параметры и вызовем скрипт
	 *
	 * @throws cs_TwoFaTypeIsInvalid
	 * @throws \parseException
	 */
	public static function exec():void {

		$params = self::_setParams();
		self::_doWork($params);
	}

	/**
	 * Обновляем дефолтные ключи файлов
	 *
	 * @param array $params
	 *
	 * @throws \cs_RowIsEmpty
	 */
	protected static function _doWork(array $params):void {

		$default_file_list = Type_File_Default::getDefaultFileList();

		foreach ($default_file_list as $default_file) {

			$default_file_row = Gateway_Db_PivotSystem_DefaultFileList::get($default_file["dictionary_key"]);

			try {
				Type_Pack_File::doDecrypt($default_file_row["file_key"]);
			} catch (\cs_DecryptHasFailed) {

				try {
					$file_map = Type_Pack_File::doDecrypt($default_file_row["file_key"], $params["old_encrypt_iv"], $params["old_encrypt_key"]);
					$file_key = Type_Pack_File::doEncrypt($file_map);

					Gateway_Db_PivotSystem_DefaultFileList::set(
						$default_file["dictionary_key"], $file_key, $default_file_row["file_hash"], $default_file_row["extra"]);
				} catch (\cs_DecryptHasFailed) {
					continue;
				}
			}
		}
	}

	/**
	 * Установим параметры
	 *
	 * @return array
	 */
	protected static function _setParams():array {

		console("Введите старый encrypt_iv");
		$old_encrypt_iv = trim(readline());

		console("Введите старый encrypt_key");
		$old_encrypt_key = trim(readline());

		return [
			"old_encrypt_iv"  => $old_encrypt_iv,
			"old_encrypt_key" => $old_encrypt_key,
		];
	}
}

Script_Send_Sms_In_Bot::exec();

// успешное выполнение скрипта
console("DONE");
