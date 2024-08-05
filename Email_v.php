<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = isset($_POST['email']) ? $_POST['email'] : '';

        if (!$email) {
            throw new Exception('El correo electrónico es requerido.');
        }

        // Validar el formato del correo electrónico
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El formato del correo electrónico no es válido.');
        }

        // Verificar si el correo puede recibir emails
        $isValid = verificar_correo_puede_recibir($email);

        echo json_encode([
            'email' => $email,
            'valid' => $isValid,
        ]);
    } else {
        echo json_encode(['error' => 'Método de solicitud no válido']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

function verificar_correo_puede_recibir($email) {
    $domain = substr(strrchr($email, "@"), 1);

    // Verificar si el dominio tiene registros MX
    try {
        $records = dns_get_record($domain, DNS_MX);
        if ($records === false || count($records) == 0) {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }

    // Intentar conectarse al servidor SMTP del dominio usando TLS
    foreach ($records as $mx) {
        $mx_server = $mx['target'];
        try {
            // Conexión segura al servidor SMTP usando TLS en el puerto 587
            $contextOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ],
            ];
            $context = stream_context_create($contextOptions);
            $connection = @stream_socket_client("tls://$mx_server:587", $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $context);

            if (!$connection) {
                continue;
            }

            stream_set_timeout($connection, 20);
            $response = fgets($connection, 1024);

            fwrite($connection, "EHLO example.com\r\n");
            $response = fgets($connection, 1024);

            fwrite($connection, "MAIL FROM:<Gabrielbg21@hotmail.com>\r\n");
            $response = fgets($connection, 1024);

            fwrite($connection, "RCPT TO:<$email>\r\n");
            $response = fgets($connection, 1024);

            fwrite($connection, "QUIT\r\n");
            fclose($connection);

            // Verificar respuesta del servidor SMTP
            if (strpos($response, '250') !== false || strpos($response, '251') !== false) {
                return true;
            }
        } catch (Exception $e) {
            continue;
        }
    }

    return false;
}
?>
