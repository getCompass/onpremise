<?php

namespace Compass\Speaker;

// родительский класс для всех посущностей пользователя

/**
 * @property \User $user
 */
class Type_User_Default {

	protected $user;

	// @mixed
	function __construct($user) {

		$this->user = $user;
	}
}
