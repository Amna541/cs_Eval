<?php
include 'db_connect.php';

// Function to check if table exists
function tableExists($conn, $tableName) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result && $result->num_rows > 0;
}

// Check if required tables exist
$notificationsExist = tableExists($conn, 'notifications');
$notifications = [];

if ($notificationsExist) {
    // Fetch notifications from database
    $notificationsQuery = "SELECT * FROM notifications ORDER BY created_at DESC";
    $notificationsResult = $conn->query($notificationsQuery);
    
    if ($notificationsResult) {
        while ($row = $notificationsResult->fetch_assoc()) {
            $notifications[] = $row;
        }
    }
}

// Function to display time in a friendly format
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    
    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
    :root {
        --primary: #4f46e5;
        --primary-light: #6366f1;
        --primary-dark: #4338ca;
        --secondary: #10b981;
        --accent: #f59e0b;
        --danger: #ef4444;
        --success: #10b981;
        --info: #3b82f6;
        --warning: #f59e0b;
        
        /* Light Mode Defaults */
        --bg-color: #f9fafb;
        --text-color: #111827;
        --text-secondary: #6b7280;
        --card-bg: #ffffff;
        --sidebar-bg: #1e1e2d;
        --sidebar-text: #e5e7eb;
        --sidebar-hover: rgba(255, 255, 255, 0.1);
        --sidebar-active: var(--primary);
        --input-bg: #ffffff;
        --input-text: #111827;
        --input-border: #e5e7eb;
        --input-focus: #d1d5db;
        --card-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        --card-hover-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        --border-radius: 12px;
    }
    
    .dark-mode {
        --bg-color: #111827;
        --text-color: #f9fafb;
        --text-secondary: #9ca3af;
        --card-bg: #1f2937;
        --sidebar-bg: #0f172a;
        --sidebar-text: #e5e7eb;
        --sidebar-hover: rgba(255, 255, 255, 0.15);
        --sidebar-active: var(--primary-light);
        --input-bg: #1f2937;
        --input-text: #f9fafb;
        --input-border: #374151;
        --input-focus: #4b5563;
        --card-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        --card-hover-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.25), 0 4px 6px -2px rgba(0, 0, 0, 0.1);
    }
    
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: 'Inter', sans-serif;
        background-color: var(--bg-color);
        color: var(--text-color);
        line-height: 1.5;
        min-height: 100vh;
        transition: all 0.3s ease;
    }
    
    .sidebar {
        width: 280px;
        background-color: var(--sidebar-bg);
        color: var(--sidebar-text);
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        padding: 1.5rem 0;
        box-shadow: 4px 0 6px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        transition: all 0.3s ease;
    }
    
    .main-content {
        margin-left: 280px;
        padding: 2rem;
        transition: all 0.3s ease;
    }
    
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }
    
    .header h1 {
        font-size: 1.75rem;
        color: var(--text-color);
        font-weight: 600;
    }
    
    .header-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
        font-size: 0.925rem;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-icon {
        padding: 0.75rem;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        justify-content: center;
    }
    
    .btn-primary {
        background: var(--primary);
        color: white;
    }
    
    .btn-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: var(--card-hover-shadow);
    }
    
    .btn-danger {
        background: var(--danger);
        color: white;
    }
    
    .btn-danger:hover {
        background: #dc3545;
        transform: translateY(-2px);
        box-shadow: var(--card-hover-shadow);
    }
    
    .notifications-container {
        background: var(--card-bg);
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }
    
    .notification-filters {
        padding: 1.5rem 1.5rem 0;
        border-bottom: 1px solid var(--input-border);
    }
    
    .filter-tabs {
        display: flex;
        gap: 0.5rem;
    }
    
    .filter-tab {
        padding: 0.5rem 1rem;
        background: none;
        border: none;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--text-secondary);
        cursor: pointer;
        border-radius: 6px;
        transition: all 0.3s ease;
    }
    
    .filter-tab.active {
        background: var(--primary-light);
        color: white;
    }
    
    .filter-tab:hover:not(.active) {
        background: rgba(0, 0, 0, 0.05);
    }
    
    .notifications-list {
        padding: 1.5rem;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
    }
    
    .empty-state i {
        font-size: 3rem;
        color: var(--text-secondary);
        margin-bottom: 1rem;
    }
    
    .empty-state h3 {
        font-size: 1.25rem;
        margin-bottom: 0.5rem;
        color: var(--text-color);
    }
    
    .empty-state p {
        color: var(--text-secondary);
        margin-bottom: 1.5rem;
    }
    
    .notification-card {
        display: flex;
        padding: 1.25rem;
        border-radius: var(--border-radius);
        background: var(--bg-color);
        margin-bottom: 1rem;
        border-left: 4px solid transparent;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .notification-card.unread {
        border-left-color: var(--primary);
        background: var(--card-bg);
        box-shadow: var(--card-shadow);
    }
    
    .notification-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--card-hover-shadow);
    }
    
    .notification-icon {
        font-size: 1.25rem;
        margin-right: 1rem;
        color: var(--text-secondary);
        flex-shrink: 0;
    }
    
    .notification-card.unread .notification-icon {
        color: var(--primary);
    }
    
    .notification-card.alert .notification-icon {
        color: var(--danger);
    }
    
    .notification-card.update .notification-icon {
        color: var(--info);
    }
    
    .notification-content {
        flex-grow: 1;
    }
    
    .notification-title {
        font-weight: 600;
        margin-bottom: 0.25rem;
        color: var(--text-color);
    }
    
    .notification-message {
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
    }
    
    .notification-meta {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
    
    .badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        background: var(--primary);
        color: white;
    }
    
    .notification-actions {
        display: flex;
        gap: 0.5rem;
        margin-left: 1rem;
    }
    
    .tooltip {
        position: relative;
        display: inline-block;
    }
    
    .tooltip .tooltip-text {
        visibility: hidden;
        width: 120px;
        background-color: var(--card-bg);
        color: var(--text-color);
        text-align: center;
        border-radius: 6px;
        padding: 5px;
        position: absolute;
        z-index: 1;
        bottom: 125%;
        left: 50%;
        margin-left: -60px;
        opacity: 0;
        transition: opacity 0.3s;
        font-size: 0.75rem;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--input-border);
    }
    .browsing-alert {
    background: rgba(16, 185, 129, 0.1);
    border-left: 3px solid var(--success);
    padding: 0.5rem;
    margin-top: 0.5rem;
    border-radius: 4px;
    font-size: 0.85rem;
    color: var(--success);
}

