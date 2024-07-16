<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\CaseException;

/**
 * контроллер для методов www/jitsi/...
 */
class Www_Jitsi extends \BaseFrame\Controller\Www {

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"getConferenceData",
		"joinConference",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * получаем информацию о конференции
	 */
	public function getConferenceData():array {

		$link = $this->post(\Formatter::TYPE_STRING, "link");


		try {
			Type_Antispam_Ip::check(Type_Antispam_Ip::JITSI_INVALID_CONFERENCE_ID);

			$conference_data = Domain_Jitsi_Scenario_Www::getConferenceData($link);
		} catch (Domain_Jitsi_Exception_Conference_NotFound|Domain_Jitsi_Exception_Conference_WrongPassword) {

			// в случае, когда пытаются подобрать ссылку на конференцию – увеличиваем блокировку
			Type_Antispam_Ip::checkAndIncrementBlock(Type_Antispam_Ip::JITSI_INVALID_CONFERENCE_ID);
			return $this->error(1619004, "conference not found");
		} catch (Domain_Jitsi_Exception_ConferenceLink_IncorrectLink|Domain_Jitsi_Exception_Node_NotFound) {
			return $this->error(1619004, "conference not found");
		} catch (Domain_Jitsi_Exception_Conference_IsFinished) {
			return $this->error(1619002, "conference is finished");
		} catch (BlockException $e) {

			throw new CaseException(423, "join conference limit exceeded", [
				"next_attempt" => $e->getExpire(),
			]);
		}

		return $this->ok([
			"conference_data" => $conference_data->format(),
		]);
	}

	/**
	 * присоединяемся гостем к конференции
	 */
	public function joinConference():array {

		$link = $this->post(\Formatter::TYPE_STRING, "link");


		try {

			Type_Antispam_Ip::check(Type_Antispam_Ip::JITSI_INVALID_CONFERENCE_ID);

			[$jwt_token, $jitsi_conference_link, $request_media_permissions_link] = Domain_Jitsi_Scenario_Www::joinConference(Type_Session_GuestId::getGuestId(), $link);
		} catch (Domain_Jitsi_Exception_Conference_NotFound|Domain_Jitsi_Exception_Conference_WrongPassword) {

			// в случае, когда пытаются подобрать ссылку на конференцию – увеличиваем блокировку
			Type_Antispam_Ip::checkAndIncrementBlock(Type_Antispam_Ip::JITSI_INVALID_CONFERENCE_ID);
			return $this->error(1619004, "conference not found");
		} catch (Domain_Jitsi_Exception_ConferenceLink_IncorrectLink|Domain_Jitsi_Exception_Node_NotFound) {
			return $this->error(1619004, "conference not found");
		} catch (Domain_Jitsi_Exception_ConferenceMember_AttemptJoinToPrivateConference) {
			return $this->error(1619001, "attempt join to private conference");
		} catch (Domain_Jitsi_Exception_Conference_IsFinished) {
			return $this->error(1619002, "conference is finished");
		} catch (BlockException $e) {

			throw new CaseException(423, "join conference limit exceeded", [
				"next_attempt" => $e->getExpire(),
			]);
		}

		return $this->ok([
			"jwt_token"                      => (string) $jwt_token,
			"jitsi_conference_link"          => $jitsi_conference_link,
			"request_media_permissions_link" => $request_media_permissions_link,
		]);
	}

}