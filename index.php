<?php
/**
 * Sistema de Horarios de Clases - Visualizar Horarios
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
require_once BASE_PATH . '/classes/Horarios.php';

$horarios = new Horarios();

// Obtener horarios agrupados por día
$horarios_por_dia = $horarios->getHorariosPorDia();

// Calcular estadísticas generales
$total_horarios = 0;
$dias_con_horarios = 0;
foreach (['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'] as $dia) {
    $count = isset($horarios_por_dia[$dia]) ? count($horarios_por_dia[$dia]) : 0;
    $total_horarios += $count;
    if ($count > 0) $dias_con_horarios++;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Horarios - Sistema de Horarios de Clases</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
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
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .nav-tabs .nav-link {
            border-radius: 8px 8px 0 0;
            border: none;
            margin-right: 5px;
            color: #667eea;
            font-weight: 500;
        }
        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        .nav-tabs .nav-link:hover {
            background-color: #f8f9fa;
            border: none;
        }
        .table-responsive {
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,.05);
        }
        .badge-count {
            background: linear-gradient(135deg, #ff6b6b 0%, #ffa500 100%);
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
        }
        .estadisticas {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .tab-content {
            margin-top: 20px;
        }
        .sin-horarios {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 40px 20px;
        }
        .dia-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .lunes { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .martes { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
        .miercoles { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; }
        .jueves { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; }
        .viernes { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; }
        .sabado { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333; }
        .btn-floating {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        .user-info {
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
        .logout-btn {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            border-radius: 6px;
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Header -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="mb-0"><i class="fas fa-eye me-3"></i>Visualizar Horarios</h1>
                    <p class="mb-0 mt-2">Vista de consulta de horarios semanales</p>
                </div>
                <div class="col-md-3 text-center">
                    <?php if (isset($_SESSION["id_tipo"]) && ($_SESSION["id_tipo"] == 0 || $_SESSION["id_tipo"] == 2)): ?>
                    <!-- Botón Horarios Libres -->
                        <a href="horarios_libres.php" class="btn btn-light me-2">
                            <i class="fas fa-search me-2"></i>Horarios Libres
                        </a>
       
                        <!-- Dropdown Administración de Horarios -->
                        <div class="btn-group">
                            <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-cog me-2"></i>Administración de Horarios
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="lunes/index.php">
                                    <i class="fas fa-calendar-day me-2 text-primary"></i>Lunes
                                </a></li>
                                <li><a class="dropdown-item" href="martes/index.php">
                                    <i class="fas fa-calendar-day me-2 text-danger"></i>Martes
                                </a></li>
                                <li><a class="dropdown-item" href="miercoles/index.php">
                                    <i class="fas fa-calendar-day me-2 text-info"></i>Miércoles
                                </a></li>
                                <li><a class="dropdown-item" href="jueves/index.php">
                                    <i class="fas fa-calendar-day me-2 text-success"></i>Jueves
                                </a></li>
                                <li><a class="dropdown-item" href="viernes/index.php">
                                    <i class="fas fa-calendar-day me-2 text-warning"></i>Viernes
                                </a></li>
                                <li><a class="dropdown-item" href="sabado/index.php">
                                    <i class="fas fa-calendar-day me-2 text-secondary"></i>Sábado
                                </a></li>
                            </ul>
                            </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-3 text-end">
                    <div class="user-info mb-2">
                        <i class="fas fa-user me-2"></i>
                        <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Usuario'; ?>
                    </div>
                    <a href=" /blueadmin/horas.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt me-1"></i>Ir al Inicio
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Estadísticas Generales -->
        <div class="estadisticas">
            <div class="row text-center">
                <div class="col-md-3">
                    <h2 class="mb-0"><?php echo $total_horarios; ?></h2>
                    <small>Total de Horarios</small>
                </div>
                <div class="col-md-3">
                    <h2 class="mb-0"><?php echo $dias_con_horarios; ?>/6</h2>
                    <small>Días con Horarios</small>
                </div>
                <div class="col-md-3">
                    <h2 class="mb-0">
                        <?php 
                        $dia_con_mas = '';
                        $max_horarios = 0;
                        foreach (['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'] as $dia) {
                            $count = isset($horarios_por_dia[$dia]) ? count($horarios_por_dia[$dia]) : 0;
                            if ($count > $max_horarios) {
                                $max_horarios = $count;
                                $dia_con_mas = $dia === 'Miercoles' ? 'Miércoles' : $dia;
                            }
                        }
                        echo $dia_con_mas ?: 'N/A';
                        ?>
                    </h2>
                    <small>Día con Más Horarios</small>
                </div>
                <div class="col-md-3">
                    <h2 class="mb-0">
                        <?php 
                        // Configurar zona horaria de México
                        date_default_timezone_set('America/Mexico_City');
                        echo date('d/m/Y H:i'); 
                        ?>
                    </h2>
                    <small>Última Actualización</small>
                </div>
            </div>
        </div>

        <!-- Horarios por Día -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-eye me-2"></i>Vista de Horarios por Día de la Semana
                    <span class="badge bg-light text-primary ms-2">Solo Lectura</span>
                </h5>
            </div>
            <div class="card-body">
                <!-- Nav Tabs -->
                <ul class="nav nav-tabs" id="diasTabs" role="tablist">
                    <?php 
                    $dias_display = [
                        'Lunes' => 'lunes',
                        'Martes' => 'martes', 
                        'Miercoles' => 'miercoles',
                        'Jueves' => 'jueves',
                        'Viernes' => 'viernes',
                        'Sabado' => 'sabado'
                    ];
                    $first = true;
                    foreach ($dias_display as $dia => $clase_css): 
                        $dia_label = $dia === 'Miercoles' ? 'Miércoles' : $dia;
                        $count = isset($horarios_por_dia[$dia]) ? count($horarios_por_dia[$dia]) : 0;
                    ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo $first ? 'active' : ''; ?>" 
                                    id="<?php echo strtolower($dia); ?>-tab" 
                                    data-bs-toggle="tab" 
                                    data-bs-target="#<?php echo strtolower($dia); ?>" 
                                    type="button" 
                                    role="tab">
                                <i class="fas fa-calendar-day me-1"></i>
                                <?php echo $dia_label; ?>
                                <span class="badge-count ms-1"><?php echo $count; ?></span>
                            </button>
                        </li>
                    <?php 
                        $first = false;
                    endforeach; 
                    ?>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="diasTabsContent">
                    <?php 
                    $first = true;
                    foreach ($dias_display as $dia => $clase_css): 
                        $horarios_del_dia = isset($horarios_por_dia[$dia]) ? $horarios_por_dia[$dia] : [];
                        $dia_label = $dia === 'Miercoles' ? 'Miércoles' : $dia;
                    ?>
                        <div class="tab-pane fade <?php echo $first ? 'show active' : ''; ?>" 
                             id="<?php echo strtolower($dia); ?>" 
                             role="tabpanel">
                             
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">
                                    <span class="dia-badge <?php echo $clase_css; ?>">
                                        <i class="fas fa-calendar-day me-1"></i><?php echo $dia_label; ?>
                                    </span>
                                    <span class="text-muted ms-2">
                                        <?php echo count($horarios_del_dia); ?> 
                                        <?php echo count($horarios_del_dia) === 1 ? 'horario' : 'horarios'; ?> registrado<?php echo count($horarios_del_dia) === 1 ? '' : 's'; ?>
                                    </span>
                                </h6>
                                
                                <?php if (!empty($horarios_del_dia)): ?>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        <?php 
                                        $primer_horario = formatTime($horarios_del_dia[0]['horario_inicio']);
                                        $ultimo_horario = formatTime(end($horarios_del_dia)['horario_fin']);
                                        echo "Desde {$primer_horario} hasta {$ultimo_horario}";
                                        ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                             
                            <?php if (empty($horarios_del_dia)): ?>
                                <div class="sin-horarios">
                                    <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No hay horarios para <?php echo $dia_label; ?></h5>
                                    <p class="text-muted">Aún no se han registrado clases para este día.</p>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Información:</strong> Para registrar horarios, utilice la página principal del sistema.
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th width="20%">
                                                    <i class="fas fa-clock me-1"></i>Horario
                                                </th>
                                                 <th width="20%">
                                                    <i class="fas fa-clock me-1"></i>Duración
                                                </th>
                                                <th width="25%">
                                                    <i class="fas fa-chalkboard-teacher me-1"></i>Maestra
                                                </th>
                                                <th width="25%">
                                                    <i class="fas fa-user-graduate me-1"></i>Alumno
                                                </th>
                                                <th width="20%">
                                                    <i class="fas fa-map-marker-alt me-1"></i>Ubicación
                                                </th>
                                                <th width="10%">
                                                    <i class="fas fa-users me-1"></i>Tipo
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($horarios_del_dia as $item): ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold text-primary">
                                                            <?php echo formatTime($item['horario_inicio']); ?>
                                                        </div>
                                                        <small class="text-muted">
                                                            hasta <?php echo formatTime($item['horario_fin']); ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <span class="badge <?php echo ($duracion_minutos == 30 || $duracion_minutos == 45) ? 'bg-success' : 'bg-warning'; ?>">
                                                            <?php echo $duracion_minutos; ?> min
                                                        </span>
                                                    </td>


                                                    <td>
                                                        <div class="fw-bold">
                                                            <?php echo htmlspecialchars($item['maestra_nombre']); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <?php 
                                                            // Mostrar solo los nombres (sin apellidos)
                                                            $nombre_completo = htmlspecialchars($item['alumno_nombre']);
                                                            $palabras = explode(' ', $nombre_completo);
                                                            echo $palabras[0];
                                                            ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark">
                                                            <i class="fas fa-map-marker-alt me-1"></i>
                                                            <?php echo htmlspecialchars($item['ubicacion']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                    <?php switch ($item['tipo_clase']):
                                                        case 'Grupal': ?>
                                                            <span class="badge bg-info">
                                                                <i class="fas fa-users me-1"></i>Grupal
                                                            </span>
                                                        <?php break;
                                                        case 'Reposición': ?>
                                                            <span class="badge bg-secondary">
                                                                <i class="fas fa-user me-1"></i>Reposición
                                                            </span>
                                                        <?php break;
                                                            case 'Clase Muestra': ?>
                                                            <span class="badge bg-warning">
                                                                <i class="fas fa-user me-1"></i>Clase Muestra
                                                            </span>
                                                        <?php break;
                                                        default: ?>
                                                            <span class="badge bg-success">
                                                                <i class="fas fa-user me-1"></i>Individual
                                                            </span>
                                                    <?php endswitch; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Resumen del día -->
                                <div class="mt-3 p-3 bg-light rounded">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <strong class="text-primary"><?php echo count($horarios_del_dia); ?></strong>
                                            <small class="d-block text-muted">Total Clases</small>
                                        </div>
                                                                                <div class="col-md-3">
                                            <strong class="text-warning">
                                                <?php 
                                                $maestras_unicas = array_unique(array_column($horarios_del_dia, 'maestra_nombre'));
                                                echo count($maestras_unicas);
                                                ?>
                                            </strong>
                                            <small class="d-block text-muted">Maestras</small>
                                        </div>
                                    </div>
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <strong class="text-success">
                                                <?php 
                                                $individuales = array_filter($horarios_del_dia, function($h) { return $h['tipo_clase'] === 'Individual'; });
                                                echo count($individuales);
                                                ?>
                                            </strong>
                                            <small class="d-block text-muted">Individuales</small>
                                        </div>
                                        <div class="col-md-3">
                                            <strong class="text-info">
                                                <?php 
                                                $grupales = array_filter($horarios_del_dia, function($h) { return $h['tipo_clase'] === 'Grupal'; });
                                                echo count($grupales);
                                                ?>
                                            </strong>
                                            <small class="d-block text-muted">Grupales</small>
                                        </div>
                                                                                <div class="col-md-3">
                                            <strong class="text-info">
                                                <?php 
                                                $grupales = array_filter($horarios_del_dia, function($h) { return $h['tipo_clase'] === 'Reposición'; });
                                                echo count($grupales);
                                                ?>
                                            </strong>
                                            <small class="d-block text-muted">Reposiciones</small>
                                        </div>
                                                                                <div class="col-md-3">
                                            <strong class="text-info">
                                                <?php 
                                                $grupales = array_filter($horarios_del_dia, function($h) { return $h['tipo_clase'] === 'Clase Muestra'; });
                                                echo count($grupales);
                                                ?>
                                            </strong>
                                            <small class="d-block text-muted">Clases Muestra</small>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php 
                        $first = false;
                    endforeach; 
                    ?>
                </div>
            </div>
        </div>
    </div>

    

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Activar tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Cambio de tab con animación
            $('#diasTabs button').on('shown.bs.tab', function (e) {
                $(e.target.getAttribute('data-bs-target')).find('.table').hide().fadeIn(300);
            });
            
            // Resaltar tab activo
            $('.nav-tabs .nav-link').on('click', function() {
                $('.nav-tabs .nav-link').removeClass('active');
                $(this).addClass('active');
            });
            
            // Confirmación al cerrar sesión
            $('a[href*="logout"]').on('click', function(e) {
                if (!confirm('¿Está seguro de que desea cerrar la sesión?')) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
</body>
</html>