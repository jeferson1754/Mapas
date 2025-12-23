<?php
// Incluir archivo de conexión a la base de datos
include 'bd.php';

$sql = "SELECT lat, lng, name, address FROM markers";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

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
    <title>Mapa Interactivo Pro</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        :root {
            --primary-color: #3498db;
            --dark-bg: #34495e;
            --success-color: #2ecc71;
            --accent-color: #e74c3c;
            --border-radius: 12px;
            --shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body,
        html {
            height: 100%;
            width: 100%;
            overflow: hidden;
            background: #f0f2f5;
        }

        .main-wrapper {
            position: relative;
            height: 100vh;
            width: 100vw;
        }

        #map {
            height: 80%;
            width: 100%;
            z-index: 1;
        }

        .fab-add {
            position: absolute;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            cursor: pointer;
            box-shadow: var(--shadow);
            z-index: 1000;
            transition: var(--transition);
            border: none;
        }

        .fab-add2 {
            position: absolute;
            bottom: 100px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: var(--success-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            cursor: pointer;
            box-shadow: var(--shadow);
            z-index: 1000;
            transition: var(--transition);
            border: none;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 500px;
            position: relative;
            box-shadow: var(--shadow);
            animation: slideUp 0.4s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .coord-display {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
            margin-bottom: 15px;
            font-size: 14px;
        }

        .btn-block {
            width: 100%;
            padding: 15px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateX(200%);
            transition: var(--transition);
            z-index: 3000;
        }

        .notification.show {
            transform: translateX(0);
        }

        .current-loc-popup {
            color: #2980b9;
            font-weight: bold;
            text-align: center;
        }

        .tooltip {
            position: absolute;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            z-index: 1001;
            pointer-events: none;
            opacity: 0;
        }
    </style>
</head>

<body>
    <div class="main-wrapper">
        <?php include 'menu.php'; ?>
        <div id="map"></div>
        <button class="fab-add" id="btnLocation"><i class="fa-solid fa-location-crosshairs"></i></button>
        <button class="fab-add2" id="openModalBtn"><i class="fas fa-plus"></i></button>
        <div class="tooltip" id="tooltip"></div>
    </div>

    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-map-marker-alt"></i> Nuevo Marcador</h3>
                <span class="close-modal" id="closeModal" style="cursor:pointer; font-size:24px;">&times;</span>
            </div>
            <form action="insertar_marcador.php" method="POST" id="markerForm" enctype="multipart/form-data">
                <div class="coord-display">
                    <strong>Coordenadas:</strong><br>
                    <span id="coordText">Detectando ubicación...</span>
                </div>
                <input type="hidden" id="lati" name="lati">
                <input type="hidden" id="long" name="long">
                <div class="form-group">
                    <label>Nombre del lugar:</label>
                    <input type="text" name="name" placeholder="Ej: Mi oficina" required>
                </div>
                <div class="form-group">
                    <label>Imagen:</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                <button type="submit" class="btn-block"><i class="fas fa-save"></i> Guardar Ubicación</button>
            </form>
        </div>
    </div>

    <div class="notification" id="notification">
        <i id="notifIcon" class="fas fa-check-circle"></i>
        <span id="notifText"></span>
    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // Icono rojo para DB
        const dbIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        const map = L.map('map', {
            zoomControl: false
        }).setView([0, 0], 2);
        L.control.zoom({
            position: 'topright'
        }).addTo(map);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        let userMarker = null;
        let tempMarker = null;

        // --- LÓGICA DE LOCALIZACIÓN ---
        function findMe() {
            showNotification("Buscando tu posición...", "#3498db");
            map.locate({
                setView: true,
                maxZoom: 16
            });
        }

        function onLocationFound(e) {
            const lat = e.latlng.lat.toFixed(6);
            const lng = e.latlng.lng.toFixed(6);

            // Llenar formulario
            document.getElementById('lati').value = lat;
            document.getElementById('long').value = lng;
            document.getElementById('coordText').innerHTML = `Lat: ${lat} <br> Lng: ${lng} (Tu ubicación actual)`;

            // Marcador de usuario (Pin azul estándar)
            if (userMarker) map.removeLayer(userMarker);
            userMarker = L.marker(e.latlng).addTo(map)
                .bindPopup('<b>Estás aquí</b>')
                .openPopup();

            showNotification("Ubicación actualizada");
        }

        map.on('locationfound', onLocationFound);
        map.on('locationerror', (e) => showNotification("No se pudo obtener la ubicación", "#e74c3c"));

        // Ejecutar al cargar la página
        findMe();

        // Botón de ubicación actual
        document.getElementById('btnLocation').onclick = findMe;
        // ------------------------------

        map.on('click', function(e) {
            const lat = e.latlng.lat.toFixed(6);
            const lng = e.latlng.lng.toFixed(6);
            document.getElementById('lati').value = lat;
            document.getElementById('long').value = lng;
            document.getElementById('coordText').innerHTML = `Lat: ${lat} <br> Lng: ${lng} (Punto manual)`;

            if (tempMarker) map.removeLayer(tempMarker);
            tempMarker = L.marker(e.latlng).addTo(map).bindPopup("Nuevo punto seleccionado").openPopup();
        });

        const dbMarkers = <?php echo json_encode($markers); ?>;
        dbMarkers.forEach(p => {
            const popupContent = `<b>${p.name}</b><br>${p.image ? `<img src="${p.image}" width="150" style="border-radius:4px; margin-top:5px">` : '<i>Sin imagen</i>'}`;
            L.marker([p.lat, p.lng], {
                icon: dbIcon
            }).addTo(map).bindPopup(popupContent);
        });

        const modal = document.getElementById('modalOverlay');
        document.getElementById('openModalBtn').onclick = () => modal.style.display = 'flex';
        document.getElementById('closeModal').onclick = () => modal.style.display = 'none';

        function showNotification(msg, color = "#2ecc71") {
            const notif = document.getElementById('notification');
            document.getElementById('notifText').innerText = msg;
            notif.style.borderLeft = `5px solid ${color}`;
            notif.classList.add('show');
            setTimeout(() => notif.classList.remove('show'), 3000);
        }

        const tooltip = document.getElementById('tooltip');
        map.on('mousemove', (e) => {
            tooltip.style.opacity = 1;
            tooltip.style.left = (e.containerPoint.x + 15) + 'px';
            tooltip.style.top = (e.containerPoint.y - 15) + 'px';
            tooltip.innerText = `${e.latlng.lat.toFixed(4)}, ${e.latlng.lng.toFixed(4)}`;
        });
        map.on('mouseout', () => tooltip.style.opacity = 0);
    </script>
</body>

</html>