<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// Funcion para validar el correo
function verificar_correo_puede_recibir($email) {
    // Obtener el dominio del correo electrónico
    $domain = substr(strrchr($email, "@"), 1);

    // Verificar si el dominio tiene registros MX
    try {
        $records = dns_get_record($domain, DNS_MX);
        if ($records === false || count($records) == 0) {
            return false; // No hay registros MX, no puede recibir correos
        }
    } catch (Exception $e) {
        return false; // Error al obtener registros MX
    }

    // Intentar conectarse al servidor SMTP del dominio
    foreach ($records as $mx) {
        $mx_server = $mx['target'];
        try {
            // Intentar conectarse al servidor SMTP en el puerto 25 o 587
            $connection = @stream_socket_client("tcp://$mx_server:25", $errno, $errstr, 30);

            if (!$connection) {
                // Si el puerto 25 no funciona, probar el puerto 587
                $connection = @stream_socket_client("tcp://$mx_server:587", $errno, $errstr, 30);
            }

            if (!$connection) {
                // Mostrar error de conexión si no se puede conectar
                echo "Error de conexión: $errstr ($errno)<br>";
                continue;
            }

            stream_set_timeout($connection, 30);

            // Leer respuesta de la conexión
            $response = fgets($connection, 1024);
            echo "Conectado: " . htmlspecialchars($response) . "<br>";

            // Verificar si la conexión fue exitosa
            if (strpos($response, '220') === false) {
                fclose($connection);
                continue;
            }

            // Enviar comando EHLO
            fwrite($connection, "EHLO example.com\r\n");
            $response = fgets($connection, 1024);
            echo "EHLO: " . htmlspecialchars($response) . "<br>";

            // Verificar respuesta del EHLO
            if (strpos($response, '250') === false) {
                fclose($connection);
                continue;
            }

            // Intentar iniciar una conexión segura
            fwrite($connection, "STARTTLS\r\n");
            $response = fgets($connection, 1024);
            echo "STARTTLS: " . htmlspecialchars($response) . "<br>";

            // Verificar respuesta de STARTTLS
            if (strpos($response, '220') === false) {
                fclose($connection);
                continue;
            }

            // Habilitar criptografía SSL/TLS
            stream_socket_enable_crypto($connection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

            // Repetir EHLO después de habilitar STARTTLS
            fwrite($connection, "EHLO example.com\r\n");
            $response = fgets($connection, 1024);
            echo "EHLO (después de STARTTLS): " . htmlspecialchars($response) . "<br>";

            // Verificar respuesta del segundo EHLO
            if (strpos($response, '250') === false) {
                fclose($connection);
                continue;
            }

            // Enviar comando MAIL FROM
            fwrite($connection, "MAIL FROM:<Gabrielbg21@hotmail.com>\r\n");
            $response = fgets($connection, 1024);
            echo "MAIL FROM: " . htmlspecialchars($response) . "<br>";

            // Enviar comando RCPT TO
            fwrite($connection, "RCPT TO:<$email>\r\n");
            $response = fgets($connection, 1024);
            echo "RCPT TO: " . htmlspecialchars($response) . "<br>";

            // Enviar comando QUIT
            fwrite($connection, "QUIT\r\n");
            fclose($connection);

            // Verificar si el correo es válido y puede recibir correos
            if (strpos($response, '250') !== false || strpos($response, '251') !== false) {
                return true;
            }
        } catch (Exception $e) {
            echo "Excepción: " . $e->getMessage() . "<br>";
            continue;
        }
    }

    return false; // No se pudo verificar el correo
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obtener el correo electrónico del POST
        $email = isset($_POST['email']) ? $_POST['email'] : '';

        // Validar si el correo electrónico fue proporcionado
        if (!$email) {
            throw new Exception('El correo electrónico es requerido.');
        }

        // Validar el formato del correo electrónico
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El formato del correo electrónico no es válido.');
        }

        // Verificar si el correo puede recibir emails
        $isValid = verificar_correo_puede_recibir($email);

        // Devolver el resultado en formato JSON
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
?>
