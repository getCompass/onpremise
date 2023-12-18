<?php

namespace Compass\Company;

/**
 * Данные для аутентификации сессии в компании
 */
class Struct_User_AuthenticationData {

	/**
	 * Struct_User_Info constructor.
	 */
	public function __construct(
		public bool $need_block_if_premium_inactive,
		public int  $active_till,
	) {

	}
}
