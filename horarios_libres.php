<?php
/**
 * Sistema de Horarios de Clases - Horarios Libres
 */

 // Iniciar sesión al principio del archivo
session_start();

// Definir ruta base del proyecto
define('BASE_PATH', __DIR__);

// Validar sesión de usuario - DEBE IR ANTES DE CUALQUIER SALIDA HTML
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: /blueadmin/index.php");
    exit;
}

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/classes/HorariosLibres.php';

$horariosLibres = new HorariosLibres();
$errores = [];
$horarios_semana = [];
$maestra_info = null;
$datos_busqueda = null;

// Procesar formulario
if ($_POST) {
    $datos_busqueda = [
        'inicio_horario' => sanitize($_POST['inicio_horario']),
        'fin_horario' => sanitize($_POST['fin_horario']),
        'id_maestra' => (int)$_POST['id_maestra'],
        'duracion' => sanitize($_POST['duracion'])
    ];
    
    // Validar datos
    $errores = $horariosLibres->validarDatos($datos_busqueda);
    
    if (empty($errores)) {
        // Obtener información de la maestra
        $maestra_info = $horariosLibres->getMaestraPorId($datos_busqueda['id_maestra']);
        
        // Obtener horarios libres para toda la semana
        $horarios_semana = $horariosLibres->getHorariosLibresSemana(
            $datos_busqueda['inicio_horario'],
            $datos_busqueda['fin_horario'],
            $datos_busqueda['id_maestra'],
            $datos_busqueda['duracion']
        );
    }
}

