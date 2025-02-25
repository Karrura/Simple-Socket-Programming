<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function startClient()
    {
        $port = 3000;
        $host = '127.0.0.1';

        $aesKey = "0123456789abcdef0123456789abcdef"; // 256-bit key
        $blockSize = openssl_cipher_iv_length('AES-256-CBC');
        $aesIv = random_bytes($blockSize);

        // Plaintext message
        $plaintext = "Merisa Adha Azzahra. NIM 23/530077/PPA/";

        // Encrypt the message
        $encryptedMessage = openssl_encrypt($plaintext, 'AES-256-CBC', $aesKey, 0, $aesIv);

        // Create socket
        $clientSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($clientSocket === false) {
            return response('Socket creation failed: ' . socket_strerror(socket_last_error()), 500);
        }

        // Connect to server
        if (!socket_connect($clientSocket, $host, $port)) {
            return response('Socket connection failed: ' . socket_strerror(socket_last_error()), 500);
        }

        // Send IV to server
        socket_write($clientSocket, $aesIv, $blockSize);

        // Send encrypted message
        socket_write($clientSocket, $encryptedMessage, strlen($encryptedMessage));

        echo "Encrypted message sent: {$encryptedMessage}\n";

        socket_close($clientSocket);
    }
}
