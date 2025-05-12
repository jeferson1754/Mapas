<?php
$nombre = $_POST['nombre'] ?? '';
$directorioDestino = 'uploads/';

// Asegúrate de que el directorio exista
if (!is_dir($directorioDestino)) {
    mkdir($directorioDestino, 0777, true);
}

if (!empty($_FILES['imagen']['name'])) {
    $nombreArchivo = uniqid('img_') . '_' . basename($_FILES['imagen']['name']);
    $rutaDestino = $directorioDestino . $nombreArchivo;

    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
        // Aquí podrías guardar en la base de datos si quieres
        echo "Nombre: $nombre<br>Imagen subida correctamente: $rutaDestino";
    } else {
        echo "Error al subir la imagen.";
    }
} else {
    echo "Nombre: $nombre<br>Sin cambio de imagen.";
}
