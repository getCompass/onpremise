<?php

namespace Compass\Company;

/**
 * Класс получает список активных сотрудников в компании за переданный день
 *
 * Class Domain_Company_Action_GetActiveMembers
 */
class Domain_Company_Action_GetActiveMembers {

	/**
	 * совершаем действие
	 *
	 * @return int[]
	 */
	public static function do(int $year, int $day_num):array {

		// получаем всю статистику за день
		$user_day_stats_list = Gateway_Bus_Company_Rating::getListByDay($year, $day_num);

		// пробегаемся по всем пользователям
		$user_list = [];
		foreach ($user_day_stats_list as $user_day_stats) {

			if (self::_isHaveDayActivity($user_day_stats)) {
				$user_list[] = $user_day_stats->user_id;
			}
		}

		return $user_list;
	}

	/**
	 * имеется ли дневная активность у пользователя
	 */
	protected static function _isHaveDayActivity(Struct_Bus_Rating_UserDayStats $user_day_stats):bool {

		$result = false;
		$result |= $user_day_stats->getCallCount() > 0;
		$result |= $user_day_stats->getGeneralCount() > 0;
		$result |= $user_day_stats->getConversationMessageCount() > 0;
		$result |= $user_day_stats->getThreadMessageCount() > 0;
		$result |= $user_day_stats->getFileCount() > 0;
		$result |= $user_day_stats->getReactionCount() > 0;
		$result |= $user_day_stats->getVoiceCount() > 0;
		$result |= $user_day_stats->getRespectCount() > 0;
		$result |= $user_day_stats->getExactingnessCount() > 0;

		return $result;
	}
}