<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

/**
 * Чинитель портов на домино.
 */
class InvalidPortRepairer {

	/**
	 * Запускает скрипт по починке портов.
	 */
	public static function exec(array $status_list = [Domain_Domino_Entity_Port_Registry::STATUS_INVALID], bool $ignore_confirm = false):void {

		do {

			$domino_list = static::_getDominoes();

			if (count($domino_list) === 0) {
				console("нет домино для работы");
			}

			$domino = static::_pickDomino($domino_list);

			do {

				$port_list = static::_getPorts($domino->domino_id, $status_list);

				if (count($port_list) === 0) {

					console("на домино {$domino->domino_id} не найдены порты в нужных статусах");
					break;
				}

				$port = static::_pickPort($port_list);

				if ($ignore_confirm || Type_Script_InputHelper::assertConfirm(sprintf("сбрасываем порт %s?", static::_makePortDescription($port, true)))) {
					Domain_Domino_Action_Port_Reset::run($domino, $port);
				}

				console("порт успешно сброшен");
			} while ($ignore_confirm || Type_Script_InputHelper::assertConfirm("пробуем следующий порт?"));
		} while (Type_Script_InputHelper::assertConfirm("пробуем следующее домино?"));
	}

	/**
	 * Возвращает все домино, доступные для работы.
	 *
	 * @return Struct_Db_PivotCompanyService_DominoRegistry[]
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _getDominoes():array {

		// получаем все домино
		return Gateway_Db_PivotCompanyService_DominoRegistry::getAll();
	}

	/**
	 * Выдает пользователю список домино для выбора.
	 */
	protected static function _pickDomino(array $domino_list):Struct_Db_PivotCompanyService_DominoRegistry {

		console("выбери домино для поиска невалидных портов");

		/** @var  $domino Struct_Db_PivotCompanyService_DominoRegistry */
		foreach ($domino_list as $key => $domino) {

			$desc = static::_makeDominoDescription($domino);
			console("{$key}) $desc");
		}

		while (true) {

			$picked = (int) readline();

			if (isset($domino_list[$picked])) {
				return $domino_list[$picked];
			}

			console(redText("какое-то неправильно домино, попробуй еще раз"));
		}
	}

	/**
	 * Генерирует информирующее описание для домино.
	 */
	protected static function _makeDominoDescription(Struct_Db_PivotCompanyService_DominoRegistry $domino):string {

		return "{$domino->domino_id}, common {$domino->common_active_port_count}/{$domino->common_port_count}, reserve {$domino->reserve_active_port_count}/{$domino->reserved_port_count}, service {$domino->service_active_port_count}/{$domino->service_port_count}";
	}

	/**
	 * Возвращает все порты на указанном домино, подходящие для починки.
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _getPorts(string $domino_id, array $status_list):array {

		// получаем все подходящие порты
		return Gateway_Db_PivotCompanyService_PortRegistry::getForReset($domino_id, $status_list);
	}

	/**
	 * Выдает пользователю список портов для выбора.
	 */
	protected static function _pickPort(array $port_list):Struct_Db_PivotCompanyService_PortRegistry {

		console("выбери порт для сброса");

		/** @var $port Struct_Db_PivotCompanyService_PortRegistry */
		foreach ($port_list as $key => $port) {

			$desc = static::_makePortDescription($port);
			console("{$key}) {$desc}");
		}

		while (true) {

			$picked = (int) readline();

			if (isset($port_list[$picked])) {
				return $port_list[$picked];
			}

			console(redText("какой-то неправильный порт, попробуй еще раз"));
		}
	}

	/**
	 * Генерирует информирующее описание для порта.
	 */
	protected static function _makePortDescription(Struct_Db_PivotCompanyService_PortRegistry $port, bool $need_extended = false):string {

		$extended = "";

		if ($port->company_id !== 0 && $need_extended) {

			$extended .= "занят компанией $port->company_id\n";
			$company_init_item = Gateway_Db_PivotCompanyService_CompanyInitRegistry::getOne($port->company_id);

			$extended .= sprintf("создание компании %s / %s\n", date("d/m/Y H:i:s", $company_init_item->creating_started_at), date("d/m/Y H:i:s", $company_init_item->creating_finished_at));
			$extended .= sprintf("стала свободной %s\n", date("d/m/Y H:i:s", $company_init_item->became_vacant_at));
			$extended .= sprintf("занята пользователем %d %s / %s\n", $company_init_item->occupant_user_id, date("d/m/Y H:i:s", $company_init_item->occupation_started_at), date("d/m/Y H:i:s", $company_init_item->occupation_finished_at));
		}

		return "{$port->port}, $extended";
	}
}

if (Type_Script_InputHelper::needShowUsage()) {

	console(yellowText("скрипт для сброса портов"));
	console("сбрасывает порты на домино, при сбросе пытается отвязать порт, вне зависимости от его статуса");
	console("конфиги не пересоздает, поэтому нужно быть максимально аккуратным с ним, иначе случайно можно поломать компанию, если она к нему привязана");
	console("параметры:");
	console("  --any-status [opt] позволяет выбрать статусы, в котором можно сбрасывать порты");
	console("  --no-confirm [opt] отключает подтверждение при сбросе порта");
	console("\n");

	exit(0);
}

// пропускаем ли подтверждение очистки
$ignore_confirm = Type_Script_InputParser::getArgumentValue("no-confirm", Type_Script_InputParser::TYPE_NONE, false, false);

// указываем ли статусы или берем только невалидные
if (Type_Script_InputParser::getArgumentValue("any-status", Type_Script_InputParser::TYPE_NONE, false, false)) {

	console("введи статусы портов для работы через запятую (active — 20, locked — 30, invalid — 90)");
	$status_list = explode(",", str_replace(" ", "", readline()));
} else {
	$status_list = [Domain_Domino_Entity_Port_Registry::STATUS_INVALID];
}

InvalidPortRepairer::exec($status_list, $ignore_confirm);