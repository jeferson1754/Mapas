<?php
include 'bd.php';

// Validar si los datos esperados están presentes
if (isset($_POST['name'], $_POST['lati'], $_POST['long']) && isset($_FILES['image'])) {
    $name = $_POST['name'];
    $lat = floatval($_POST['lati']);
    $lng = floatval($_POST['long']);

    // Validar coordenadas numéricas
    if (!is_numeric($lat) || !is_numeric($lng)) {
        echo "❌ Coordenadas inválidas.";
        exit;
    }

    // Procesar imagen
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
        exit;
    }

    $nombre_imagen = uniqid("img_") . "_" . basename($imagen['name']);
    $ruta_final = $carpeta_destino . $nombre_imagen;

    if (move_uploaded_file($imagen['tmp_name'], $ruta_final)) {
        // Insertar en base de datos
        $sql = "INSERT INTO markers (name, address, lat, lng) VALUES (?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssdd", $name, $ruta_final, $lat, $lng);

        if ($stmt->execute()) {
            echo "✔️ Marcador insertado correctamente.";
            header('Location: index.php');
            exit;
        } else {
            echo "❌ Error al insertar el marcador: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "❌ Error al mover la imagen.";
    }

    $conexion->close();
} else {
    echo "❌ Datos incompletos.";
}
