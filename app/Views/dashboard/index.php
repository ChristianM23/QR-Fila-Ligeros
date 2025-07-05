<div class="dashboard">
    <!-- Page Header -->
    <div class="page-header">
        <h1>🚀 Dashboard</h1>
        <p>Bienvenido al sistema moderno de gestión de miembros</p>
    </div>
    
    <!-- Notifications -->
    <?php if (!empty($notifications)): ?>
        <div class="notifications-section">
            <?php foreach ($notifications as $notification): ?>
                <div class="alert alert-<?= $notification['type'] ?>">
                    <?= htmlspecialchars($notification['message']) ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <div class="stat-number"><?= $stats['total_members'] ?></div>
                <div class="stat-label">Miembros Activos</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-content">
                <div class="stat-number"><?= $stats['total_events'] ?></div>
                <div class="stat-label">Eventos Totales</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
                <div class="stat-number"><?= $stats['recent_attendance'] ?></div>
                <div class="stat-label">Asistencias (7 días)</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">🔧</div>
            <div class="stat-content">
                <div class="stat-number">v2.0</div>
                <div class="stat-label">Sistema Moderno</div>
            </div>
        </div>
    </div>
    
    <!-- Content Grid -->
    <div class="content-grid">
        <!-- Recent Activity -->
        <div class="content-card">
            <h3>📋 Actividad Reciente</h3>
            <?php if (!empty($activity)): ?>
                <div class="activity-list">
                    <?php foreach (array_slice($activity, 0, 5) as $item): ?>
                        <div class="activity-item">
                            <div class="activity-user">
                                <?= htmlspecialchars($item['name'] . ' ' . $item['surname']) ?>
                            </div>
                            <div class="activity-details">
                                <?= htmlspecialchars($item['event_name'] ?? 'Asistencia General') ?> - 
                                <?= date('d/m/Y H:i', strtotime($item['scan_datetime'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>No hay actividad reciente</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Quick Actions -->
        <div class="content-card">
            <h3>⚡ Acciones Rápidas</h3>
            <div class="quick-actions">
                <a href="/members/create" class="action-btn primary">
                    <span class="btn-icon">➕</span>
                    <span class="btn-text">Nuevo Miembro</span>
                </a>
                <a href="/events/create" class="action-btn success">
                    <span class="btn-icon">📅</span>
                    <span class="btn-text">Nuevo Evento</span>
                </a>
                <a href="/qr/scan" class="action-btn info">
                    <span class="btn-icon">📱</span>
                    <span class="btn-text">Escanear QR</span>
                </a>
                <a href="/members" class="action-btn default">
                    <span class="btn-icon">👥</span>
                    <span class="btn-text">Ver Miembros</span>
                </a>
            </div>
        </div>
        
        <!-- System Status -->
        <div class="content-card">
            <h3>🖥️ Estado del Sistema</h3>
            <div class="status-list">
                <div class="status-item">
                    <span class="status-indicator success"></span>
                    <span class="status-text">Sistema Operativo</span>
                </div>
                <div class="status-item">
                    <span class="status-indicator success"></span>
                    <span class="status-text">Base de Datos Conectada</span>
                </div>
                <div class="status-item">
                    <span class="status-indicator success"></span>
                    <span class="status-text">Seguridad Activa</span>
                </div>
                <div class="status-item">
                    <span class="status-indicator info"></span>
                    <span class="status-text">Arquitectura Moderna</span>
                </div>
            </div>
        </div>
        
        <!-- User Info -->
        <div class="content-card">
            <h3>👤 Información del Usuario</h3>
            <div class="user-details">
                <p><strong>Usuario:</strong> <?= htmlspecialchars($currentUser['username'] ?? 'N/A') ?></p>
                <p><strong>Nivel:</strong> <?= htmlspecialchars($currentUser['level_name'] ?? 'N/A') ?></p>
                <p><strong>Último acceso:</strong> <?= date('d/m/Y H:i') ?></p>
                <p><strong>Versión:</strong> <?= APP_VERSION ?></p>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard {
    max-width: 1200px;
    margin: 0 auto;
}

.page-header {
    margin-bottom: 30px;
}

.page-header h1 {
    margin: 0 0 10px 0;
    font-size: 2.5rem;
    color: #333;
}

.page-header p {
    margin: 0;
    color: #666;
    font-size: 1.1rem;
}

.notifications-section {
    margin-bottom: 30px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 20px;
    border-left: 4px solid #667eea;
}

.stat-icon {
    font-size: 2.5rem;
    opacity: 0.8;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #667eea;
    margin: 0;
}

.stat-label {
    color: #666;
    font-size: 0.9rem;
    margin: 5px 0 0 0;
}

.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.content-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.content-card h3 {
    margin: 0 0 20px 0;
    color: #333;
    font-size: 1.2rem;
}

.activity-item {
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-user {
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.activity-details {
    font-size: 0.9rem;
    color: #666;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px 20px;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.action-btn.primary {
    background: #667eea;
    color: white;
}

.action-btn.success {
    background: #28a745;
    color: white;
}

.action-btn.info {
    background: #17a2b8;
    color: white;
}

.action-btn.default {
    background: #6c757d;
    color: white;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    text-decoration: none;
    color: white;
}

.btn-icon {
    font-size: 1.2rem;
}

.status-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 0;
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.status-indicator.success {
    background: #28a745;
}

.status-indicator.info {
    background: #17a2b8;
}

.status-indicator.warning {
    background: #ffc107;
}

.user-details p {
    margin: 10px 0;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.user-details p:last-child {
    border-bottom: none;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #666;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .content-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-actions {
        grid-template-columns: 1fr;
    }
}
</style>