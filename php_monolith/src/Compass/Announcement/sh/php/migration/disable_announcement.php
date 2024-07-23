<?php

namespace Compass\Announcement;

require_once __DIR__ . "/../../../../../../start.php";

/**
 * Class Sh_Php_Disable_Technical_Works_In_Progress_Company_Announcement
 */
class Sh_Php_Disable_Technical_Works_In_Progress_Company_Announcement {

	/**
	 * Точка входа.
	 */
	public function exec():void {

		// параметры
		$announcement_id = Type_Script_InputParser::getArgumentValue("--announcement_id", Type_Script_InputParser::TYPE_INT);

		Gateway_Db_AnnouncementMain_Announcement::delete($announcement_id);
	}
}

(new Sh_Php_Disable_Technical_Works_In_Progress_Company_Announcement())->exec();