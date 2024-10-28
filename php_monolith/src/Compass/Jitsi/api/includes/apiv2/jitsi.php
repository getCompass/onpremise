<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;

/**
 * контроллер для методов api/v2/jitsi/...
 */
class Apiv2_Jitsi extends \BaseFrame\Controller\Api {

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"createConference",
		"joinConference",
		"getActiveConference",
		"setConferenceOptions",
		"finishConference",
		"leaveActiveConference",
		"getPermanentList",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * создаем конференцию
	 * @long
	 */
	public function createConference():array {

		$space_id   = $this->post(\Formatter::TYPE_INT, "space_id");
		$is_private = $this->post(\Formatter::TYPE_INT, "is_private");
		$is_lobby   = $this->post(\Formatter::TYPE_INT, "is_lobby");

		Type_Validator::assertBoolFlag($is_private);
		Type_Validator::assertBoolFlag($is_lobby);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::JITSI_CREATE_CONFERENCE);

		try {

			/**
			 * @var Struct_Api_Conference_Data        $conference_data
			 * @var Struct_Api_Conference_JoiningData $conference_joining_data
			 * @var Struct_Api_Conference_MemberData  $conference_member_data
			 * @var Struct_Api_Conference_CreatorData $conference_creator_data
			 */
			[$conference_data, $conference_joining_data, $conference_member_data, $conference_creator_data] = Domain_Jitsi_Scenario_Api
				::createConference($this->user_id, $space_id, boolval($is_private), boolval($is_lobby));
		} catch (Domain_Jitsi_Exception_UserActiveConference_UserHaveActiveConference $e) {

			// подготавливаем все для вступления участника в активную конфернцию
			/**
			 * @var Struct_Api_Conference_JoiningData $joining_data
			 * @var Struct_Api_Conference_MemberData  $conference_member_data
			 * @var Struct_Api_Conference_CreatorData $conference_creator_data
			 */
			[$conference_joining_data, $conference_member_data, $conference_creator_data] =
				Domain_Jitsi_Scenario_Api::prepareJoiningDataForActiveConference($this->user_id, $e->getConference());

			throw new CaseException(1219004, "user have active conference", [
				"conference_data"         => Struct_Api_Conference_Data::buildFromDB($e->getConference())->format(),
				"conference_joining_data" => $conference_joining_data->format(),
				"conference_member_data"  => $conference_member_data->format(),
				"conference_creator_data" => $conference_creator_data->format(),
			]);
		} catch (Domain_Jitsi_Exception_Conference_ConferenceIdDuplication|Domain_Jitsi_Exception_Node_NotFound) {
			throw new ParseFatalException("unexpected behaviour");
		} catch (Domain_Jitsi_Exception_Conference_CreatorIsNotMemberOfSpace|Domain_Space_Exception_NotFound|Domain_Space_Exception_UnexpectedStatus) {
			throw new ParamException("unexpected behaviour");
		} catch (Domain_Jitsi_Exception_Conference_NoCreatePermissions $e) {
			throw new CaseException(1219010, $e->getMessage());
		} catch (Domain_Jitsi_Exception_Conference_GuestAccessDenied $e) {
			throw new CaseException(1219011, $e->getMessage());
		}

