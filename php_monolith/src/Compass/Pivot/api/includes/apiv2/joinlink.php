<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Контроллер для работы со ссылкой приглашением в компанию
 */
class Apiv2_JoinLink extends \BaseFrame\Controller\Api {

	// поддерживаемые методы, регистр не имеет значение
	public const ALLOW_METHODS = [
		"accept",
	];

	/**
	 * Метод для принятия ссылки-приглашения
	 *
	 * @return array
	 * @throws CaseException
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedPivotException
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingPivotException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \busException
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 * @long
	 */
	public function accept():array {

		$join_link_uniq = $this->post(\Formatter::TYPE_STRING, "join_link_uniq");
		$comment        = $this->post(\Formatter::TYPE_STRING, "comment", "");

		Type_Antispam_User::assertNotBlock($this->user_id, Type_Antispam_User::JOIN_LINK_VALIDATE);

		try {

			Gateway_Bus_CollectorAgent::init()->inc("row63");
			[$user_company, $entry_option] = Domain_Company_Scenario_Api::acceptJoinLink($this->user_id, $join_link_uniq, $comment, $this->session_uniq);
		} catch (cs_JoinLinkIsExpired|cs_IncorrectJoinLink|cs_JoinLinkNotFound|cs_JoinLinkIsNotActive|cs_JoinLinkIsUsed) {

			Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::JOIN_LINK_VALIDATE);
			Gateway_Bus_CollectorAgent::init()->inc("row65");
			return $this->error(1199, "invite-link not active");
		} catch (cs_CompanyNotExist) {
			return $this->error(1102, "Company not found");
		} catch (cs_RowDuplication) {
			throw new ParamException("Passed incorrect params");
		} catch (cs_Text_IsTooLong) {
			throw new ParamException("User comment is too long");
		} catch (cs_UserAlreadyInCompany $e) {

			return $this->error(1203, "member is already in company", [
				"company_id"      => $e->getCompanyId(),
				"inviter_user_id" => $e->getInviterUserId(),
			]);
		} catch (cs_ExitTaskInProgress) {
			return $this->error(1220, "user has not finished exit the company yet");
		} catch (cs_CompanyIncorrectCompanyId) {
			throw new ParamException("invalid company id");
		} catch (Domain_Company_Exception_IsRelocating) {
			throw new \BaseFrame\Exception\Request\CompanyIsRelocatingPivotException("company is relocating");
		} catch (Domain_Company_Exception_IsHibernated) {
			throw new \BaseFrame\Exception\Request\CompanyIsHibernatedPivotException("company is hibernated");
		} catch (Domain_Company_Exception_IsNotServed) {
			throw new \BaseFrame\Exception\Request\CompanyNotServedException("company is not served");
		} catch (Domain_Link_Exception_UserNotFinishRegistration) {
			throw new CaseException(1211001, "user not finish registration");
		}

		Gateway_Bus_CollectorAgent::init()->inc("row67");

		return $this->ok([
			"company"      => (object) $user_company,
			"entry_option" => (int) $entry_option,
		]);
	}
}