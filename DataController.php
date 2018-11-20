<?php

abstract class DataController extends Controller
{
	public function index($human_readable = false)
	{
		if ($human_readable)
			return $this->humanReadable();

		return $this->json();
	}

	abstract protected function json();
	abstract protected function humanReadable();
}
