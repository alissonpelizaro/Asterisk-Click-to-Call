<?php

/**
 * Asterisk Click-to-Call via AMI
 *
 * Author: Alisson Pelizaro
 */

class AsteriskClickToCall
{
    private string $host;
    private string $port;
    private string $protocol;
    private string $username;
    private string $password;
    private bool $useWebSocket;
    private $socket;

    public function __construct(array $config)
    {
        $this->host         = $config['host'];
        $this->port         = $config['port'];
        $this->protocol     = $config['protocol'] ?? 'tcp';
        $this->username     = $config['username'];
        $this->password     = $config['password'];
        $this->useWebSocket = $config['ws'] ?? false;

        if ($this->useWebSocket) {
            $this->port .= '/ws';
        }
    }

    public function connect(): void
    {
        $this->socket = @stream_socket_client(
            "{$this->protocol}://{$this->host}:{$this->port}",
            $errno,
            $errstr,
            5
        );

        if (!$this->socket) {
            throw new RuntimeException("AMI connection failed: $errstr ($errno)");
        }
    }

    public function login(): void
    {
        $this->send([
            'Action'   => 'Login',
            'Username' => $this->username,
            'Secret'   => $this->password,
            'Events'   => 'off'
        ]);

        $response = $this->read();

        if (!str_contains($response, 'Success')) {
            throw new RuntimeException('AMI authentication failed');
        }
    }

    public function originate(array $call): void
    {
        $this->send([
            'Action'   => 'Originate',
            'Channel'  => "SIP/{$call['source']}",
            'Callerid' => $call['callerid'],
            'Exten'    => $call['target'],
            'Context'  => $call['context'],
            'Priority' => $call['priority'] ?? 0,
            'Async'    => 'yes'
        ]);

        $response = $this->read();

        if (!str_contains($response, 'Success')) {
            throw new RuntimeException('Call originate failed');
        }
    }

    public function close(): void
    {
        if ($this->socket) {
            fclose($this->socket);
        }
    }

    private function send(array $data): void
    {
        $payload = '';
        foreach ($data as $key => $value) {
            $payload .= "$key: $value\r\n";
        }
        $payload .= "\r\n";

        fwrite($this->socket, $payload);
    }

    private function read(): string
    {
        stream_set_timeout($this->socket, 2);
        return fread($this->socket, 4096);
    }
}
