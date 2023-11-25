<!DOCTYPE html>
<html>

<head>
    <title>Maps</title>
    <link rel="stylesheet" type="text/css" href="estilo.css">
</head>

<body>
    <?php
    include 'menu.php';
    ?>
    <div id="map"></div>
    <button id="get-location-btn">Obtener Ubicación Actual</button>
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

    <script src="script.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBDaeWicvigtP9xPv919E-RNoxfvC-Hqik&callback=iniciarMap"></script>
</body>

</html>