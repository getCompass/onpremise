<?php

namespace Compass\Jitsi;

use BaseFrame\Server\ServerProvider;

/**
 * Класс для работы с аналитикой действий пользователей
 */
class Type_Analytics_ConferenceEvent
{
	protected const _EVENT_KEY = "conference_event";

	/** список всех логируемых действий */
	public const EVENT_CONFERENCE_CREATED = "conference_created"; // пользователь создал конференцию

	public const EVENT_CONFERENCE_USER_JOIN_TOKEN_GENERATED     = "conference_user_join_token_generated"; // пользователь получил токен подключения к вкс
	public const EVENT_CONFERENCE_STARTED                       = "conference_started"; // конференция началась
	public const EVENT_CONFERENCE_USER_JOINED                   = "conference_user_joined"; // пользователь подключился к конференции
	public const EVENT_CONFERENCE_USER_MODERATOR_RIGHTS_GRANTED = "conference_user_moderator_rights_granted"; // пользователь получил права модератора
	public const EVENT_CONFERENCE_USER_LEFT                     = "conference_user_left"; // пользователь покинул конференцию
	public const EVENT_CONFERENCE_ENDED                         = "conference_ended"; // конференция завершилась

	/**
	 * Пишем аналитику по действиям пользователя
	 */
	public static function send(
		string $event,
		string $conference_id,
		int $creator_user_id,
		int $space_id,
		string $member_id,
		string $user_agent,
		string $ipv4,
		string $conference_link,
		string $conference_type,
		string | false $member_name = false,
		string $meeting_id = ""
	): void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		try {
			$user_id            = Domain_Jitsi_Entity_ConferenceMember_MemberId::resolveId($member_id);
			$member_type        = Domain_Jitsi_Entity_ConferenceMember_MemberId::resolveMemberType($member_id);
			$string_member_type = Domain_Jitsi_Entity_ConferenceMember_MemberId::getStringifyMemberType($member_type);
		} catch (Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId) {

			$user_id            = 0;
			$member_type        = 0;
			$string_member_type = "unknown";
		}

		// получаем информацию о пользователе
		$full_name = $member_name === false ? "" : $member_name;
		try {

			if ($member_type == Domain_Jitsi_Entity_ConferenceMember_Type::COMPASS_USER) {

				$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);
				$full_name = $user_info->full_name;
			}
		} catch (\Exception) {
			// не ломаем аналитику если не смогли получить
		}

		Gateway_Bus_CollectorAgent::init()->log(self::_EVENT_KEY, [
			"conference_id"        => $conference_id,
			"creator_user_id"      => $creator_user_id,
			"space_id"             => $space_id,
			"member_id"            => $member_id,
			"member_type"          => $string_member_type,
			"full_name"            => $full_name,
			"event"                => $event,
			"user_agent"           => $user_agent,
			"ipv4"                 => $ipv4,
			"conference_link_hash" => mb_strlen($conference_link) > 0 ? sha1($conference_link) : "",
			"conference_type"      => $conference_type,
			"meeting_id"           => $meeting_id,
		]);
	}
}
