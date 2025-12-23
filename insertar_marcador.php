<?php
include 'bd.php';

// Validar si los datos de texto y coordenadas están presentes
if (isset($_POST['name'], $_POST['lati'], $_POST['long'])) {
    $name = $_POST['name'];
    $lat = floatval($_POST['lati']);
    $lng = floatval($_POST['long']);
    $ruta_final = ""; // Valor por defecto si no hay imagen

    // Validar coordenadas numéricas
    if (!is_numeric($lat) || !is_numeric($lng)) {
        echo "❌ Coordenadas inválidas.";
        exit;
    }

    // 1. Procesar imagen SOLO si se ha subido una y no tiene errores
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imagen = $_FILES['image'];
        $carpeta_destino = "uploads/";

        // Crear carpeta si no existe
        if (!file_exists($carpeta_destino)) {
            mkdir($carpeta_destino, 0777, true);
        }

        // Validar tipo de archivo
        $permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($imagen['type'], $permitidos)) {
            echo "❌ Tipo de imagen no permitido.";
            exit; // Detenemos si el tipo es incorrecto
        }

        // Generar nombre único y mover
        $nombre_imagen = uniqid("img_") . "_" . basename($imagen['name']);
        $ruta_temp = $carpeta_destino . $nombre_imagen;

        if (move_uploaded_file($imagen['tmp_name'], $ruta_temp)) {
            $ruta_final = $ruta_temp;
        } else {
            echo "⚠️ Error al mover la imagen, pero se intentará guardar el marcador.";
        }
    }

    // 2. Insertar en base de datos (independientemente de si hay imagen o no)
    $sql = "INSERT INTO markers (name, address, lat, lng) VALUES (?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);

    // "ssdd" -> string, string, double, double
    $stmt->bind_param("ssdd", $name, $ruta_final, $lat, $lng);

    if ($stmt->execute()) {
        // Marcador insertado correctamente
        header('Location: index.php?status=success');
        exit;
    } else {
        echo "❌ Error al insertar el marcador: " . $stmt->error;
    }

    $stmt->close();
    $conexion->close();
} else {
    echo "❌ Datos incompletos.";
}
