<?php

namespace Compass\Pivot;

use Tariff\Plan\MemberCount;

/**
 * Класс для управления витринами.
 */
class Domain_SpaceTariff_Plan_MemberCount_Showcase {

	public const TYPE = \Tariff\Loader::MEMBER_COUNT_PLAN_KEY;

	public const ACTION_ACTIVATE = "activate";
	public const ACTION_PROLONG  = "prolong";
	public const ACTION_CHANGE   = "change";

	public const PAGE_10   = 10;
	public const PAGE_15   = 15;
	public const PAGE_20   = 20;
	public const PAGE_25   = 25;
	public const PAGE_30   = 30;
	public const PAGE_35   = 35;
	public const PAGE_40   = 40;
	public const PAGE_45   = 45;
	public const PAGE_50   = 50;
	public const PAGE_55   = 55;
	public const PAGE_60   = 60;
	public const PAGE_65   = 65;
	public const PAGE_70   = 70;
	public const PAGE_75   = 75;
	public const PAGE_80   = 80;
	public const PAGE_85   = 85;
	public const PAGE_90   = 90;
	public const PAGE_95   = 95;
	public const PAGE_100  = 100;
	public const PAGE_110  = 110;
	public const PAGE_120  = 120;
	public const PAGE_130  = 130;
	public const PAGE_140  = 140;
	public const PAGE_150  = 150;
	public const PAGE_160  = 160;
	public const PAGE_170  = 170;
	public const PAGE_180  = 180;
	public const PAGE_190  = 190;
	public const PAGE_200  = 200;
	public const PAGE_210  = 210;
	public const PAGE_220  = 220;
	public const PAGE_230  = 230;
	public const PAGE_240  = 240;
	public const PAGE_250  = 250;
	public const PAGE_260  = 260;
	public const PAGE_270  = 270;
	public const PAGE_280  = 280;
	public const PAGE_290  = 290;
	public const PAGE_300  = 300;
	public const PAGE_310  = 310;
	public const PAGE_320  = 320;
	public const PAGE_330  = 330;
	public const PAGE_340  = 340;
	public const PAGE_350  = 350;
	public const PAGE_360  = 360;
	public const PAGE_370  = 370;
	public const PAGE_380  = 380;
	public const PAGE_390  = 390;
	public const PAGE_400  = 400;
	public const PAGE_410  = 410;
	public const PAGE_420  = 420;
	public const PAGE_430  = 430;
	public const PAGE_440  = 440;
	public const PAGE_450  = 450;
	public const PAGE_460  = 460;
	public const PAGE_470  = 470;
	public const PAGE_480  = 480;
	public const PAGE_490  = 490;
	public const PAGE_500  = 500;
	public const PAGE_510  = 510;
	public const PAGE_520  = 520;
	public const PAGE_530  = 530;
	public const PAGE_540  = 540;
	public const PAGE_550  = 550;
	public const PAGE_560  = 560;
	public const PAGE_570  = 570;
	public const PAGE_580  = 580;
	public const PAGE_590  = 590;
	public const PAGE_600  = 600;
	public const PAGE_610  = 610;
	public const PAGE_620  = 620;
	public const PAGE_630  = 630;
	public const PAGE_640  = 640;
	public const PAGE_650  = 650;
	public const PAGE_660  = 660;
	public const PAGE_670  = 670;
	public const PAGE_680  = 680;
	public const PAGE_690  = 690;
	public const PAGE_700  = 700;
	public const PAGE_710  = 710;
	public const PAGE_720  = 720;
	public const PAGE_730  = 730;
	public const PAGE_740  = 740;
	public const PAGE_750  = 750;
	public const PAGE_760  = 760;
	public const PAGE_770  = 770;
	public const PAGE_780  = 780;
	public const PAGE_790  = 790;
	public const PAGE_800  = 800;
	public const PAGE_810  = 810;
	public const PAGE_820  = 820;
	public const PAGE_830  = 830;
	public const PAGE_840  = 840;
	public const PAGE_850  = 850;
	public const PAGE_860  = 860;
	public const PAGE_870  = 870;
	public const PAGE_880  = 880;
	public const PAGE_890  = 890;
	public const PAGE_900  = 900;
	public const PAGE_910  = 910;
	public const PAGE_920  = 920;
	public const PAGE_930  = 930;
	public const PAGE_940  = 940;
	public const PAGE_950  = 950;
	public const PAGE_960  = 960;
	public const PAGE_970  = 970;
	public const PAGE_980  = 980;
	public const PAGE_990  = 990;
	public const PAGE_1000 = 1000;
	public const PAGE_1050 = 1050;
	public const PAGE_1100 = 1100;
	public const PAGE_1150 = 1150;
	public const PAGE_1200 = 1200;
	public const PAGE_1250 = 1250;
	public const PAGE_1300 = 1300;
	public const PAGE_1350 = 1350;
	public const PAGE_1400 = 1400;
	public const PAGE_1450 = 1450;
	public const PAGE_1500 = 1500;
	public const PAGE_1550 = 1550;
	public const PAGE_1600 = 1600;
	public const PAGE_1650 = 1650;
	public const PAGE_1700 = 1700;
	public const PAGE_1750 = 1750;
	public const PAGE_1800 = 1800;
	public const PAGE_1850 = 1850;
	public const PAGE_1900 = 1900;
	public const PAGE_1950 = 1950;
	public const PAGE_2000 = 2000;
	public const PAGE_2100 = 2100;
	public const PAGE_2200 = 2200;
	public const PAGE_2300 = 2300;
	public const PAGE_2400 = 2400;
	public const PAGE_2500 = 2500;
	public const PAGE_2600 = 2600;
	public const PAGE_2700 = 2700;
	public const PAGE_2800 = 2800;
	public const PAGE_2900 = 2900;
	public const PAGE_3000 = 3000;

