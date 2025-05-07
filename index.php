<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa Interactivo</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --accent-color: #e74c3c;
            --light-bg: #f5f7fa;
            --dark-bg: #34495e;
            --success-color: #2ecc71;
            --text-color: #2c3e50;
            --light-text: #ecf0f1;
            --border-radius: 8px;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light-bg);
            color: var(--text-color);
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .header {
            background: var(--dark-bg);
            color: var(--light-text);
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .header p {
            opacity: 0.8;
            font-size: 14px;
        }

        .map-container {
            position: relative;
            width: 100%;
            height: 500px;
        }

        #map {
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .controls-overlay {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .map-button {
            background: white;
            border: none;
            border-radius: var(--border-radius);
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: var(--shadow);
            transition: var(--transition);
            width: 40px;
            height: 40px;
        }

        .map-button:hover {
            background: var(--primary-color);
            color: white;
        }

        .map-button i {
            font-size: 18px;
        }

        .active {
            background: var(--primary-color);
            color: white;
        }

        .form-container {
            padding: 20px;
            background: white;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
            align-items: center;
        }

        .form-group {
            flex: 1;
            min-width: 250px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--text-color);
        }

        .form-group input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        .coord-input {
            flex: 1;
            min-width: 120px;
        }

        .coordinates-container {
            background: #f8f9fa;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-top: 15px;
            border-left: 4px solid var(--primary-color);
        }

        .coordinates-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            color: var(--primary-color);
            font-weight: 600;
        }

        .btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .btn-success {
            background: var(--success-color);
        }

        .btn-location {
            background: var(--accent-color);
        }

        .btn-location:hover {
            background: #c0392b;
        }

        .footer {
            padding: 15px;
            text-align: center;
            background: var(--dark-bg);
            color: var(--light-text);
            font-size: 14px;
        }

        .tooltip {
            position: absolute;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 1001;
        }

        @media (max-width: 768px) {
            .container {
                border-radius: 0;
            }

            .map-container {
                height: 400px;
            }

            .form-row {
                flex-direction: column;
                gap: 10px;
            }

            .form-group {
                width: 100%;
            }
        }

        /* Estilos para notificaciones emergentes */
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: var(--border-radius);
            background: white;
            color: var(--text-color);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 2000;
            transform: translateY(100px);
            opacity: 0;
            transition: transform 0.3s, opacity 0.3s;
        }

        .notification.show {
            transform: translateY(0);
            opacity: 1;
        }

        .notification i {
            font-size: 20px;
        }

        .notification-success {
            border-left: 4px solid var(--success-color);
        }

        .notification-success i {
            color: var(--success-color);
        }

        .notification-error {
            border-left: 4px solid var(--accent-color);
        }

        .notification-error i {
            color: var(--accent-color);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-map-marked-alt"></i> Mapa Interactivo</h1>
            <p>Explora y marca ubicaciones en tiempo real</p>
        </div>

        <?php include 'menu.php'; ?>

        <div class="map-container">
            <div id="map"></div>
            <div class="controls-overlay">
                <button class="map-button" id="zoomIn" title="Acercar">
                    <i class="fas fa-plus"></i>
                </button>
                <button class="map-button" id="zoomOut" title="Alejar">
                    <i class="fas fa-minus"></i>
                </button>
                <button class="map-button" id="centerMap" title="Centrar mapa">
                    <i class="fas fa-crosshairs"></i>
                </button>
            </div>
            <div class="tooltip" id="tooltip"></div>
        </div>

        <div class="form-container">
            <form action="insertar_marcador.php" method="POST" id="markerForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name"><i class="fas fa-tag"></i> Nombre del lugar:</label>
                        <input type="text" id="name" name="name" placeholder="Ej: Casa" required>
                    </div>
                    <div class="form-group">
                        <label for="address"><i class="fas fa-map-pin"></i> Dirección:</label>
                        <input type="text" id="address" name="address" placeholder="Ej: Av. Principal 123" required>
                    </div>
                </div>

                <div class="coordinates-container">
                    <div class="coordinates-title">
                        <i class="fas fa-location-dot"></i> Coordenadas
                    </div>
                    <div class="form-row">
                        <div class="form-group coord-input">
                            <label for="lati">Latitud:</label>
                            <input type="text" id="lati" name="lati" readonly>
                        </div>
                        <div class="form-group coord-input">
                            <label for="long">Longitud:</label>
                            <input type="text" id="long" name="long" readonly>
                        </div>
                        <button type="button" class="btn btn-location" onclick="obtenerUbicacionActual()">
                            <i class="fas fa-location-crosshairs"></i> Ubicación actual
                        </button>
                    </div>
                </div>

                <div class="form-row" style="justify-content: flex-end; margin-top: 20px;">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus-circle"></i> Agregar marcador
                    </button>
                </div>
            </form>
        </div>

        <div class="footer">
            <p>&copy; 2025 Sistema de Mapas Interactivos | Desarrollado con <i class="fas fa-heart" style="color: #e74c3c;"></i></p>
        </div>
    </div>

    <div class="notification" id="notification">
        <i class="fas fa-info-circle"></i>
        <span id="notificationText">Notificación</span>
    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // Inicialización del mapa
        var map = L.map('map').setView([-33.4489, -70.6693], 13);
        var marker = null;
        var userLocationMarker = null;
        var userLocationCircle = null;
        var initialLocation = [-33.4489, -70.6693];

        // Configuración de la capa base del mapa
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);

        // Iconos personalizados
        var userIcon = L.icon({
            iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34]
        });

        // Controladores de eventos para los botones del mapa
        document.getElementById('zoomIn').addEventListener('click', function() {
            map.zoomIn();
        });

        document.getElementById('zoomOut').addEventListener('click', function() {
            map.zoomOut();
        });

        document.getElementById('centerMap').addEventListener('click', function() {
            map.setView(initialLocation, 13);
            showNotification('Mapa centrado en la ubicación predeterminada', 'success');
        });

        // Función para mostrar una notificación
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            const notificationText = document.getElementById('notificationText');
            
            notification.className = 'notification';
            notification.classList.add('show');
            notificationText.textContent = message;
            
            if (type === 'success') {
                notification.classList.add('notification-success');
                notification.querySelector('i').className = 'fas fa-check-circle';
            } else {
                notification.classList.add('notification-error');
                notification.querySelector('i').className = 'fas fa-exclamation-circle';
            }
            
            setTimeout(function() {
                notification.classList.remove('show');
            }, 3000);
        }

        // Función para obtener la ubicación actual
        function obtenerUbicacionActual() {
            showNotification('Obteniendo ubicación...', 'success');
            
            map.locate({
                setView: false,
                maxZoom: 18,
                enableHighAccuracy: true
            });
        }

        // Controlador de eventos cuando se encuentra la ubicación
        function onLocationFound(e) {
            const radius = e.accuracy / 2;
            const latitud = e.latlng.lat.toFixed(6);
            const longitud = e.latlng.lng.toFixed(6);

            // Actualizar los campos del formulario
            document.getElementById('lati').value = latitud;
            document.getElementById('long').value = longitud;

            // Eliminar marcadores anteriores si existen
            if (userLocationMarker) {
                map.removeLayer(userLocationMarker);
            }
            if (userLocationCircle) {
                map.removeLayer(userLocationCircle);
            }

            // Crear nuevo marcador y círculo
            userLocationMarker = L.marker(e.latlng, { icon: userIcon })
                .addTo(map)
                .bindPopup(`<strong>Tu ubicación actual</strong><br>Precisión: ${radius.toFixed(0)} metros`)
                .openPopup();

            userLocationCircle = L.circle(e.latlng, {
                radius: radius,
                color: '#3498db',
                fillColor: '#3498db',
                fillOpacity: 0.15
            }).addTo(map);

            // Animar el movimiento del mapa a la ubicación encontrada
            map.flyTo(e.latlng, 16, {
                duration: 1.5
            });

            showNotification('Ubicación actual obtenida correctamente', 'success');
        }

        // Controlador de eventos cuando ocurre un error al obtener la ubicación
        function onLocationError(e) {
            showNotification('Error al obtener la ubicación: ' + e.message, 'error');
        }

        // Evento de clic en el mapa para seleccionar ubicación
        map.on('click', function(e) {
            const latitud = e.latlng.lat.toFixed(6);
            const longitud = e.latlng.lng.toFixed(6);

            // Actualizar los campos del formulario
            document.getElementById('lati').value = latitud;
            document.getElementById('long').value = longitud;

            // Eliminar marcador anterior si existe
            if (marker) {
                map.removeLayer(marker);
            }

            // Crear nuevo marcador
            marker = L.marker(e.latlng)
                .addTo(map)
                .bindPopup('Ubicación seleccionada')
                .openPopup();

            showNotification('Ubicación seleccionada en el mapa', 'success');
        });

        // Tooltip para mostrar coordenadas al mover el mouse
        const tooltip = document.getElementById('tooltip');
        
        map.on('mousemove', function(e) {
            const lat = e.latlng.lat.toFixed(6);
            const lng = e.latlng.lng.toFixed(6);
            
            tooltip.innerText = `Lat: ${lat}, Lng: ${lng}`;
            tooltip.style.left = (e.containerPoint.x + 15) + 'px';
            tooltip.style.top = (e.containerPoint.y - 25) + 'px';
            tooltip.style.opacity = 1;
        });

        map.on('mouseout', function() {
            tooltip.style.opacity = 0;
        });

        // Verificar si hay soporte para geolocalización
        if ("geolocation" in navigator) {
            // Precargar la ubicación si está disponible
            navigator.geolocation.getCurrentPosition(function(position) {
                const latitud = position.coords.latitude;
                const longitud = position.coords.longitude;

                // Actualizar los campos del formulario
                document.getElementById('lati').value = latitud.toFixed(6);
                document.getElementById('long').value = longitud.toFixed(6);
            }, function(error) {
                console.log("Error obteniendo ubicación inicial:", error.message);
            });
        }

        // Validación del formulario
        document.getElementById('markerForm').addEventListener('submit', function(e) {
            const lat = document.getElementById('lati').value;
            const lng = document.getElementById('long').value;
            
            if (!lat || !lng) {
                e.preventDefault();
                showNotification('Debes seleccionar una ubicación en el mapa', 'error');
            }
        });

        // Registrar eventos de localización
        map.on('locationfound', onLocationFound);
        map.on('locationerror', onLocationError);
    </script>
</body>

</html>