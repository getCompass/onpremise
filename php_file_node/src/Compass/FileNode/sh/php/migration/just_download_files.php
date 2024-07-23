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
$save_file_path       = _getFilePath();

require_once __DIR__ . "/../../../../../../start.php";
ini_set("memory_limit", "4096M");
ini_set("display_errors", "1");
set_time_limit(0);

/**
 * Пишем сообщение
 */
class Migration_Just_Download_Files {

	protected const  _REDIRECT_MAX_COUNT               = 10;
	protected const  _MAX_FILE_DOWNLOAD_CONTENT_LENGTH = 1024 * 1024 * 1024;
	protected const  _RAW_FILE_TABLE                   = "raw_file";
	protected const  _FILES_COUNT                      = 1000000;

	protected string $_local_manticore_host;
	protected int    $_local_manticore_port;
	protected string $_save_file_path;

	/**
	 * Грузим файлы к нам
	 */
	public function run(string $local_manticore_host, int $local_manticore_port, string $save_file_path):void {

		$this->_local_manticore_host = $local_manticore_host;
		$this->_local_manticore_port = $local_manticore_port;
		$this->_save_file_path       = $save_file_path;

		// получаем список файлов из raw
		$raw_file_list       = $this->_getFiles();
		$raw_file_list_count = count($raw_file_list);
		console("Всего файлов для импорта: {$raw_file_list_count}");

		$file_url_counter = 0;
		foreach ($raw_file_list as $raw_file) {

			$file_url           = $raw_file["download_link"];
			$file_source        = $raw_file["source_type"];
			$original_file_name = $raw_file["original_name"];

			try {

				if (mb_strlen($original_file_name) < 1) {
					throw new Domain_File_Exception_IncorrectFileName();
				}

				// пробуем скачать файл
				$file_content = $this->_downloadFile($file_url);

				// сохраняем содержимое скачиваемого файла во временный файл
				$file_extension = Type_File_Utils::getExtension($raw_file["original_name"]);
				$tmp_file_path  = Type_File_Utils::generateTmpPath();
				Type_File_Utils::saveContentToFile($tmp_file_path, $file_content);

				if ($file_source === FILE_SOURCE_MESSAGE_VOICE) {

					ffmpeg_exec("-i", $tmp_file_path, "-map", "a", $new_tmp_file_path = Type_File_Utils::generateTmpPath("aac"));
					$original_file_name = pathinfo($original_file_name, PATHINFO_FILENAME) . ".aac";
					unlink($tmp_file_path);
					$tmp_file_path = $new_tmp_file_path;
				}

				$file_path = Type_File_Main::generatePathPart($save_file_path, $tmp_file_path, $file_extension);
				$this->_writeToFile("migration-file-download", "{$raw_file["uniq"]}|||||{$original_file_name}|||||{$file_source}|||||{$file_path}");

				// делаем вывод чтобы было понятно что скрипт что-то делает
				console("Успешно скачали файл: {$file_url}");
			} catch (Domain_File_Exception_IncorrectFileName|cs_DownloadFailed|\cs_CurlError) {

				console(yellowText("Не удалось скачать файл: {$file_url}, uniq: {$raw_file["uniq"]}"));
				Type_System_Admin::log("migration-file-download-error", "uniq: {$raw_file["uniq"]} download_link: {$raw_file["download_link"]}");
			} catch (cs_InvalidFileTypeForSource) {

				console(yellowText("Невалидный file_source $file_source для файла {$file_url}"));
				Type_System_Admin::log("migration-file-download-error", "uniq: {$raw_file["uniq"]} download_link: {$raw_file["download_link"]}");
			} catch (Exception) {

				console(yellowText("Не удалось скачать файл: {$file_url}"));
				Type_System_Admin::log("migration-file-download-error", "uniq: {$raw_file["uniq"]} download_link: {$raw_file["download_link"]}");
			}

			$file_url_counter++;
			console(greenText("Скачано файлов: {$file_url_counter}/{$raw_file_list_count}"));
		}
	}

