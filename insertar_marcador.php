<?php
include 'bd.php';

// Obtener los datos del marcador desde el formulario
$name = $_POST['name'];
$address = $_POST['address'];
$lat = $_POST['lati'];
$lng = $_POST['long'];

echo $name . "<br>";
echo $address . "<br>";
echo $lat . "<br>";
echo $lng . "<br>";

if (is_numeric($lat) && is_numeric($lng)) {
    // Preparar la sentencia SQL para insertar el marcador en la tabla
    $sql = "INSERT INTO markers (name, address, lat, lng) VALUES (?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssdd", $name, $address, $lat, $lng);

    // Ejecutar la sentencia SQL y comprobar si se insertó el marcador
    if ($stmt->execute()) {
        echo "Marcador insertado con éxito";
    } else {
        echo "Error al insertar el marcador: " . $stmt->error;
    }

    // Cerrar la sentencia y la conexión
    $stmt->close();
    $conexion->close();
} else {
    echo $lat . "<br>";
    echo $lng . "<br>";
    echo "Coordenadas incorrectas";
}
