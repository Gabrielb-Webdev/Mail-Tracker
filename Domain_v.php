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
        $domain = isset($_POST['domain']) ? $_POST['domain'] : '';

        if (!$domain) {
            throw new Exception('El dominio es requerido.');
        }

        // Eliminar esquemas como 'http://' o 'https://'
        $domain = preg_replace('/^https?:\/\//', '', $domain);
        $domain = rtrim($domain, '/');

        // Verificar si el dominio es válido
        $isValid = verificar_dominio_existe($domain);

        echo json_encode([
            'domain' => $domain,
            'exists' => $isValid,
            'url' => $isValid ? 'http://' . $domain : null,
        ]);
    } else {
        echo json_encode(['error' => 'Método de solicitud no válido']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

function verificar_dominio_existe($domain) {
    // Verificar si el dominio tiene una dirección IP válida
    $ip = gethostbyname($domain);
    if ($ip === $domain) {
        return false; // No se pudo resolver el dominio a una dirección IP
    }

    // Verificar si el dominio tiene registros DNS válidos
    try {
        $records = dns_get_record($domain, DNS_ANY);
        if (count($records) > 0) {
            // Hacer una solicitud HTTP para confirmar si el dominio es accesible
            $url = 'http://' . $domain;
            $headers = @get_headers($url);

            if ($headers && strpos($headers[0], '200') !== false) {
                return true; // Dominio válido y accesible
            } else {
                // Intentar con HTTPS
                $url = 'https://' . $domain;
                $headers = @get_headers($url);
                if ($headers && strpos($headers[0], '200') !== false) {
                    return true; // Dominio válido y accesible con HTTPS
                }
            }
        }
    } catch (Exception $e) {
        return false; // Error al obtener registros DNS
    }

    return false; // No se encontraron registros DNS válidos o el dominio no es accesible
}
?>
