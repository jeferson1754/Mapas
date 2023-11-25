<?php
include 'bd.php';

// Preparar la consulta SQL
$sql = "SELECT lat, lng, name FROM markers";
// Ejecutar la consulta SQL y asignar el resultado a la variable $query
$query = mysqli_query($conexion, $sql);
// Obtener el número de filas afectadas
$rowCount = $query->num_rows;

$stmt = $conexion->prepare($sql);
// Ejecutar la consulta SQL
$stmt->execute();

// Comprobar si hay resultados
if ($rowCount > 0) {
  echo "Se encontraron $rowCount lugares";
} else {
  echo "No se encontraron lugares";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>

<style>
  #mapa {
    width: 600px;
    height: 400px;
    border: 1px solid black;
  }
</style>

<body>
  <div id="mapa"></div>
  <script>
    // Crear el mapa y establecer la ubicación y el zoom inicial
    var mapa = L.map('mapa').setView([-33.4489, -70.6693], 13);

    // Añadir un mapa base de OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
    }).addTo(mapa);

    // Añadir un marcador en Santiago de Chile
    L.marker([-33.4489, -70.6693]).addTo(mapa).bindPopup('Santiago de Chile');

    // Añadir un círculo en Puente Alto
    L.circle([-33.6117, -70.5758], {
      color: 'red',
      fillColor: '#f03',
      fillOpacity: 0.5,
    }).addTo(mapa).bindPopup('Puente Alto');

    <?php
    // Recorrer los datos de la base de datos
    while ($row = mysqli_fetch_assoc($query)) {
      // Extraer los valores de la fila
      $lat = $row['lat'];
      $lng = $row['lng'];
      $name = $row['name'];
      // Crear un marcador por cada punto y mostrar el nombre en un mensaje emergente
      echo "L.marker([$lat, $lng]).addTo(mapa).bindPopup('$name');";
      // Hacer algo con los valores
    }
    ?>
  </script>

</body>




</html>