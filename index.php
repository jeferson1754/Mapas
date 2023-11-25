<!DOCTYPE html>
<html>

<head>
    <title>Mapa con botones</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>

<body>
    <?php
    include 'menu.php';
    ?>
    <!-- Aquí se mostrará el mapa -->
    <div id="map" style="width: 100%; height: 400px;"></div>

    <!-- Botones para obtener la ubicación actual, agregar el marcador y agregar marcador en la ubicación clicada -->
    <button onclick="obtenerUbicacionActual()">Obtener Ubicación Actual</button>
    <br>
    <form action="insertar_marcador.php" method="POST">
        <label for="name">Nombre:</label>
        <input type="text" id="name" name="name" required>
        <label for="address">Dirección:</label>
        <input type="text" id="address" name="address" required>
        <button type="submit">Agregar marcador</button>
        <!--        -->
        <br>
        <input type="text" id="lati" name="lati">
        <input type="text" id="long" name="long">

    </form>
    <script>
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(function(position) {
                // Obtener las coordenadas
                var latitud = position.coords.latitude;
                var longitud = position.coords.longitude;

                // Mostrarlas en el HTML
                document.getElementById("lati").value = latitud;
                document.getElementById("long").value = longitud;
            });
        }
    </script>

    <script>
        var map = L.map('map').setView([-33.4489, -70.6693], 13); // Ubicación inicial y nivel de zoom
        var marker;

        function onLocationFound(e) {
            var radius = e.accuracy / 2;

            if (marker) {
                map.removeLayer(marker);
            }

            // Utiliza un icono personalizado para la ubicación actual
            var ubicacionIcono = L.icon({
                iconUrl: 'https://leafletjs.com/examples/custom-icons/leaf-green.png',
                iconSize: [38, 95],
                iconAnchor: [22, 94],
                popupAnchor: [-3, -76],
            });

            marker = L.marker(e.latlng, {
                    icon: ubicacionIcono
                }).addTo(map)
                .bindPopup("Estás dentro de " + radius + " metros de este punto").openPopup();

            L.circle(e.latlng, radius).addTo(map);

            // Establece la vista con el máximo nivel de zoom con animación
            map.flyTo(e.latlng, 18, {
                duration: 2, // Duración de la animación en segundos
            });
        }

        function onLocationError(e) {
            alert("Error al obtener la ubicación: " + e.message);
        }

        // Añade un mapa base de OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        function obtenerUbicacionActual() {
            map.locate({
                setView: false,
                maxZoom: 18,
                enableHighAccuracy: true
            });
        }

        function agregarMarcador() {
            if (marker) {
                map.removeLayer(marker);
            }

            // Obtén la ubicación actual y agrega un nuevo marcador sin animación
            map.locate({
                setView: false,
                maxZoom: 18,
                enableHighAccuracy: true,
                watch: false
            });

            map.on('locationfound', function(e) {
                var ubicacionMarcador = e.latlng;
                marker = L.marker(ubicacionMarcador).addTo(map)
                    .bindPopup("Marcador en esta ubicación").openPopup();

                // Establece la vista en la ubicación del marcador con el máximo nivel de zoom sin animación
                map.setView(ubicacionMarcador, 18, {
                    animate: false
                });
            });
        }

        // Evento de clic en el mapa para agregar marcador
        map.on('click', function(e) {
            agregarMarcadorEnUbicacionClicada(e.latlng);
        });
        /*
        function agregarMarcadorEnUbicacionClicada(latlng) {
            if (marker) {
                map.removeLayer(marker);
            }

            marker = L.marker(latlng).addTo(map)
                .bindPopup("Marcador en esta ubicación").openPopup();

            // Establece la vista en la ubicación del marcador con el máximo nivel de zoom sin animación
            map.setView(latlng, 18, { animate: false });
        }*/

        // Escucha el evento de ubicación encontrada
        map.on('locationfound', onLocationFound);
        map.on('locationerror', onLocationError);
    </script>
</body>

</html>