.browsing-alert i {
    margin-right: 0.5rem;
}
    .tooltip:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
    }
    
    .fade-in {
        animation: fadeIn 0.4s ease forwards;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @media (max-width: 768px) {
        .sidebar {
            width: 80px;
        }
        
        .main-content {
            margin-left: 80px;
        }
        
        .filter-tabs {
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: 0.5rem;
        }
        
        .notification-card {
            flex-direction: column;
        }
        
        .notification-actions {
            margin-left: 0;
            margin-top: 1rem;
            justify-content: flex-end;
        }
    }
    </style>
</head>
<body>
    <div class="sidebar">
        <!-- Same sidebar content as before -->
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Notifications</h1>
            <div class="header-actions">
                <?php if ($notificationsExist && !empty($notifications)): ?>
                    <div class="tooltip">
                        <button class="btn btn-primary btn-icon" id="markAllRead">
                            <i class="fas fa-check-double"></i>
                            <span class="tooltip-text">Mark all as read</span>
                        </button>
                    </div>
                    <div class="tooltip">
                        <button class="btn btn-danger btn-icon" id="clearNotifications">
                            <i class="fas fa-trash"></i>
                            <span class="tooltip-text">Clear all notifications</span>
                        </button>
                    </div>
                <?php endif; ?>
                <!-- Same mode toggle and user profile as before -->
            </div>
        </div>

        <div class="notifications-container fade-in">
            <?php if (!$notificationsExist): ?>
                <div class="empty-state">
                    <i class="fas fa-database"></i>
                    <h3>Notifications system not set up</h3>
                    <p>The notifications table doesn't exist in the database.</p>
                    <a href="create_tables.php" class="btn btn-primary">
                        <i class="fas fa-cog"></i> Initialize Database
                    </a>
                </div>
            <?php else: ?>
                <div class="notification-filters">
                    <div class="filter-tabs">
                        <button class="filter-tab active" data-filter="all">All</button>
                        <button class="filter-tab" data-filter="unread">Unread</button>
                        <button class="filter-tab" data-filter="alert">Alerts</button>
                        <button class="filter-tab" data-filter="update">Updates</button>
                    </div>
                </div>

                <div class="notifications-list">
                    <?php if (empty($notifications)): ?>
                        <div class="empty-state">
                            <i class="fas fa-bell-slash"></i>
                            <h3>No notifications yet</h3>
                            <p>You'll see important alerts and updates here</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notifications as $notification): ?>
                            <div class="notification-card <?= $notification['is_read'] ? '' : 'unread' ?> <?= $notification['type'] ?>" 
                                 data-id="<?= $notification['id'] ?>" 
                                 data-type="<?= $notification['type'] ?>">
                                <div class="notification-icon">
                                    <?php if ($notification['type'] == 'alert'): ?>
                                        <i class="fas fa-exclamation-circle"></i>
                                    <?php elseif ($notification['type'] == 'update'): ?>
                                        <i class="fas fa-sync-alt"></i>
                                    <?php else: ?>
                                        <i class="fas fa-info-circle"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="notification-content">
                                    <h4 class="notification-title"><?= htmlspecialchars($notification['title']) ?></h4>
                                    <p class="notification-message"><?= htmlspecialchars($notification['message']) ?></p>
                                    <div class="notification-meta">
                                        <span class="time"><?= time_elapsed_string($notification['created_at']) ?></span>
                                        <?php if ($notification['is_read'] == 0): ?>
                                            <span class="badge">New</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="notification-actions">
                                    <div class="tooltip">
                                        <button class="btn btn-icon mark-as-read">
                                            <i class="fas fa-check"></i>
                                            <span class="tooltip-text">Mark as read</span>
                                        </button>
                                    </div>
                                    <div class="tooltip">
                                        <button class="btn btn-icon delete-notification">
                                            <i class="fas fa-times"></i>
                                            <span class="tooltip-text">Dismiss</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>

        // Function to check for active browsing sessions
function checkActiveBrowsers() {
    const notificationCards = document.querySelectorAll('.notification-card');
    
    notificationCards.forEach(card => {
        const notificationId = card.dataset.id;
        
        if (notificationId) {
            fetch(`get_active_sessions.php?notification_id=${notificationId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        // Create or update active browsers indicator
                        let indicator = card.querySelector('.active-browsers');
                        if (!indicator) {
                            indicator = document.createElement('div');
                            indicator.className = 'active-browsers';
                            card.querySelector('.notification-content').appendChild(indicator);
                        }
                        
                        // Update with current active browsers
                        indicator.innerHTML = `
                            <div class="browsing-alert">
                                <i class="fas fa-user-clock"></i>
                                Currently browsing: 
                                ${data.map(session => 
                                    `${session.user_id} (${session.time_ago})`
                                ).join(', ')}
                            </div>
                        `;
                    } else {
                        // Remove indicator if no active browsers
                        const indicator = card.querySelector('.active-browsers');
                        if (indicator) {
                            indicator.remove();
                        }
                    }
                });
        }
    });
}

// Check every 30 seconds
setInterval(checkActiveBrowsers, 30000);

// Initial check
checkActiveBrowsers();
    // Notification interactions
    document.querySelectorAll('.mark-as-read').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const notificationCard = this.closest('.notification-card');
            const notificationId = notificationCard.dataset.id;
            
            // Mark as read in UI
            notificationCard.classList.remove('unread');
            notificationCard.querySelector('.badge')?.remove();
            
            // AJAX call to mark as read in database
            fetch('mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: notificationId })
            });
        });
    });

    document.querySelectorAll('.delete-notification').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const notificationCard = this.closest('.notification-card');
            const notificationId = notificationCard.dataset.id;
            
            // Remove from UI
            notificationCard.style.opacity = '0';
            setTimeout(() => {
                notificationCard.remove();
                if (document.querySelectorAll('.notification-card').length === 0) {
                    document.querySelector('.notifications-list').innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-bell-slash"></i>
                            <h3>No notifications yet</h3>
                            <p>You'll see important alerts and updates here</p>
                        </div>
                    `;
                }
            }, 300);
            
            // AJAX call to delete from database
            fetch('delete_notification.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: notificationId })
            });
        });
    });

    document.getElementById('markAllRead').addEventListener('click', function() {
        // Mark all as read in UI
        document.querySelectorAll('.notification-card.unread').forEach(card => {
            card.classList.remove('unread');
            card.querySelector('.badge')?.remove();
        });
        
        // AJAX call to mark all as read
        fetch('mark_all_notifications_read.php', {
            method: 'POST'
        });
    });

    document.getElementById('clearNotifications').addEventListener('click', function() {
        if (confirm('Are you sure you want to clear all notifications?')) {
            // Clear all in UI
            document.querySelector('.notifications-list').innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-bell-slash"></i>
                    <h3>No notifications yet</h3>
                    <p>You'll see important alerts and updates here</p>
                </div>
            `;
            
            // AJAX call to clear all
            fetch('clear_all_notifications.php', {
                method: 'POST'
            });
        }
    });

    // Filter notifications
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelector('.filter-tab.active').classList.remove('active');
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            document.querySelectorAll('.notification-card').forEach(card => {
                if (filter === 'all' || 
                    (filter === 'unread' && card.classList.contains('unread')) ||
                    (filter === card.dataset.type)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
    // Show preview on hover
document.querySelectorAll('.notification-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        if (this.scrollHeight > this.clientHeight) {
            const preview = document.createElement('div');
            preview.className = 'notification-preview';
            preview.textContent = this.querySelector('.notification-message').textContent;
            document.body.appendChild(preview);
            
            const rect = this.getBoundingClientRect();
            preview.style.top = `${rect.bottom}px`;
            preview.style.left = `${rect.left}px`;
        }
    });
    
    card.addEventListener('mouseleave', function() {
        const preview = document.querySelector('.notification-preview');
        if (preview) preview.remove();
    });
});
document.querySelectorAll('.snooze-notification').forEach(btn => {
    btn.addEventListener('click', function() {
        const notificationId = this.closest('.notification-card').dataset.id;
        const hours = prompt('Snooze for how many hours?', '4');
        
        fetch('snooze_notification.php', {
            method: 'POST',
            body: JSON.stringify({ id: notificationId, hours: hours })
        }).then(() => {
            this.closest('.notification-card').style.display = 'none';
        });
    });
});
    </script>
</body>
</html>