		return $this->ok([
			"conference_data"         => $conference_data->format(),
			"conference_joining_data" => $conference_joining_data->format(),
			"conference_member_data"  => $conference_member_data->format(),
			"conference_creator_data" => $conference_creator_data->format(),
		]);
	}

	/**
	 * присоединяемся к конференции
	 * @long
	 */
	public function joinConference():array {

		$link     = $this->post(\Formatter::TYPE_STRING, "link");
		$space_id = $this->post(\Formatter::TYPE_INT, "space_id", 0);

		try {

			Type_Antispam_Ip::check(Type_Antispam_Ip::JITSI_INVALID_CONFERENCE_ID);

			/**
			 * @var Struct_Api_Conference_Data        $conference_data
			 * @var Struct_Api_Conference_JoiningData $conference_joining_data
			 * @var Struct_Api_Conference_MemberData  $conference_member_data
			 * @var Struct_Api_Conference_CreatorData $conference_creator_data
			 */
			[$conference_data, $conference_joining_data, $conference_member_data, $conference_creator_data] =
				Domain_Jitsi_Scenario_Api::joinConference($this->user_id, $space_id, $link);
		} catch (Domain_Jitsi_Exception_UserActiveConference_UserHaveActiveConference $e) {

			// подготавливаем все для вступления участника в активную конфернцию
			/**
			 * @var Struct_Api_Conference_JoiningData $joining_data
			 * @var Struct_Api_Conference_MemberData  $conference_member_data
			 * @var Struct_Api_Conference_CreatorData $conference_creator_data
			 */
			[$conference_joining_data, $conference_member_data, $conference_creator_data] = Domain_Jitsi_Scenario_Api::prepareJoiningDataForActiveConference($this->user_id, $e->getConference());

			throw new CaseException(1219004, "user have active conference", [
				"conference_data"         => Struct_Api_Conference_Data::buildFromDB($e->getConference())->format(),
				"conference_joining_data" => $conference_joining_data->format(),
				"conference_member_data"  => $conference_member_data->format(),
				"conference_creator_data" => $conference_creator_data->format(),
			]);
		} catch (Domain_Jitsi_Exception_ConferenceLink_IncorrectLink|Domain_Jitsi_Exception_Conference_NotFound|Domain_Jitsi_Exception_Conference_WrongPassword|Domain_Jitsi_Exception_Node_NotFound) {

			// в случае, когда пытаются подобрать ссылку на конференцию – увеличиваем блокировку
			Type_Antispam_Ip::checkAndIncrementBlock(Type_Antispam_Ip::JITSI_INVALID_CONFERENCE_ID);
			throw new CaseException(1219003, "conference not found");
		} catch (Domain_Jitsi_Exception_ConferenceMember_AttemptJoinToPrivateConference) {
			throw new CaseException(1219001, "attempt join to private conference");
		} catch (Domain_Jitsi_Exception_Conference_IsFinished|Domain_Jitsi_Exception_PermanentConference_ConferenceIsDeleted) {
			throw new CaseException(1219005, "conference is finished");
		} catch (BlockException $e) {

			throw new CaseException(423, "join conference limit exceeded", [
				"next_attempt" => $e->getExpire(),
			]);
		}

		return $this->ok([
			"conference_data"         => $conference_data->format(),
			"conference_joining_data" => $conference_joining_data->format(),
			"conference_member_data"  => $conference_member_data->format(),
			"conference_creator_data" => $conference_creator_data->format(),
		]);
	}

	/**
	 * получаем информацию об активной конференции
	 */
	public function getActiveConference():array {

		try {

			/**
			 * @var Struct_Api_Conference_Data        $conference_data
			 * @var Struct_Api_Conference_JoiningData $joining_data
			 * @var Struct_Api_Conference_MemberData  $conference_member_data
			 * @var Struct_Api_Conference_CreatorData $conference_creator_data
			 */
			[$conference_data, $joining_data, $conference_member_data, $conference_creator_data, $conference_active_at]
				= Domain_Jitsi_Scenario_Api::getActiveConference($this->user_id);
		} catch (Domain_Jitsi_Exception_UserActiveConference_NotFound|
		Domain_Jitsi_Exception_Conference_IsFinished|
		Domain_Jitsi_Exception_ConferenceMember_AttemptJoinToPrivateConference|
		Domain_Jitsi_Exception_ConferenceLink_IncorrectLink|
		Domain_Jitsi_Exception_Conference_NotFound|
		Domain_Jitsi_Exception_Conference_WrongPassword|
		Domain_Jitsi_Exception_Node_NotFound) {
			throw new CaseException(1219006, "no active conference");
		}

		return $this->ok([
			"conference_data"         => $conference_data->format(),
			"conference_joining_data" => $joining_data->format(),
			"conference_member_data"  => $conference_member_data->format(),
			"conference_creator_data" => $conference_creator_data->format(),
			"conference_active_at"    => (int) $conference_active_at,
		]);
	}

	/**
	 * устанавливаем параметры конференции
	 */
	public function setConferenceOptions():array {

		$conference_id = $this->post(\Formatter::TYPE_STRING, "conference_id");
		$is_private    = $this->post(\Formatter::TYPE_INT, "is_private");
		$is_lobby      = $this->post(\Formatter::TYPE_INT, "is_lobby");

		Type_Validator::assertBoolFlag($is_private);
		Type_Validator::assertBoolFlag($is_lobby);

		try {
			$conference_data = Domain_Jitsi_Scenario_Api::setConferenceOptions($this->user_id, $conference_id, boolval($is_private), boolval($is_lobby));
		} catch (Domain_Jitsi_Exception_ConferenceLink_IncorrectLink
		|Domain_Jitsi_Exception_Conference_NotFound
		|Domain_Jitsi_Exception_Conference_WrongPassword
		|Domain_Jitsi_Exception_Node_NotFound) {
			throw new CaseException(1219003, "conference not found");
		} catch (Domain_Jitsi_Exception_Conference_IsFinished) {
			throw new CaseException(1219005, "conference is finished");
		} catch (Domain_Jitsi_Exception_ConferenceMember_NoModeratorRights) {
			throw new CaseException(1219007, "member have not moderator rights");
		} catch (Domain_Jitsi_Exception_ConferenceMember_NotFound|Domain_jitsi_Exception_ConferenceMember_UnexpectedStatus) {
			throw new CaseException(1219008, "user is not member of conference");
		}

		return $this->ok([
			"conference_data" => $conference_data->format(),
		]);
	}

	/**
	 * завершаем конференцию
	 */
	public function finishConference():array {

		$conference_id = $this->post(\Formatter::TYPE_STRING, "conference_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::JITSI_FINISH_CONFERENCE);

		try {

			Domain_Jitsi_Scenario_Api::finishConference($this->user_id, $conference_id);
		} catch (Domain_Jitsi_Exception_ConferenceLink_IncorrectLink|Domain_Jitsi_Exception_Conference_NotFound|Domain_Jitsi_Exception_Conference_WrongPassword|Domain_Jitsi_Exception_Node_NotFound) {
			throw new CaseException(1219003, "conference not found");
		} catch (Domain_Jitsi_Exception_Conference_IsFinished) {
			throw new CaseException(1219005, "conference is finished");
		} catch (Domain_Jitsi_Exception_ConferenceMember_NoModeratorRights) {
			throw new CaseException(1219007, "member have not moderator rights");
		} catch (Domain_Jitsi_Exception_ConferenceMember_NotFound|Domain_jitsi_Exception_ConferenceMember_UnexpectedStatus) {
			throw new CaseException(1219008, "user is not member of conference");
		}

		return $this->ok();
	}

	/**
	 * Метод для покидания активной конференции
	 *
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId
	 * @throws Domain_Jitsi_Exception_Conference_NotFound
	 * @throws Domain_Jitsi_Exception_Node_NotFound
	 * @throws ParseFatalException
	 * @throws \cs_CurlError
	 */
	public function leaveActiveConference():array {

		Domain_Jitsi_Scenario_Api::leaveActiveConference($this->user_id);

		return $this->ok();
	}

}