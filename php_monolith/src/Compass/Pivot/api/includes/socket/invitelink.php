<?php

namespace Compass\Pivot;

/**
 * класс описывающий socket-контроллер для работы с ссылками-приглашениями для собственников
 */
class Socket_InviteLink extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"create",
		"edit",
		"remove",
		"add",
		"delete",
	];

	/**
	 * выполняем создание ссылки-приглашения для собственников
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function create():array {

		$invite_code      = $this->post(\Formatter::TYPE_STRING, "invite_code");
		$invite_code_hash = $this->post(\Formatter::TYPE_STRING, "invite_code_hash");
		$partner_id       = $this->post(\Formatter::TYPE_INT, "partner_id");
		$discount         = $this->post(\Formatter::TYPE_INT, "discount");
		$can_reuse_after  = $this->post(\Formatter::TYPE_INT, "can_reuse_after");
		$expires_at       = $this->post(\Formatter::TYPE_INT, "expires_at");

		Domain_Partner_Scenario_Socket_InviteLink::create($invite_code, $invite_code_hash, $partner_id, $discount, $can_reuse_after, $expires_at);

		return $this->ok();
	}

	/**
	 * выполняем редактирование ссылки-приглашения для собственников
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function edit():array {

		$invite_code_hash = $this->post(\Formatter::TYPE_STRING, "invite_code_hash");
		$discount         = $this->post(\Formatter::TYPE_INT, "discount");

		Domain_Partner_Scenario_Socket_InviteLink::edit($invite_code_hash, $discount);

		return $this->ok();
	}

	/**
	 * выполняем удаление ссылки-приглашения для собственников
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function remove():array {

		$invite_code_hash = $this->post(\Formatter::TYPE_STRING, "invite_code_hash");

		Domain_Partner_Scenario_Socket_InviteLink::remove($invite_code_hash);

		return $this->ok();
	}

	/**
	 * Зеркально сохраняем ссылку на пивоте
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function add():array {

		$invite_code = $this->post(\Formatter::TYPE_STRING, "invite_code");
		$partner_id  = $this->post(\Formatter::TYPE_INT, "partner_id");

		Domain_Partner_Scenario_Socket_InviteLink::add($invite_code, $partner_id);

		return $this->ok();
	}

	/**
	 * Удаляем ссылку на пивоте
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function delete():array {

		$invite_code = $this->post(\Formatter::TYPE_STRING, "invite_code");

		Domain_Partner_Scenario_Socket_InviteLink::delete($invite_code);

		return $this->ok();
	}
}