	public const SLOT_30  = 30;
	public const SLOT_60  = 60;
	public const SLOT_90  = 90;
	public const SLOT_120 = 120;
	public const SLOT_150 = 150;
	public const SLOT_180 = 180;
	public const SLOT_210 = 210;
	public const SLOT_240 = 240;
	public const SLOT_270 = 270;
	public const SLOT_300 = 300;
	public const SLOT_330 = 330;
	public const SLOT_365 = 365;

	#[\JetBrains\PhpStorm\Deprecated]
	public const SHOWCASE_VERSION_1 = 1;
	#[\JetBrains\PhpStorm\Deprecated]
	public const SHOWCASE_VERSION_2 = 2;
	public const SHOWCASE_VERSION_3 = 3;

	/** @var string[] разрешенные действия */
	protected const _ALLOWED_ACTION_LIST = [
		self::ACTION_ACTIVATE,
		self::ACTION_PROLONG,
		self::ACTION_CHANGE,
	];

	/** @var int[] поддерживаемые версии витрины плана числа пользователей */
	protected const _SUPPORTED_SHOWCASE_VERSION_LIST = [
		self::SHOWCASE_VERSION_1,
		self::SHOWCASE_VERSION_2,
		self::SHOWCASE_VERSION_3,
	];

	/**
	 * Проверяем, что версия витрины передана корректно.
	 * @throws Domain_SpaceTariff_Exception_UnsupportedShowcaseVersion
	 */
	public static function assertVersion(int $version):void {

		if (!in_array($version, static::_SUPPORTED_SHOWCASE_VERSION_LIST)) {
			throw new Domain_SpaceTariff_Exception_UnsupportedShowcaseVersion("showcase version $version in not supported");
		}
	}

	/**
	 * Возвращает витрину с товарами тарифного плана числа участников.
	 */
	public static function getShowcaseActions(int $customer_user_id, Struct_Db_PivotCompany_Company $space, int $version, MemberCount\MemberCount $plan):array {

		$template = static::_loadTemplate($version);
		return static::_fillTemplate($customer_user_id, $space, $template, $plan);
	}

	/**
	 * Возвращает список промо для витрины тарифного плана числа участников.
	 */
	public static function getShowcasePromo(int $customer_user_id, Struct_SpaceTariff_SpaceInfo $space_info, MemberCount\MemberCount $plan):array {

		$space = $space_info->space;

		$member_count = Domain_Company_Entity_Company::getMemberCount($space->extra);
		$member_count = array_reduce(
			\Tariff\Plan\MemberCount\OptionLimit::ALLOWED_VALUE_LIST,
			static fn(int $carry, int $item) => $item >= $member_count ? min($carry, $item) : $carry,
			PHP_INT_MAX
		);

		$material = new Struct_SpaceTariff_MemberCount_ProductMaterial($customer_user_id, $space, $member_count, static::SLOT_30, $plan);

		/** @noinspection PhpParamsInspection */
		if ($plan->isTrialAvailable() && $space_info->space_occupied_at + DAY1 * 30 > time()) {

			$welcome_bonus_promo_item = Domain_SpaceTariff_Plan_MemberCount_Product_ActivateWelcomeBonus::makeShowcaseItem($material);

			$output["welcome_bonus"]           = static::_convertToClientShowcaseItem($welcome_bonus_promo_item);
			$output["welcome_bonus"]["action"] = "unavailable";
		}

		return $output ?? [];
	}

