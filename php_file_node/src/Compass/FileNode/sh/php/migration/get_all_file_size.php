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

require_once __DIR__ . "/../../../../../../start.php";
ini_set("memory_limit", "4096M");
ini_set("display_errors", "1");
set_time_limit(0);

/**
 * Пишем сообщение
 */
class Migration_Get_All_File_Size {

	protected const  _RAW_FILE_TABLE = "raw_file";
	protected const  _FILES_COUNT    = 1000000;

	protected string $_local_manticore_host;
	protected int    $_local_manticore_port;

	/**
	 * Грузим файлы к нам
	 */
	public function run(string $local_manticore_host, int $local_manticore_port):void {

		$this->_local_manticore_host = $local_manticore_host;
		$this->_local_manticore_port = $local_manticore_port;

		// получаем список файлов из raw
		$raw_file_list       = $this->_getFiles();
		$raw_file_list_count = count($raw_file_list);

		$all_file_size = 0;
		foreach ($raw_file_list as $raw_file) {

			$all_file_size += $raw_file["size_byte"];
		}
		console("files_count={$raw_file_list_count} files_size_bytes={$all_file_size}");
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

(new Migration_Get_All_File_Size())->run($local_manticore_host, $local_manticore_port);