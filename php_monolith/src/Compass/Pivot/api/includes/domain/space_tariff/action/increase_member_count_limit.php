<?php

namespace Compass\Pivot;

/**
 * Увеличить лимит участников в пространстве
 */
class Domain_SpaceTariff_Action_IncreaseMemberCountLimit {

	/**
	 * Выполняем
	 *
	 * @throws Domain_SpaceTariff_Exception_TimeLimitReached
	 * @throws cs_CompanyIncorrectCompanyId
	 * @long отладка
	 */
	public static function do(int $user_id, Struct_Db_PivotCompany_Company $space):array {

		$tariff            = Domain_SpaceTariff_Repository_Tariff::get($space->company_id);
		$member_count_plan = $tariff->memberCount();

		$member_count = Domain_Company_Entity_Company::getMemberCount($space->extra) + 1;
		$circumstance = new \Tariff\Plan\MemberCount\Circumstance(
			$member_count,
			getConfig("TARIFF")["member_count"]["postpayment_period"]
		);

		// если участник влезает - отдаем об этом ответ в пространство
		if ($member_count_plan->isFit($circumstance)) {
			return [true, false];
		}

		$was_trial = $member_count_plan->isTrial(time());

		// если триал нельзя активировать, то просто запрещаем добавлять пользователя
		if (!$was_trial && !$member_count_plan->isTrialAvailable(time())) {

			Type_System_Admin::log("increase_member_count_limit_failed", "trial issue");
			return [false, false];
		}

		// запускаем альтерацию
		$alteration = Domain_SpaceTariff_Plan_MemberCount_Product_ActivateTrial::makeAlteration($member_count_plan, $member_count);

		try {

			// пытаемся провернуть альтерацию по увеличению числа участников
			// если не получается, то просто говорим, что нельзя расшириться
			Domain_SpaceTariff_Action_AlterMemberCount::run(
				$user_id, $space->company_id, \Tariff\Plan\BaseAction::METHOD_DETACHED,
				$alteration, reason: Domain_SpaceTariff_Action_AlterMemberCount::REASON_SELF_USER_JOIN
			);
		} catch (Domain_SpaceTariff_Exception_AlterationUnsuccessful $e) {

			// здесь бы еще куда-то информацию отписать
			// почему не удалось выполнить альтерацию
			Type_System_Admin::log("increase_member_count_limit_failed", $e->getMessage());
			return [false, !$was_trial];
		}

		return [true, !$was_trial];
	}
}