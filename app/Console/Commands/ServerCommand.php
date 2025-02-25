<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ServerCommand extends Command
{
    protected $signature = 'socket:start-server';
    protected $description = 'Start the socket server';

    public function handle()
    {
        $port = 3000;
        $host = '127.0.0.1';

        $aesKey = "0123456789abcdef0123456789abcdef"; // 256-bit key
        $blockSize = openssl_cipher_iv_length('AES-256-CBC');

        // Create socket
        $serverSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($serverSocket === false) {
            $this->error('Socket creation failed: ' . socket_strerror(socket_last_error()));
            return 1;
        }

        // Bind the socket
        if (!socket_bind($serverSocket, $host, $port)) {
            $this->error('Socket bind failed: ' . socket_strerror(socket_last_error()));
            return 1;
        }

        // Listen for connections
        if (!socket_listen($serverSocket)) {
            $this->error('Socket listen failed: ' . socket_strerror(socket_last_error()));
            return 1;
        }

        $this->info("Server is running on {$host}:{$port}");

        while (true) {
            $clientSocket = socket_accept($serverSocket);
            if ($clientSocket === false) {
                $this->error('Socket accept failed: ' . socket_strerror(socket_last_error()));
                continue;
            }

            // Receive IV
            $aesIv = socket_read($clientSocket, $blockSize);

            // Receive encrypted message
            $encryptedMessage = socket_read($clientSocket, 1024);

            // Decrypt the message
            $decryptedMessage = openssl_decrypt($encryptedMessage, 'AES-256-CBC', $aesKey, 0, $aesIv);
            $this->info("Decrypted message from client: {$decryptedMessage}");

            socket_close($clientSocket);
        }

        socket_close($serverSocket);
    }
}
