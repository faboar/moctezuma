<?php
/**
 * Sistema de Horarios de Clases - Página Principal
 */

// Definir ruta base del proyecto (un nivel arriba del directorio actual)
define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/classes/Horarios.php';

$horarios = new Horarios();
$errores = [];
$mensaje_exito = '';

// --- NUEVO BLOQUE: Procesar eliminación si se recibe una solicitud POST de eliminación ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar_horario') {
    if (isset($_POST['id_horario']) && is_numeric($_POST['id_horario'])) {
        $id_a_eliminar = (int)$_POST['id_horario'];
        if ($horarios->eliminarHorario($id_a_eliminar)) {
            $mensaje_exito = "Horario eliminado exitosamente.";
        } else {
            $errores[] = "Error al eliminar el horario. Por favor, inténtelo de nuevo.";
        }
    } else {
        $errores[] = "ID de horario no válido para eliminar.";
    }
    // Redirigir para evitar reenvío del formulario al recargar la página (PRG Pattern)
    // Esto es crucial para que los mensajes de éxito/error se muestren una vez y el formulario no se reenvíe.
    header("Location: index.php?status=" . (empty($errores) ? 'success' : 'error'));
    exit;
}
// --- FIN NUEVO BLOQUE ---

// Procesar formulario de AGREGAR horario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['horario_inicio'])) { // Asegurarse de que sea el formulario de agregar
    $data = [
        'horario_inicio' => sanitize($_POST['horario_inicio']),
        'horario_fin' => sanitize($_POST['horario_fin']),
        'id_maestra' => (int)$_POST['id_maestra'],
        'id_alumno' => (int)$_POST['id_alumno'],
        'id_ubicacion' => (int)$_POST['id_ubicacion'],
        'tipo_clase' => sanitize($_POST['tipo_clase']),
        'dia_semana' => 'Viernes'
    ];
    
    // Validar datos
    $errores = $horarios->validarHorario($data);
    
    if (empty($errores)) {
        if ($horarios->insertarHorario($data)) {
            $mensaje_exito = "Horario registrado exitosamente";
            // Limpiar formulario
            $_POST = [];
            // Redirigir para evitar reenvío del formulario (PRG Pattern)
            header("Location: index.php?status=success_add");
            exit;
        } else {
            $errores[] = "Error al guardar el horario";
        }
    }
}

// --- Manejar mensajes después de redirección ---
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') {
        $mensaje_exito = "Operación realizada exitosamente."; // Mensaje genérico para éxito en eliminación
    } elseif ($_GET['status'] === 'success_add') {
        $mensaje_exito = "Horario registrado exitosamente.";
    } elseif ($_GET['status'] === 'error') {
        $errores[] = "Hubo un error durante la operación."; // Mensaje genérico para error en eliminación
    }
    // Puedes añadir más lógica si necesitas errores específicos desde la eliminación
}
// --- FIN Manejar mensajes ---

