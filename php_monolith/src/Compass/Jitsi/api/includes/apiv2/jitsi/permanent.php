<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;

/**
 * контроллер для методов api/v2/jitsi/permanent/...
 */
class Apiv2_Jitsi_Permanent extends \BaseFrame\Controller\Api {

	// Поддерживаемые методы. Регистр не имеет значение
	public const ALLOW_METHODS = [
		"create",
		"getList",
		"remove",
		"change",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * Создаем конференцию
	 *
	 * @throws CaseException
	 * @throws Domain_Jitsi_Exception_PermanentConference_ConferenceIdDuplication
	 * @throws Domain_Space_Exception_NotFound
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws BlockException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public function create():array {

		$space_id                   = $this->post(\Formatter::TYPE_INT, "space_id");
		$is_private                 = $this->post(\Formatter::TYPE_INT, "is_private");
		$is_lobby                   = $this->post(\Formatter::TYPE_INT, "is_lobby");
		$conference_url_custom_name = $this->post(\Formatter::TYPE_STRING, "conference_url_custom_name");
		$description                = $this->post(\Formatter::TYPE_STRING, "description");

		Type_Validator::assertBoolFlag($is_private);
		Type_Validator::assertBoolFlag($is_lobby);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::JITSI_CREATE_PERMANENT_CONFERENCE);

		try {

			$conference_data = Domain_Jitsi_Scenario_Api_Permanent::createPermanent($this->user_id, $space_id, (bool) $is_private, (bool) $is_lobby, $conference_url_custom_name, $description);
		} catch (Domain_Jitsi_Exception_Conference_ConferenceIdDuplication|Domain_Jitsi_Exception_Node_NotFound) {
			throw new ParseFatalException("unexpected behaviour");
		} catch (Domain_Jitsi_Exception_Conference_CreatorIsNotMemberOfSpace|Domain_Space_Exception_UnexpectedStatus) {
			throw new ParamException("unexpected behaviour");
		} catch (Domain_Jitsi_Exception_Conference_NoCreatePermissions $e) {
			throw new CaseException(1219010, $e->getMessage());
		} catch (Domain_Jitsi_Exception_Conference_GuestAccessDenied $e) {
			throw new CaseException(1219011, $e->getMessage());
		} catch (Domain_Jitsi_Exception_PermanentConference_ConferenceExist) {
			throw new CaseException(1219017, "conference already exist");
		} catch (Domain_Jitsi_Exception_PermanentConference_ConferenceLimit) {
			throw new CaseException(1219018, "conference limit reached");
		}

		return $this->ok([
			"conference_data" => $conference_data->format(),
		]);
	}

	/**
	 * Устанавливаем параметры конференции
	 *
	 * @throws BlockException
	 * @throws CaseException
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId
	 * @throws Domain_Jitsi_Exception_Conference_ConferenceIdDuplication
	 * @throws Domain_Jitsi_Exception_Node_RequestFailed
	 * @throws Domain_Jitsi_Exception_PermanentConference_ConferenceIdDuplication
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws RowNotFoundException
	 * @throws \cs_CurlError
	 * @throws \parseException
	 * @throws \queryException
	 */
	public function change():array {

		$conference_id              = $this->post(\Formatter::TYPE_STRING, "conference_id");
		$conference_url_custom_name = $this->post(\Formatter::TYPE_STRING, "conference_url_custom_name");
		$description                = $this->post(\Formatter::TYPE_STRING, "description");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::JITSI_CHANGE_PERMANENT_CONFERENCE);

		try {
			$conference_data = Domain_Jitsi_Scenario_Api_Permanent::change($this->user_id, $conference_id, $conference_url_custom_name, $description);
		} catch (Domain_Jitsi_Exception_ConferenceLink_IncorrectLink
		|Domain_Jitsi_Exception_Conference_NotFound
		|Domain_Jitsi_Exception_Conference_WrongPassword
		|Domain_Jitsi_Exception_Node_NotFound) {
			throw new CaseException(1219003, "conference not found");
		} catch (Domain_Jitsi_Exception_Conference_IsFinished|Domain_Jitsi_Exception_PermanentConference_ConferenceIsDeleted) {
			throw new CaseException(1219005, "conference is finished");
		} catch (Domain_Jitsi_Exception_Conference_AllowedOnlyForCreator|Domain_Jitsi_Exception_ConferenceMember_NotFound|Domain_jitsi_Exception_ConferenceMember_UnexpectedStatus) {
			throw new ParamException("action allowed only for conference creator");
		} catch (Domain_Jitsi_Exception_PermanentConference_ConferenceExist) {
			throw new CaseException(1219017, "conference already exist");
		} catch (Domain_Jitsi_Exception_Conference_UnexpectedType) {
			throw new CaseException(1219019, "trying change field only for permanent conference");
		}

		return $this->ok([
			"conference_data" => $conference_data->format(),
		]);
	}

	/**
	 * Метод получения списка постоянных конференций
	 *
	 * @throws ParamException
	 * @throws ParseFatalException
	 */
	public function getList():array {

		$space_id = $this->post(\Formatter::TYPE_INT, "space_id");

		// получаем массив
		$conference_list = Domain_Jitsi_Scenario_Api_Permanent::getList($this->user_id, $space_id);

		return $this->ok([
			"conference_list" => $conference_list,
		]);
	}

	/**
	 * Удаляем конференцию
	 *
	 * @throws BlockException
	 * @throws CaseException
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId
	 * @throws Domain_Jitsi_Exception_ConferenceMember_NotFound
	 * @throws Domain_Jitsi_Exception_Node_NotFound
	 * @throws Domain_Jitsi_Exception_Node_RequestFailed
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \cs_CurlError
	 * @throws \parseException
	 */
	public function remove():array {

		$conference_id = $this->post(\Formatter::TYPE_STRING, "conference_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::JITSI_REMOVE_PERMANENT_CONFERENCE);

		try {
			Domain_Jitsi_Scenario_Api_Permanent::remove($this->user_id, $conference_id);
		} catch (Domain_Jitsi_Exception_Conference_NotFound) {
			throw new CaseException(1219003, "conference not found");
		} catch (Domain_Jitsi_Exception_Conference_UnexpectedType) {
			throw new CaseException(1219019, "conference not found");
		} catch (Domain_Jitsi_Exception_Conference_AllowedOnlyForCreator) {
			throw new ParamException("action allowed only for conference creator");
		}

		return $this->ok();
	}
}