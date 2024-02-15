<?php

namespace Compass\Pivot;

/**
 * класс для фильтрации списка посещений по правилам
 * @package Compass\Pivot
 */
class Domain_User_Entity_Attribution_VisitFilter {

	/**
	 * Запускаем фильтр посещений по правилам
	 *
	 * @param Struct_Db_PivotAttribution_LandingVisit[]                   $visit_list
	 * @param Domain_User_Entity_Attribution_VisitFilter_Rule_Interface[] $rule_list
	 *
	 * @return array
	 */
	public static function run(Struct_Db_PivotAttribution_UserAppRegistration $user_app_registration, array $visit_list, array $rule_list):array {

		$output = $visit_list;

		foreach ($rule_list as $rule) {
			$output = array_filter($output, static fn(Struct_Db_PivotAttribution_LandingVisit $visit) => $rule->check($user_app_registration, $visit));
		}

		return $output;
	}

	/**
	 * Проверяем, что хотя бы одно посещение соответствует одному из правил
	 *
	 * @param Struct_Db_PivotAttribution_LandingVisit[]                   $visit_list
	 * @param Domain_User_Entity_Attribution_VisitFilter_Rule_Interface[] $rule_list
	 *
	 * @return bool
	 */
	public static function anySatisfy(Struct_Db_PivotAttribution_UserAppRegistration $user_app_registration, array $visit_list, array $rule_list):bool {

		foreach ($rule_list as $rule) {

			foreach ($visit_list as $visit) {

				if ($rule->check($user_app_registration, $visit)) {
					return true;
				}
			}
		}

		return false;
	}
}