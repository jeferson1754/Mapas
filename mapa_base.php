<?php
// Incluir archivo de conexión a la base de datos
include 'bd.php';

// Preparar la consulta SQL de manera segura
$sql = "SELECT lat, lng, name, address FROM markers";

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
    'name' => htmlspecialchars($row['name']),
    'image' => htmlspecialchars($row['address']) // Aquí ahora es la ruta de la imagen
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
      max-width: 1500px;
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

    /* Estilos básicos y reseteo */
    .info-panel {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      max-width: 100%;
      margin: 0 auto;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      background-color: #ffffff;
    }

    /* Contador de ubicaciones */
    .location-counter {
      display: flex;
      align-items: center;
      padding: 12px 16px;
      border-radius: 6px;
      margin-bottom: 15px;
      font-weight: 500;
      color: #ffffff;
      transition: all 0.3s ease;
    }

    .location-counter i {
      margin-right: 10px;
      font-size: 18px;
    }

    .location-counter.success {
      background-color: #27ae60;
    }

    .location-counter.error {
      background-color: #e74c3c;
    }

    /* Descripción del panel */
    .panel-description {
      margin-bottom: 20px;
      color: #555;
      font-size: 16px;
      line-height: 1.5;
    }

    /* Contenedor de tabla con scroll horizontal */
    .locations-table-container {
      overflow-x: auto;
      margin-bottom: 15px;
      border-radius: 6px;
      box-shadow: 0 1px 6px rgba(0, 0, 0, 0.05);
    }

    /* Estilos de tabla */
    .locations-table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
      overflow: hidden;
    }

    .locations-table th,
    .locations-table td {
      padding: 12px 15px;
      border-bottom: 1px solid #eaeaea;
    }

    .locations-table th {
      background-color: #f5f5f5;
      font-weight: 600;
      color: #333;
      text-transform: uppercase;
      font-size: 14px;
      letter-spacing: 0.5px;
    }

    .locations-table tr:hover {
      background-color: #f9f9f9;
    }

    .locations-table tr:last-child td {
      border-bottom: none;
    }

    /* Imágenes en la tabla */
    .location-image img {
      width: 70px;
      height: 70px;
      object-fit: cover;
      border-radius: 6px;
      border: 2px solid #eaeaea;
      transition: transform 0.2s;
    }

    .location-image img:hover {
      transform: scale(1.05);
    }

    /* Nombre de ubicación */
    .location-name {
      font-weight: 500;
      color: #2c3e50;
    }

    /* Botón de acción */
    .btn-center {
      padding: 8px 14px;
      background-color: #3498db;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
      transition: background-color 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .btn-center i {
      margin-right: 6px;
    }

    .btn-center:hover {
      background-color: #2980b9;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .info-panel {
        padding: 15px;
      }

      .locations-table th,
      .locations-table td {
        padding: 10px;
      }

      .location-image img {
        width: 50px;
        height: 50px;
      }

      .btn-center {
        padding: 6px 10px;
        font-size: 13px;
      }
    }

    @media (max-width: 480px) {
      .location-counter {
        flex-direction: column;
        text-align: center;
        padding: 10px;
      }

      .location-counter i {
        margin-right: 0;
        margin-bottom: 5px;
      }
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




    .popup-header {
      color: black;
      font-size: 20px;
      padding: 10px 15px;
      font-weight: bold;
      text-align: center;
    }

    .popup-body {
      text-align: center;
    }

    .popup-img {
      margin-top: 10px;
    }
  </style>
</head>

<body>
  <div class="container">
    <?php include 'menu.php'; ?>

    <div class="row">
      <div class="col-12">

        <!-- Encabezado -->
        <div class="card shadow-sm">
          <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
              <h2 class="fw-bold m-0"><i class="fas fa-map me-2"></i> Mapa de Ubicaciones</h2>
              <?php if ($rowCount > 0): ?>
                <div class="badge bg-success fs-6 animate__animated animate__pulse">
                  <i class="fas fa-map-marker-alt me-1"></i>
                  <?php echo $rowCount; ?> Ubicación<?php echo $rowCount != 1 ? 'es' : ''; ?>
                </div>
              <?php else: ?>
                <div class="badge bg-danger fs-6">
                  <i class="fas fa-exclamation-triangle me-1"></i>
                  No se encontraron ubicaciones
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Controles del mapa -->
          <div class="px-3 py-2 border-bottom bg-light">
            <div class="d-flex gap-2 flex-wrap justify-content-center justify-content-md-start">
              <button class="btn btn-outline-primary" id="btnZoomIn"><i class="fas fa-search-plus me-1"></i>Acercar</button>
              <button class="btn btn-outline-primary" id="btnZoomOut"><i class="fas fa-search-minus me-1"></i>Alejar</button>
              <button class="btn btn-outline-secondary" id="btnReset"><i class="fas fa-sync me-1"></i>Reiniciar vista</button>
            </div>
          </div>

          <!-- Cuerpo -->
          <div class="card-body p-0">
            <div class="row g-0">

              <!-- Mapa -->
              <div class="col-lg-8">
                <div id="mapa" class="w-100" style="height: 500px;"></div>
              </div>

              <!-- Lista -->
              <div class="col-lg-4 border-start">
                <?php if ($rowCount > 0): ?>
                  <div class="p-3">
                    <h5 class="fw-bold mb-3"><i class="fas fa-list-ul me-2"></i>Listado de ubicaciones</h5>

                    <div class="table-responsive">
                      <table class="table table-hover location-table align-middle mb-0">
                        <thead class="table-light">
                          <tr>
                            <th class="text-center">Ubicación</th>
                            <th class="text-center">Imagen</th>
                            <th class="text-center">Acción</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($markers as $index => $marker): ?>
                            <tr class="location-item" data-lat="<?php echo $marker['lat']; ?>" data-lng="<?php echo $marker['lng']; ?>">
                              <td class="text-center"><?php echo $marker['name']; ?></td>
                              <td class="text-center">
                                <img src="<?php echo $marker['image']; ?>" class="img-thumbnail" alt="imagen" width="50">
                              </td>
                              <td class="text-center">
                                <button class="btn btn-sm btn-outline-info" onclick="centrarEn(<?php echo $marker['lat']; ?>, <?php echo $marker['lng']; ?>, '<?php echo addslashes($marker['name']); ?>')">
                                  <i class="fas fa-crosshairs me-1"></i>Ver
                                </button>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                <?php else: ?>
                  <div class="p-4 text-center">
                    <div class="alert alert-warning">
                      <i class="fas fa-exclamation-triangle me-2"></i>
                      No hay ubicaciones disponibles para mostrar
                    </div>
                    <p class="mb-0">Añade ubicaciones para visualizarlas en el mapa.</p>
                  </div>
                <?php endif; ?>
              </div>

            </div>
          </div>
        </div>

      </div>
    </div>


    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
      // Coordenadas iniciales (Santiago de Chile)
      const initialCoords = [-33.4889, -70.6693];
      const initialZoom = 11;

      // Crear el mapa
      const mapa = L.map('mapa', {
        zoomControl: false,
        minZoom: 3,
        maxZoom: 18
      }).setView(initialCoords, initialZoom);

      // Capa base
      L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 19
      }).addTo(mapa);

      // Marcador fijo en Santiago de Chile
      const santiagoIcon = L.icon({
        iconUrl: 'https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/images/marker-icon.png',
        shadowUrl: 'https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
      });


      // Cargar marcadores desde PHP
      const dbMarkers = <?php echo json_encode($markers); ?>;

      const markersGroup = L.layerGroup();

      dbMarkers.forEach(function(point) {
        const popupContent = `
        <div class="custom-popup">
          <div class="popup-header">${point.name}</div>
          <div class="popup-body">
            <img src="${point.image}" class="popup-img" alt="${point.name}" width="150">
          </div>
        </div>
      `;



        L.marker([point.lat, point.lng])
          .bindPopup(popupContent)
          .addTo(markersGroup);
      });

      markersGroup.addTo(mapa);


      function centrarEn(lat, lng) {
        mapa.setView([lat, lng], 16); // Ajusta el zoom si deseas otro nivel
      }

      // Botones de control
      document.getElementById('btnZoomIn').addEventListener('click', function() {
        mapa.zoomIn();
      });

      document.getElementById('btnZoomOut').addEventListener('click', function() {
        mapa.zoomOut();
      });

      document.getElementById('btnReset').addEventListener('click', function() {
        mapa.setView(initialCoords, initialZoom);
      });

      // Ajustar mapa en redimensionamiento
      window.addEventListener('resize', function() {
        mapa.invalidateSize();
      });
    </script>

</body>

</html>