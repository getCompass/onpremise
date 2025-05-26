<?php

/**
 *
 */
interface RouteHandler {

	/**
	 * Функция — обработчик запроса.
	 * Должна обрабатывать входные данные для обработчика запросов.
	 *
	 * Возвращает массив, который затем будет закодирован в json для возврата.
	 */
	public function handle(string $route, array $post_data):array;

	/**
	 * Возвращает обслуживаемые пути.
	 */
	public function getServedRoutes():array;

	/**
	 * Возвращает тип обслуживаемых запросов (apiv1, apiv2, socket).
	 */
	public function getType():string;

	/**
	 * Метод конвертации в строку нужно для удобства реализовать.
	 */
	public function __toString():string;

}