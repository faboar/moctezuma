<?php
/**
 * Clase para manejar los horarios libres de las maestras
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

class HorariosLibres {
    private $db;
    private $dias_semana = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
    
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
     * Obtener horarios libres de 30 minutos para un día específico
     */
    public function getHorariosLibres30min($inicio_horario, $fin_horario, $id_maestra, $dia) {
        try {
            $sql = "CALL sp_encontrar_bloques_libres(?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$inicio_horario, $fin_horario, $id_maestra, $dia]);
            
            $result = $stmt->fetchAll();
            $stmt->closeCursor(); // Importante para procedimientos almacenados
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error en sp_encontrar_bloques_libres: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener horarios libres de 45 minutos para un día específico
     */
    public function getHorariosLibres45min($inicio_horario, $fin_horario, $id_maestra, $dia) {
        try {
            $sql = "CALL sp_encontrar_bloques_libres_45min(?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$inicio_horario, $fin_horario, $id_maestra, $dia]);
            
            $result = $stmt->fetchAll();
            $stmt->closeCursor(); // Importante para procedimientos almacenados
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error en sp_encontrar_bloques_libres_45min: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener horarios libres para todos los días de la semana
     */
    public function getHorariosLibresSemana($inicio_horario, $fin_horario, $id_maestra, $duracion) {
        $horarios_semana = [];
        
        foreach ($this->dias_semana as $dia) {
            if ($duracion === '30') {
                $horarios_semana[$dia] = $this->getHorariosLibres30min(
                    $inicio_horario, 
                    $fin_horario, 
                    $id_maestra, 
                    $dia
                );
            } else {
                $horarios_semana[$dia] = $this->getHorariosLibres45min(
                    $inicio_horario, 
                    $fin_horario, 
                    $id_maestra, 
                    $dia
                );
            }
        }
        
        return $horarios_semana;
    }
    
    /**
     * Validar datos del formulario
     */
    public function validarDatos($data) {
        $errores = [];
        
        // Validar formato de horarios
        if (!validateTimeFormat($data['inicio_horario'])) {
            $errores[] = "El formato de horario de inicio no es válido (HH:MM)";
        }
        
        if (!validateTimeFormat($data['fin_horario'])) {
            $errores[] = "El formato de horario de fin no es válido (HH:MM)";
        }
        
        // Validar que horario inicio sea menor que horario fin
        if (validateTimeFormat($data['inicio_horario']) && validateTimeFormat($data['fin_horario'])) {
            if (strtotime($data['inicio_horario']) >= strtotime($data['fin_horario'])) {
                $errores[] = "El horario de inicio debe ser menor que el horario de fin";
            }
        }
        
        // Validar maestra
        if (empty($data['id_maestra']) || !is_numeric($data['id_maestra'])) {
            $errores[] = "Debe seleccionar una maestra válida";
        }
        
        // Validar duración
        if (!in_array($data['duracion'], ['30', '45'])) {
            $errores[] = "Debe seleccionar una duración válida (30 o 45 minutos)";
        }
        
        return $errores;
    }
    
    /**
     * Obtener información de la maestra por ID
     */
    public function getMaestraPorId($id) {
        $sql = "SELECT id, nombre FROM maestras WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Formatear horario para mostrar
     */
    public function formatearHorario($inicio, $fin) {
        return formatTime($inicio) . ' - ' . formatTime($fin);
    }
    
    /**
     * Contar total de bloques libres
     */
    public function contarBloquesLibres($horarios_semana) {
        $total = 0;
        foreach ($horarios_semana as $dia => $horarios) {
            $total += count($horarios);
        }
        return $total;
    }
    
    /**
     * Obtener estadísticas de disponibilidad
     */
    public function getEstadisticas($horarios_semana) {
        $stats = [
            'total_bloques' => 0,
            'dias_con_disponibilidad' => 0,
            'mejor_dia' => '',
            'mejor_dia_bloques' => 0
        ];
        
        foreach ($horarios_semana as $dia => $horarios) {
            $cantidad = count($horarios);
            $stats['total_bloques'] += $cantidad;
            
            if ($cantidad > 0) {
                $stats['dias_con_disponibilidad']++;
            }
            
            if ($cantidad > $stats['mejor_dia_bloques']) {
                $stats['mejor_dia'] = $dia;
                $stats['mejor_dia_bloques'] = $cantidad;
            }
        }
        
        return $stats;
    }
}
?>