<?php
// ==================================================
// PÁGINA PHP: Comparación de Horas y Horarios por Período
// ==================================================

// Incluir configuración de la base de datos
require_once 'config/database2.php';

// Procesar el formulario
$nombre_seleccionado = isset($_POST['nombre']) ? $_POST['nombre'] : '';
$fecha_inicial = isset($_POST['fecha_inicial']) ? $_POST['fecha_inicial'] : date('Y-m-d', strtotime('-7 days'));
$fecha_final = isset($_POST['fecha_final']) ? $_POST['fecha_final'] : date('Y-m-d');

$resultados = [];
$nombres_disponibles = [];
$error = '';

// Obtener lista de nombres disponibles
try {
    $stmt = $pdo->prepare("CALL obtener_nombres_disponibles()");
    $stmt->execute();
    $nombres_disponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
} catch(PDOException $e) {
    $error = "Error al obtener nombres: " . $e->getMessage();
}

// Procesar consulta principal si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['nombre']) && !empty($_POST['fecha_inicial']) && !empty($_POST['fecha_final'])) {
    try {
        $stmt = $pdo->prepare("CALL horas_horarios_periodo(?, ?, ?)");
        $stmt->execute([$nombre_seleccionado, $fecha_inicial, $fecha_final]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
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
    <title>Comparación por Período - Horas y Horarios</title>
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
        .form-row {
            display: flex;
            gap: 20px;
            align-items: end;
        }
        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
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
            width: 100%;
            box-sizing: border-box;
        }
        select[name="nombre"] {
            min-width: 250px;
        }
        button {
            background-color: #007bff;
            color: white;
            cursor: pointer;
            padding: 12px 24px;
            border: none;
            font-weight: bold;
            margin-top: 10px;
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
        .fecha-grupo {
            background-color: #e8f5e8;
            font-weight: bold;
            border-left: 4px solid #28a745;
        }
        .fecha-diferente {
            background-color: #fff3e0;
            border-left: 4px solid #ff9800;
        }
        .separador-fecha {
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
        .resumen {
            background-color: #e2e3e5;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .resumen-item {
            display: inline-block;
            margin-right: 20px;
            padding: 5px 10px;
            background-color: white;
            border-radius: 3px;
            font-weight: bold;
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .fecha-columna {
            font-weight: bold;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Comparación por Período - Horas y Horarios</h1>
        
        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group">
                    <label for="nombre">Seleccionar Persona:</label>
                    <select id="nombre" name="nombre" required>
                        <option value="">-- Seleccione una persona --</option>
                        <?php foreach ($nombres_disponibles as $nombre): ?>
                            <option value="<?php echo htmlspecialchars($nombre['nombre']); ?>" 
                                    <?php echo ($nombre_seleccionado == $nombre['nombre']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($nombre['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="fecha_inicial">Fecha Inicial:</label>
                    <input type="date" id="fecha_inicial" name="fecha_inicial" 
                           value="<?php echo htmlspecialchars($fecha_inicial); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="fecha_final">Fecha Final:</label>
                    <input type="date" id="fecha_final" name="fecha_final" 
                           value="<?php echo htmlspecialchars($fecha_final); ?>" required>
                </div>
                <div class="form-group">
                   
                </div>
                <div class="form-group">
                    <button type="submit">🔍 Analizar Período</button>
                </div>
            </div>
        </form>

        <?php if ($error): ?>
            <div class="error">
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($nombre_seleccionado) && empty($error)): ?>
            <div class="info">
                <strong>Análisis para:</strong> <?php echo htmlspecialchars($nombre_seleccionado); ?> 
                | <strong>Período:</strong> <?php echo htmlspecialchars($fecha_inicial); ?> al <?php echo htmlspecialchars($fecha_final); ?>
            </div>

            <?php if (count($resultados) > 0): 
                // Calcular estadísticas
                $total_registros = count($resultados);
                $coincidencias = array_filter($resultados, function($r) { return $r['estado'] == 'COINCIDEN'; });
                $solo_clases = array_filter($resultados, function($r) { return $r['estado'] == 'SOLO_CLASE_OTORGADA'; });
                $solo_horas = array_filter($resultados, function($r) { return $r['estado'] == 'SOLO_REGISTRO_HORAS'; });
            ?>
                <div class="resumen">
                    <strong>Resumen del Análisis:</strong><br>
                    <span class="resumen-item">Total: <?php echo $total_registros; ?></span>
                    <span class="resumen-item estado-coinciden">Coinciden: <?php echo count($coincidencias); ?></span>
                    <span class="resumen-item estado-solo-clase">Solo Clases: <?php echo count($solo_clases); ?></span>
                    <span class="resumen-item estado-solo-hora">Solo Horas: <?php echo count($solo_horas); ?></span>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Horario</th>
                            <th>Tipo Clase</th>
                            <th>Tipo Hora</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $fecha_anterior = '';
                        $contador_grupo = 0;
                        foreach ($resultados as $index => $fila): 
                            $fecha_actual = $fila['fecha'] ?? '';
                            
                            // Verificar si es un nuevo grupo de fechas
                            if ($fecha_actual !== $fecha_anterior) {
                                $contador_grupo++;
                                $fecha_anterior = $fecha_actual;
                                
                                // Mostrar separador si no es el primer grupo
                                if ($index > 0): ?>
                                    <tr class="separador-fecha">
                                        <td colspan="5" style="text-align: center; font-size: 12px; color: #666;">
                                            ▲ Fin día anterior • Inicio nuevo día ▼
                                        </td>
                                    </tr>
                                <?php endif;
                            }
                            
                            // Determinar la clase CSS para la fecha
                            $clase_fila = ($contador_grupo % 2 == 1) ? 'fecha-grupo' : 'fecha-diferente';
                        ?>
                            <tr class="<?php echo $clase_fila; ?>">
                                <td class="fecha-columna">
                                    <?php 
                                    $fecha_formateada = date('d/m/Y', strtotime($fecha_actual));
                                    $dia_semana = date('l', strtotime($fecha_actual));
                                    $dias_es = [
                                        'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles',
                                        'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo'
                                    ];
                                    echo htmlspecialchars($fecha_formateada);
                                    ?>
                                    <br><small style="color: #666;"><?php echo $dias_es[$dia_semana] ?? $dia_semana; ?></small>
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
                    No se encontraron registros para la persona y período seleccionados.
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
        // Validar que fecha final no sea menor que fecha inicial
        document.getElementById('fecha_final').addEventListener('change', function() {
            const fechaInicial = document.getElementById('fecha_inicial').value;
            const fechaFinal = this.value;
            
            if (fechaInicial && fechaFinal && fechaFinal < fechaInicial) {
                alert('La fecha final no puede ser menor que la fecha inicial');
                this.value = fechaInicial;
            }
        });
        
        document.getElementById('fecha_inicial').addEventListener('change', function() {
            const fechaInicial = this.value;
            const fechaFinal = document.getElementById('fecha_final').value;
            
            if (fechaInicial && fechaFinal && fechaFinal < fechaInicial) {
                document.getElementById('fecha_final').value = fechaInicial;
            }
        });
    </script>
</body>
</html>