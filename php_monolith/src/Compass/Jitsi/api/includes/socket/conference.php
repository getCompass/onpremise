<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Для сокет запросов к конференциям
 */
class Socket_Conference extends \BaseFrame\Controller\Socket {

	public const ALLOW_METHODS = [
		"removeAllPermanentConference",
	];

	/**
	 * Удаляем все комнаты
	 *
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \cs_CurlError
	 * @throws \parseException
	 */
	public function removeAllPermanentConference():array {

		$user_id  = $this->post(\Formatter::TYPE_INT, "user_id");
		$space_id = $this->post(\Formatter::TYPE_INT, "space_id");

		Domain_Jitsi_Entity_PermanentConference::removeWhenUserKick($user_id, $space_id);

		return $this->ok();
	}
}
