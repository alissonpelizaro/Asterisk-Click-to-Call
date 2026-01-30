<?php
require_once __DIR__ . '/AsteriskClickToCall.php';

$config = [
    'host'     => '172.0.0.1',
    'port'     => '5038',
    'protocol' => 'tcp',
    'username' => 'user',
    'password' => 'secret',
    'ws'       => false
];

$call = [
    'source'   => '1003',
    'target'   => '1006',
    'context'  => '1-GRam-Grupo_1',
    'callerid' => 'Alisson'
];

try {
    $asterisk = new AsteriskClickToCall($config);
    $asterisk->connect();
    $asterisk->login();
    $asterisk->originate($call);
    $asterisk->close();

    echo "Call successfully initiated\n";
} catch (Throwable $e) {
    echo "Error: {$e->getMessage()}\n";
}
