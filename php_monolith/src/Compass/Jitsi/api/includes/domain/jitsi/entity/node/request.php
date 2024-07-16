<?php

namespace Compass\Jitsi;

/**
 * класс для отправки rest api запросов к jitsi ноде
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Entity_Node_Request {

	/** существующие действия при изменении комнаты */
	protected const _PATCH_ACTION_ENABLE_ROOM_LOBBY  = "enable_room_lobby";
	protected const _PATCH_ACTION_DISABLE_ROOM_LOBBY = "disable_room_lobby";
	protected const _PATCH_ACTION_KICK_MEMBER        = "kick_member";

	protected function __construct(
		protected Struct_Jitsi_Node_Config $_node_config,
	) {
	}

	/** инициализируем объект */
	public static function init(Struct_Jitsi_Node_Config $node_config):self {

		return new self($node_config);
	}

	/**
	 * создаем комнату
	 *
	 * @throws Domain_Jitsi_Exception_Node_RequestFailed
	 * @throws \cs_CurlError
	 */
	public function createRoom(string $room, bool $lobby_enabled, string $lobby_password):void {

		// получаем url, куда будем стучаться
		$url = $this->_prepareUrl();

		// апгрейдим название комнаты, если надо
		$room = $this->_upgradeRoomNameIfNeeded($room);

		// параметры запроса
		$params = [
			"room"           => $room,
			"lobby_enabled"  => $lobby_enabled,
			"lobby_password" => $lobby_password,
		];
		$url    .= "?" . http_build_query($params);

		$curl     = new \Curl();
		$response = $curl->put($url, headers: $this->_prepareHeaders());

		if ($curl->getResponseCode() !== 200) {
			throw new Domain_Jitsi_Exception_Node_RequestFailed($curl->getResponseCode(), $response, $url);
		}
	}

	/**
	 * включаем лобби в комнате
	 *
	 * @throws Domain_Jitsi_Exception_Node_RequestFailed
	 * @throws \cs_CurlError
	 */
	public function enableRoomLobby(string $room, string $lobby_password):void {

		$this->_patchRoom($room, [
			"action"         => self::_PATCH_ACTION_ENABLE_ROOM_LOBBY,
			"lobby_password" => $lobby_password,
		]);
	}

	/**
	 * выключаем лобби в комнате
	 *
	 * @throws Domain_Jitsi_Exception_Node_RequestFailed
	 * @throws \cs_CurlError
	 */
	public function disableRoomLobby(string $room):void {

		$this->_patchRoom($room, [
			"action" => self::_PATCH_ACTION_DISABLE_ROOM_LOBBY,
		]);
	}

	/**
	 * изменяем параметры комнаты
	 *
	 * @throws Domain_Jitsi_Exception_Node_RequestFailed
	 * @throws \cs_CurlError
	 */
	protected function _patchRoom(string $room, array $parameters):void {

		// апгрейдим название комнаты, если надо
		$room = $this->_upgradeRoomNameIfNeeded($room);

		// получаем url, куда будем стучаться
		$url = $this->_prepareUrl();

		// параметры запроса
		$params = [
			"room" => $room,
		];
		$params = array_merge($params, $parameters);
		$url    .= "?" . http_build_query($params);

		$curl     = new \Curl();
		$response = $curl->patch($url, headers: $this->_prepareHeaders());

		if ($curl->getResponseCode() !== 200) {
			throw new Domain_Jitsi_Exception_Node_RequestFailed($curl->getResponseCode(), $response, $url);
		}
	}

	/**
	 * удаляем комнату
	 *
	 * @throws Domain_Jitsi_Exception_Node_RequestFailed
	 * @throws \cs_CurlError
	 */
	public function destroyRoom(string $room):void {

		// апгрейдим название комнаты, если надо
		$room = $this->_upgradeRoomNameIfNeeded($room);

		// получаем url, куда будем стучаться
		$url = $this->_prepareUrl();

		// параметры запроса
		$params = [
			"room" => $room,
		];
		$url    .= "?" . http_build_query($params);

		$curl     = new \Curl();
		$response = $curl->delete($url, $this->_prepareHeaders());

		if ($curl->getResponseCode() !== 200) {
			throw new Domain_Jitsi_Exception_Node_RequestFailed($curl->getResponseCode(), $response, $url);
		}
	}

	/**
	 * исключаем участника из комнаты
	 *
	 * @throws Domain_Jitsi_Exception_Node_RequestFailed
	 * @throws \cs_CurlError
	 */
	public function kickMember(string $room, string $member_id):void {

		$this->_patchRoom($room, [
			"action"    => self::_PATCH_ACTION_KICK_MEMBER,
			"member_id" => $member_id,
		]);
	}

	/**
	 * подготавливаем url к jitsi ноде
	 *
	 * @return string
	 */
	protected function _prepareUrl():string {

		return sprintf("https://%s/api/room", $this->_node_config->domain);
	}

	/**
	 * подготавливаем заголовки
	 *
	 * @return array
	 */
	protected function _prepareHeaders():array {

		return [
			"authorization" => $this->_node_config->rest_api_auth_token,
		];
	}

	/**
	 * апгрейдим название комнаты, если требуется
	 *
	 * @return string
	 */
	protected function _upgradeRoomNameIfNeeded(string $room_name):string {

		// если у ноды имеется сабдир
		if (mb_strlen($this->_node_config->subdir) > 0) {

			$prefix    = sprintf("[%s]", preg_replace("/[^a-zA-Z]/", "", $this->_node_config->subdir));
			$room_name = $prefix . $room_name;
		}

		return $room_name;
	}
}