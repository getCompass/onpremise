<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Server\ServerProvider;
use UnhandledMatchError;

/**
 * класс обработчик событий приходящих от jitsi нод
 * @package Compass\Jitsi
 */
class JitsiEvent_Handler {

	/**
	 * обрабатываем события
	 *
	 * @return array
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId
	 * @throws EndpointAccessDeniedException
	 * @throws ParamException
	 * @throws ParseFatalException
	 */
	public static function handle(array $event_data):array {

		self::_assertEnv();

		// проверяем заголовок запроса, что прислан корректный токен
		self::_assertAuthorizationToken();

		// проверяем, что имеются все обязательные поля запроса
		self::_assertEventStructure($event_data);

		// передаем обработку запроса нужному обработчику
		try {
			match ($event_data["event_name"]) {
				"muc-room-created"         => self::_handleRoomCreated($event_data),
				"muc-room-destroyed"       => self::_handleRoomDestroyed($event_data),
				"muc-occupant-joined"      => self::_handleRoomMemberJoined($event_data),
				"muc-occupant-left"        => self::_handleRoomMemberLeft($event_data),
				"moderator-rights-granted" => self::_handleModeratorRightsGranted($event_data),
			};
		} catch (UnhandledMatchError) {

			// ничего не делаем
		}

		// возвращаем ответ
		return ["status" => "ok"];
	}

	/**
	 * проверяем, что на данном окружении доступна работа хендлера
	 *
	 * @throws EndpointAccessDeniedException
	 */
	protected static function _assertEnv():void {

		// на окружении для тестов данный хендлер работать не должен, так как поведение клиента в jitsi не симулируется
		if (ServerProvider::isCi()) {
			throw new EndpointAccessDeniedException("handler is not allowed");
		}
	}

	/**
	 * проверяем, что передан корректный токен для авторизации запроса
	 *
	 * @throws EndpointAccessDeniedException
	 */
	protected static function _assertAuthorizationToken():void {

		$jitsi_domain        = self::_getRequestJitsiDomain();
		$authorization_token = getHeader("HTTP_AUTHORIZATION");

		// получаем конфиг ноды
		try {
			$jitsi_node_config = Domain_Jitsi_Entity_Node::getConfig($jitsi_domain);
		} catch (Domain_Jitsi_Exception_Node_NotFound) {
			throw new EndpointAccessDeniedException("jitsi node not found");
		}

		// сверяемся, что перед корректный токен
		if ($authorization_token !== $jitsi_node_config->event_auth_token) {

			throw new EndpointAccessDeniedException("incorrect token");
		}
	}

	/**
	 * получаем домен jitsi ноды, которая прислала событие
	 *
	 * @return string
	 */
	protected static function _getRequestJitsiDomain():string {

		return getHeader("HTTP_JITSI_DOMAIN");
	}

	/**
	 * проверяем, что передана ожидаемая структура события
	 *
	 * @throws ParamException
	 */
	protected static function _assertEventStructure(array $event_data):void {

		if (!isset($event_data["event_name"])) {
			throw new ParamException("incorrect event structure");
		}
	}

	/**
	 * обрабатываем событие создания конференции
	 *
	 * @throws ParseFatalException
	 * @throws ParamException
	 */
	protected static function _handleRoomCreated(array $event_data):void {

		if (!isset($event_data["room_name"])) {
			throw new ParamException("incorrect event structure");
		}

		$event_data["room_name"] = self::_sanitizeRoomName($event_data["room_name"]);

		// получаем информацию о конференции
		try {
			$conference = Domain_Jitsi_Entity_Conference::get($event_data["room_name"]);
		} catch (Domain_Jitsi_Exception_Conference_NotFound) {
			$conference = null;
		}

		// если не удалось получить информацию о конференции или она завершена, то останавливаем конференцию в самой jitsi
		if (is_null($conference) || $conference->status == Domain_Jitsi_Entity_Conference::STATUS_FINISHED) {

			try {
				Domain_Jitsi_Entity_Node_Request::init(Domain_Jitsi_Entity_Node::getConfig(self::_getRequestJitsiDomain()))->destroyRoom($event_data["room_name"]);
			} catch (Domain_Jitsi_Exception_Node_RequestFailed) {
			}
			return;
		}

		Domain_Jitsi_Scenario_Event::onConferenceStarted($event_data["room_name"]);
	}