// Obtener datos para los dropdowns
$maestras = $horariosLibres->getMaestras();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horarios Libres - Sistema de Horarios de Clases</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .select2-container {
            width: 100% !important;
        }
        .card {
            box-shadow: 0 0 20px rgba(0,0,0,.1);
            border: none;
            border-radius: 15px;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }
        .alert-success {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
            border: none;
            color: white;
        }
        .alert-danger {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            border: none;
            color: white;
        }
        .alert-info {
            background: linear-gradient(135deg, #36d1dc 0%, #5b86e5 100%);
            border: none;
            color: white;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .horario-libre {
            background: linear-gradient(135deg, #307f23 0%, #307f23 100%);
            color: white;
            padding: 8px 12px;
            margin: 3px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
            min-width: 120px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .dia-columna {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            min-height: 200px;
            padding: 15px;
        }
        .dia-header {
            font-weight: bold;
            color: #667eea;
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .sin-horarios {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 20px;
        }
        .estadisticas {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .nav-pills .nav-link {
            border-radius: 25px;
            margin: 0 5px;
        }
        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-light">
    <!-- Header -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0"><i class="fas fa-search me-3"></i>Horarios Libres</h1>
                    <p class="mb-0 mt-2">Consulta la disponibilidad de horarios de las maestras</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="index.php" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Volver al Sistema
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Mensajes -->
        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>¡Errores encontrados!</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Formulario de Búsqueda -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros de Búsqueda</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <!-- Periodo de Tiempo -->
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label for="inicio_horario" class="form-label">
                                        <i class="fas fa-clock me-1"></i>Hora Inicio
                                    </label>
                                    <input type="time" class="form-control" id="inicio_horario" name="inicio_horario" 
                                           value="<?php echo $datos_busqueda['inicio_horario'] ?? '08:00'; ?>" required>
                                </div>
                                <div class="col-6">
                                    <label for="fin_horario" class="form-label">
                                        <i class="fas fa-clock me-1"></i>Hora Fin
                                    </label>
                                    <input type="time" class="form-control" id="fin_horario" name="fin_horario" 
                                           value="<?php echo $datos_busqueda['fin_horario'] ?? '18:00'; ?>" required>
                                </div>
                            </div>

                            <!-- Maestra -->
                            <div class="mb-3">
                                <label for="id_maestra" class="form-label">
                                    <i class="fas fa-chalkboard-teacher me-1"></i>Maestra
                                </label>
                                <select class="form-select select2" id="id_maestra" name="id_maestra" required>
                                    <option value="">Seleccione una maestra...</option>
                                    <?php foreach ($maestras as $maestra): ?>
                                        <option value="<?php echo $maestra['id']; ?>" 
                                                <?php echo (isset($datos_busqueda['id_maestra']) && $datos_busqueda['id_maestra'] == $maestra['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($maestra['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Duración del Bloque -->
                            <div class="mb-4">
                                <label for="duracion" class="form-label">
                                    <i class="fas fa-stopwatch me-1"></i>Duración del Bloque
                                </label>
                                <select class="form-select" id="duracion" name="duracion" required>
                                    <option value="">Seleccione la duración...</option>
                                    <option value="30" <?php echo (isset($datos_busqueda['duracion']) && $datos_busqueda['duracion'] == '30') ? 'selected' : ''; ?>>
                                        30 minutos
                                    </option>
                                    <option value="45" <?php echo (isset($datos_busqueda['duracion']) && $datos_busqueda['duracion'] == '45') ? 'selected' : ''; ?>>
                                        45 minutos
                                    </option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Buscar Horarios Libres
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Información de la búsqueda -->
                <?php if ($maestra_info && $datos_busqueda): ?>
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Datos de la Consulta</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-2"><strong>Maestra:</strong> <?php echo htmlspecialchars($maestra_info['nombre']); ?></p>
                            <p class="mb-2"><strong>Período:</strong> <?php echo formatTime($datos_busqueda['inicio_horario']) . ' - ' . formatTime($datos_busqueda['fin_horario']); ?></p>
                            <p class="mb-0"><strong>Duración:</strong> <?php echo $datos_busqueda['duracion']; ?> minutos</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Resultados -->
            <div class="col-lg-8">
                <?php if (!empty($horarios_semana)): ?>
                    <?php 
                    $estadisticas = $horariosLibres->getEstadisticas($horarios_semana); 
                    ?>
                    
                    <!-- Estadísticas -->
                    <div class="estadisticas">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h3 class="mb-0"><?php echo $estadisticas['total_bloques']; ?></h3>
                                <small>Bloques Libres</small>
                            </div>
                            <div class="col-md-3">
                                <h3 class="mb-0"><?php echo $estadisticas['dias_con_disponibilidad']; ?>/6</h3>
                                <small>Días Disponibles</small>
                            </div>
                            <div class="col-md-3">
                                <h3 class="mb-0"><?php echo $estadisticas['mejor_dia']; ?></h3>
                                <small>Mejor Día</small>
                            </div>
                            <div class="col-md-3">
                                <h3 class="mb-0"><?php echo $estadisticas['mejor_dia_bloques']; ?></h3>
                                <small>Bloques en Mejor Día</small>
                            </div>
                        </div>
                    </div>

                    <!-- Horarios por Día -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar-week me-2"></i>Horarios Disponibles por Día
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="row g-0">
                                <?php foreach (['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'] as $dia): ?>
                                    <div class="col-md-2 dia-columna">
                                        <div class="dia-header">
                                            <i class="fas fa-calendar-day me-1"></i>
                                            <?php echo $dia; ?>
                                            <div class="badge bg-primary mt-1">
                                                <?php echo count($horarios_semana[$dia] ?? []); ?>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($horarios_semana[$dia])): ?>
                                            <?php foreach ($horarios_semana[$dia] as $horario): ?>
                                                <div class="horario-libre">
                                                    <?php echo formatTime($horario['horario_inicio']) . '-' . formatTime($horario['horario_fin']); ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="sin-horarios">
                                                <i class="fas fa-times-circle"></i><br>
                                                Sin horarios disponibles
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                <?php elseif ($_POST): ?>
                    <!-- Sin resultados -->
                    <div class="card">
                        <div class="card-body text-center p-5">
                            <i class="fas fa-search fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">No se encontraron horarios libres</h4>
                            <p class="text-muted">
                                No hay bloques de <?php echo $datos_busqueda['duracion'] ?? ''; ?> minutos disponibles 
                                para la maestra seleccionada en el período especificado.
                            </p>
                            <div class="alert alert-info">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>Sugerencias:</strong>
                                <ul class="list-unstyled mt-2 mb-0">
                                    <li>• Intenta con un rango de horario más amplio</li>
                                    <li>• Prueba con una duración diferente</li>
                                    <li>• Verifica otra maestra</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Estado inicial -->
                    <div class="card">
                        <div class="card-body text-center p-5">
                            <i class="fas fa-calendar-check fa-4x text-primary mb-3"></i>
                            <h4 class="text-primary">Consulta de Horarios Libres</h4>
                            <p class="text-muted">
                                Utiliza el formulario de la izquierda para buscar los horarios disponibles 
                                de una maestra en el período de tiempo que necesites.
                            </p>
                            <div class="row mt-4">
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <i class="fas fa-clock fa-2x text-info mb-2"></i>
                                        <h6>Período Flexible</h6>
                                        <small class="text-muted">Define tu rango de horario ideal</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <i class="fas fa-stopwatch fa-2x text-success mb-2"></i>
                                        <h6>Bloques de 30 o 45 min</h6>
                                        <small class="text-muted">Elige la duración que necesites</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <i class="fas fa-calendar-week fa-2x text-warning mb-2"></i>
                                        <h6>Vista Semanal</h6>
                                        <small class="text-muted">Revisa toda la semana de un vistazo</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2 para dropdown de maestra
            $('.select2').select2({
                placeholder: "Buscar maestra...",
                allowClear: true,
                language: {
                    noResults: function() {
                        return "No se encontraron resultados";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            });
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
            
            // Validación del formulario
            $('form').on('submit', function(e) {
                var inicio = $('#inicio_horario').val();
                var fin = $('#fin_horario').val();
                
                if (inicio && fin && inicio >= fin) {
                    e.preventDefault();
                    alert('La hora de inicio debe ser menor que la hora de fin');
                    return false;
                }
            });
        });
    </script>
</body>
</html>