<?php
/**
 * DashboardController - Dashboard Moderno
 */

namespace App\Controllers;

class DashboardController extends BaseController {
    
    /**
     * Dashboard principal
     */
    public function index($request = null) {
        // Requerir autenticación
        $this->requireAuth();
        
        // Obtener estadísticas
        $stats = $this->getStats();
        
        // Obtener actividad reciente
        $activity = $this->getRecentActivity();
        
        // Preparar datos para la vista
        $data = [
            'title' => 'Dashboard',
            'stats' => $stats,
            'activity' => $activity,
            'notifications' => $this->getNotifications(),
            'breadcrumbs' => [
                ['title' => 'Dashboard']
            ]
        ];
        
        return $this->view('dashboard.index', $data);
    }
    
    /**
     * Obtener estadísticas del dashboard
     */
    private function getStats() {
        $stats = [
            'total_members' => 0,
            'active_members' => 0,
            'total_events' => 0,
            'recent_attendance' => 0
        ];
        
        try {
            $db = app('db');
            
            if ($db) {
                // Total de miembros
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM members WHERE is_active = 1");
                $stmt->execute();
                $result = $stmt->fetch();
                $stats['total_members'] = (int)($result['count'] ?? 0);
                $stats['active_members'] = $stats['total_members'];
                
                // Asistencia reciente (últimos 7 días)
                $stmt = $db->prepare("
                    SELECT COUNT(*) as count 
                    FROM attendance_log 
                    WHERE scan_datetime >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ");
                $stmt->execute();
                $result = $stmt->fetch();
                $stats['recent_attendance'] = (int)($result['count'] ?? 0);
            }
            
        } catch (\Exception $e) {
            error_log('Error getting stats: ' . $e->getMessage());
        }
        
        return $stats;
    }
    
    /**
     * Obtener actividad reciente
     */
    private function getRecentActivity() {
        $activity = [];
        
        try {
            $db = app('db');
            
            if ($db) {
                $stmt = $db->prepare("
                    SELECT 
                        al.scan_datetime,
                        m.name,
                        m.surname,
                        al.event_name
                    FROM attendance_log al
                    JOIN members m ON al.member_id = m.id
                    ORDER BY al.scan_datetime DESC
                    LIMIT 10
                ");
                $stmt->execute();
                $activity = $stmt->fetchAll();
            }
            
        } catch (\Exception $e) {
            error_log('Error getting activity: ' . $e->getMessage());
        }
        
        return $activity;
    }
    
    /**
     * Obtener notificaciones del usuario
     */
    private function getNotifications() {
        $notifications = [];
        
        // Notificaciones según nivel de usuario
        if ($this->hasPermission(9)) {
            $notifications[] = [
                'type' => 'success',
                'message' => '🎉 Sistema migrado exitosamente a arquitectura moderna',
                'time' => date('H:i')
            ];
        }
        
        return $notifications;
    }
}
?>