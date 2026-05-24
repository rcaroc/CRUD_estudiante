<?php

require_once 'config/database.php';

$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'])) {

    $stmt = $pdo->prepare(
        "INSERT INTO estudiantes (nombre, email, carrera)
         VALUES (:n, :e, :c)"
    );

    $stmt->execute([
        ':n' => htmlspecialchars($_POST['nombre']),
        ':e' => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
        ':c' => htmlspecialchars($_POST['carrera']),
    ]);

    header('Location: /');
    exit;
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

$estudiantes = $pdo->query(
    "SELECT * FROM estudiantes ORDER BY creado_en DESC"
)->fetchAll();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Estudiantes</title>
</head>
<body>

<h2>Registrar Estudiante</h2>

<form method="POST">

    <input name="nombre" placeholder="Nombre completo" required>

    <input name="email" type="email" placeholder="Email" required>

    <input name="carrera" placeholder="Carrera" required>

    <button type="submit">
        Guardar
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
    <th>Acciones</th>
</tr>

<?php foreach ($estudiantes as $e): ?>

<tr>

<td><?= $e['id'] ?></td>

<td><?= htmlspecialchars($e['nombre']) ?></td>

<td><?= htmlspecialchars($e['email']) ?></td>

<td><?= htmlspecialchars($e['carrera']) ?></td>

<td>
    <a href="?delete=<?= $e['id'] ?>"
       onclick="return confirm('¿Eliminar?')">
       Eliminar
    </a>
</td>

</tr>

<?php endforeach; ?>

</table>

</body>
</html>