<?php
// Incluir archivo de conexión a la base de datos
include 'bd.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // EDITAR MARCADOR
  if (isset($_POST['action']) && $_POST['action'] === 'editar') {
    $id = $_POST['id'];
    $nuevoNombre = $_POST['name'];
    $nuevaImagen = $_POST['image'];

    $sql = "UPDATE markers SET name = ?, address = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssi", $nuevoNombre, $nuevaImagen, $id);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit;
  }

  // ELIMINAR MARCADOR
  if (isset($_POST['action']) && $_POST['action'] === 'eliminar') {
    $id = $_POST['id'];

    $sql = "DELETE FROM markers WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit;
  }
}

// Preparar la consulta SQL de manera segura
$sql = "SELECT id, lat, lng, name, address FROM markers";

// Preparar y ejecutar la consulta usando consultas preparadas
$stmt = $conexion->prepare($sql);
$stmt->execute();

// Obtener el resultado
$result = $stmt->get_result();
$rowCount = $result->num_rows;

// Almacenar los marcadores en un array para usarlos en JavaScript
$markers = [];
while ($row = $result->fetch_assoc()) {
  $markers[] = [
    'id' => (int)$row['id'],
    'lat' => (float)$row['lat'],
    'lng' => (float)$row['lng'],
    'name' => htmlspecialchars($row['name']),
    'image' => htmlspecialchars($row['address'])
  ];
}

