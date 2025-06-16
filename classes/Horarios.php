<?php
/**
 * Clase para manejar los horarios de clases
 */

// Incluir configuración de base de datos con ruta absoluta
if (!class_exists('Database')) {
    $config_path = dirname(__DIR__) . '/config/database.php';
    if (file_exists($config_path)) {
        require_once $config_path;
    } else {
        // Ruta alternativa para cuando se incluye desde index.php
        require_once 'config/database.php';
    }
}

class Horarios {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Obtener todas las maestras
     */
    public function getMaestras() {
        $stmt = $this->db->query("SELECT id, nombre FROM maestras ORDER BY nombre");
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener todos los alumnos
     */
    public function getAlumnos() {
        $stmt = $this->db->query("SELECT id, nombre FROM h_alumnos ORDER BY nombre");
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener todas las ubicaciones
     */
    public function getUbicaciones() {
        $stmt = $this->db->query("SELECT id, ubicacion FROM ubicaciones ORDER BY ubicacion");
        return $stmt->fetchAll();
    }
    
    /**
     * Validar si un alumno ya tiene clase individual en el día especificado
     */
    public function alumnoTieneClaseIndividual($id_alumno, $dia_semana, $excluir_id = null) {
        $sql = "SELECT COUNT(*) as total FROM horarios_asignados 
                WHERE id_alumno = ? AND dia = ? AND tipo_clase = 'Individual'";
        
        if ($excluir_id) {
            $sql .= " AND id != ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_alumno, $dia_semana, $excluir_id]);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_alumno, $dia_semana]);
        }
        
        $result = $stmt->fetch();
        return $result['total'] > 0;
    }
    
    /**
     * Validar si una maestra ya tiene clase en el mismo horario y día
     */
    public function maestraTieneClaseEnHorario($id_maestra, $horario_inicio, $dia_semana, $excluir_id = null) {
        $sql = "SELECT COUNT(*) as total FROM horarios_asignados 
                WHERE id_maestra = ? AND dia = ? AND horario_inicio = ?";
        
        if ($excluir_id) {
            $sql .= " AND id != ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_maestra, $dia_semana, $horario_inicio, $excluir_id]);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_maestra, $dia_semana, $horario_inicio]);
        }
        
        $result = $stmt->fetch();
        return $result['total'] > 0;
    }
    
    /**
     * Validar si un alumno ya tiene clase grupal en el día especificado
     */
    public function alumnoTieneClaseGrupal($id_alumno, $dia_semana, $excluir_id = null) {
        $sql = "SELECT COUNT(*) as total FROM horarios_asignados 
                WHERE id_alumno = ? AND dia = ? AND tipo_clase = 'Grupal'";
        
        if ($excluir_id) {
            $sql .= " AND id != ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_alumno, $dia_semana, $excluir_id]);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_alumno, $dia_semana]);
        }
        
        $result = $stmt->fetch();
        return $result['total'] > 0;
    }
    
    /**
     * Validar horarios (inicio debe ser menor que fin)
     */
    public function validarHorarios($horario_inicio, $horario_fin) {
        return strtotime($horario_inicio) < strtotime($horario_fin);
    }
    
    /**
     * Validar todas las reglas de negocio
     */
    public function validarHorario($data, $excluir_id = null) {
        $errores = [];
        
        // Validar formato de horarios
        if (!validateTimeFormat($data['horario_inicio'])) {
            $errores[] = "El formato de horario de inicio no es válido (HH:MM)";
        }
        
        if (!validateTimeFormat($data['horario_fin'])) {
            $errores[] = "El formato de horario de fin no es válido (HH:MM)";
        }
        
        // Validar que horario inicio sea menor que horario fin
        if (!$this->validarHorarios($data['horario_inicio'], $data['horario_fin'])) {
            $errores[] = "El horario de inicio debe ser menor que el horario de fin";
        }
        
        // Obtener el día de la semana (por defecto 'Lunes' si no se especifica)
        $dia_semana = isset($data['dia_semana']) ? $data['dia_semana'] : 'Lunes';
        
        // Regla 1: Clase individual no puede ser asignada si ya existe una clase individual para ese alumno en el día
        if ($data['tipo_clase'] === 'Individual') {
            if ($this->alumnoTieneClaseIndividual($data['id_alumno'], $dia_semana, $excluir_id)) {
                $errores[] = "El alumno ya tiene una clase individual asignada el " . $dia_semana;
            }
        }
        
        // Regla 2: Clase individual no puede ser creada si ya existe una clase en el mismo horario con la misma maestra
        if ($data['tipo_clase'] === 'Individual') {
            if ($this->maestraTieneClaseEnHorario($data['id_maestra'], $data['horario_inicio'], $dia_semana, $excluir_id)) {
                $errores[] = "La maestra ya tiene una clase asignada en ese horario el " . $dia_semana;
            }
        }
        
        // Regla 3: Clase grupal no puede ser asignada si ya existe una clase grupal con ese alumno en el día
        if ($data['tipo_clase'] === 'Grupal') {
            if ($this->alumnoTieneClaseGrupal($data['id_alumno'], $dia_semana, $excluir_id)) {
                $errores[] = "El alumno ya tiene una clase grupal asignada el " . $dia_semana;
            }
        }
        
        return $errores;
    }
    
    /**
     * Insertar nuevo horario
     */
    public function insertarHorario($data) {
        // Obtener el día de la semana (por defecto 'Lunes' si no se especifica)
        $dia_semana = isset($data['dia_semana']) ? $data['dia_semana'] : 'Lunes';
        
        $sql = "INSERT INTO horarios_asignados (dia, horario_inicio, horario_fin, id_maestra, id_alumno, id_ubicacion, tipo_clase) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $dia_semana,
            $data['horario_inicio'],
            $data['horario_fin'],
            $data['id_maestra'],
            $data['id_alumno'],
            $data['id_ubicacion'],
            $data['tipo_clase']
        ]);
    }
    