	/**
	 * обрабатываем событие завершения конференци
	 *
	 * @throws ParseFatalException
	 * @throws ParamException
	 */
	protected static function _handleRoomDestroyed(array $event_data):void {

		if (!isset($event_data["room_name"])) {
			throw new ParamException("incorrect event structure");
		}

		$event_data["room_name"] = self::_sanitizeRoomName($event_data["room_name"]);

		Domain_Jitsi_Scenario_Event::onConferenceFinished($event_data["room_name"]);
	}

	/**
	 * обрабатываем событие вступления участника в конференцию
	 *
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId
	 * @throws ParamException
	 * @throws ParseFatalException
	 */
	protected static function _handleRoomMemberJoined(array $event_data):void {

		if (!isset($event_data["room_name"], $event_data["occupant"], $event_data["occupant"]["id"])) {
			throw new ParamException("incorrect event structure");
		}

		$event_data["room_name"] = self::_sanitizeRoomName($event_data["room_name"]);

		Domain_Jitsi_Scenario_Event::onConferenceMemberJoined($event_data["room_name"], $event_data["occupant"]["id"]);
	}

	/**
	 * обрабатываем событие покидания конференции участником
	 *
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId
	 * @throws ParamException
	 * @throws ParseFatalException
	 */
	protected static function _handleRoomMemberLeft(array $event_data):void {

		if (!isset($event_data["room_name"], $event_data["occupant"], $event_data["occupant"]["id"])) {
			throw new ParamException("incorrect event structure");
		}

		$event_data["room_name"] = self::_sanitizeRoomName($event_data["room_name"]);
		$is_lost_connection      = self::_checkLostConnectionState($event_data);

		Domain_Jitsi_Scenario_Event::onConferenceMemberLeft($event_data["room_name"], $event_data["occupant"]["id"], $is_lost_connection);
	}

	/**
	 * обрабатываем событие выдачи прав модератора
	 *
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId
	 * @throws ParamException
	 * @throws ParseFatalException
	 */
	protected static function _handleModeratorRightsGranted(array $event_data):void {

		if (!isset($event_data["room_name"], $event_data["occupant"], $event_data["occupant"]["id"])) {
			throw new ParamException("incorrect event structure");
		}

		$event_data["room_name"] = self::_sanitizeRoomName($event_data["room_name"]);

		Domain_Jitsi_Scenario_Event::onConferenceMemberModeratorRightsGranted($event_data["room_name"], $event_data["occupant"]["id"]);
	}

	/**
	 * подготавливаем название комнаты
	 *
	 * @return string
	 */
	protected static function _sanitizeRoomName(string $room_name):string {

		// регулярное выражение для поиска текста в квадратных скобках
		$pattern = "/\[.*?\]/";

		// заменяем найденный текст на пустую строку
		return preg_replace($pattern, "", $room_name);
	}

	/**
	 * Проверяем было ли потеряно соединение
	 */
	protected static function _checkLostConnectionState(array $event_data):bool {

		// проверяем наличие параметра stanza
		if (!isset($event_data["stanza"]) || count($event_data["stanza"]) < 1) {
			return false;
		}

		// логика ниже определяет, что участник потерял соединение
		$stanza = $event_data["stanza"];
		if ($stanza["name"] !== "presence") {
			return false;
		}

		$status_tag = [];
		foreach ($stanza["tags"] as $tag) {

			if ($tag["name"] === "status") {

				$status_tag = $tag;
				break;
			}
		}

		if (!isset($status_tag["name"])) {
			return false;
		}

		if (isset($status_tag["__array"]) && is_array($status_tag["__array"])) {

			$lost_connection_item = array_filter($status_tag["__array"], function(mixed $item) {

				if (!is_string($item)) {
					return false;
				}

				$item = mb_strtolower($item);
				return inHtml($item, "disconnected") && inHtml($item, "client silent");
			});

			return count($lost_connection_item) > 0;
		}

		return false;
	}
}