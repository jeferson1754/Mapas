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
  echo "No se encontra<ron lugares";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" type="text/css" href="estilo.css">
  <title>Document</title>
</head>

<body>
  <div id="map"></div>

  <script>
    function iniciarMap() {

      var map = new google.maps.Map(document.getElementById('map'), {
        center: {
          lat: -33.61188888549805,
          lng: -70.57500457763672
        },
        zoom: 14
      });
      var coord = {
        lat: -33.61188888549805,
        lng: -70.57500457763672
      };


      // Función para agregar un marcador en una ubicación específica
      function addMarker(lat, lng, title) {
        var marker = new google.maps.Marker({
          position: {
            lat: lat,
            lng: lng
          },
          map: map,
          title: title
        });
      }

      // Añade un marcador en la ubicación actual
      addMarker(coord.lat, coord.lng, "Punto Inicial");
      <?php

      //$lat = "-33.71188888549805";
      //$lng = "-70.57500457763672";
      // Recorrer los datos de la base de datos

      while ($row = mysqli_fetch_assoc($query)) {
        // Extraer los valores de la fila
        $lat = $row['lat'];
        $lng = $row['lng'];
        $name = $row['name'];
        // Crear un marcador por cada punto y mostrar el nombre en un mensaje emergente
        echo "addMarker($lat, $lng,'$name');";
        // Hacer algo con los valores
      }

      ?>
    }
  </script>
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBDaeWicvigtP9xPv919E-RNoxfvC-Hqik&callback=iniciarMap"></script>

</body>




</html>