<?php

namespace Compass\Jitsi;

/**
 * класс для работы с jwt токеном для аутентификации участника в конференции
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Entity_Authentication_Jwt {

	/** @var int продолжительность жизни jwt токена – спустя какое время токен становится протухшим */
	protected const _JWT_LIFE_TIME = 60 * 30;

	/**
	 * создаем токен
	 *
	 * @return string
	 * @long большая структура
	 */
	public static function create(Struct_Db_JitsiData_Conference $conference, Struct_Jitsi_Authentication_Jwt_UserContext $user_context, bool $grant_moderator_role, Struct_Jitsi_Node_Config $node_config):string {

		$header  = [
			"alg" => "HS256",
			"typ" => "JWT",
		];
		$payload = [
			"context"   => [
				"user" => [
					"name"               => $user_context->name,
					"id"                 => $user_context->id,
					"avatar"             => $user_context->avatar,
					"type"               => $user_context->type,
					"jitsi_frontend_url" => Domain_Jitsi_Entity_ConferenceLink_Main::getHandlerProvider()::getByConference($conference)::prepareLandingConferenceLink($conference),
					"is_moderator"       => $grant_moderator_role,
					// должен ли участник при вступлении обходить зал ожидания
					"lobby_bypass"       => $grant_moderator_role,
					// включено ли лобби в комнате
					"is_lobby_enabled"   => $conference->is_lobby,
				],
			],
			"iss"       => $node_config->jwt_issuer,
			"aud"       => $node_config->jwt_audience,
			"sub"       => $node_config->domain,
			"room"      => $conference->conference_id,
			"nbf"       => time(),
			"exp"       => time() + self::_JWT_LIFE_TIME,
			"moderator" => $grant_moderator_role,
		];
		return \Jwt::generate($node_config->jwt_secret, $payload, $header);
	}
}