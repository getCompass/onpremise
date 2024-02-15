<?php

namespace Compass\Pivot;

/**
 * Класс описывает работу атрибуции для органического типа трафика – такие пользователи пришли в обход лендинга/ссылок-приглашений
 * Наследуется от Domain_User_Entity_Attribution_Traffic_Landing
 */
class Domain_User_Entity_Attribution_Traffic_Organic extends Domain_User_Entity_Attribution_Traffic_Landing {

	/** @var string тип трафика */
	protected const _TRAFFIC_TYPE = "organic";
}