	/**
	 * Скачивание файла
	 *
	 * @throws \cs_CurlError
	 * @throws cs_DownloadFailed
	 */
	protected function _downloadFile(string $file_url, bool $is_source_trusted = false, int $timeout = 120, string|bool $node_id = false, int $max_file_size = self::_MAX_FILE_DOWNLOAD_CONTENT_LENGTH):string {

		// если файл находится на этой же ноде
		if (NODE_ID == $node_id) {

			$path = parse_url($file_url)["path"];
			$path = strstr($path, FOLDER_FILE_NAME);

			return file_get_contents(PATH_WWW . $path);
		}

		$curl = new \Curl();
		self::_setTimeout($curl, $timeout);

		// получаем файл
		$curl->setOpt(CURLOPT_NOBODY, true);
		$curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
		$curl->setOpt(CURLOPT_MAXREDIRS, self::_REDIRECT_MAX_COUNT);

		$curl->get($file_url);

		if ($curl->getResponseCode() != 200) {
			throw new cs_DownloadFailed();
		}

		// если нам отдали хедер content-length, то на берегу проверяем, что мы можем скачать файл такой длины
		$headers = $curl->getHeaders();
		$headers = array_change_key_case($headers, CASE_LOWER);
		if (!$is_source_trusted && isset($headers["content-length"])) {

			// если нам отдали массив из длины контента
			if (is_array($headers["content-length"])) {
				$headers["content-length"] = array_pop($headers["content-length"]);
			}

			// если размер контента больше 1000mb - выходим
			if ($headers["content-length"] > $max_file_size) {
				throw new cs_DownloadFailed();
			}
		}

		// скачиваем файл
		$file_url = $curl->getEffectiveUrl();
		$curl->setOpt(CURLOPT_NOBODY, false);
		$curl->setOpt(CURLOPT_FOLLOWLOCATION, true);

		// мониторим, сколько скачали, если больше 1000 мб, то завершаем
		// нельзя верить только хедеру content-length. Злоумышленник или кривые руки разраба может им манипулировать. Для этого и добавлена функция прогресса
		$curl->setOpt(CURLOPT_MAXFILESIZE, $max_file_size);
		$curl->setOpt(CURLOPT_PROGRESSFUNCTION, function(int $download_size, int $downloaded, int $max_file_size) {

			return ($downloaded > $max_file_size) ? 1 : 0;
		});

		try {
			$content = $curl->getImage($file_url);
		} catch (\cs_CurlError) {

			// ошибка курла возникнет, если вышли за пределы загружаемого или поймали таймаут
			throw new cs_DownloadFailed();
		}

		if ($curl->getResponseCode() != 200) {
			throw new cs_DownloadFailed();
		}
		return $content;
	}

	/**
	 * Устанавливаем timeout для curl
	 */
	protected static function _setTimeout(\Curl $curl, int $timeout):void {

		$curl->setTimeout($timeout);
	}

	/**
	 * Получаем файлы из бд слака
	 *
	 * @throws ExecutionException
	 */
	protected function _getFiles():array {

		$query = "SELECT * FROM ?t WHERE `id`>?i LIMIT ?i OFFSET ?i OPTION max_matches=?i";
		return $this->_search()->select($query, [self::_RAW_FILE_TABLE, 0, self::_FILES_COUNT, 0, self::_FILES_COUNT]);
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

	/**
	 * Пишем в файл, что скачали и куда
	 */
	protected function _writeToFile(string $log_name, $txt):void {

		// если пришел массив - то превращаем его в строку и аргументы разделяем между собой
		if (is_array($txt)) {
			$txt = formatArgs($txt);
		}

		$txt = "{$txt}\n";
		@file_put_contents(mb_strtolower($log_name) . ".log", $txt, FILE_APPEND);
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
 * Получаем id от которого будут загружены файлы
 */
function _getFilePath():string {

	try {

		$save_file_path = Type_Script_InputParser::getArgumentValue("--save_file_path");
	} catch (\Exception) {

		console("Передайте путь до папки, в которую будут загружены файлы, например: --save_file_path=/files/migration/");
		exit;
	}

	return $save_file_path;
}

(new Migration_Just_Download_Files())->run($local_manticore_host, $local_manticore_port, $save_file_path);