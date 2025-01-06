<?php
declare(strict_types=1);

// Función para verificar los enlaces
function checkLink(string $url): string|null {

    // Si la URL no es válida, devolvemos un NULL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return null; 
    }

    // Obtenemos los headers HTTP. get_headers() no lanza excepciones, por lo que usamos el operador de control de errores '@'
    $headers = @get_headers($url);

    // Si no se obtienen los headers, el enlace está caído
    if (!$headers) {
        return null; // Enlace inaccesible
    }

    // Analizamos el código de estado HTTP
    $statusCode = substr((string) $headers[0], 9, 3);
    return $statusCode; // Devuelve el código de estado (ej. '404', '200', etc.)
}


function checkBrokenLinks(string $host, string $username, string $password, string $dbname, string $table, string $column): void {
    
    // Intentamos la conexión a la base de datos
    $mysqli = new mysqli($host, $username, $password, $dbname);

    // Verificamos si hay errores de conexión
    if ($mysqli->connect_error) {
        die("Conexión fallida: " . $mysqli->connect_error);
    }

    echo "Conexión exitosa a la base de datos\n";

    // Realizamos la consulta para obtener los enlaces
    $sql = sprintf('SELECT %s FROM %s', $column, $table);
    if (!$result = $mysqli->query($sql)) {
        die("Error en la consulta: " . $mysqli->error);
    }

    $totalLinks = 0;
    $accessibleLinks = 0;
    $brokenLinks = [
        '404' => ['count' => 0, 'urls' => []],
        '500' => ['count' => 0, 'urls' => []],
        'other' => ['count' => 0, 'urls' => []]
    ];

    // Recorremos los resultados
    while ($row = $result->fetch_assoc()) {

        $url = $row[$column];

        if (empty($url)) {
            continue;
        } // Ignoramos los enlaces vacíos
   
        ++$totalLinks;

        // Verificamos el enlace
        $status = checkLink($url);

        if ($status === null) {
            ++$brokenLinks['other']['count'];
            $brokenLinks['other']['urls'][] = $url;
        } else {
            switch ($status) {
                case '404':
                    ++$brokenLinks['404']['count'];
                    $brokenLinks['404']['urls'][] = $url;
                    break;
                case '500':
                    ++$brokenLinks['500']['count'];
                    $brokenLinks['500']['urls'][] = $url;
                    break;
                default:
                    ++$accessibleLinks;
                    break;
            }
        }
    }

    // Cerramos la conexión a la base de datos
    $mysqli->close();

    // Imprimimos el resumen
    echo $totalLinks . ' links verificados:
';
    echo " - {$accessibleLinks} links accesibles correctamente\n";

    echo " - " . $brokenLinks['404']['count'] . " links con errores 404: " . implode(", ", $brokenLinks['404']['urls']) . "\n";
    echo " - " . $brokenLinks['500']['count'] . " links con errores 500: " . implode(", ", $brokenLinks['500']['urls']) . "\n";
    echo " - " . $brokenLinks['other']['count'] . " links con otros errores: " . implode(", ", $brokenLinks['other']['urls']) . "\n";
}

// Datos de entrada
$host = "localhost"; // Host
$username = "root"; // Usuario
$password = ""; // Contraseña
$dbname = "ejercicio0"; // Base de datos
$table = "posts"; // Nombre de la tabla
$column = "body"; // Nombre del campo que contiene los enlaces

// Llamamos a la función
checkBrokenLinks($host, $username, $password, $dbname, $table, $column);

?>
