<?php

namespace Compass\Thread;

use CompassApp\Domain\Member\Entity\Extra;
use CompassApp\Domain\Member\Entity\Member;

/**
 * Класс для работы с упомянутыми в сообщении
 *
 * !!! - дублируется в php_conversation
 */
class Domain_Thread_Entity_MentionUsers {

	// паттерн для поиска бейджа, только если в структуре user_id = 0
	protected const _MENTION_BY_BADGE_PATTERN = "/\[\"@\"\|0\|\"(.*)\"]/mU";

	// паттерн для поиска user_id в структуре упоминания
	protected const _MENTION_BY_USER_ID_PATTERN = "/\[\"@\"\|(\d{1,20})\|\".*\"]/mU";

	protected const _MENTION_ALL_USERS = "all";

	// список вариантов переводов упоминания для гостя
	// (клиент бадж гостя у себя самостоятельно локализует в зависимости от языка)
	protected const _MENTION_GUEST_ROLE_LOCALE_LIST = [
		"guest",
		"гость",
	];

	/**
	 * Получить список упомянутых в тексте.
	 */
	public static function getList(string $text, array $users):array {

		preg_match_all(self::_MENTION_BY_BADGE_PATTERN, $text, $matches);

		// если никого не нашли - отдаём пустоту
		if (!isset($matches[1]) || count($matches[1]) < 1) {
			return [];
		}

		// если нужно призвать всех участников чата
		if (in_array(self::_MENTION_ALL_USERS, $matches[1])) {
			return array_keys($users);
		}

		// иначе считаем, что упоминание по баджу - получаем инфу по участникам чата
		$member_list = Gateway_Bus_CompanyCache::getMemberList(array_keys($users));

		$mention_user_id_list = [];
		foreach ($matches[1] as $mention_text) {

			$badge_text = mb_strtolower($mention_text);

			// ищем совпадение по баджу среди участников чата
			foreach ($member_list as $member) {

				// если упоминание из текста для гостя, то получаем участников с ролью гостя
				if (in_array($badge_text, self::_MENTION_GUEST_ROLE_LOCALE_LIST) && $member->role == Member::ROLE_GUEST) {

					$mention_user_id_list[] = $member->user_id;
					continue;
				}

				// если упоминание совпадает с бейджем пользователя
				if ($badge_text == mb_strtolower(Extra::getBadgeContent($member->extra))) {
					$mention_user_id_list[] = $member->user_id;
				}
			}
		}

		return $mention_user_id_list;
	}

	/**
	 * В тексте имеется упоминание по бейджу?
	 */
	public static function isMentionByBadge(string $text):bool {

		preg_match_all(self::_MENTION_BY_BADGE_PATTERN, $text, $matches);

		return isset($matches[1]) & count($matches[1]) > 0;
	}

	/**
	 * Собрать user_id из упоминания
	 */
	public static function getMentionUsersForText(string $text):array {

		preg_match_all(self::_MENTION_BY_USER_ID_PATTERN, $text, $matches);

		if (!isset($matches[1]) || count($matches[1]) < 1) {
			return [];
		}

		return $matches[1];
	}
}