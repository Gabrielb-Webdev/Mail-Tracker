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

        // Eliminar posibles esquemas como 'http://' o 'https://'
        $domain = preg_replace('/^https?:\/\//', '', $domain);

        // Verificar si el dominio existe
        $isValid = verificar_dominio_existe($domain);

        echo json_encode([
            'domain' => $domain,
            'exists' => $isValid,
        ]);
    } else {
        echo json_encode(['error' => 'Método de solicitud no válido']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

function verificar_dominio_existe($domain) {
    // Intentar obtener la dirección IP del dominio
    $ip = gethostbyname($domain);
    if ($ip === $domain) {
        return false; // No se pudo resolver el dominio
    }

    // Verificar si el dominio tiene registros A o CNAME
    try {
        $recordsA = dns_get_record($domain, DNS_A);
        $recordsCNAME = dns_get_record($domain, DNS_CNAME);
        if (count($recordsA) > 0 || count($recordsCNAME) > 0) {
            return true; // El dominio existe y es accesible
        }
    } catch (Exception $e) {
        return false; // Error al obtener registros DNS
    }

    return false; // No se encontraron registros DNS válidos
}
?>
