<?php
// ==================================================
// PÁGINA PHP: Consulta de Horas y Horarios
// ==================================================

// Incluir configuración de la base de datos
require_once 'config/database2.php';

// Procesar el formulario
$fecha_seleccionada = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
$sucursal = isset($_POST['sucursal']) ? $_POST['sucursal'] : 'MOC';
$resultados = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['fecha'])) {
    try {
        $stmt = $pdo->prepare("CALL horas_horarios(?, ?)");
        $stmt->execute([$fecha_seleccionada, $sucursal]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor(); // Importante para procedimientos almacenados
    } catch(PDOException $e) {
        $error = "Error al ejecutar la consulta: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Horas y Horarios</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input, select, button {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        input[type="date"], select {
            width: 200px;
        }
        button {
            background-color: #007bff;
            color: white;
            cursor: pointer;
            padding: 12px 24px;
            border: none;
            font-weight: bold;
        }
        button:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #495057;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .nombre-grupo {
            background-color: #e3f2fd;
            font-weight: bold;
            border-left: 4px solid #2196f3;
        }
        .nombre-diferente {
            background-color: #fff3e0;
            border-left: 4px solid #ff9800;
        }
        .separador-nombre {
            background-color: #f5f5f5;
            border-top: 2px solid #ddd;
            font-weight: bold;
        }
        .estado-coinciden {
            background-color: #d4edda;
            color: #155724;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        .estado-solo-clase {
            background-color: #fff3cd;
            color: #856404;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        .estado-solo-hora {
            background-color: #f8d7da;
            color: #721c24;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        .error {
            color: #dc3545;
            background-color: #f8d7da;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📅 Consulta de Horas y Horarios</h1>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="fecha">Seleccionar Fecha:</label>
                <input type="date" id="fecha" name="fecha" value="<?php echo htmlspecialchars($fecha_seleccionada); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="sucursal">Sucursal:</label>
                <select id="sucursal" name="sucursal">
                    <option value="MOC" <?php echo ($sucursal == 'MOC') ? 'selected' : ''; ?>>MOC</option>
                    <option value="CEN" <?php echo ($sucursal == 'CEN') ? 'selected' : ''; ?>>CEN</option>
                    <option value="SUR" <?php echo ($sucursal == 'SUR') ? 'selected' : ''; ?>>SUR</option>
                </select>
            </div>
            
            <button type="submit">🔍 Consultar</button>
        </form>

        <?php if ($error): ?>
            <div class="error">
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($error)): ?>
            <div class="info">
                <strong>Resultados para:</strong> <?php echo htmlspecialchars($fecha_seleccionada); ?> - Sucursal: <?php echo htmlspecialchars($sucursal); ?>
                <br><strong>Total de registros:</strong> <?php echo count($resultados); ?>
            </div>

            <?php if (count($resultados) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Horario</th>
                            <th>Tipo Clase</th>
                            <th>Tipo Hora</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $nombre_anterior = '';
                        $contador_grupo = 0;
                        foreach ($resultados as $index => $fila): 
                            $nombre_actual = $fila['nombre'] ?? '';
                            
                            // Verificar si es un nuevo grupo de nombres
                            if ($nombre_actual !== $nombre_anterior) {
                                $contador_grupo++;
                                $nombre_anterior = $nombre_actual;
                                
                                // Mostrar separador si no es el primer grupo
                                if ($index > 0): ?>
                                    <tr class="separador-nombre">
                                        <td colspan="5" style="text-align: center; font-size: 12px; color: #666;">
                                            ▲ Fin grupo anterior • Inicio nuevo grupo ▼
                                        </td>
                                    </tr>
                                <?php endif;
                            }
                            
                            // Determinar la clase CSS para el nombre
                            $clase_fila = ($contador_grupo % 2 == 1) ? 'nombre-grupo' : 'nombre-diferente';
                        ?>
                            <tr class="<?php echo $clase_fila; ?>">
                                <td>
                                    <strong><?php echo htmlspecialchars($nombre_actual); ?></strong>
                                    <br><small style="color: #666;">Grupo #<?php echo $contador_grupo; ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($fila['horario'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($fila['tipo_clase'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($fila['tipo_hora'] ?? '-'); ?></td>
                                <td>
                                    <?php 
                                    $estado = $fila['estado'] ?? '';
                                    $clase_css = '';
                                    switch($estado) {
                                        case 'COINCIDEN':
                                            $clase_css = 'estado-coinciden';
                                            break;
                                        case 'SOLO_CLASE_OTORGADA':
                                            $clase_css = 'estado-solo-clase';
                                            break;
                                        case 'SOLO_REGISTRO_HORAS':
                                            $clase_css = 'estado-solo-hora';
                                            break;
                                    }
                                    ?>
                                    <span class="<?php echo $clase_css; ?>">
                                        <?php echo htmlspecialchars($estado); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="info">
                    No se encontraron registros para la fecha seleccionada.
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>