    /**
     * Obtener horarios filtrados por día (opcional)
     */
    public function getHorarios($dia_semana = null) {
        $sql = "SELECT h.*, m.nombre as maestra_nombre, a.nombre as alumno_nombre, u.ubicacion 
                FROM horarios_asignados h
                JOIN maestras m ON h.id_maestra = m.id
                JOIN h_alumnos a ON h.id_alumno = a.id
                JOIN ubicaciones u ON h.id_ubicacion = u.id";
        
        if ($dia_semana) {
            $sql .= " WHERE h.dia = ?";
            $sql .= " ORDER BY h.horario_inicio, m.nombre";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dia_semana]);
        } else {
            // Si no se especifica día, mostrar solo Lunes por compatibilidad
            $sql .= " WHERE h.dia = 'Lunes'";
            $sql .= " ORDER BY h.horario_inicio, m.nombre";
            $stmt = $this->db->query($sql);
        }
        
        return $stmt->fetchAll();
    }
    
    /**
     * Eliminar horario
     */
    public function eliminarHorario($id) {
        $sql = "DELETE FROM horarios_asignados WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Obtener todos los días de la semana disponibles
     */
    public function getDiasSemana() {
        return [
            'Lunes' => 'Lunes',
            'Martes' => 'Martes', 
            'Miercoles' => 'Miércoles',
            'Jueves' => 'Jueves',
            'Viernes' => 'Viernes',
            'Sabado' => 'Sábado',
            'Domingo' => 'Domingo'
        ];
    }
    
    /**
     * Obtener horarios agrupados por día de la semana
     */
    public function getHorariosPorDia() {
    if (isset($_SESSION["id_tipo"]) && ($_SESSION["id_tipo"] == 3)) {
        $sql = "SELECT h.*, m.nombre as maestra_nombre, a.nombre as alumno_nombre, u.ubicacion 
                FROM horarios_asignados h
                JOIN maestras m ON h.id_maestra = m.id
                JOIN h_alumnos a ON h.id_alumno = a.id
                JOIN ubicaciones u ON h.id_ubicacion = u.id 
                WHERE m.id = ?
                ORDER BY h.dia, h.horario_inicio, m.nombre";
                
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$_SESSION["id_maestra"]]);
    }
    else {
        $sql = "SELECT h.*, m.nombre as maestra_nombre, a.nombre as alumno_nombre, u.ubicacion 
                FROM horarios_asignados h
                JOIN maestras m ON h.id_maestra = m.id
                JOIN h_alumnos a ON h.id_alumno = a.id
                JOIN ubicaciones u ON h.id_ubicacion = u.id 
                ORDER BY h.dia, h.horario_inicio, m.nombre";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }
    
    $todos_horarios = $stmt->fetchAll();
    
    // Agrupar por día
    $horarios_por_dia = [];
    foreach ($todos_horarios as $horario) {
        $horarios_por_dia[$horario['dia']][] = $horario;
    }
    
    return $horarios_por_dia;
    }
    public function getHorarioPorId($id) {
        $sql = "SELECT * FROM horarios_asignados WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
?>