<?php

require_once 'config/database.php';

$pdo = getDBConnection();

$estudiante_a_editar = null;

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM estudiantes WHERE id = :id");
    $stmt->execute([':id' => (int)$_GET['edit']]);
    $estudiante_a_editar = $stmt->fetch();
}

$errores = []; // Creamos una lista para guardar los mensajes de error

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'])) {
    // Ingresamos los datos
    $nombre = trim($_POST['nombre']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $carrera = htmlspecialchars($_POST['carrera']);
    
    // Recogemos el ID si es que viene del formulario
    $id = isset($_POST['id_editar']) ? (int)$_POST['id_editar'] : null;

    // Agregamos verificacion de formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del email no es válido.";
    }

    // Agregamos verificacion de longitud del nombre
    if (strlen($nombre) < 3 || strlen($nombre) > 100) {
        $errores[] = "El nombre debe tener entre 3 y 100 caracteres.";
    }

    if (empty($errores)) {
    if ($id) {
        // Si ya existe el ID, usamos la sentencia UPDATE
        $stmt = $pdo->prepare("UPDATE estudiantes SET nombre=:n, email=:e, carrera=:c WHERE id=:id");
        $stmt->execute([':n' => $nombre, ':e' => $email, ':c' => $carrera, ':id' => $id]);
    } else {
        // Si no hay ID hacemos el INSERT que teniamos
        $stmt = $pdo->prepare("INSERT INTO estudiantes (nombre, email, carrera) VALUES (:n, :e, :c)");
        $stmt->execute([':n' => $nombre, ':e' => $email, ':c' => $carrera]);
    }
    header('Location: /'); exit;

    }
}

if (isset($_GET['delete'])) {

    $stmt = $pdo->prepare(
        "DELETE FROM estudiantes WHERE id = :id"
    );

    $stmt->execute([
        ':id' => (int)$_GET['delete']
    ]);

    header('Location: /');
    exit;
}


$estudiantes_por_pagina = 5;
// Leemos la página actual desde la URL index.php?p=2
$pagina_actual = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;

// Cálculo del desplazamiento (OFFSET)
// Si estoy en pág 1 (1-1) * 5 = 0 (no se salta ninguno)
// Si estoy en pág 2 (2-1) * 5 = 5 (se salta los primeros 5)
$offset = ($pagina_actual - 1) * $estudiantes_por_pagina;

// Consulta preparada con LIMIT y OFFSET
$stmt = $pdo->prepare("SELECT * FROM estudiantes ORDER BY fe_crea DESC LIMIT :limit OFFSET :offset");

// bindValue es necesario porque LIMIT/OFFSET solo aceptan números enteros
$stmt->bindValue(':limit', $estudiantes_por_pagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$estudiantes = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Estudiantes</title>
</head>
<body>
<?php if (!empty($errores)): ?>
    <div style="background-color: #ffcccc; color: #cc0000; padding: 10px; border: 1px solid #cc0000; margin-bottom: 20px;">
        <ul>
            <?php foreach ($errores as $error): ?>
                <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<h2>Registrar Estudiante</h2>

<form method="POST">
    <input type="hidden" name="id_editar" value="<?= $estudiante_a_editar['id'] ?? '' ?>">

    <input name="nombre" value="<?= $estudiante_a_editar['nombre'] ?? '' ?>" required>
    <input name="email" value="<?= $estudiante_a_editar['email'] ?? '' ?>" required>
    <input name="carrera" value="<?= $estudiante_a_editar['carrera'] ?? '' ?>" required>

    <button type="submit">
        <?= $estudiante_a_editar ? 'Actualizar' : 'Guardar' ?>
    </button>
</form>

<h2>
    Lista de Estudiantes
    (<?= count($estudiantes) ?>)
</h2>

<table border="1">

<tr>
    <th>ID</th>
    <th>Nombre</th>
    <th>Email</th>
    <th>Carrera</th>
    <th>Fecha</th>
    <th>Acciones</th>
</tr>

<?php foreach ($estudiantes as $e): ?>

<tr>

<td><?= $e['id'] ?></td>
<td><?= htmlspecialchars($e['nombre']) ?></td>
<td><?= htmlspecialchars($e['email']) ?></td>
<td><?= htmlspecialchars($e['carrera']) ?></td>
<td><?= $e['fe_crea'] ?></td>

<td>
    <a href="?edit=<?= $e['id'] ?>" style="margin-right: 10px;">
       Editar
    </a>

    <a href="?delete=<?= $e['id'] ?>"
       onclick="return confirm('¿Eliminar?')">
       Eliminar
    </a>
</td>

<div style="margin-top: 20px; text-align: center;">
    
    <?php if ($pagina_actual > 1): ?>
        <a href="?p=<?= $pagina_actual - 1 ?>" style="text-decoration: none; padding: 5px 10px; border: 1px solid #ccc;">
            « Anterior
        </a>
    <?php endif; ?>

    <span style="margin: 0 15px;">
        Página <strong><?= $pagina_actual ?></strong>
    </span>

    <?php if (count($estudiantes) === $estudiantes_por_pagina): ?>
        <a href="?p=<?= $pagina_actual + 1 ?>" style="text-decoration: none; padding: 5px 10px; border: 1px solid #ccc;">
            Siguiente »
        </a>
    <?php endif; ?>

</div>

</tr>

<?php endforeach; ?>

</table>

</body>
</html>