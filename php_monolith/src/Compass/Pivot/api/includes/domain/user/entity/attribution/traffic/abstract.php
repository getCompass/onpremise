<?php

namespace Compass\Pivot;

/**
 * Абстрактный класс описывает схожие черты для всех типов трафика
 */
abstract class Domain_User_Entity_Attribution_Traffic_Abstract {

	/** @var string тип трафика */
	protected const _TRAFFIC_TYPE = "";

	/** @var Struct_Db_PivotAttribution_UserAppRegistration Параметры атрибуции с которыми пользователь зарегистрировался в приложении */
	protected Struct_Db_PivotAttribution_UserAppRegistration $_user_app_registration;

	/** @var Domain_User_Entity_Attribution_Comparator_Abstract С помощью чего сравниваем параметры посещений и регистрации */
	protected Domain_User_Entity_Attribution_Comparator_Abstract $_comparator;

	/**
	 * @var Struct_Db_PivotAttribution_LandingVisit|null Посещение, параметры которого больше всего совпали с параметрами регистрации пользователя
	 * Для каждого типа трафика определяется по своим критериям – @see Domain_User_Entity_Attribution_Traffic_Abstract::detectMatchedVisit()
	 */
	protected null|Struct_Db_PivotAttribution_LandingVisit $_matched_visit = null;

	/** @var Struct_Db_PivotAttribution_LandingVisit[] Посещения, отфильтрованные по типу трафика (собственники, участники) */
	protected array $_traffic_filtered_visit_list = [];

	/** @var bool Нужно ли собирать аналитику по пользователю. По умолчанию – да */
	protected bool $_should_collect_analytics = true;

	/** @var int действия, которые должно выполнить клиентское приложение после определения атрибуции */
	public const CLIENT_ACTION_OPEN_DASHBOARD     = 1;
	public const CLIENT_ACTION_OPEN_ENTERING_LINK = 2;
	public const CLIENT_ACTION_OPEN_JOIN_LINK     = 3;
	protected int $_client_action = self::CLIENT_ACTION_OPEN_DASHBOARD;

	function __construct(
		Struct_Db_PivotAttribution_UserAppRegistration     $user_app_registration,
		Domain_User_Entity_Attribution_Comparator_Abstract $comparator,
		array                                              $traffic_filtered_visit_list,
	) {

		$this->_user_app_registration       = $user_app_registration;
		$this->_comparator                  = $comparator;
		$this->_traffic_filtered_visit_list = $traffic_filtered_visit_list;
	}

	/**
	 * Определяем и, если имеется, сохраняем в базу посещение, которое привело к регистрации пользователя
	 */
	public function detectMatchedVisit():void {

		// выбираем самое подходящее посещение
		$this->_matched_visit = $this->chooseMatchedVisit();

		// сохраняем в базу, если нашли совпадения
		!is_null($this->_matched_visit) && Domain_User_Entity_Attribution::saveUserVisitRel($this->_user_app_registration, $this->_matched_visit);

		// отправляем аналитику
		$this->_sendAnalytics();

		// выполняем действие после того как определили посещение
		$this->_after();
	}

	/**
	 * Определяем посещение, которое привело к регистрации пользователя
	 * У каждого типа трафика своя реализация
	 *
	 * @return Struct_Db_PivotAttribution_LandingVisit|null
	 */
	abstract public function chooseMatchedVisit():null|Struct_Db_PivotAttribution_LandingVisit;

	/**
	 * Получаем действие, которые должно выполнить клиентское приложение после определения посещения
	 *
	 * @return int
	 */
	public function getClientAction():int {

		return $this->_client_action;
	}

	/**
	 * Получаем посещение, которое привело к регистрации пользователя
	 *
	 * @return Struct_Db_PivotAttribution_LandingVisit|null
	 */
	public function getMatchedVisit():null|Struct_Db_PivotAttribution_LandingVisit {

		return $this->_matched_visit;
	}

	/**
	 * Устанавливаем значение в параметр – нужно ли собирать аналитику по пользователю
	 */
	public function setCollectAnalyticsFlag(bool $value):void {

		$this->_should_collect_analytics = $value;
	}

	/**
	 * Выполняем действие после того как определили посещение
	 */
	protected function _after():void {}

	/**
	 * Отправляем аналитику
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected function _sendAnalytics():void {

		// если аналитику по пользователю собирать не нужно, то ничего не делаем
		if (!$this->_should_collect_analytics) {
			return;
		}

		$join_space_analytics = new Struct_Dto_User_Action_Attribution_Detect_JoinSpaceAnalytics(
			$this->_user_app_registration->user_id,
			Domain_User_Entity_Attribution_JoinSpaceAnalytics::resolveResultMaskByClientAction($this->_client_action),
			!is_null($this->_matched_visit) ? $this->_matched_visit->visit_id : "",
			static::_TRAFFIC_TYPE,
			$this->_getParametersComparingResult(),
		);

		Domain_User_Entity_Attribution_JoinSpaceAnalytics::createUserJoinSpaceAnalytics($join_space_analytics);
	}

	/**
	 * Получаем результаты сравнения параметров регистрации и посещений /join/ страниц – для аналитики
	 * Переопределяется в классах, где это действительно нужно
	 *
	 * @return array
	 */
	protected function _getParametersComparingResult():array {

		return [];
	}
}