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
        .navbar-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    padding: 1rem 0;
    border-bottom: 3px solid rgba(255,255,255,0.1);
}

.navbar-brand {
    font-weight: 700;
    font-size: 1.5rem;
    color: white !important;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.navbar-brand .brand-icon {
    background: rgba(255,255,255,0.2);
    padding: 0.5rem;
    border-radius: 10px;
    backdrop-filter: blur(10px);
}

.nav-menu {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn-nav {
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.25);
    color: white;
    padding: 0.6rem 1.2rem;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    white-space: nowrap;
}

.btn-nav:hover {
    background: rgba(255,255,255,0.25);
    border-color: rgba(255,255,255,0.4);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.btn-nav.btn-primary-alt {
    background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%);
    border-color: transparent;
}

.btn-nav.btn-primary-alt:hover {
    background: linear-gradient(135deg, #0072ff 0%, #00c6ff 100%);
    transform: translateY(-2px) scale(1.05);
}

.dropdown-nav {
    position: relative;
}

.dropdown-toggle-nav {
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.25);
    color: white;
    padding: 0.6rem 1.2rem;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.dropdown-toggle-nav:hover {
    background: rgba(255,255,255,0.25);
    border-color: rgba(255,255,255,0.4);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.dropdown-menu-nav {
    position: absolute;
    top: 100%;
    left: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    border: none;
    padding: 0.5rem 0;
    min-width: 220px;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    margin-top: 0.5rem;
}

.dropdown-nav.show .dropdown-menu-nav {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-item-nav {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.25rem;
    color: #333;
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.dropdown-item-nav:hover {
    background: linear-gradient(90deg, #f8f9ff 0%, #e3f2fd 100%);
    color: #667eea;
    border-left-color: #667eea;
    transform: translateX(5px);
}

.dropdown-item-nav .icon {
    width: 20px;
    text-align: center;
    font-size: 1rem;
}

.user-section {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-left: auto;
}

.user-info-card {
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.25);
    border-radius: 12px;
    padding: 0.75rem 1rem;
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar {
    background: rgba(255,255,255,0.3);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: white;
    font-size: 1.1rem;
}

.user-details {
    display: flex;
    flex-direction: column;
    color: white;
}

.user-name {
    font-weight: 600;
    font-size: 0.9rem;
    margin: 0;
}

.user-role {
    font-size: 0.75rem;
    opacity: 0.8;
    margin: 0;
}

.btn-logout {
    background: rgba(255,107,107,0.2);
    border: 1px solid rgba(255,107,107,0.4);
    color: white;
    padding: 0.6rem 1rem;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.btn-logout:hover {
    background: rgba(255,107,107,0.3);
    border-color: rgba(255,107,107,0.6);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255,107,107,0.3);
}

/* Responsividad mejorada */
@media (max-width: 1200px) {
    .nav-menu {
        gap: 0.75rem;
    }
    
    .btn-nav, .dropdown-toggle-nav {
        padding: 0.5rem 1rem;
        font-size: 0.85rem;
    }
}

@media (max-width: 992px) {
    .navbar-custom {
        padding: 0.75rem 0;
    }
    
    .nav-menu {
        gap: 0.5rem;
        margin-top: 1rem;
        width: 100%;
        justify-content: center;
    }
    
    .user-section {
        margin-left: 0;
        margin-top: 1rem;
        width: 100%;
        justify-content: space-between;
    }
    
    .user-info-card {
        flex: 1;
        margin-right: 1rem;
    }
}

@media (max-width: 768px) {
    .navbar-brand {
        font-size: 1.25rem;
    }
    
    .nav-menu {
        flex-direction: column;
        gap: 0.75rem;
        width: 100%;
    }
    
    .btn-nav, .dropdown-toggle-nav {
        width: 100%;
        justify-content: center;
        text-align: center;
    }
    
    .user-section {
        flex-direction: column;
        gap: 0.75rem;
        width: 100%;
    }
    
    .user-info-card, .btn-logout {
        width: 100%;
        justify-content: center;
    }
    
    .dropdown-menu-nav {
        position: relative;
        box-shadow: none;
        border: 1px solid #e9ecef;
        margin-top: 0.5rem;
        opacity: 1;
        visibility: visible;
        transform: none;
    }
}

/* Indicador de página activa */
.btn-nav.active {
    background: rgba(255,255,255,0.3);
    border-color: rgba(255,255,255,0.5);
    box-shadow: 0 0 15px rgba(255,255,255,0.3);
}
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-custom">
    <div class="container">
        <div class="row w-100 align-items-center">
            <!-- Brand -->
            <div class="col-lg-3 col-md-12 mb-lg-0 mb-3">
                <a class="navbar-brand" href="index.php">
                    <div class="brand-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div>
                        <div>Visualizar Horarios</div>
                        <small style="font-size: 0.7rem; opacity: 0.8;">Sistema de Gestión</small>
                    </div>
                </a>
            </div>

            <!-- Navegación principal -->
            <div class="col-lg-6 col-md-12 mb-lg-0 mb-3">
                <div class="nav-menu">
                    <?php if (isset($_SESSION["id_tipo"]) && ($_SESSION["id_tipo"] == 0 || $_SESSION["id_tipo"] == 2)): ?>
                        
                        <!-- Botón Horarios Libres -->
                        <a href="horarios_libres.php" class="btn-nav btn-primary-alt">
                            <i class="fas fa-search"></i>
                            <span>Horarios Libres</span>
                        </a>

                        <!-- Dropdown Administración -->
                        <div class="dropdown-nav">
                            <div class="dropdown-toggle-nav" onclick="toggleDropdown(this)">
                                <i class="fas fa-cog"></i>
                                <span>Administración</span>
                                <i class="fas fa-chevron-down" style="transition: transform 0.3s ease;"></i>
                            </div>
                            <div class="dropdown-menu-nav">
                                <div style="padding: 0.5rem 1rem; border-bottom: 1px solid #eee; margin-bottom: 0.5rem;">
                                    <small style="color: #666; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                        Gestión por día
                                    </small>
                                </div>
                                <a class="dropdown-item-nav" href="lunes/index.php">
                                    <i class="fas fa-calendar-day icon" style="color: #667eea;"></i>
                                    <div>
                                        <div style="font-weight: 500;">Lunes</div>
                                        <small style="color: #666;">Administrar horarios</small>
                                    </div>
                                </a>
                                <a class="dropdown-item-nav" href="martes/index.php">
                                    <i class="fas fa-calendar-day icon" style="color: #f5576c;"></i>
                                    <div>
                                        <div style="font-weight: 500;">Martes</div>
                                        <small style="color: #666;">Administrar horarios</small>
                                    </div>
                                </a>
                                <a class="dropdown-item-nav" href="miercoles/index.php">
                                    <i class="fas fa-calendar-day icon" style="color: #00f2fe;"></i>
                                    <div>
                                        <div style="font-weight: 500;">Miércoles</div>
                                        <small style="color: #666;">Administrar horarios</small>
                                    </div>
                                </a>
                                <a class="dropdown-item-nav" href="jueves/index.php">
                                    <i class="fas fa-calendar-day icon" style="color: #38f9d7;"></i>
                                    <div>
                                        <div style="font-weight: 500;">Jueves</div>
                                        <small style="color: #666;">Administrar horarios</small>
                                    </div>
                                </a>
                                <a class="dropdown-item-nav" href="viernes/index.php">
                                    <i class="fas fa-calendar-day icon" style="color: #fee140;"></i>
                                    <div>
                                        <div style="font-weight: 500;">Viernes</div>
                                        <small style="color: #666;">Administrar horarios</small>
                                    </div>
                                </a>
                                <a class="dropdown-item-nav" href="sabado/index.php">
                                    <i class="fas fa-calendar-day icon" style="color: #fed6e3;"></i>
                                    <div>
                                        <div style="font-weight: 500;">Sábado</div>
                                        <small style="color: #666;">Administrar horarios</small>
                                    </div>
                                </a>
                            </div>
                        </div>

                    <?php endif; ?>
                    
                    <!-- Botón adicional de navegación rápida -->
                    <a href="/blueadmin/horas.php" class="btn-nav">
                        <i class="fas fa-plus-circle"></i>
                        <span>Nuevo Registro</span>
                    </a>
                </div>
            </div>

            <!-- Sección de usuario -->
            <div class="col-lg-3 col-md-12">
                <div class="user-section">
                    <div class="user-info-card">
                        <div class="user-avatar">
                            <?php 
                            $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Usuario';
                            echo strtoupper(substr($username, 0, 1)); 
                            ?>
                        </div>
                        <div class="user-details">
                            <div class="user-name">
                                <?php echo htmlspecialchars($username); ?>
                            </div>
                            <div class="user-role">
                                <?php 
                                if (isset($_SESSION["id_tipo"])) {
                                    switch($_SESSION["id_tipo"]) {
                                        case 0: echo "Administrador"; break;
                                        case 1: echo "Maestra"; break;
                                        case 2: echo "Coordinador"; break;
                                        default: echo "Usuario"; break;
                                    }
                                } else {
                                    echo "Usuario";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <a href="/blueadmin/index.php" class="btn-logout" onclick="return confirm('¿Está seguro de que desea ir al inicio?')">
                        <i class="fas fa-home"></i>
                        <span>Inicio</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

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
                                                    <i class="fas fa-hourglass me-1"></i>Duración
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
                                            <?php
                                                // Calcular duración
                                                $inicio = strtotime($item['horario_inicio']);
                                                $fin = strtotime($item['horario_fin']);
                                                $duracion_minutos = ($fin - $inicio) / 60;
                                            ?>
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
                                                            <span class="badge bg-primary">
                                                                <i class="fas fa-users me-1"></i>Grupal
                                                            </span>
                                                        <?php break;
                                                        case 'Reposición': ?>
                                                            <span class="badge bg-warning">
                                                                <i class="fas fa-user me-1"></i>Reposición
                                                            </span>
                                                        <?php break;
                                                         case 'Pareja': ?>
                                                            <span class="badge bg-info">
                                                                <i class="fas fa-user me-1"></i>Pareja
                                                            </span>
                                                        <?php break;
                                                         case 'Completa': ?>
                                                            <span class="badge bg-secondary">
                                                                <i class="fas fa-user me-1"></i>Completa
                                                            </span>
                                                        <?php break;
                                                         case 'Adelanto': ?>
                                                            <span class="badge bg-warning">
                                                                <i class="fas fa-user me-1"></i>Adelanto
                                                            </span>
                                                        <?php break;
                                                            case 'Clase Muestra': ?>
                                                            <span class="badge bg-danger">
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
    <script>
function toggleDropdown(element) {
    const dropdown = element.parentElement;
    const chevron = element.querySelector('.fa-chevron-down');
    
    // Cerrar otros dropdowns
    document.querySelectorAll('.dropdown-nav.show').forEach(item => {
        if (item !== dropdown) {
            item.classList.remove('show');
            item.querySelector('.fa-chevron-down').style.transform = 'rotate(0deg)';
        }
    });
    
    // Toggle el dropdown actual
    dropdown.classList.toggle('show');
    
    // Animar el chevron
    if (dropdown.classList.contains('show')) {
        chevron.style.transform = 'rotate(180deg)';
    } else {
        chevron.style.transform = 'rotate(0deg)';
    }
}

// Cerrar dropdown al hacer click fuera
document.addEventListener('click', function(event) {
    if (!event.target.closest('.dropdown-nav')) {
        document.querySelectorAll('.dropdown-nav.show').forEach(dropdown => {
            dropdown.classList.remove('show');
            dropdown.querySelector('.fa-chevron-down').style.transform = 'rotate(0deg)';
        });
    }
});

// Marcar página activa
document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.btn-nav');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href'))) {
            link.classList.add('active');
        }
    });
});
</script>
</body>
</html>