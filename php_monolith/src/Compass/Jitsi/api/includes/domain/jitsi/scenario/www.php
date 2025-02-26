<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс описывающий сценарии методов www/jitsi/
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Scenario_Www {

	/**
	 * получаем данные о конференции
	 *
	 * @param string $link
	 *
	 * @return Struct_Api_Conference_Data
	 * @throws Domain_Jitsi_Exception_Conference_NotFound
	 * @throws Domain_Jitsi_Exception_Conference_WrongPassword
	 * @throws Domain_Jitsi_Exception_PermanentConference_ConferenceIsDeleted
	 * @throws ParseFatalException
	 * @throws RowNotFoundException
	 * @throws Domain_Jitsi_Exception_Conference_IsFinished
	 */
	public static function getConferenceData(string $link):Struct_Api_Conference_Data {

		// парсим ссылку
		$parsed_link = Domain_Jitsi_Entity_ConferenceLink_Main::getHandlerProvider()::getByLink($link)::parse($link);

		// верифицируем ссылку на конференцию
		$conference = Domain_Jitsi_Entity_Conference::verifyConferenceLink($parsed_link);

		// проверяем, что конференция не завершена
		Domain_Jitsi_Entity_Conference_Asserts::init($conference)->assertNotFinished();

		// проверяем что постоянная конференция не удалена
		if (Domain_Jitsi_Entity_Conference::isPermanent($conference)) {

			$permanent_conference = Domain_Jitsi_Entity_PermanentConference::getOne($conference->conference_id);
			Domain_Jitsi_Entity_PermanentConference::assertNotDeleted($permanent_conference);
		}

		return Struct_Api_Conference_Data::buildFromDB($conference);
	}

	/**
	 * Присоединяем участника в конференцию, если можно
	 *
	 * @throws Domain_Jitsi_Exception_Conference_NotFound
	 * @throws Domain_Jitsi_Exception_Conference_WrongPassword
	 * @throws Domain_Jitsi_Exception_Node_NotFound
	 * @throws Domain_Jitsi_Exception_Node_RequestFailed
	 * @throws ParseFatalException
	 * @throws RowNotFoundException
	 * @throws \cs_CurlError
	 * @throws \queryException
	 */
	public static function joinConference(string $guest_id, string $link):array {

		// парсим ссылку
		$conference_link_handler = Domain_Jitsi_Entity_ConferenceLink_Main::getHandlerProvider()::getByLink($link);
		$parsed_link             = $conference_link_handler::parse($link);

		// верифицируем ссылку на конференцию
		$conference = Domain_Jitsi_Entity_Conference::verifyConferenceLink($parsed_link);

		// выполняем проверки, что пользователь Compass может вступить в конференцию
		$member_context = Domain_Jitsi_Entity_ConferenceMember_JoiningAsserts::createMemberContextForGuest($guest_id, getIp(), getUa(), false);
		/** @noinspection PhpParamsInspection */
		Domain_Jitsi_Entity_ConferenceMember_JoiningAsserts::check($member_context, $conference, [
			Domain_Jitsi_Entity_ConferenceMember_JoiningAsserts_ConferenceState::class,
			Domain_Jitsi_Entity_ConferenceMember_JoiningAsserts_PrivateConference::class,
		]);

		// запускаем участника в конференцию
		Domain_Jitsi_Entity_ConferenceMember::join($member_context, $conference);

		// создаем jwt токен для авторизованного пользователя
		$jwt_token = Domain_Jitsi_Action_Conference_JoinAsGuest::do($guest_id, $conference);

		// пересоздаем комнату в jitsi для постоянной конференции, если пользователь подключается к конференции первым
		if (Domain_Jitsi_Entity_Conference::isPermanent($conference) && Domain_Jitsi_Entity_Conference::STATUS_NEW == $conference->status) {
			Domain_Jitsi_Action_Conference_RecreateJitsiConference::do($conference);
		}

		// получаем конфиг ноды
		$node_config = Domain_Jitsi_Entity_Node::getConfig($conference->jitsi_instance_domain);

		return [
			$jwt_token,
			$conference_link_handler::prepareJitsiConferenceLink($conference, $node_config, $jwt_token),
			$conference_link_handler::prepareJitsiRequestMediaPermissionsLink($conference),
		];
	}
}