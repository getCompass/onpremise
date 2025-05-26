<?php

namespace BaseFrame\Router;

use BaseFrame\Controller\Base;

/**
 * запрос который проходит через middleware
 */
class Request {

	public array  $post_data            = [];
	public string $route                = "";
	public string $controller_name      = "";
	public ?Base  $controller_class     = null;
	public string $method_name          = "";
	public int    $method_version       = 1;
	public int    $user_id              = 0;
	public array  $extra                = [];
	public array  $response             = [];
	public array  $counter_list         = [];

	public function __construct(string $route, array $post_data, array $extra = []) {

		$this->route     = $route;
		$this->post_data = $post_data;
		$this->extra     = $extra;
	}
}