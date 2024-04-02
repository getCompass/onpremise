<?php /** @noinspection DuplicatedCode */

declare(strict_types = 1);

/**
 * Файл модуля.
 * Отвечает за загрузку обработчиков запроса.
 *
 * Должен вернуть все возможные точки входа в модуль:
 * — apiv*
 * — socket
 *
 * Тут можно чуть оптимизировать — сделать загрузку через замыкания,
 * чтобы не сразу грузить классы, а лениво, когда они понадобятся.
 *
 * @package Compass\Federation
 */

namespace Compass\Federation;

return [new Socket_Handler(), new Onpremiseweb_Handler()];