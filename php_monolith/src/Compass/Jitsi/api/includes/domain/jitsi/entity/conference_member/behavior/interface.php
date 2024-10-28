<?php

namespace Compass\Jitsi;

/**
 * интерфейс описывающий поведение участников в конференции в зависимости от их типа
 */
interface Domain_Jitsi_Entity_ConferenceMember_Behavior_Interface {

	/**
	 * обработка события о присоединении в конференцию
	 */
	public function onJoinConference(string $conference_id, string $member_id):void;

	/**
	 * обработка события о покидании конференции
	 */
	public function onLeftConference(string $conference_id, string $member_id, bool $is_lost_connections):void;

	/**
	 * обработка события о выдаче прав модератора
	 */
	public function onConferenceModeratorRightsGranted(string $conference_id, string $member_id):void;
}