<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\CaseException;

/**
 * Контроллер управления тарифными планами.
 */
class Apiv2_Space_Tariff extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"activate",
		"getShowcase",
		"validateLicenseKey",
		"activateLicenseKey",
	];

	/**
	 * Активирует тарифный план по переданному goods_id.
	 */
	public function activate():array {

		$goods_id = $this->post(\Formatter::TYPE_STRING, "goods_id");

		try {

			$current_tariff = Domain_SpaceTariff_Scenario_Api::activate($this->user_id, $goods_id);
		} catch (Domain_SpaceTariff_Exception_AlterationUnsuccessful $e) {

			// если ошибка известна как ошибка-апи
			// то возвращаем для клиентов кастомный код
			if ($e->getKnowApiError() !== 0) {
				return $this->error($e->getKnowApiError(), $e->getMessage());
			}

			// если ошибка неизвестная, то бросаем неопределенное исключение
			throw new \BaseFrame\Exception\Request\ParamException($e->getMessage());
		} catch (Domain_SpaceTariff_Exception_TimeLimitReached|cs_CompanyIncorrectCompanyId $e) {
			throw new \BaseFrame\Exception\Request\ParamException($e->getMessage());
		}

		return $this->ok([
			"tariff" => [
				"plan_info" => $current_tariff,
			],
		]);
	}

	/**
	 * Вернуть витрину товаров
	 */
	public function getShowcase():array {

		$type     = $this->post(\Formatter::TYPE_STRING, "type");
		$space_id = $this->post(\Formatter::TYPE_INT, "space_id");
		$version  = $this->post(\Formatter::TYPE_INT, "version", Domain_SpaceTariff_Plan_MemberCount_Showcase::SHOWCASE_VERSION_1);

		try {
			$showcase = Domain_SpaceTariff_Scenario_Api::getShowcase($this->user_id, $space_id, $type, $version);
		} catch (Domain_SpaceTariff_Exception_InvalidShowcaseType|Domain_SpaceTariff_Exception_UnsupportedShowcaseVersion $e) {
			throw new \BaseFrame\Exception\Request\ParamException("passed incorrect showcase: {$e->getMessage()}");
		}

		return $this->ok([
			"showcase" => (object) $showcase,
		]);
	}

	/**
	 * валидируем ключ лицензии
	 */
	public function validateLicenseKey():array {

		$license_key = $this->post(\Formatter::TYPE_STRING, "license_key");
		$space_uniq  = $this->post(\Formatter::TYPE_STRING, "space_uniq");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::VALIDATE_LICENSE_KEY);

		try {
			Domain_SpaceTariff_Scenario_Api::validateLicenseKey($license_key, $space_uniq);
		} catch (Domain_SpaceTariff_Exception_InvalidLicenseKey) {
			throw new CaseException(1214001, "invalid license key");
		} catch (Domain_SpaceTariff_Exception_LowerTariffLicenseKey $e) {
			throw new CaseException(1214002, "license key with a lower rate", [
				"member_count"             => $e->getMemberCount(),
				"license_key_member_count" => $e->getLicenseKeyMemberCount(),
			]);
		} catch (Domain_SpaceTariff_Exception_UsedLicenseKey) {
			throw new CaseException(1214003, "license key already used");
		} catch (Domain_SpaceTariff_Exception_InvalidSpaceUniq) {
			throw new CaseException(1214006, "invalid space uniq");
		} catch (Domain_SpaceTariff_Exception_UserNotPermission) {
			throw new CaseException(1214007, "not a space administrator");
		}

		return $this->ok([
			"license_key_member_count" => (int) 20,
			"license_key_day_count"    => (int) 90,
		]);
	}

	/**
	 * активируем ключ лицензии
	 */
	public function activateLicenseKey():array {

		$license_key = $this->post(\Formatter::TYPE_STRING, "license_key");
		$space_uniq  = $this->post(\Formatter::TYPE_STRING, "space_uniq");

		Type_Antispam_User::check($this->user_id, Type_Antispam_User::ACTIVATE_LICENSE_KEY);

		try {
			Domain_SpaceTariff_Scenario_Api::activateLicenseKey($this->user_id, $license_key, $space_uniq);
		} catch (Domain_SpaceTariff_Exception_InvalidLicenseKey) {
			throw new CaseException(1214001, "invalid license key");
		} catch (Domain_SpaceTariff_Exception_LowerTariffLicenseKey $e) {
			throw new CaseException(1214002, "license key with a lower rate", [
				"member_count"             => $e->getMemberCount(),
				"license_key_member_count" => $e->getLicenseKeyMemberCount(),
			]);
		} catch (Domain_SpaceTariff_Exception_UsedLicenseKey) {
			throw new CaseException(1214003, "license key already used");
		} catch (Domain_SpaceTariff_Exception_InvalidSpaceUniq) {
			throw new CaseException(1214006, "invalid space uniq");
		} catch (Domain_SpaceTariff_Exception_UserNotPermission) {
			throw new CaseException(1214007, "not a space administrator");
		}

		return $this->ok([
			"license_key_member_count" => (int) 20,
			"license_key_day_count"    => (int) 90,
		]);
	}
}