<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Class Struct_Event_Base
 * Базовая структура события.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_User_Observer extends Struct_Default {

	/** @var array список работ для обсервера */
	public array $user_job_list_batch;

	/**
	 * Статический конструктор.
	 *
	 * @param array batch_user_job_list
	 *
	 * @throws \parseException
	 */
	public static function build(array $user_job_list_batch):static {

		return new static([

			"user_job_list_batch" => $user_job_list_batch,

		]);
	}
}
