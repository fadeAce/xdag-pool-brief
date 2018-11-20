<?php

$param = $_GET['param'];
echo $param;


$socket_file = "/home/pool/xdag1/client/unix_sock.dat";

$version = "";

if ($param == "state"){
    statejson();
}
if ($param == "stats"){
    statsjson();
}
if ($param == "netconn"){
    netjson();
}

function statejson()
{
    $data = [
        'version' => getVersion(),
        'state' => getState(),
    ];

    responseJson($data);
}

function statsjson()
{
    $data = [
        'version' => getVersion(),
        'stats' => getStats(),
    ];

    responseJson($data);
}

function netjson()
{
    $data = [
        'version' => getVersion(),
        'net_conn' => getConnections(),
    ];
    responseJson($data);
}


function getVersion()
{
    global $version;
    global $socket_file;
    if ($version)
        return $version;

    $file = str_replace('"', '\"', dirname($socket_file) . '/xdag');
    exec('"' . $file . '" --help', $out);

    if (!$out)
        return '???';

    $line = current($out);
    $line = preg_split('/\s+/', trim($line));
    return $version = rtrim(end($line), '.');
}

function getState()
{
    return command('state');
}

function getStats()
{
    $stats = [];

    foreach (commandStream('stats') as $line) {
        if (preg_match('/\s*(.*): (.*)/i', $line, $matches)) {
            $key = strtolower(trim($matches[1]));
            $values = explode(' of ', $raw_value = strtolower(trim($matches[2])));

            if (count($values) == 2) {
                foreach ($values as $i => $value)
                    if (preg_match('/^[0-9]+$/', $value))
                        $values[$i] = (int)$value;
                    else if (is_numeric($value))
                        $values[$i] = (float)$value;

                $stats[str_replace(' ', '_', $key)] = $values;

                if (strpos($key, 'hashrate') !== false && !isset($stats['hashrate']))
                    $stats['hashrate'] = [$values[0] * 1024 * 1024, $values[1] * 1024 * 1024];
            } else {
                if (preg_match('/^[0-9]+$/', $raw_value))
                    $raw_value = (int)$raw_value;
                else if (is_numeric($raw_value))
                    $raw_value = (float)$raw_value;

                $stats[str_replace(' ', '_', $key)] = $raw_value;
            }
        }
    }

    return $stats;
}

function getConnections()
{
    $connections = [];
    foreach (commandStream('net conn') as $line) {
        $line = preg_split('/\s+/', trim($line));

        if (count($line) != 11)
            continue;

        $connections[] = [
            'host' => $line[1],
            'seconds' => (int)$line[2],
            'in_out_bytes' => array_map('intval', explode('/', $line[4])),
            'in_out_packets' => array_map('intval', explode('/', $line[7])),
            'in_out_dropped' => array_map('intval', explode('/', $line[9])),
        ];
    }

    return $connections;
}


function command($cmd)
{
    $lines = [];
    foreach (commandStream($cmd) as $line)
        $lines[] = $line;

    return implode("\n", $lines);
}

function commandStream($cmd)
{
    global $socket_file;
    $socket = socket_create(AF_UNIX, SOCK_STREAM, 0);

    if (!$socket || !socket_connect($socket, $socket_file))
        echo('Error establishing a connection with the socket');

    $command = "$cmd\0";
    socket_send($socket, $command, strlen($command), 0);

    while ($line = @socket_read($socket, 1024, PHP_NORMAL_READ))
        yield rtrim($line, "\n");

    socket_close($socket);
}


function response($data)
{
    echo "$data\n";
}

function responseJson($data)
{
    $data = @json_encode($data, JSON_PRETTY_PRINT);

    if ($data === false)
        echo "Internal error while encoding JSON.\n";

    response($data);
}
