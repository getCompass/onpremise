<?php

namespace Compass\Company;

/**
 * Экшн для конвертации прав
 */
class Domain_Member_Action_PermissionsUpdate_Handler {

	// до какой версии апгрейд
	public const CURRENT_PERMISSIONS_VERSION = 4;

	/**
	 * Список классов, обновляющих права
	 */
	protected const _UPDATE_PERMISSIONS_CLASS_LIST = [
		Domain_Member_Action_PermissionsUpdate_V2::PERMISSIONS_VERSION => Domain_Member_Action_PermissionsUpdate_V2::class,
		Domain_Member_Action_PermissionsUpdate_V3::PERMISSIONS_VERSION => Domain_Member_Action_PermissionsUpdate_V3::class,
		Domain_Member_Action_PermissionsUpdate_V4::PERMISSIONS_VERSION => Domain_Member_Action_PermissionsUpdate_V4::class,
	];

	/**
	 * Выполняем конвертацию
	 *
	 * @param array                 $member_list
	 * @param \BaseFrame\System\Log $log
	 * @param bool                  $is_dry
	 *
	 * @return \BaseFrame\System\Log
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function do(array $member_list, \BaseFrame\System\Log $log, bool $is_dry = true):\BaseFrame\System\Log {

		$current_version = self::CURRENT_PERMISSIONS_VERSION;

		$config = Domain_Company_Entity_Config::get(Domain_Company_Entity_Config::PERMISSIONS_VERSION);

		// если уже последняя версия прав - завершаем выполнение
		if ($config["value"] >= self::CURRENT_PERMISSIONS_VERSION) {

			$log->addText("Версия прав в пространстве равняется {$current_version}, завершаю выполнение...");
			return $log;
		}

		// инкрементируем версию, чтобы не накатить повторно текущую
		$from_version = $config["value"] + 1;

		// для каждой версии накатываем подготовленную функцию
		for ($version = $from_version; $version <= $current_version; $version++) {

			if (!isset(self::_UPDATE_PERMISSIONS_CLASS_LIST[$version])) {

				$log->addText("Для версии прав {$version} не существует класса, завершаю выполнение...");
				return $log;
			}

			/** @var Domain_Member_Action_PermissionsUpdate_Main $action_class */
			$action_class = self::_UPDATE_PERMISSIONS_CLASS_LIST[$version];

			[$member_list, $log] = $action_class::do($member_list, $log, $is_dry);

			if ($is_dry) {
				return $log;
			}

			// апгрейдим версию прав до текущей
			Domain_Company_Entity_Config::set(Domain_Company_Entity_Config::PERMISSIONS_VERSION, $version);
		}

		return $log;
	}

}