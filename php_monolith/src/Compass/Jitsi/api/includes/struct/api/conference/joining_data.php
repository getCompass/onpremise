<?php

namespace Compass\Jitsi;

/** структура api-сущности описывающей данные для присоединения к конференции */
class Struct_Api_Conference_JoiningData {

	public function __construct(
		public string $domain,
		public string $subdir,
		public string $room,
		public string $jwt_token,
	) {
	}

	public static function build(Struct_Db_JitsiData_Conference $conference, string $jwt_token):self {

		// получаем конфиг ноды
		$node_config = Domain_Jitsi_Entity_Node::getConfig($conference->jitsi_instance_domain);

		return new self($conference->jitsi_instance_domain, $node_config->subdir, $conference->conference_id, $jwt_token);
	}

	/** форматируем сущность для ответа */
	public function format():array {

		// апгрейдим room, если имеется subdir
		$room = $this->room;
		if (mb_strlen($this->subdir) > 0) {
			$room = sprintf("/%s/%s", trim($this->subdir, "/"), $this->room);
		}

		return [
			"domain"    => (string) $this->domain,
			"room"      => (string) $room,
			"jwt_token" => (string) $this->jwt_token,
		];
	}
}
