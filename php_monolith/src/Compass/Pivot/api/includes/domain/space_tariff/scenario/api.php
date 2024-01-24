<?php

namespace Compass\Pivot;

/**
 * Сценарии точки входа api для домена тарифного плана пространства.
 */
class Domain_SpaceTariff_Scenario_Api {

	protected const _LENGTH_SPACE_UNIQ = 14;

	/**
	 * Возвращает витрину указанного типа.
	 *
	 * @throws Domain_SpaceTariff_Exception_InvalidShowcaseType
	 * @throws Domain_SpaceTariff_Exception_UnsupportedShowcaseVersion
	 */
	public static function getShowcase(int $user_id, int $space_id, string $type, int $version):Struct_SpaceTariff_Showcase {

		try {
			$space = Domain_Company_Entity_Company::get($space_id);
		} catch (cs_CompanyIncorrectCompanyId|cs_CompanyNotExist) {
			throw new \BaseFrame\Exception\Request\ParamException("company not found");
		}

		if (!Domain_Company_Entity_Company::isAllowedForUserActions($space)) {
			throw new \BaseFrame\Exception\Request\ParamException("company is not available");
		}

		try {
			[$is_admin, $space_occupied_at] = Gateway_Socket_Company::getInfoForPurchase($user_id, $space);
		} catch (Gateway_Socket_Exception_CompanyIsNotServed) {
			throw new \BaseFrame\Exception\Request\ParamException("company not served");
		}

		if (!$is_admin) {
			throw new \BaseFrame\Exception\Request\ParamException("you have no permission to activate goods");
		}

		$space_info = new Struct_SpaceTariff_SpaceInfo($space, $space_occupied_at);
		return Domain_SpaceTariff_Entity_ShowcaseManager::makeShowcase($user_id, $space_info, $type, $version);
	}

	/**
	 * Активирует указанный товар для тарифа пространства.
	 *
	 * @throws Domain_SpaceTariff_Exception_AlterationUnsuccessful
	 * @throws Domain_SpaceTariff_Exception_TimeLimitReached
	 * @throws cs_CompanyIncorrectCompanyId
	 *
	 * @long много try-catch
	 */
	public static function activate(int $user_id, string $goods_id):array {

		// пытаемся получить подходящий тарифный план для изменения
		$activation = Domain_SpaceTariff_Entity_ActivationResolver::resolve($goods_id);

		if ($activation === false || $user_id !== $activation->customer_user_id) {
			throw new \BaseFrame\Exception\Request\ParamException("passed incorrect goods_id");
		}

		try {
			$space = Domain_Company_Entity_Company::get($activation->space_id);
		} catch (cs_CompanyIncorrectCompanyId|cs_CompanyNotExist) {
			throw new \BaseFrame\Exception\Request\ParamException("company not found");
		}

		if (!Domain_Company_Entity_Company::isAllowedForUserActions($space)) {
			throw new \BaseFrame\Exception\Request\ParamException("company is not available");
		}

		try {
			[$is_admin,] = Gateway_Socket_Company::getInfoForPurchase($user_id, $space);
		} catch (Gateway_Socket_Exception_CompanyIsNotServed) {
			throw new \BaseFrame\Exception\Request\ParamException("company not served");
		}

		if (!$is_admin) {
			throw new \BaseFrame\Exception\Request\ParamException("you have no permission to activate goods");
		}

		// устанавливаем метод — просто попытка что-то сделать
		// если появятся промо-акции, то их тут нужно будет вычислять
		$method = \Tariff\Plan\BaseAction::METHOD_DETACHED;

		if ($activation->plan_type === \Tariff\Loader::MEMBER_COUNT_PLAN_KEY) {

			$tariff = Domain_SpaceTariff_Action_AlterMemberCount::run(
				$activation->customer_user_id, $space->company_id,
				$method, $activation->alteration
			);

			$formatted_plan = Apiv2_Format::memberCountPlan($tariff->memberCount(), Domain_Company_Entity_Company::getMemberCount($space->extra));
			return [\Tariff\Loader::MEMBER_COUNT_PLAN_KEY => $formatted_plan];
		}

		throw new \BaseFrame\Exception\Domain\ParseFatalException("resolved unknown tariff plan");
	}

