<?php

use App\Xdag\Xdag;

class Controller
{
	protected $config, $xdag;

	public function __construct(array $config, Xdag $xdag)
	{
		$this->config = $config;
		$this->xdag = $xdag;
	}

	protected function response($data)
	{
		echo "$data\n";
	}

	protected function responseJson($data)
	{
		$data = @json_encode($data, JSON_PRETTY_PRINT);

		if ($data === false)
			echo "Internal error while encoding JSON.\n";

		$this->response($data);
	}
}
