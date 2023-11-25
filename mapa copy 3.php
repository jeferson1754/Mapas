<!DOCTYPE html>
<html>

<head>
    <title>Obtener Coordenadas</title>
    <style>
        #map {
            height: 300px;
            width: 100%;
        }
    </style>
    <script>
        function initMap() {
            var map = new google.maps.Map(document.getElementById('map'), {
                center: {
                    lat: 0,
                    lng: 0
                },
                zoom: 2
            });

            // Función para obtener las coordenadas de un lugar utilizando la API de Geocodificación
            function getCoordinates() {
                var geocoder = new google.maps.Geocoder();
                var address = document.getElementById('location-input').value;

                geocoder.geocode({
                    'address': address
                }, function(results, status) {
                    if (status === 'OK') {
                        var location = results[0].geometry.location;
                        map.setCenter(location);
                        new google.maps.Marker({
                            map: map,
                            position: location
                        });
                    } else {
                        alert('Error al obtener las coordenadas: ' + status);
                    }
                });
            }

            // Event listener para el botón de obtener coordenadas
            document.getElementById('get-coordinates-btn').addEventListener('click', getCoordinates);
        }
    </script>
</head>

<body>
    <input type="text" id="location-input" placeholder="Ingrese el lugar">
    <button id="get-coordinates-btn">Obtener Coordenadas</button>
    <div id="map"></div>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBDaeWicvigtP9xPv919E-RNoxfvC-Hqik&callback=initMap"></script>
</body>

</html>