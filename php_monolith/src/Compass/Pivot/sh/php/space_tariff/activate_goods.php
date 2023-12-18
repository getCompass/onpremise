<?php

namespace Compass\Pivot;

use Tariff\Plan\MemberCount\Alteration;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Вспомогательный класс для активации goods_id в указанном пространстве.
 * @since 21.08.23, безопасен для повторного использования.
 */
class ActivateGoods {

	/**
	 * Показать справку по скрипту.
	 */
	public static function showUsage():void {

		console("---");
		console("Скрипт активации goods_id для пространства");
		console("--goods-id [REQ, STR] активируемый товар");
		console("--action   [OPT, INT] действие активации — payment (по умолчанию), promo, detached, force");
		console("--help     [OPT,    ] показать это сообщение");
	}

	/**
	 * Вспомогательный класс для активации goods_id в указанном пространстве.
	 */
	public function __construct(
		protected string $_goods_id,
		protected int    $_action,
		protected bool   $_is_dry,
	) {
	}

	/**
	 * Запускает скрипт в исполнение.
	 */
	public function exec():void {

		$current_time = time();

		// пытаемся получить подходящий тарифный план для изменения
		$activation_item = Domain_SpaceTariff_Entity_ActivationResolver::resolve($this->_goods_id);
		$activation_item === false && die(redText("error — passed bad goods id"));

		/** @var Alteration $alteration */
		$alteration = $activation_item->alteration;

		// проверяем что пользователь состоит в пространстве
		Domain_Company_Entity_User_Member::assertUserIsMemberOfCompany($activation_item->customer_user_id, $activation_item->space_id);

		// получаем данные плана пользователей
		$tariff_rows    = Gateway_Db_PivotCompany_TariffPlan::getBySpace($activation_item->space_id);
		$current_tariff = Domain_SpaceTariff_Tariff::load($tariff_rows)->memberCount();

		console("----------------------------------");
		console("activation user : {$activation_item->customer_user_id}");
		console("space id : {$activation_item->space_id}");
		console("----------------------------------");
		console(yellowText("current tariff plan details:"));
		console("is active:          " . ($current_tariff->isActive($current_time) ? "yes" : "no"));
		console("active till:        " . (date("d/m/y H:i", $current_tariff->getActiveTill())));
		console("member_count:       " . ($current_tariff->getLimit()));
		console("is free:            " . ($current_tariff->isFree($current_time) ? "yes" : "no"));
		console("is trial:           " . ($current_tariff->isTrial($current_time) ? "yes" : "no"));
		console("is trial available: " . ($current_tariff->isTrialAvailable() ? "yes" : "no"));
		console("----------------------------------");

		if ($this->_is_dry) {

			console(yellowText("dry run — done"));
			return;
		}

		if (!Type_Script_InputHelper::assertConfirm("continue with goods id {$this->_goods_id} (y/n)?")) {

			console(redText("aborted"));
			return;
		}

		$tariff = Domain_SpaceTariff_Action_AlterMemberCount::run(
			$activation_item->customer_user_id,
			$activation_item->space_id,
			$this->_action,
			$alteration
		);

		$current_tariff = $tariff->memberCount();

		console("----------------------------------");
		console(purpleText("updated tariff plan details:"));
		console("is active:          " . ($current_tariff->isActive($current_time) ? "yes" : "no"));
		console("active till:        " . (date("d/m/y H:i", $current_tariff->getActiveTill())));
		console("member_count:       " . ($current_tariff->getLimit()));
		console("is free:            " . ($current_tariff->isFree($current_time) ? "yes" : "no"));
		console("is trial:           " . ($current_tariff->isTrial($current_time) ? "yes" : "no"));
		console("is trial available: " . ($current_tariff->isTrialAvailable() ? "yes" : "no"));
		console("----------------------------------");

		console(greenText("done!"));
	}
}

if (Type_Script_InputHelper::needShowUsage()) {

	ActivateGoods::showUsage();
	exit;
}

$goods_id = Type_Script_InputParser::getArgumentValue("--goods-id");
$action   = Type_Script_InputParser::getArgumentValue("--action", default: "payment");
$is_dry   = Type_Script_InputHelper::isDry();

$action = match ($action) {
	"payment"  => \Tariff\Plan\BaseAction::METHOD_PAYMENT,
	"promo"    => \Tariff\Plan\BaseAction::METHOD_PROMO,
	"detached" => \Tariff\Plan\BaseAction::METHOD_DETACHED,
	"force"    => \Tariff\Plan\BaseAction::METHOD_FORCE,
	default    => die(redText("passed incorrect action, see --help")),
};

(new ActivateGoods($goods_id, $action, $is_dry))->exec();
