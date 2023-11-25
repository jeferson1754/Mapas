
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

    var marker = new google.maps.Marker({
        position: coord,
        map: map
    });

    // Función para agregar un marcador en una ubicación específica
    function addMarker(location, title) {
        var marker = new google.maps.Marker({
            position: location,
            map: map,
            title: title,
        });
    }


    // Función para obtener la ubicación actual del usuario
    function getCurrentLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                var currentLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude

                };
                var latitud = position.coords.latitude;
                var longitud = position.coords.longitude;

                // Mostrarlas en el HTML
                document.getElementById("lati").value = latitud;
                document.getElementById("long").value = longitud;


                // Añade un marcador en la ubicación actual
                addMarker(currentLocation, "INACAP");

                // Centra el mapa en la ubicación actual
                map.setCenter(currentLocation);
            }, function (error) {
                console.error('Error getting current location:', error);
            });
        } else {
            alert('Geolocation is not supported by this browser.');
        }
    }

    // Event listener para el botón de obtener ubicación
    document.getElementById('get-location-btn').addEventListener('click', getCurrentLocation);
}