	/**
	 * валидируем ключ лицензии
	 */
	public static function validateLicenseKey(string $license_key, string $space_uniq):void {

		if (mb_strlen($space_uniq) != self::_LENGTH_SPACE_UNIQ) {
			throw new Domain_SpaceTariff_Exception_InvalidSpaceUniq("invalid space uniq");
		}

		// в зависимости от ключа
		switch ($license_key) {

			case "QXPXXX-WSSQWE-DRETOP-LYHPOI":
				throw new Domain_SpaceTariff_Exception_LowerTariffLicenseKey("license key with a lower rate", 90, 50);

			case "7XWNXL-UWERTY-1GFTTW-XTRJKH":
				throw new Domain_SpaceTariff_Exception_UsedLicenseKey("used license key");

			case "SXWXTR-RTYUWE-FGFTTE-1TRJK9":
				throw new Domain_SpaceTariff_Exception_UserNotPermission("user not a space administrator");

			case "ZSAQWE-5GHPDT-SDAKJH-XHG78P": // ок
			case "LSAQWS-EGHPDT-SDAKJH-4HG78U":
			case "JSAQWD-7GHPDT-SDAKJH-THG78M":
			case "QSAQWN-SGHPDT-SDAKJH-IHG78Z":
			case "WSAQWM-MGHPDT-SDAKJH-QHG78N":
				break;

			default:
				throw new Domain_SpaceTariff_Exception_InvalidLicenseKey("invalid license key");
		}
	}

	/**
	 * активируем ключ лицензии
	 */
	public static function activateLicenseKey(int $user_id, string $license_key, string $space_uniq):void {

		if (mb_strlen($space_uniq) != self::_LENGTH_SPACE_UNIQ) {

			Type_Antispam_User::increment($user_id, Type_Antispam_User::ACTIVATE_LICENSE_KEY);
			throw new Domain_SpaceTariff_Exception_InvalidSpaceUniq("invalid space uniq");
		}

		// в зависимости от ключа
		switch ($license_key) {

			case "QXPXXX-WSSQWE-DRETOP-LYHPOI":
			case "LSAQWS-EGHPDT-SDAKJH-4HG78U":

				Type_Antispam_User::increment($user_id, Type_Antispam_User::ACTIVATE_LICENSE_KEY);
				throw new Domain_SpaceTariff_Exception_LowerTariffLicenseKey("license key with a lower rate", 90, 50);

			case "7XWNXL-UWERTY-1GFTTW-XTRJKH":
			case "JSAQWD-7GHPDT-SDAKJH-THG78M":

				Type_Antispam_User::increment($user_id, Type_Antispam_User::ACTIVATE_LICENSE_KEY);
				throw new Domain_SpaceTariff_Exception_UsedLicenseKey("used license key");

			case "SXWXTR-RTYUWE-FGFTTE-1TRJK9":
			case "QSAQWN-SGHPDT-SDAKJH-IHG78Z":

				Type_Antispam_User::increment($user_id, Type_Antispam_User::ACTIVATE_LICENSE_KEY);
				throw new Domain_SpaceTariff_Exception_UserNotPermission("used license key");

			case "ZSAQWE-5GHPDT-SDAKJH-XHG78P": // ок
				break;

			case "WSAQWM-MGHPDT-SDAKJH-QHG78N":
			default:

				Type_Antispam_User::increment($user_id, Type_Antispam_User::ACTIVATE_LICENSE_KEY);
				throw new Domain_SpaceTariff_Exception_InvalidLicenseKey("invalid license key");
		}
	}
}