<?php
class CmdParser extends DataController
{

    protected $socket_file;


	protected function json()
	{
		$data = [
			'version' => getVersion(),
			'state' => getState(),
			'stats' => getStats(),
			'pool_config' => getPoolConfig(),
			'net_conn' => getConnections(),
			'date' => exec('date'),
		];

		$this->responseJson($data);
	}

	protected function humanReadable()
	{
		$data =
		"Version: " . getVersion() . "\n\n" .
		"State: " . $this->command('state') . "\n\n" .
        $this->command('stats') . "\n\n" .
        $this->command('pool') . "\n\n" .
		$this->command('net conn') . "\n\n" .
		"Date: " . exec('date');

		$this->response($data);
	}











    public function command($cmd)
    {
        $lines = [];
        foreach ($this->commandStream($cmd) as $line)
            $lines[] = $line;

        return implode("\n", $lines);
    }

    public function commandStream($cmd)
    {
        $socket = socket_create(AF_UNIX, SOCK_STREAM, 0);

        if (!$socket || !socket_connect($socket, $this->socket_file))
            echo ('Error establishing a connection with the socket');

        $command = "$cmd\0";
        socket_send($socket, $command, strlen($command), 0);

        while ($line = @socket_read($socket, 1024, PHP_NORMAL_READ))
            yield rtrim($line, "\n");

        socket_close($socket);
    }
}
