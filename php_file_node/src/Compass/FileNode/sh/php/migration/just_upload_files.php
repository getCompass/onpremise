<?php declare(strict_types = 1);

namespace Compass\FileNode;

use BaseFrame\Search\Exception\ExecutionException;
use BaseFrame\Search\Manticore;
use Matrix\Exception;

require_once "/app/src/Compass/FileNode/api/includes/type/script/input_parser.php";
require_once "/app/src/Compass/FileNode/api/includes/type/script/input_helper.php";
require_once "/app/src/Modules/vendor/autoload.php";

\BaseFrame\Domino\DominoHandler::init("domino");
\CompassApp\Company\DomainHandler::init("regex");

$local_manticore_host = _getLocalManticoreHost();
$local_manticore_port = _getLocalManticorePort();
$company_url          = _getCompanyUrl();
$space_id             = _getSpaceId();
$sender_user_id       = _getSenderUserId();
$domino_url           = _getDominoUrl();
$need_work            = _getNeedWork();

putenv("COMPANY_ID=$space_id");
$_SERVER["HTTP_HOST"] = $company_url;

require_once __DIR__ . "/../../../../../../start.php";
ini_set("memory_limit", "4096M");
ini_set("display_errors", "1");
set_time_limit(0);

/**
 * Пишем сообщение
 */
class Migration_Just_Upload_Files {

	protected const  _BOUND_FILE_TABLE     = "bound_file";
	protected const  _MAX_FILE_NAME_LENGTH = 127;
	protected string $_local_manticore_host;
	protected int    $_local_manticore_port;

	/**
	 * Грузим файлы к нам
	 */
	public function run(string $local_manticore_host, int $local_manticore_port, int $space_id, int $sender_user_id, string $domino_url, int $need_work):void {

		$this->_local_manticore_host = $local_manticore_host;
		$this->_local_manticore_port = $local_manticore_port;

		// получаем все файлы
		$raw_file_list       = $this->_getFiles("migration-file-download");
		$raw_file_list_count = count($raw_file_list);

		console("Всего файлов для импорта: {$raw_file_list_count}");

		$file_url_counter = 0;
		foreach ($raw_file_list as $raw_file) {

			$file_source        = (int) $raw_file["source_type"];
			$original_file_name = $raw_file["original_name"];
			$tmp_file_path      = "/app/www" . $raw_file["tmp_file_path"];

			// обрезаем имя файла если необходимо
			$original_file_name = $this->_cutFileName($original_file_name);

			try {

				$file_row = Helper_File::uploadFileByMigration($sender_user_id, $space_id, $domino_url, $file_source, $original_file_name, $tmp_file_path, $need_work);
				$file_map = \CompassApp\Pack\File::tryDecrypt($file_row["file_key"]);
				$this->_insertBoundFiles($raw_file["uniq"], $file_map, $file_row["file_name"], $file_row["file_type"], $file_row["file_hash"]);

				// делаем вывод чтобы было понятно что скрипт что-то делает
				console("Успешно загрузили файл {$file_map}");
			} catch (Domain_File_Exception_IncorrectFileName|cs_DownloadFailed|\cs_CurlError $e) {

				console("Не смогли загрузить файл {$tmp_file_path}");
				Type_System_Admin::log("migration-file-upload-error", "uniq: {$raw_file["uniq"]}");
			} catch (cs_InvalidFileTypeForSource) {

				console("Невалидный file_source $file_source для файла {$tmp_file_path}");
				Type_System_Admin::log("migration-file-upload-error", "uniq: {$raw_file["uniq"]}");
			} catch (Exception $e) {

				console("Не смогли загрузить файл {$tmp_file_path} из-за непредвиденной ошибки");
				Type_System_Admin::log("migration-file-upload-error", "uniq: {$raw_file["uniq"]}");
			}

			$file_url_counter++;
			console("Загрузили файлов {$file_url_counter}/{$raw_file_list_count}");
		}
	}

	/**
	 * Получаем файлы из текстового файла
	 */
	protected function _getFiles(string $log_name):array {

		$output           = [];
		$content          = file_get_contents(mb_strtolower($log_name) . ".log");
		$file_string_list = explode(PHP_EOL, $content);
		foreach ($file_string_list as $file_string) {

			$file_string_key_list = explode("|||||", $file_string);

			if (count($file_string_key_list) < 4) {
				continue;
			}

			$output[] = [
				"uniq"          => $file_string_key_list[0],
				"original_name" => $file_string_key_list[1],
				"source_type"   => $file_string_key_list[2],
				"tmp_file_path" => $file_string_key_list[3],
			];
		}

		return $output;
	}

