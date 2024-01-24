<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\CaseException;

/**
 * Контроллер для работы со ссылкой приглашением
 */
class Apiv2_Link extends \BaseFrame\Controller\Api {

	// поддерживаемые методы, регистр не имеет значение
	public const ALLOW_METHODS = [
		"validate",
	];

	/**
	 * Метод для валидации ссылки
	 *
	 * @return array
	 * @throws CaseException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedPivotException
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingPivotException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 *
	 * @long много исключений всяких-разных
	 */
	public function validate():array {

		$link = $this->post(\Formatter::TYPE_STRING, "link");

		Type_Antispam_User::assertNotBlock($this->user_id, Type_Antispam_User::JOIN_LINK_VALIDATE);

		try {
			$output = Domain_Link_Scenario_Api::validateLink($this->user_id, $link, $this->session_uniq, $this->method_version);
		} catch (cs_JoinLinkIsExpired|cs_IncorrectJoinLink|cs_JoinLinkNotFound) {

			Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::JOIN_LINK_VALIDATE);
			return $this->error(1199, "incorrect link");
		} catch (cs_JoinLinkIsNotActive|cs_JoinLinkIsUsed) {
			return $this->error(1211002, "inactive link");
		} catch (cs_UserAlreadyInCompany $e) {

			return $this->error(1203, "member is already in company", [
				"company_id"      => $e->getCompanyId(),
				"inviter_user_id" => $e->getInviterUserId(),
			]);
		} catch (cs_CompanyNotExist) {
			return $this->error(1102, "Company not found");
		} catch (Domain_Company_Exception_IsRelocating) {
			throw new \BaseFrame\Exception\Request\CompanyIsRelocatingPivotException("company is relocating");
		} catch (Domain_Company_Exception_IsHibernated) {
			throw new \BaseFrame\Exception\Request\CompanyIsHibernatedPivotException("company is hibernated");
		} catch (Domain_Company_Exception_IsNotServed) {
			throw new \BaseFrame\Exception\Request\CompanyNotServedException("company is not served");
		} catch (Domain_Link_Exception_LinkNotFound|Domain_InviteLink_Exception_InviteCodeNotExist) {
			throw new CaseException(1205001, "can't validate link");
		} catch (Domain_InviteLink_Exception_UserAlreadyAcceptInviteLink) {
			throw new CaseException(1210001, "user already accept invite code");
		} catch (Domain_InviteLink_Exception_InviteCodeExpired) {
			throw new CaseException(1205002, "the link has expired");
		} catch (Domain_Link_Exception_UserNotFinishRegistration) {
			throw new CaseException(1211001, "user not finish registration");
		} catch (Domain_InviteLink_Exception_InviteCodeCreatedByMe) {
			throw new CaseException(1210002, "invite code created by me");
		} catch (Domain_Link_Exception_SupportCompanyTemporarilyUnavailable $e) {

			// ссылка на компанию группу-поддержки недоступна в данный момент
			if (Type_System_Legacy::isSupportCompanyErrorAllowed()) {
				return $this->error(1205005, "support company temporarily unavailable", ["next_attempt_at" => $e->valid_till]);
			}
			return $this->error(1199, "incorrect link");
		}

		return $this->ok($output);
	}
}