// Cerrar la consulta
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Ubicaciones</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome para iconos -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <!-- Animate.css para animaciones -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">

  <!-- SweetAlert2 para diálogos mejorados -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.5/sweetalert2.min.css" rel="stylesheet">

  <!-- Leaflet CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" rel="stylesheet">

  <style>
    :root {
      --primary-color: #3498db;
      --secondary-color: #2ecc71;
      --danger-color: #e74c3c;
      --dark-color: #34495e;
      --light-color: #ecf0f1;
      --transition-speed: 0.3s;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f8f9fa;
      color: #333;
      padding-top: 20px;
    }

    .location-container {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      padding: 25px;
      margin-bottom: 30px;
      transition: transform var(--transition-speed);
    }

    .location-container:hover {
      transform: translateY(-5px);
    }

    .section-title {
      position: relative;
      margin-bottom: 30px;
      padding-bottom: 15px;
      color: var(--dark-color);
      font-weight: 600;
    }

    .section-title:after {
      content: '';
      position: absolute;
      left: 0;
      bottom: 0;
      height: 4px;
      width: 60px;
      background: var(--primary-color);
      border-radius: 2px;
    }

    .location-table {
      border-radius: 8px;
      overflow: hidden;
    }

    .table {
      margin-bottom: 0;
    }

    .table thead th {
      background-color: var(--dark-color);
      color: white;
      font-weight: 500;
      border: none;
      padding: 15px;
      white-space: nowrap;
    }

    .table tbody tr {
      transition: background-color var(--transition-speed);
    }

    .table tbody tr:hover {
      background-color: rgba(52, 152, 219, 0.05);
    }

    .table td {
      padding: 15px;
      vertical-align: middle;
    }

    .location-img {
      border-radius: 6px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      transition: transform var(--transition-speed);
      width: 80px;
      height: 80px;
      object-fit: cover;
    }

    .location-img:hover {
      transform: scale(1.05);
      cursor: pointer;
    }

    .btn {
      border-radius: 6px;
      font-weight: 500;
      padding: 8px 16px;
      transition: all var(--transition-speed);
      margin-right: 5px;
    }

    .btn-primary {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
    }

    .btn-primary:hover {
      background-color: #2980b9;
      border-color: #2980b9;
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
    }

    .btn-danger {
      background-color: var(--danger-color);
      border-color: var(--danger-color);
    }

    .btn-danger:hover {
      background-color: #c0392b;
      border-color: #c0392b;
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(231, 76, 60, 0.3);
    }

    .btn-sm i {
      margin-right: 5px;
    }

    #map {
      height: 400px;
      border-radius: 10px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      margin-top: 20px;
      margin-bottom: 20px;
    }

    .empty-state {
      padding: 60px 20px;
      text-align: center;
      background-color: #f8f9fa;
      border-radius: 10px;
      border: 2px dashed #ddd;
    }

    .empty-state i {
      font-size: 3rem;
      color: #ccc;
      margin-bottom: 15px;
    }

    .empty-state h4 {
      margin-bottom: 15px;
      color: #777;
    }

    .card-scroll {
      max-height: 600px;
      overflow-y: auto;
      scrollbar-width: thin;
    }

    /* Estilo para scrollbar personalizado */
    .card-scroll::-webkit-scrollbar {
      width: 6px;
    }

    .card-scroll::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 10px;
    }

    .card-scroll::-webkit-scrollbar-thumb {
      background: #ccc;
      border-radius: 10px;
    }

    .card-scroll::-webkit-scrollbar-thumb:hover {
      background: #aaa;
    }

    /* Vista previa de imagen ampliada */
    .img-preview-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.8);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s;
    }

    .img-preview-overlay.active {
      opacity: 1;
      visibility: visible;
    }

    .img-preview-content {
      max-width: 90%;
      max-height: 90%;
    }

    .img-preview-content img {
      max-width: 100%;
      max-height: 90vh;
      border-radius: 8px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }

    .preview-close {
      position: absolute;
      top: 20px;
      right: 20px;
      color: white;
      font-size: 24px;
      cursor: pointer;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: rgba(255, 255, 255, 0.2);
      border-radius: 50%;
      transition: all 0.3s;
    }

    .preview-close:hover {
      background-color: rgba(255, 255, 255, 0.4);
      transform: rotate(90deg);
    }

    /* Loader para operaciones */
    .loader-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(255, 255, 255, 0.8);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s;
    }

    .loader-overlay.active {
      opacity: 1;
      visibility: visible;
    }

    .loader {
      width: 50px;
      height: 50px;
      border: 5px solid #f3f3f3;
      border-top: 5px solid var(--primary-color);
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    /* Animaciones para elementos nuevos o eliminados */
    .fade-in {
      animation: fadeIn 0.5s;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .fade-out {
      animation: fadeOut 0.5s;
    }

    @keyframes fadeOut {
      from {
        opacity: 1;
        transform: translateY(0);
      }

      to {
        opacity: 0;
        transform: translateY(20px);
      }
    }

    /* Estilos para dispositivos móviles */
    @media (max-width: 767.98px) {
      .table-responsive {
        border: none;
      }

      .table th:nth-child(1),
      .table td:nth-child(1) {
        display: none;
      }

      .btn {
        padding: 6px 12px;
        font-size: 0.875rem;
      }

      .location-img {
        width: 60px;
        height: 60px;
      }

      .section-title {
        font-size: 1.5rem;
      }
    }

    /* Modo oscuro - activado con clase .dark-mode en el body */
    body.dark-mode {
      background-color: #121212;
      color: #e0e0e0;
    }

    body.dark-mode .location-container {
      background-color: #1e1e1e;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }

    body.dark-mode .table {
      color: #e0e0e0;
    }

    body.dark-mode .table tbody tr:hover {
      background-color: rgba(255, 255, 255, 0.05);
    }

    body.dark-mode .empty-state {
      background-color: #1e1e1e;
      border-color: #333;
    }

    body.dark-mode .empty-state i,
    body.dark-mode .empty-state h4 {
      color: #666;
    }

    .dark-mode-toggle {
      position: fixed;
      bottom: 20px;
      right: 20px;
      z-index: 999;
      background-color: var(--dark-color);
      color: white;
      border: none;
      border-radius: 50%;
      width: 50px;
      height: 50px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
      transition: all var(--transition-speed);
    }

    .dark-mode-toggle:hover {
      transform: scale(1.1);
    }
  </style>
</head>

<body>
  <div class="container">
    <?php include 'menu.php'; ?>

    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="location-container animate__animated animate__fadeIn">
          <h2 class="section-title">Gestión de Ubicaciones</h2>

          <!-- Tabla de ubicaciones -->
          <div class="card-scroll">
            <?php if (count($markers) > 0): ?>
              <div class="table-responsive location-table">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Nombre</th>
                      <th>Imagen</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($markers as $index => $marker): ?>
                      <tr id="fila-<?= $marker['id'] ?>" class="fade-in">
                        <td><?= $index + 1 ?></td>
                        <td id="nombre-<?= $marker['id'] ?>"><?= $marker['name'] ?></td>
                        <td id="imagen-<?= $marker['id'] ?>">
                          <img src="<?= $marker['image'] ?>" alt="<?= $marker['name'] ?>"
                            class="img-fluid location-img"
                            onclick="mostrarVistaPrevia('<?= $marker['image'] ?>', '<?= $marker['name'] ?>')">
                        </td>
                        <td>
                          <button class="btn btn-primary btn-sm" onclick="editarUbicacion(<?= $marker['id'] ?>)">
                            <i class="fas fa-edit"></i> Editar
                          </button>
                          <button class="btn btn-danger btn-sm" onclick="eliminarUbicacion(<?= $marker['id'] ?>)">
                            <i class="fas fa-trash-alt"></i> Eliminar
                          </button>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="empty-state">
                <i class="fas fa-map-marker-alt"></i>
                <h4>No hay ubicaciones disponibles</h4>
                <p class="text-muted">Aún no se han añadido ubicaciones al mapa.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Botón de modo oscuro -->
  <button class="dark-mode-toggle" id="darkModeToggle">
    <i class="fas fa-moon"></i>
  </button>

  <!-- Vista previa de imagen -->
  <div class="img-preview-overlay" id="imgPreviewOverlay">
    <div class="preview-close" id="previewClose">
      <i class="fas fa-times"></i>
    </div>
    <div class="img-preview-content">
      <img id="previewImage" src="" alt="">
    </div>
  </div>

  <!-- Loader para operaciones -->
  <div class="loader-overlay" id="loaderOverlay">
    <div class="loader"></div>
  </div>

  <!-- Scripts -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.5/sweetalert2.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>

  <script>
    function initDarkMode() {
      const darkModeToggle = document.getElementById('darkModeToggle');
      const icon = darkModeToggle.querySelector('i');

      // Verificar preferencia guardada
      const isDarkMode = localStorage.getItem('darkMode') === 'true';

      // Aplicar modo oscuro si está activado
      if (isDarkMode) {
        document.body.classList.add('dark-mode');
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
      }

      // Cambiar entre modos
      darkModeToggle.addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');

        // Guardar preferencia
        if (document.body.classList.contains('dark-mode')) {
          localStorage.setItem('darkMode', 'true');
          icon.classList.remove('fa-moon');
          icon.classList.add('fa-sun');
        } else {
          localStorage.setItem('darkMode', 'false');
          icon.classList.remove('fa-sun');
          icon.classList.add('fa-moon');
        }
      });
    }

    function initImagePreview() {
      const overlay = document.getElementById('imgPreviewOverlay');
      const closeBtn = document.getElementById('previewClose');

      // Cerrar vista previa al hacer clic en el botón de cierre
      closeBtn.addEventListener('click', function() {
        overlay.classList.remove('active');
      });

      // Cerrar vista previa al hacer clic fuera de la imagen
      overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
          overlay.classList.remove('active');
        }
      });
    }

    function mostrarVistaPrevia(imagenUrl, nombre) {
      const overlay = document.getElementById('imgPreviewOverlay');
      const previewImage = document.getElementById('previewImage');

      previewImage.src = imagenUrl;
      previewImage.alt = nombre;

      overlay.classList.add('active');
    }

    function mostrarLoader() {
      document.getElementById('loaderOverlay').classList.add('active');
    }

    function ocultarLoader() {
      document.getElementById('loaderOverlay').classList.remove('active');
    }

    function editarUbicacion(id) {
      const nombreTd = document.getElementById('nombre-' + id);
      const imagenTd = document.getElementById('imagen-' + id);
      const nombreActual = nombreTd.textContent;
      const imagenActual = imagenTd.querySelector('img').src;

      Swal.fire({
        title: 'Editar ubicación',
        html: `
    <div class="mb-3">
      <label for="swal-nombre" class="form-label">Nombre</label>
      <input id="swal-nombre" class="form-control" value="${nombreActual}">
    </div>
    <div class="mb-3">
      <label for="swal-imagen" class="form-label">Subir nueva imagen</label>
      <input type="file" id="swal-imagen" class="form-control" accept="image/*">
      <img id="preview-imagen" src="${imagenActual}" class="img-fluid mt-2" style="max-height: 150px;">
    </div>
  `,
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        customClass: {
          confirmButton: 'btn btn-primary',
          cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false,
        didOpen: () => {
          const fileInput = document.getElementById('swal-imagen');
          const preview = document.getElementById('preview-imagen');

          fileInput.addEventListener('change', () => {
            const file = fileInput.files[0];
            if (file) {
              const reader = new FileReader();
              reader.onload = (e) => {
                preview.src = e.target.result;
              };
              reader.readAsDataURL(file);
            }
          });
        },
        preConfirm: () => {
          return {
            nombre: document.getElementById('swal-nombre').value,
            imagen: document.getElementById('swal-imagen').files[0] || null
          };
        }
      }).then((result) => {
        if (result.isConfirmed) {
          const formData = new FormData();
          formData.append('id', idUbicacion); // Asegúrate de tener el ID
          formData.append('nombre', result.value.nombre);
          if (result.value.imagen) {
            formData.append('imagen', result.value.imagen);
          }

          fetch('subir_ubicacion.php', {
              method: 'POST',
              body: formData
            })
            .then(response => response.text())
            .then(data => {
              Swal.fire('Éxito', data, 'success');
            })
            .catch(error => {
              console.error(error);
              Swal.fire('Error', 'Hubo un problema al guardar.', 'error');
            });
        }
      });
    }


    function eliminarUbicacion(id) {
      // Confirmar eliminación con SweetAlert2
      Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#3498db',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          mostrarLoader();

          fetch('editar_mapa.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
              },
              body: `action=eliminar&id=${id}`
            })
            .then(res => res.json())
            .then(data => {
              ocultarLoader();

              if (data.success) {
                const fila = document.getElementById('fila-' + id);
                fila.classList.add('fade-out');

                // Esperar a que termine la animación antes de eliminar
                setTimeout(() => {
                  fila.remove();

                  // Verificar si la tabla está vacía
                  const tbody = document.querySelector('tbody');
                  if (tbody.children.length === 0) {
                    const tablaContainer = document.querySelector('.table-responsive');
                    tablaContainer.innerHTML = `
                    <div class="empty-state">
                      <i class="fas fa-map-marker-alt"></i>
                      <h4>No hay ubicaciones disponibles</h4>
                      <p class="text-muted">Aún no se han añadido ubicaciones al mapa.</p>
                    </div>
                  `;
                  }
                }, 500);

                // Notificar éxito
                Swal.fire({
                  icon: 'success',
                  title: '¡Eliminado!',
                  text: 'La ubicación ha sido eliminada correctamente.',
                  timer: 2000,
                  showConfirmButton: false
                });

                // Recargar la página para actualizar el mapa
                setTimeout(() => {
                  window.location.reload();
                }, 2000);
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: 'No se pudo eliminar la ubicación.',
                });
              }
            })
            .catch(error => {
              ocultarLoader();
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error de red.',
              });
            });
        }
      });
    }
  </script>
</body>

</html>