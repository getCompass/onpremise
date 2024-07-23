<?php declare(strict_types = 1);

namespace Compass\Conversation;

require_once "/app/src/Compass/Conversation/api/includes/type/script/input_parser.php";
require_once "/app/src/Compass/Conversation/api/includes/type/script/input_helper.php";
require_once "/app/src/Modules/vendor/autoload.php";

$company_url = _getCompanyUrl();
$space_id    = _getSpaceId();

putenv("COMPANY_ID=$space_id");
$_SERVER["HTTP_HOST"] = $company_url;

require_once __DIR__ . "/../../../../../../start.php";
set_time_limit(0);
ini_set("memory_limit", "4096M");
ini_set("display_errors", "1");

/**
 * Запускаем задачу реиндексации пространства
 */
class Migration_Start_Full_Reindex {

	protected const  _DB_KEY    = "space_search";
	protected const  _TABLE_KEY = "index_task_queue";

	/**
	 * Запускаем задачу реиндексации пространства
	 */
	public function run():void {

		ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_KEY, [
			"type"        => 1,
			"error_count" => 0,
			"created_at"  => time(),
			"updated_at"  => 0,
			"data"        => [],
		]);
	}
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

(new Migration_Start_Full_Reindex())->run();