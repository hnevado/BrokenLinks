<?php
// Función para verificar los enlaces
function checkLink(string $url): string|null {

    // Si la URL no es válida, devolvemos un NULL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return null; 
    }

    // Obtenemos los headers HTTP
    $headers = @get_headers($url);

    // Si no se obtienen los headers, el enlace está caído
    if (!$headers) {
        return null; // Enlace inaccesible
    }

    // Analizamos el código de estado HTTP
    $statusCode = substr($headers[0], 9, 3);
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
    $sql = "SELECT $column FROM $table";
    if (!$result = $mysqli->query($sql)) {
        die("Error en la consulta: " . $mysqli->error);
    }

    $totalLinks = 0;
    $accessibleLinks = 0;
    $brokenLinks = [
        '404' => 0,
        '500' => 0,
        'other' => 0
    ];

    // Recorremos los resultados
    while ($row = $result->fetch_assoc()) {

        $url = $row[$column];

        if ($url === "")
            continue; // Ignoramos los enlaces vacíos
   
        $totalLinks++;

        // Verificamos el enlace
        $status = checkLink($url);

        if ($status === null) {
            $brokenLinks['other']++;
        } else {
            switch ($status) {
                case '404':
                    $brokenLinks['404']++;
                    break;
                case '500':
                    $brokenLinks['500']++;
                    break;
                default:
                    $accessibleLinks++;
                    break;
            }
        }
    }

    // Cerramos la conexión a la base de datos
    $mysqli->close();

    // Imprimimos el resumen
    echo "$totalLinks links verificados:\n";
    echo " - $accessibleLinks links accesibles correctamente\n";
    echo " - " . $brokenLinks['404'] . " links con errores 404\n";
    echo " - " . $brokenLinks['500'] . " links con errores 500\n";
    echo " - " . $brokenLinks['other'] . " links con otros errores\n";
}

// Datos de entrada
$host = "localhost"; // Host
$username = "root"; // Usuario
$password = ""; // Contraseña
$dbname = "test"; // Base de datos
$table = "posts"; // Nombre de la tabla
$column = "body"; // Nombre del campo que contiene los enlaces

// Llamamos a la función
checkBrokenLinks($host, $username, $password, $dbname, $table, $column);

?>