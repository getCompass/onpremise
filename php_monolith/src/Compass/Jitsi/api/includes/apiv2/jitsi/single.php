<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;

/**
 * контроллер для методов api/v2/jitsi/single/...
 */
class Apiv2_Jitsi_Single extends \BaseFrame\Controller\Api {

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"create",
		"reject",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * создаем конференцию
	 * @long
	 */
	public function create():array {

		$space_id         = $this->post(\Formatter::TYPE_INT, "space_id");
		$opponent_user_id = $this->post(\Formatter::TYPE_INT, "opponent_user_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::JITSI_CREATE_CONFERENCE);

		try {

			/**
			 * @var Struct_Api_Conference_Data        $conference_data
			 * @var Struct_Api_Conference_JoiningData $conference_joining_data
			 * @var Struct_Api_Conference_MemberData  $conference_member_data
			 */
			[$conference_data, $conference_joining_data, $conference_member_data, $conference_creator_data] = Domain_Jitsi_Scenario_Api::createSingle(
				$this->user_id, $opponent_user_id, $space_id);
		} catch (Domain_Jitsi_Exception_UserActiveConference_UserHaveActiveConference $e) {

			// подготавливаем все для вступления участника в активную конфернцию
			/**
			 * @var Struct_Api_Conference_JoiningData $joining_data
			 * @var Struct_Api_Conference_MemberData  $conference_member_data
			 */
			[$conference_joining_data, $conference_member_data] = Domain_Jitsi_Scenario_Api::prepareJoiningDataForActiveConference(
				$this->user_id, $e->getConference());

			throw new CaseException(1219004, "user have active conference", [
				"conference_data"         => Struct_Api_Conference_Data::buildFromDB($e->getConference())->format(),
				"conference_joining_data" => $conference_joining_data->format(),
				"conference_member_data"  => $conference_member_data->format(),
			]);
		} catch (Domain_Jitsi_Exception_Conference_ConferenceIdDuplication|Domain_Jitsi_Exception_Node_NotFound) {
			throw new ParseFatalException("unexpected behaviour");
		} catch (Domain_Jitsi_Exception_Conference_UsersAreNotMembersOfSpace|Domain_Space_Exception_NotFound|Domain_Space_Exception_UnexpectedStatus) {
			throw new ParamException("unexpected behaviour");
		} catch (Domain_Jitsi_Exception_Conference_NoCreatePermissions $e) {
			throw new CaseException(1219010, $e->getMessage());
		} catch (Domain_Jitsi_Exception_Conference_GuestAccessDenied $e) {
			throw new CaseException(1219011, $e->getMessage());
		} catch (Domain_Jitsi_Exception_UserActiveConference_OpponentUserHaveActiveConference $e) {
			throw new CaseException(1219012, $e->getMessage());
		} catch (Domain_Jitsi_Exception_Conference_IsFinished) {
			throw new CaseException(1219005, "conference is finished");
		}

		return $this->ok([
			"conference_data"         => $conference_data->format(),
			"conference_joining_data" => $conference_joining_data->format(),
			"conference_member_data"  => $conference_member_data->format(),
			"conference_creator_data" => $conference_creator_data->format(),
		]);
	}

	/**
	 * @return array
	 * @throws BlockException
	 * @throws CaseException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \cs_CurlError
	 * @throws \parseException
	 */
	public function reject():array {

		$conference_id = $this->post(\Formatter::TYPE_STRING, "conference_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::JITSI_CREATE_CONFERENCE);

		try {
			Domain_Jitsi_Scenario_Api::rejectSingle($this->user_id, $conference_id);
		} catch (Domain_Jitsi_Exception_ConferenceLink_IncorrectLink|Domain_Jitsi_Exception_Conference_NotFound|Domain_Jitsi_Exception_Conference_WrongPassword|Domain_Jitsi_Exception_Node_NotFound) {
			throw new CaseException(1219003, "conference not found");
		} catch (Domain_Jitsi_Exception_ConferenceMember_IsSpeaking) {
			throw new CaseException(1219015, "conference member is speaking");
		} catch (Domain_Jitsi_Exception_ConferenceMember_NotFound|Domain_jitsi_Exception_ConferenceMember_UnexpectedStatus) {
			throw new CaseException(1219008, "user is not member of conference");
		} catch (Domain_Jitsi_Exception_Node_RequestFailed|Domain_Jitsi_Exception_Conference_UnexpectedStatus|Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId) {
			throw new ParseFatalException("unexpected behaviour");
		} catch (Domain_Jitsi_Exception_Conference_IsFinished) {
			throw new CaseException(1219005, "conference is finished");
		}

		return $this->ok();
	}
}