	/**
	 * Загружает заготовку для витрины.
	 */
	protected static function _loadTemplate(int $version):array {

		$showcase_template_list = getConfig("SPACETARIFF_PLAN_SHOWCASE");

		if (!isset($showcase_template_list[static::TYPE])) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("passed unknown plan type");
		}

		$showcase_template_list_by_version = $showcase_template_list[static::TYPE];

		if (!isset($showcase_template_list_by_version[$version])) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("showcase version not found");
		}

		return $showcase_template_list_by_version[$version];
	}

	/**
	 * Заполняет шаблон витрины готовыми элементами.
	 */
	protected static function _fillTemplate(int $customer_user_id, Struct_Db_PivotCompany_Company $space, array $showcase_template, MemberCount\MemberCount $plan):array {

		foreach ($showcase_template as $action => $action_page_list) {

			$output[$action] = match ($action) {
				static::ACTION_ACTIVATE => static::_fillActivateAction($customer_user_id, $space, $action_page_list, $plan),
				static::ACTION_PROLONG => static::_fillProlongAction($customer_user_id, $space, $action_page_list, $plan),
				static::ACTION_CHANGE => static::_fillChangeAction($customer_user_id, $space, $action_page_list, $plan),
				default => throw new \BaseFrame\Exception\Domain\ParseFatalException("passed unknown action type")
			};
		}

		return $output ?? [];
	}

	/**
	 * Заполняет витрину данными для activate-товаров.
	 */
	protected static function _fillActivateAction(int $customer_user_id, Struct_Db_PivotCompany_Company $space, array $showcase_action_template, MemberCount\MemberCount $plan):?array {

		// в ответе может быть или activate или prolong,
		// поэтому обязательно используем одно и то же условие;
		// если тут что-то изменится, нужно поправить логику
		// метода tariff/get в пространстве и убедиться
		// что изменения не поломают клиентов
		if ((!$plan->isActive(time()) || $plan->isFree(time())) && !$plan->isTrial(time())) {
			return static::_fillPreparedTemplate($customer_user_id, $space, $showcase_action_template, $plan);
		}

		return null;
	}

	/**
	 * Заполняет витрину данными для prolong-товаров.
	 */
	protected static function _fillProlongAction(int $customer_user_id, Struct_Db_PivotCompany_Company $space, array $showcase_action_template, MemberCount\MemberCount $plan):?array {

		// в ответе может быть или activate или prolong,
		// поэтому обязательно используем одно и то же условие;
		// если тут что-то изменится, нужно поправить логику
		// метода tariff/get в пространстве и убедиться
		// что изменения не поломают клиентов
		if (($plan->isActive(time()) && !$plan->isFree(time())) || ($plan->isTrial(time()) && $plan->getLimit() > 10)) {

			$showcase_action_template = static::_prepareProlongAction($showcase_action_template, $plan);
			return static::_fillPreparedTemplate($customer_user_id, $space, $showcase_action_template, $plan);
		}

		return null;
	}

	/**
	 * Убирает неподходящие элементы из шаблона действия.
	 */
	protected static function _prepareProlongAction(array $showcase_action_template, MemberCount\MemberCount $plan):array {

		$output = [];

		foreach ($showcase_action_template as $member_count => $value) {

			if ($plan->getLimit() !== $member_count) {
				continue;
			}

			$output[$member_count] = $value;
		}

		return $output;
	}

	/**
	 * Заполняет витрину данными для change-товаров.
	 */
	protected static function _fillChangeAction(int $customer_user_id, Struct_Db_PivotCompany_Company $space, array $showcase_action_template, MemberCount\MemberCount $plan):?array {

		$time = time();

		// нельзя менять, если сейчас активно бесплатное пользование
		// но в рамках пробного менять можно как угодно
		if ($plan->isTrial($time) || !$plan->isFree($time)) {

			$showcase_action_template = self::_prepareChangeAction($showcase_action_template, $plan);
			return static::_fillPreparedTemplate($customer_user_id, $space, $showcase_action_template, $plan);
		}

		return null;
	}

	/**
	 * Убирает неподходящие элементы из шаблона действия.
	 */
	protected static function _prepareChangeAction(array $showcase_action_template, MemberCount\MemberCount $plan):array {

		$output = [];

		foreach ($showcase_action_template as $page => $showcase_item_class_list) {

			if (count($showcase_item_class_list) < 1) {
				return $output;
			}

			$key_first                   = array_key_first($showcase_item_class_list);
			$altered_key                 = max(0, $plan->getActiveTill() - time());
			$output[$page][$altered_key] = $showcase_item_class_list[$key_first];
		}

		return $output;
	}

	/**
	 * Заполняет указанный шаблон данными.
	 */
	protected static function _fillPreparedTemplate(int $customer_user_id, Struct_Db_PivotCompany_Company $space, array $page_list, MemberCount\MemberCount $plan):array {

		$output       = [];
		$circumstance = new MemberCount\Circumstance(Domain_Company_Entity_Company::getMemberCount($space->extra), static::_resolvePostPaymentPeriod());

		foreach ($page_list as $page => $slot_list) {

			if (is_null($slot_list)) {

				$output[] = ["member_count" => $page, "goods_list" => []];
				continue;
			}

			$page_item_list = []; // список товаров для формирующейся страницы

			foreach ($slot_list as $slot => $showcase_item_class_list) {

				$material = new Struct_SpaceTariff_MemberCount_ProductMaterial($customer_user_id, $space, $page, $slot, $plan);

				$showcase_item    = static::_resolveShowcaseItem($circumstance, $plan, $material, $showcase_item_class_list);
				$page_item_list[] = static::_convertToClientShowcaseItem($showcase_item);
			}

			$output[] = [
				"member_count" => $page,
				"goods_list"   => $page_item_list ?? [],
			];
		}

		return $output;
	}

	/**
	 * Пытается определить самый подходящий элемент для витрины.
	 *
	 * @param MemberCount\Circumstance                       $circumstance
	 * @param MemberCount\MemberCount                        $plan
	 * @param Struct_SpaceTariff_MemberCount_ProductMaterial $material
	 * @param array                                          $showcase_item_class_list
	 *
	 * @return Struct_SpaceTariff_MemberCount_ShowcaseItem
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _resolveShowcaseItem(MemberCount\Circumstance $circumstance, MemberCount\MemberCount $plan, Struct_SpaceTariff_MemberCount_ProductMaterial $material, array $showcase_item_class_list):Struct_SpaceTariff_MemberCount_ShowcaseItem {

		foreach ($showcase_item_class_list as $showcase_item_class) {

			// пытаем сделать элемент витрины
			$showcase_item = $showcase_item_class::makeShowcaseItem($material);

			// проверяем, что получили корректный класс
			if (!($showcase_item instanceof Struct_SpaceTariff_MemberCount_ShowcaseItem)) {
				throw new \BaseFrame\Exception\Domain\ParseFatalException("passed incorrect showcase item class list");
			}

			// устрожаем альтерацию
			$plan->arrangeAlteration($showcase_item->alteration, $circumstance, time());

			// если альтерация после строгости может
			// быть применена, то дальше не перебираем
			if ($showcase_item->alteration->availability->isAvailable()) {

				return $showcase_item;
			}
		}

		if (!isset($showcase_item)) {
			throw new \BaseFrame\Exception\Domain\ReturnFatalException("can not resolve showcase item");
		}

		// если ранее не вернули, то тут вернется
		// последний элемент из массива шаблона витрины
		return $showcase_item;
	}

	/**
	 * Конвертирует данные элемента витрины в к понятный клиенту формат.
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["goods_id" => "string", "action" => "string", "duration" => "int", "base_duration" => "int", "available_till" => "int", "unavailable_reason" => "string", "data" => "array"])]
	protected static function _convertToClientShowcaseItem(Struct_SpaceTariff_MemberCount_ShowcaseItem $showcase_item):array {

		$client_action = match ($showcase_item->alteration->availability->availability) {
			\Tariff\Plan\AlterationAvailability::UNAVAILABLE_SAME => "current",
			\Tariff\Plan\AlterationAvailability::AVAILABLE_FREE,
			\Tariff\Plan\AlterationAvailability::AVAILABLE_DETACHED,
			\Tariff\Plan\AlterationAvailability::AVAILABLE_WHILE_TRIAL => "free",
			\Tariff\Plan\AlterationAvailability::AVAILABLE_REASON_REQUIRED => "sell",
			default => "unavailable",
		};

		return [
			"goods_id"           => $showcase_item->goods_id,
			"action"             => $client_action,
			"duration"           => $showcase_item->prolong_duration * DAY1,
			"base_duration"      => $showcase_item->prolong_duration * DAY1,
			"available_till"     => $showcase_item->available_till,
			"unavailable_reason" => $showcase_item->alteration->availability->getMessage(),
			"data"               => [
				"limit" => $showcase_item->limit,
			],
		];
	}

	/**
	 * Возвращает задержку для постоплатного периода.
	 */
	protected static function _resolvePostPaymentPeriod():int {

		return getConfig("TARIFF")["member_count"]["postpayment_period"];
	}
}