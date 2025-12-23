<?php
include 'bd.php';

// Consulta SQL
$sql = "SELECT lat, lng, name, address FROM markers";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$rowCount = $result->num_rows;

$markers = [];
while ($row = $result->fetch_assoc()) {
  $markers[] = [
    'lat' => (float)$row['lat'],
    'lng' => (float)$row['lng'],
    'name' => htmlspecialchars($row['name']),
    'image' => htmlspecialchars($row['address'])
  ];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mapa de Ubicaciones Premium</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

  <style>
    :root {
      --primary-color: #4361ee;
      --secondary-color: #3f37c9;
      --bg-light: #f8f9fa;
    }

    body {
      background-color: var(--bg-light);
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .main-card {
      border: none;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }

    #mapa {
      height: 500px;
      width: 100%;
      z-index: 1;
    }

    /* Lista de ubicaciones estilo Sidebar */
    .location-list-container {
      max-height: 500px;
      overflow-y: auto;
      background: white;
    }

    .location-item {
      cursor: pointer;
      transition: all 0.2s;
      border-left: 4px solid transparent;
    }

    .location-item:hover {
      background-color: #f1f4ff;
      border-left-color: var(--primary-color);
    }

    .img-thumb-custom {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 8px;
    }

    /* Popups de Leaflet */
    .leaflet-popup-content-wrapper {
      border-radius: 12px;
      padding: 0;
    }

    .custom-popup img {
      border-radius: 8px 8px 0 0;
      width: 100%;
      height: 120px;
      object-fit: cover;
    }

    .popup-info {
      padding: 10px;
      text-align: center;
    }

    /* Ajustes Responsivos */
    @media (max-width: 991.98px) {
      #mapa {
        height: 350px;
      }

      .location-list-container {
        max-height: 400px;
      }
    }
  </style>
</head>

<body>

  <div class="container py-4">
    <?php include 'menu.php'; ?>

    <div class="main-card card">
      <div class="card-header bg-white py-3 border-bottom">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <div>
            <h4 class="mb-0 fw-bold text-dark">
              <i class="fas fa-map text-primary me-2"></i>Explorar Ubicaciones
            </h4>
            <small class="text-muted">Visualiza y gestiona tus puntos registrados</small>
          </div>
          <span class="badge bg-primary rounded-pill px-3 py-2">
            <i class="fas fa-map-marker-alt me-1"></i> <?php echo $rowCount; ?> Registros
          </span>
        </div>
      </div>

      <div class="bg-light p-2 d-flex gap-2 border-bottom overflow-x-auto">
        <button class="btn btn-sm btn-white border shadow-sm" id="btnReset"><i class="fas fa-home"></i></button>
        <button class="btn btn-sm btn-white border shadow-sm" id="btnZoomIn"><i class="fas fa-plus"></i></button>
        <button class="btn btn-sm btn-white border shadow-sm" id="btnZoomOut"><i class="fas fa-minus"></i></button>
      </div>

      <div class="row g-0">
        <div class="col-lg-8 border-end">
          <div id="mapa"></div>
        </div>

        <div class="col-lg-4">
          <div class="location-list-container">
            <div class="list-group list-group-flush">
              <?php if ($rowCount > 0): ?>
                <?php foreach ($markers as $index => $marker): ?>
                  <div class="list-group-item location-item p-3"
                    onclick="centrarEn(<?php echo $marker['lat']; ?>, <?php echo $marker['lng']; ?>, '<?php echo addslashes($marker['name']); ?>')">
                    <div class="d-flex align-items-center gap-3">
                      <img src="<?php echo $marker['image']; ?>" class="img-thumb-custom shadow-sm">
                      <div class="flex-grow-1">
                        <h6 class="mb-1 fw-bold text-dark"><?php echo $marker['name']; ?></h6>
                        <p class="mb-0 text-muted small" id="direccion-<?php echo $index; ?>">
                          <i class="fas fa-spinner fa-spin"></i> Obteniendo dirección...
                        </p>
                      </div>
                      <i class="fas fa-chevron-right text-light"></i>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="p-5 text-center">
                  <i class="fas fa-map-pin fa-3x text-light mb-3"></i>
                  <p class="text-muted">No hay ubicaciones para mostrar</p>
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
    const initialCoords = [-33.4889, -70.6693];
    const initialZoom = 12;

    // Inicializar Mapa
    const mapa = L.map('mapa', {
      zoomControl: false
    }).setView(initialCoords, initialZoom);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
      attribution: '&copy; OpenStreetMap'
    }).addTo(mapa);

    const dbMarkers = <?php echo json_encode($markers); ?>;
    const markersGroup = L.featureGroup();

    // Icono Personalizado
    const customIcon = L.icon({
      iconUrl: 'https://cdn-icons-png.flaticon.com/512/2776/2776067.png',
      iconSize: [38, 38],
      iconAnchor: [19, 38],
      popupAnchor: [0, -34]
    });

    dbMarkers.forEach((point, index) => {
      const popupContent = `
            <div class="custom-popup">
                <img src="${point.image}">
                <div class="popup-info">
                    <strong style="display:block; font-size:14px;">${point.name}</strong>
                    <small class="text-muted">Lat: ${point.lat}</small>
                </div>
            </div>`;

      const marker = L.marker([point.lat, point.lng], {
          icon: customIcon
        })
        .bindPopup(popupContent);

      marker.addTo(markersGroup);

      // Reverse Geocoding (Nominatim)
      fetch(`https://nominatim.openstreetmap.org/reverse?lat=${point.lat}&lon=${point.lng}&format=json`)
        .then(res => res.json())
        .then(data => {
          const display = data.display_name.split(',').slice(0, 2).join(',');
          document.getElementById('direccion-' + index).innerHTML = `<i class="fas fa-map-marker-alt me-1"></i> ${display}`;
        })
        .catch(() => {
          document.getElementById('direccion-' + index).innerText = 'Ubicación registrada';
        });
    });

    markersGroup.addTo(mapa);

    // Funciones de Navegación
    function centrarEn(lat, lng, name) {
      mapa.flyTo([lat, lng], 16, {
        animate: true,
        duration: 1.5
      });
      // Abrir popup automáticamente al hacer clic en la lista
      markersGroup.eachLayer(layer => {
        if (layer.getLatLng().lat === lat && layer.getLatLng().lng === lng) {
          layer.openPopup();
        }
      });

      // En móviles, hacer scroll hacia el mapa
      if (window.innerWidth < 992) {
        document.getElementById('mapa').scrollIntoView({
          behavior: 'smooth'
        });
      }
    }

    // Botones de Control
    document.getElementById('btnZoomIn').onclick = () => mapa.zoomIn();
    document.getElementById('btnZoomOut').onclick = () => mapa.zoomOut();
    document.getElementById('btnReset').onclick = () => mapa.setView(initialCoords, initialZoom);

    // Ajustar mapa
    window.onload = () => mapa.invalidateSize();
  </script>

</body>

</html>