	/**
	 * Обрезаем имя файла если необходимо
	 */
	protected function _cutFileName(string $file_name_with_extension):string {

		// проверяем лимит длины имени файла
		if (mb_strlen($file_name_with_extension) <= static::_MAX_FILE_NAME_LENGTH) {
			return $file_name_with_extension;
		}

		$file_name      = pathinfo($file_name_with_extension, PATHINFO_FILENAME);
		$file_extension = pathinfo($file_name_with_extension, PATHINFO_EXTENSION);

		// получаем максимальную длину имени файла до которой можем обрезать c точкой вместе
		$file_extension_length = mb_strlen($file_extension);
		$file_name_limit       = static::_MAX_FILE_NAME_LENGTH - $file_extension_length - 1;

		// обрезаем до макс длины
		$cut_file_name = mb_substr($file_name, 0, $file_name_limit);

		// убираем пробел чтобы красиво
		$cut_file_name = rtrim($cut_file_name);

		if (mb_strlen($file_extension) < 1) {
			return $cut_file_name;
		}

		return $cut_file_name . "." . $file_extension;
	}

	/**
	 * Пишем загруженные файлы в бд
	 *
	 * @throws ExecutionException
	 */
	protected function _insertBoundFiles(string $uniq, string $file_map, string $file_name, int $file_type, string $file_uid):void {

		$insert[] = [
			"uniq"      => $uniq,
			"file_map"  => $file_map,
			"file_name" => $file_name,
			"file_type" => $file_type,
			"file_uid"  => $file_uid,
		];

		$this->_search()->insert(static::_BOUND_FILE_TABLE, $insert);
	}

	/**
	 * Розетка для временного поднятого контейнера manticore
	 */
	protected function _search():Manticore {

		$conf = [
			"host" => $this->_local_manticore_host,
			"port" => $this->_local_manticore_port,
		];

		// получаем конфиг с базой данных
		return \BaseFrame\Search\Provider::instance()->connect(new \BaseFrame\Search\Config\Connection(...$conf));
	}
}

/**
 * Получаем хост для manticore
 */
function _getLocalManticoreHost():string {

	try {

		$local_manticore_host = Type_Script_InputParser::getArgumentValue("--local_manticore_host");
	} catch (\Exception) {

		console("Передайте корректный адрес хоста временного manticore контейнера, например: --local_manticore_host='82.148.27.130'");
		exit;
	}

	return $local_manticore_host;
}

/**
 * Получаем порт для manticore
 */
function _getLocalManticorePort():int {

	try {

		$local_manticore_port = Type_Script_InputParser::getArgumentValue("--local_manticore_port", Type_Script_InputParser::TYPE_INT);
	} catch (\Exception) {

		console("Передайте корректный адрес порта временного manticore контейнера, например: --local_manticore_port=9306");
		exit;
	}

	return $local_manticore_port;
}

/**
 * Получаем url компании
 */
function _getCompanyUrl():string {

	try {

		$company_url = Type_Script_InputParser::getArgumentValue("--company_url");
	} catch (\Exception) {

		console("Передайте корректный url компании в которую обращаемся, например: --company_url='c1-bob.nikitak.backend-local.apitest.team'");
		exit;
	}

	return $company_url;
}

/**
 * Получаем url domino
 */
function _getDominoUrl():string {

	try {

		$domino_url = Type_Script_InputParser::getArgumentValue("--domino_url");
	} catch (\Exception) {

		console("Передайте корректный адрес домино с которого грузим файлы, например: --domino_url='https://82.148.27.130:31529/'");
		exit;
	}

	return $domino_url;
}

/**
 * Получаем id пространства
 */
function _getSpaceId():int {

	try {

		$space_id = Type_Script_InputParser::getArgumentValue("--space_id", Type_Script_InputParser::TYPE_INT);
	} catch (\Exception) {

		console("Передайте корректный id пространства, например: --space_id=1");
		exit;
	}

	return $space_id;
}

/**
 * Получаем время когда начнем нарезку файлов
 */
function _getNeedWork():int {

	try {

		$space_id = Type_Script_InputParser::getArgumentValue("--need_work", Type_Script_InputParser::TYPE_INT);
	} catch (\Exception) {

		console("Передайте время в timestamp когда нужно начать нарезку файлов, например: --need_work=1720775372");
		exit;
	}

	return $space_id;
}

/**
 * Получаем id от которого будут загружены файлы
 */
function _getSenderUserId():int {

	try {

		$sender_user_id = Type_Script_InputParser::getArgumentValue("--sender_user_id", Type_Script_InputParser::TYPE_INT);
	} catch (\Exception) {

		console("Передайте корректный id пользователя, от которого будут загружены файлы, например: --sender_user_id=1");
		exit;
	}

	return $sender_user_id;
}

(new Migration_Just_Upload_Files())->run($local_manticore_host, $local_manticore_port, $space_id, $sender_user_id, $domino_url, $need_work);