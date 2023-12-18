<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Методы для работы с приглашениями через веб-сайт on-premise решений.
 */
class Onpremiseweb_Joinlink extends \BaseFrame\Controller\Api {

	public const ECODE_JL_BAD         = 1711001;
	public const ECODE_JL_INACTIVE    = 1711002;
	public const ECODE_JL_EXPIRED     = 1711003;
	public const ECODE_JL_SELF_INVITE = 1711004;
	public const ECODE_JL_TRY_LATER   = 1711005;

	public const ECODE_UJL_ALREADY_ACCEPTED = 1711006;
	public const ECODE_UJL_ACCEPTED_BEFORE  = 1711002;

	public const ECODE_USER_NEED_FULL_REG    = 1708010;
	public const ECODE_USER_DISMISS_PROGRESS = 1708011;

	public const ALLOW_METHODS = [
		"prepare",
		"accept",
	];

	/**
	 * Метод проверки ссылки-приглашения.
	 * @long - try..catch
	 */
	public function prepare():array {

		$raw_join_link = $this->post(\Formatter::TYPE_STRING, "raw_join_link");

		try {

			Type_Antispam_Ip::check(Type_Antispam_Ip::JOIN_LINK_VALIDATE);

			$validation_result = Domain_Link_Scenario_OnPremiseWeb::prepare($this->user_id, $raw_join_link);
		} catch (cs_JoinLinkIsExpired|cs_IncorrectJoinLink|cs_JoinLinkNotFound|Domain_Link_Exception_LinkNotFound|Domain_InviteLink_Exception_InviteCodeNotExist) {

			Type_Antispam_Ip::checkAndIncrementBlock(Type_Antispam_Ip::JOIN_LINK_VALIDATE);
			return $this->error(static::ECODE_JL_BAD, "incorrect link");
		} catch (cs_JoinLinkIsNotActive|cs_JoinLinkIsUsed) {
			return $this->error(static::ECODE_JL_INACTIVE, "inactive link");
		} catch (cs_UserAlreadyInCompany $e) {

			return $this->error(static::ECODE_UJL_ALREADY_ACCEPTED, "already a space member", [
				"company_id"         => $e->getCompanyId(),
				"inviter_user_id"    => $e->getInviterUserId(),
				"inviter_full_name"  => $e->getInviterFullName(),
				"is_post_moderation" => $e->getFlagPostModeration(),
				"role"               => ($e->getEntryOption() === 2 ? "guest" : "member"),
				"was_member"         => $e->getFlagWasMember(),
			]);
		} catch (cs_ExitTaskInProgress) {
			return $this->error(static::ECODE_USER_DISMISS_PROGRESS, "user has not finished exit the company yet");
		} catch (Domain_Company_Exception_IsNotServed) {
			throw new ParamException("space is not served");
		} catch (Domain_InviteLink_Exception_UserAlreadyAcceptInviteLink) {
			throw new CaseException(static::ECODE_UJL_ACCEPTED_BEFORE, "user already accept invite code");
		} catch (Domain_InviteLink_Exception_InviteCodeExpired) {
			throw new CaseException(static::ECODE_JL_EXPIRED, "the link has expired");
		} catch (Domain_Link_Exception_UserNotFinishRegistration) {
			throw new CaseException(static::ECODE_USER_NEED_FULL_REG, "user not finish registration");
		} catch (Domain_InviteLink_Exception_InviteCodeCreatedByMe) {
			throw new CaseException(static::ECODE_JL_SELF_INVITE, "invite code created by me");
		} catch (Domain_Link_Exception_TemporaryUnavailable) {
			throw new CaseException(static::ECODE_JL_TRY_LATER, "can't validate link");
		} catch (cs_UserNotFound) {
			throw new ReturnFatalException("unhandled error");
		} catch (cs_blockException $e) {

			throw new CaseException(423, "prepare linnk limit exceeded", [
				"expires_at" => $e->getNextAttempt(),
			]);
		}

		return $this->ok(["validation_result" => Onpremiseweb_Format::joinLinkInfo($validation_result)]);
	}

	/**
	 * Метод принятия приглашения в пространство по ссылке-приглашению.
	 * @long - try..catch
	 */
	public function accept():array {

		$join_link_uniq = $this->post(\Formatter::TYPE_STRING, "join_link_uniq");

		if ($this->user_id === 0) {
			throw new EndpointAccessDeniedException("need authenticate first");
		}

		try {

			Type_Antispam_Ip::check(Type_Antispam_Ip::JOIN_LINK_VALIDATE);

			Gateway_Bus_CollectorAgent::init()->inc("row63");
			Domain_Link_Scenario_OnPremiseWeb::accept($this->user_id, $join_link_uniq, $this->session_uniq);
		} catch (cs_blockException $e) {

			throw new CaseException(423, "prepare linnk limit exceeded", [
				"expires_at" => $e->getNextAttempt(),
			]);
		} catch (cs_JoinLinkIsExpired|cs_IncorrectJoinLink|cs_JoinLinkNotFound) {
			return $this->error(static::ECODE_JL_BAD, "incorrect link");
		} catch (cs_JoinLinkIsNotActive|cs_JoinLinkIsUsed) {

			Type_Antispam_Ip::checkAndIncrementBlock(Type_Antispam_Ip::JOIN_LINK_VALIDATE);

			Gateway_Bus_CollectorAgent::init()->inc("row65");
			return $this->error(static::ECODE_JL_INACTIVE, "inactive link");
		} catch (cs_Text_IsTooLong) {
			throw new ParamException("User comment is too long");
		} catch (cs_UserAlreadyInCompany $e) {

			return $this->error(static::ECODE_UJL_ALREADY_ACCEPTED, "already a space member", [
				"company_id"      => $e->getCompanyId(),
				"inviter_user_id" => $e->getInviterUserId(),
			]);
		} catch (cs_ExitTaskInProgress) {
			return $this->error(static::ECODE_USER_DISMISS_PROGRESS, "user has not finished exit the company yet");
		} catch (cs_CompanyIncorrectCompanyId) {
			throw new ParamException("invalid space id");
		} catch (Domain_Link_Exception_UserNotFinishRegistration) {
			throw new CaseException(static::ECODE_USER_NEED_FULL_REG, "user not finish registration");
		} catch (Domain_Company_Exception_IsNotServed) {
			throw new ParamException("space is not served");
		} catch (Domain_Link_Exception_TemporaryUnavailable|cs_CompanyIsHibernate) {
			throw new CaseException(static::ECODE_JL_TRY_LATER, "can't validate link");
		} catch (cs_UserNotFound) {
			throw new ReturnFatalException("unhandled error");
		}

		Gateway_Bus_CollectorAgent::init()->inc("row67");
		return $this->ok();
	}
}