<?php
// Incluir archivo de conexión a la base de datos
include 'bd.php';

// Preparar la consulta SQL de manera segura
$sql = "SELECT lat, lng, name FROM markers";

// Preparar y ejecutar la consulta usando consultas preparadas
$stmt = $conexion->prepare($sql);
$stmt->execute();

// Obtener el resultado
$result = $stmt->get_result();
$rowCount = $result->num_rows;

// Almacenar los marcadores en un array para usarlos en JavaScript
$markers = [];
while ($row = $result->fetch_assoc()) {
  $markers[] = [
    'lat' => (float)$row['lat'],
    'lng' => (float)$row['lng'],
    'name' => htmlspecialchars($row['name'])
  ];
}

// Cerrar la consulta
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mapa de Ubicaciones</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      padding: 20px;
      background-color: #f5f5f5;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    h1 {
      color: #2c3e50;
      margin-bottom: 20px;
      text-align: center;
    }

    .info-panel {
      background-color: #f8f9fa;
      padding: 15px;
      border-radius: 5px;
      margin-bottom: 20px;
      border-left: 5px solid #3498db;
    }

    #mapa {
      width: 100%;
      height: 500px;
      border-radius: 10px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .location-counter {
      display: inline-block;
      padding: 8px 15px;
      border-radius: 20px;
      font-weight: bold;
      color: white;
      background-color: #3498db;
      margin-bottom: 15px;
    }

    .map-controls {
      margin-top: 15px;
      text-align: center;
    }

    .btn {
      padding: 8px 15px;
      margin: 5px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      background-color: #3498db;
      color: white;
      font-weight: bold;
      transition: background-color 0.3s;
    }

    .btn:hover {
      background-color: #2980b9;
    }

    .btn i {
      margin-right: 5px;
    }

    @media (max-width: 768px) {
      #mapa {
        height: 350px;
      }

      .container {
        padding: 10px;
      }
    }
  </style>
</head>

<body>
  <div class="container">
    <?php include 'menu.php'; ?>

    <h1>Mapa de Ubicaciones</h1>

    <div class="info-panel">
      <?php if ($rowCount > 0): ?>
        <div class="location-counter">
          <i class="fas fa-map-marker-alt"></i>
          Se encontraron <?php echo $rowCount; ?> ubicaciones
        </div>
      <?php else: ?>
        <div class="location-counter" style="background-color: #e74c3c;">
          <i class="fas fa-exclamation-triangle"></i>
          No se encontraron ubicaciones
        </div>
      <?php endif; ?>

      <p>Explore las ubicaciones en el mapa interactivo a continuación.</p>
    </div>

    <div id="mapa"></div>

    <div class="map-controls">
      <button class="btn" id="btnZoomIn"><i class="fas fa-search-plus"></i> Acercar</button>
      <button class="btn" id="btnZoomOut"><i class="fas fa-search-minus"></i> Alejar</button>
      <button class="btn" id="btnReset"><i class="fas fa-sync"></i> Reiniciar vista</button>
    </div>
  </div>

  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script>
    // Coordenadas iniciales (Santiago de Chile)
    const initialCoords = [-33.4489, -70.6693];
    const initialZoom = 12;

    // Crear el mapa con opciones mejoradas
    const mapa = L.map('mapa', {
      zoomControl: false, // Desactivamos los controles por defecto para usar nuestros botones
      minZoom: 3,
      maxZoom: 18
    }).setView(initialCoords, initialZoom);

    // Añadir mapa base con mejor atribución
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(mapa);

    // Añadir un marcador personalizado para Santiago de Chile
    const santiagoIcon = L.icon({
      iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
      shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
      iconSize: [25, 41],
      iconAnchor: [12, 41],
      popupAnchor: [1, -34]
    });

    L.marker([-33.4489, -70.6693], {
        icon: santiagoIcon
      })
      .addTo(mapa)
      .bindPopup('<strong>Santiago de Chile</strong><br>Capital de Chile');

    // Añadir un círculo en Puente Alto con estilo mejorado
    L.circle([-33.6117, -70.5758], {
      color: '#e74c3c',
      fillColor: '#e74c3c',
      fillOpacity: 0.3,
      radius: 3000
    }).addTo(mapa).bindPopup('<strong>Puente Alto</strong>');

    // Añadir los marcadores desde la base de datos
    const dbMarkers = <?php echo json_encode($markers); ?>;

    // Crear un grupo de marcadores para facilitar operaciones en conjunto
    const markersGroup = L.layerGroup();

    // Añadir cada marcador desde la base de datos
    dbMarkers.forEach(function(point) {
      L.marker([point.lat, point.lng])
        .bindPopup('<strong>' + point.name + '</strong>')
        .addTo(markersGroup);
    });

    // Añadir el grupo de marcadores al mapa
    markersGroup.addTo(mapa);

    // Funcionalidad para los botones
    document.getElementById('btnZoomIn').addEventListener('click', function() {
      mapa.zoomIn();
    });

    document.getElementById('btnZoomOut').addEventListener('click', function() {
      mapa.zoomOut();
    });

    document.getElementById('btnReset').addEventListener('click', function() {
      mapa.setView(initialCoords, initialZoom);
    });

    // Hacer que el mapa se ajuste automáticamente cuando cambie el tamaño de la ventana
    window.addEventListener('resize', function() {
      mapa.invalidateSize();
    });
  </script>
</body>

</html>