// Obtener datos para los dropdowns
$maestras = $horarios->getMaestras();
$alumnos = $horarios->getAlumnos();
$ubicaciones = $horarios->getUbicaciones();
$lista_horarios = $horarios->getHorarios('Viernes'); // Se mantiene 'Viernes' como día por defecto

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Horarios de Clases - Viernes</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .select2-container {
            width: 100% !important;
        }
        .table-responsive {
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,.1);
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
        .alert-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
        .duracion-botones {
            margin-top: 0.5rem;
        }
        .duracion-botones .btn {
            margin-right: 0.25rem;
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0"><i class="fas fa-calendar-alt me-3"></i>Sistema de Horarios</h1>
                    <p class="mb-0 mt-2">Gestión de clases del día Viernes</p>
                </div>
                <div class="col-md-3 text-center">
                    <!-- Botón Volver -->
                    <a href="../index.php" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Volver al Sistema
                    </a>
                    
                    <!-- Dropdown Administración de Horarios -->
                    <div class="btn-group">
                        <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cog me-2"></i>Administración de Horarios
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../lunes/index.php">
                                <i class="fas fa-calendar-day me-2 text-info"></i>Lunes
                            </a></li>
                            <li><a class="dropdown-item" href="../martes/index.php">
                                <i class="fas fa-calendar-day me-2 text-danger"></i>Martes
                            </a></li>
                            <li><a class="dropdown-item" href="../miercoles/index.php">
                                <i class="fas fa-calendar-day me-2 text-success"></i>Miércoles
                            </a></li>
                            <li><a class="dropdown-item" href="../jueves/index.php">
                                <i class="fas fa-calendar-day me-2 text-warning"></i>Jueves
                            </a></li>
                            <li><a class="dropdown-item" href="../sabado/index.php">
                                <i class="fas fa-calendar-day me-2 text-secondary"></i>Sábado
                            </a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="badge bg-light text-dark fs-6">
                        <i class="fas fa-clock me-2"></i><?php date_default_timezone_set('America/Mexico_City'); echo date('d/m/Y H:i'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
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

        <?php if ($mensaje_exito): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong>¡Éxito!</strong> <?php echo $mensaje_exito; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Registrar Nuevo Horario</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" id="formHorario">
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label for="horario_inicio" class="form-label">
                                        <i class="fas fa-clock me-1"></i>Horario Inicio
                                    </label>
                                    <input type="time" class="form-control" id="horario_inicio" name="horario_inicio" 
                                           value="<?php echo $_POST['horario_inicio'] ?? ''; ?>" required>
                                </div>
                                <div class="col-6">
                                    <label for="horario_fin" class="form-label">
                                        <i class="fas fa-clock me-1"></i>Horario Fin
                                    </label>
                                    <input type="time" class="form-control" id="horario_fin" name="horario_fin" 
                                           value="<?php echo $_POST['horario_fin'] ?? ''; ?>" required>
                                    <div class="duracion-botones">
                                        <small class="text-muted d-block mb-1">Duraciones rápidas:</small>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="establecerDuracion(30)">30 min</button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="establecerDuracion(45)">45 min</button>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="id_maestra" class="form-label">
                                    <i class="fas fa-chalkboard-teacher me-1"></i>Maestra
                                </label>
                                <select class="form-select select2" id="id_maestra" name="id_maestra" required>
                                    <option value="">Seleccione una maestra...</option>
                                    <?php foreach ($maestras as $maestra): ?>
                                        <option value="<?php echo $maestra['id']; ?>" 
                                                <?php echo (isset($_POST['id_maestra']) && $_POST['id_maestra'] == $maestra['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($maestra['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="id_alumno" class="form-label">
                                    <i class="fas fa-user-graduate me-1"></i>Alumno
                                </label>
                                <select class="form-select select2" id="id_alumno" name="id_alumno" required>
                                    <option value="">Seleccione un alumno...</option>
                                    <?php foreach ($alumnos as $alumno): ?>
                                        <option value="<?php echo $alumno['id']; ?>" 
                                                <?php echo (isset($_POST['id_alumno']) && $_POST['id_alumno'] == $alumno['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($alumno['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="id_ubicacion" class="form-label">
                                    <i class="fas fa-map-marker-alt me-1"></i>Ubicación
                                </label>
                                <select class="form-select" id="id_ubicacion" name="id_ubicacion" required>
                                    <option value="">Seleccione una ubicación...</option>
                                    <?php 
                                        $contador = 0;
                                        foreach ($ubicaciones as $ubicacion): 
                                        $contador++;
                                        $is_selected = false;
        
                                        // Si hay POST, usar el valor enviado
                                        if (isset($_POST['id_ubicacion'])) {
                                            $is_selected = ($_POST['id_ubicacion'] == $ubicacion['id']);
                                        } 
                                        // Si no hay POST, seleccionar el segundo elemento (contador == 2)
                                        else {
                                            $is_selected = ($contador == 3);
                                        }
                                    ?>
                                    <option value="<?php echo $ubicacion['id']; ?>" 
                                        <?php echo $is_selected ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($ubicacion['ubicacion']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="tipo_clase" class="form-label">
                                    <i class="fas fa-users me-1"></i>Tipo de Clase
                                </label>
                                <select class="form-select" id="tipo_clase" name="tipo_clase" required>
                                    <option value="">Seleccione el tipo...</option>
                                    <option value="Individual" <?php echo (!isset($_POST['tipo_clase']) || $_POST['tipo_clase'] == 'Individual') ? 'selected' : ''; ?>>
                                        Individual
                                    </option>
                                    <option value="Grupal" <?php echo (isset($_POST['tipo_clase']) && $_POST['tipo_clase'] == 'Grupal') ? 'selected' : ''; ?>>
                                        Grupal
                                    </option>
                                    <option value="Reposición" <?php echo (isset($_POST['tipo_clase']) && $_POST['tipo_clase'] == 'Reposición') ? 'selected' : ''; ?>>
                                        Reposición
                                    </option>
                                    <option value="Clase Muestra" <?php echo (isset($_POST['tipo_clase']) && $_POST['tipo_clase'] == 'Clase Muestra') ? 'selected' : ''; ?>>
                                        Clase Muestra
                                    </option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save me-2"></i>Registrar Horario
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Horarios Registrados
                            <span class="badge bg-light text-dark ms-2"><?php echo count($lista_horarios); ?></span>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($lista_horarios)): ?>
                            <div class="text-center p-5">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No hay horarios registrados para el día Viernes.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Día</th>
                                            <th>Horario</th>
                                            <th>Duración</th>
                                            <th>Maestra</th>
                                            <th>Alumno</th>
                                            <th>Ubicación</th>
                                            <th>Tipo</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($lista_horarios as $item): ?>
                                            <?php
                                            // Calcular duración
                                            $inicio = strtotime($item['horario_inicio']);
                                            $fin = strtotime($item['horario_fin']);
                                            $duracion_minutos = ($fin - $inicio) / 60;
                                            ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        <i class="fas fa-calendar me-1"></i><?php echo $item['dia']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong><?php echo formatTime($item['horario_inicio']); ?></strong>
                                                    -
                                                    <strong><?php echo formatTime($item['horario_fin']); ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo ($duracion_minutos == 30 || $duracion_minutos == 45) ? 'bg-success' : 'bg-warning'; ?>">
                                                        <?php echo $duracion_minutos; ?> min
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($item['maestra_nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($item['alumno_nombre']); ?></td>
                                                <td>
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    <?php echo htmlspecialchars($item['ubicacion']); ?>
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
                                                <td>
                                                    <form method="POST" action="" class="d-inline">
                                                        <input type="hidden" name="accion" value="eliminar_horario">
                                                        <input type="hidden" name="id_horario" value="<?php echo $item['id']; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2 para dropdowns con búsqueda
            $('.select2').select2({
                placeholder: "Buscar...",
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
            
            // Validación de duración en tiempo real
            function validarDuracion() {
                var inicio = $('#horario_inicio').val();
                var fin = $('#horario_fin').val();
                
                if (inicio && fin) {
                    var inicioMinutos = convertirAMinutos(inicio);
                    var finMinutos = convertirAMinutos(fin);
                    var diferencia = finMinutos - inicioMinutos;
                    
                    // Limpiar mensajes anteriores
                    $('#mensaje-duracion').remove();
                    
                    if (diferencia <= 0) {
                        mostrarMensajeDuracion('El horario de fin debe ser mayor que el de inicio', 'danger');
                        return false;
                    } else if (diferencia !== 30 && diferencia !== 45) {
                        mostrarMensajeDuracion('La duración debe ser exactamente 30 o 45 minutos. Duración actual: ' + diferencia + ' minutos', 'warning');
                        return false;
                    } else {
                        mostrarMensajeDuracion('Duración válida: ' + diferencia + ' minutos', 'success');
                        return true;
                    }
                }
                return true;
            }
            
            function convertirAMinutos(tiempo) {
                var partes = tiempo.split(':');
                return parseInt(partes[0]) * 60 + parseInt(partes[1]);
            }
            
            function mostrarMensajeDuracion(mensaje, tipo) {
                var html = '<div id="mensaje-duracion" class="alert alert-' + tipo + ' mt-2">' +
                          '<i class="fas fa-clock me-2"></i>' + mensaje + '</div>';
                $('#horario_fin').closest('.col-6').append(html);
            }
            
            // Validar duración cuando cambian los horarios
            $('#horario_inicio, #horario_fin').on('change', function() {
                validarDuracion();
            });
            
            // Validación del formulario al enviar
            $('#formHorario').on('submit', function(e) {
                var inicio = $('#horario_inicio').val();
                var fin = $('#horario_fin').val();
                
                if (inicio && fin) {
                    var inicioMinutos = convertirAMinutos(inicio);
                    var finMinutos = convertirAMinutos(fin);
                    var diferencia = finMinutos - inicioMinutos;
                    
                    if (diferencia <= 0) {
                        e.preventDefault();
                        alert('El horario de fin debe ser mayor que el de inicio');
                        return false;
                    }
                    
                    if (diferencia !== 30 && diferencia !== 45) {
                        e.preventDefault();
                        alert('La duración debe ser exactamente 30 o 45 minutos.\nDuración actual: ' + diferencia + ' minutos');
                        return false;
                    }
                }
            });
        });
        
        // Función global para establecer duración automáticamente
        function establecerDuracion(minutos) {
            var inicio = $('#horario_inicio').val();
            if (inicio) {
                var inicioMinutos = parseInt(inicio.split(':')[0]) * 60 + parseInt(inicio.split(':')[1]);
                var finMinutos = inicioMinutos + minutos;
                var horas = Math.floor(finMinutos / 60);
                var mins = finMinutos % 60;
                
                var fin = String(horas).padStart(2, '0') + ':' + String(mins).padStart(2, '0');
                $('#horario_fin').val(fin);
                $('#horario_fin').trigger('change');
            } else {
                alert('Primero seleccione la hora de inicio');
            }
        }
    </script>
</body>
</html>