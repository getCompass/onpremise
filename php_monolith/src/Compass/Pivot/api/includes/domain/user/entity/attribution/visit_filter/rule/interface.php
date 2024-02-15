<?php

namespace Compass\Pivot;

/**
 * Интерфейс, которому соответствует каждое правило
 */
interface Domain_User_Entity_Attribution_VisitFilter_Rule_Interface {

	/**
	 * Проверяем выполнение правила
	 *
	 * @return bool
	 */
	public function check(Struct_Db_PivotAttribution_UserAppRegistration $user_app_registration, Struct_Db_PivotAttribution_LandingVisit $visit):bool;
}