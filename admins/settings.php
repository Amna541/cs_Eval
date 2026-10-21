<?php
// dashboard.php — single-file demo admin dashboard
// NOTE: This is a front-end heavy PHP page with lightweight sample queries.
// Wire the marked endpoints (update_*.php) on your server to persist changes securely.

require_once __DIR__.'/db_connect.php';

// ---- AUTH PLACEHOLDER (replace with your auth/session logic) ----
// session_start();
// if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$userId = 1; // <- replace with the logged-in admin ID

// ---- FETCH USER SETTINGS ----
$settings = [
  'timezone' => 'UTC',
  'date_format' => 'Y-m-d',
  'records_per_page' => 25,
  'email_notifications' => 1,
  'browser_notifications' => 1,
  'alert_notifications' => 1,
  'update_notifications' => 1,
  'report_notifications' => 1,
  'notification_frequency' => 'instant',
  'two_factor_auth' => 0,
  'ga_integration' => 0,
  'slack_integration' => 0,
  'mailchimp_integration' => 0,
];
try {
  $res = $conn->query("SELECT * FROM user_settings WHERE user_id = ".(int)$userId);
  if ($res && $row = $res->fetch_assoc()) { $settings = array_merge($settings, $row); }
} catch (Throwable $e) { /* table may not exist yet; fall back to defaults */ }

// ---- DASHBOARD METRICS (sample queries; adjust to your schema) ----
$totals = [
  'total_users' => 0,
  'active_users' => 0,
  'new_signups' => 0,
  'server_load' => rand(12, 67), // mock value; swap with real server metrics
];
try {
  if ($r = $conn->query("SELECT COUNT(*) AS c FROM users")) { $totals['total_users'] = (int)$r->fetch_assoc()['c']; }
  if ($r = $conn->query("SELECT COUNT(*) AS c FROM users WHERE last_seen >= NOW() - INTERVAL 7 DAY")) { $totals['active_users'] = (int)$r->fetch_assoc()['c']; }
  if ($r = $conn->query("SELECT COUNT(*) AS c FROM users WHERE created_at >= CURDATE()")) { $totals['new_signups'] = (int)$r->fetch_assoc()['c']; }
} catch (Throwable $e) { /* ignore if tables missing */ }

// ---- RECENT ACTIVITY (adjust table name/columns) ----
$recentActivity = [];
try {
  $q = "SELECT id, user_name, action, created_at FROM activity_log ORDER BY created_at DESC LIMIT 10";
  if ($r = $conn->query($q)) { while ($row = $r->fetch_assoc()) { $recentActivity[] = $row; } }
} catch (Throwable $e) { /* ignore */ }

// ---- NOTIFICATIONS (mock; replace with DB fetch if you have a table) ----
$notifications = [
  ['title' => 'New signup', 'time' => '2m ago'],
  ['title' => 'Server backup completed', 'time' => '1h ago'],
  ['title' => 'Password changed for user #120', 'time' => '3h ago'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />
  <style>
    :root {
      --primary: #4f46e5; --primary-light: #6366f1; --primary-dark: #4338ca;
      --secondary: #10b981; --accent: #f59e0b; --danger: #ef4444; --success: #10b981; --info: #3b82f6; --warning: #f59e0b;
      --bg-color: #f9fafb; --text-color: #111827; --text-secondary: #6b7280; --card-bg: #ffffff;
      --sidebar-bg: #1e1e2d; --sidebar-text: #e5e7eb; --sidebar-hover: rgba(255,255,255,0.1); --sidebar-active: var(--primary);
      --input-bg: #ffffff; --input-text: #111827; --input-border: #e5e7eb; --input-focus: #d1d5db;
      --card-shadow: 0 1px 3px rgba(0,0,0,0.1); --card-hover-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
      --border-radius: 12px;
    }
    body.dark {
      --bg-color: #111827; --text-color: #f9fafb; --text-secondary: #9ca3af; --card-bg: #1f2937; --sidebar-bg: #0f172a;
      --sidebar-text: #e5e7eb; --sidebar-hover: rgba(255,255,255,0.15); --sidebar-active: var(--primary-light);
      --input-bg: #1f2937; --input-text: #f9fafb; --input-border: #374151; --input-focus: #4b5563; --card-shadow: 0 1px 3px rgba(0,0,0,0.3);
      --card-hover-shadow: 0 10px 15px -3px rgba(0,0,0,0.25), 0 4px 6px -2px rgba(0,0,0,0.1);
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Inter', sans-serif; background: var(--bg-color); color: var(--text-color); min-height: 100vh; }
    a { color: inherit; text-decoration: none; }

    /* Sidebar */
    .sidebar { width: 280px; background: var(--sidebar-bg); color: var(--sidebar-text); position: fixed; inset: 0 auto 0 0; padding: 1rem 0; overflow-y: auto; transition: width .25s ease; }
    .sidebar.collapsed { width: 88px; }
    .brand { display: flex; align-items: center; gap: .75rem; padding: .75rem 1rem 1rem 1.25rem; }
    .brand i { font-size: 1.25rem; color: var(--primary-light); }
    .brand span { font-weight: 700; letter-spacing: .2px; }
    .side-section { margin-top: .25rem; }
    .side-title { font-size: .75rem; opacity: .7; padding: .75rem 1.25rem; text-transform: uppercase; letter-spacing: .06em; }
    .nav-item { display: flex; align-items: center; gap: .75rem; padding: .65rem 1.25rem; border-left: 3px solid transparent; cursor: pointer; }
    .nav-item:hover { background: var(--sidebar-hover); }
    .nav-item.active { border-left-color: var(--sidebar-active); background: rgba(79,70,229,0.12); }
    .nav-item i { width: 22px; text-align: center; }
    .nav-label { transition: opacity .2s; }
    .sidebar.collapsed .nav-label, .sidebar.collapsed .side-title { display: none; }

    /* Main */
    .main { margin-left: 280px; transition: margin-left .25s ease; padding: 1.25rem 1.5rem 2rem; }
    .main.collapsed { margin-left: 88px; }

    /* Topbar */
    .topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
    .left-actions { display:flex; align-items:center; gap:.75rem; }
    .icon-btn { border: 1px solid var(--input-border); background: var(--card-bg); padding: .55rem .7rem; border-radius: 10px; cursor: pointer; box-shadow: var(--card-shadow); }
    .search { flex: 1; max-width: 520px; display: flex; align-items: center; gap: .5rem; background: var(--card-bg); border: 1px solid var(--input-border); border-radius: 12px; padding: .5rem .75rem; }
    .search input { border: 0; outline: 0; background: transparent; width: 100%; color: var(--input-text); font-size: .95rem; }
    .right-actions { display:flex; align-items:center; gap:.5rem; }
    .badge { position: relative; }
    .badge .dot { position:absolute; right:6px; top:6px; width:8px; height:8px; background:#ef4444; border-radius: 50%; }
    .dropdown { position: relative; }
    .dropdown-menu { position:absolute; right:0; top:120%; background: var(--card-bg); border:1px solid var(--input-border); border-radius: 12px; box-shadow: var(--card-hover-shadow); min-width: 240px; display:none; z-index: 30; }
    .dropdown-menu.show { display:block; }
    .dropdown-item { padding:.75rem .9rem; border-bottom:1px solid var(--input-border); font-size:.925rem; }
    .dropdown-item:last-child { border-bottom:0; }

    /* Cards & grids */
    .grid { display: grid; gap: 1rem; }
    .grid.cards { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .card { background: var(--card-bg); border:1px solid var(--input-border); border-radius: var(--border-radius); box-shadow: var(--card-shadow); padding: 1rem; }
    .card h3 { font-size: .85rem; color: var(--text-secondary); font-weight: 600; }
    .card .metric { font-size: 1.75rem; font-weight: 700; margin-top: .35rem; }
    .subtext { font-size: .8rem; color: var(--text-secondary); }

    .grid.two { grid-template-columns: 1.5fr 1fr; }
    .grid.three { grid-template-columns: repeat(3, minmax(0,1fr)); }

    /* Tables */
    table { width: 100%; border-collapse: collapse; }
    th, td { text-align: left; padding: .65rem .5rem; border-bottom: 1px solid var(--input-border); font-size: .925rem; }
    th { color: var(--text-secondary); font-weight: 600; }

    /* Buttons */
    .btn { border: 0; background: var(--primary); color: white; padding: .65rem .95rem; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: .925rem; }
    .btn.secondary { background: var(--secondary); }
    .btn.ghost { background: transparent; color: var(--primary); border:1px solid var(--primary); }
    .btn.warning { background: var(--accent); }
    .btn.danger { background: var(--danger); }
    .btn + .btn { margin-left: .5rem; }

    .flex { display:flex; align-items:center; }
    .justify-between { justify-content: space-between; }
    .gap-6 { gap: .75rem; }
    .mt-1 { margin-top: .25rem; }
    .mt-2 { margin-top: .5rem; }
    .mt-3 { margin-top: .75rem; }
    .mt-4 { margin-top: 1rem; }
    .mt-6 { margin-top: 1.5rem; }

    /* Settings */
    .tabs { display:flex; border-bottom:1px solid var(--input-border); overflow-x:auto; }
    .tab { background:none; border:0; padding: .85rem 1rem; color: var(--text-secondary); font-weight:600; cursor:pointer; position:relative; }
    .tab.active { color: var(--primary); }
    .tab.active::after { content:""; position:absolute; left:0; bottom:-1px; width:100%; height:2px; background:var(--primary); }
    .tab-panel { display:none; padding: 1rem; }
    .tab-panel.active { display:block; }
    .form-group { margin-bottom: 1rem; }
    label { display:block; font-weight:600; margin-bottom:.45rem; }
    .input, select, input[type="number"], input[type="password"], input[type="text"] { width:100%; background: var(--input-bg); border:1px solid var(--input-border); border-radius:10px; padding:.65rem .8rem; color:var(--input-text); font-size:.95rem; }
    .hint { font-size:.8rem; color: var(--text-secondary); margin-top:.25rem; }

    .check { display:flex; align-items:center; gap:.55rem; margin:.35rem 0; cursor:pointer; }
    .switch { position:relative; display:inline-block; width:50px; height:24px; }
    .switch input { opacity:0; width:0; height:0; }
    .slider { position:absolute; inset:0; border-radius:999px; background:#ccc; transition:.2s; }
    .slider:before { content:""; position:absolute; width:16px; height:16px; left:4px; top:4px; border-radius:50%; background:white; transition:.2s; }
    .switch input:checked + .slider { background: var(--primary); }
    .switch input:checked + .slider:before { transform: translateX(26px); }

    .strength { height: 6px; background:#e5e7eb; border-radius:999px; overflow:hidden; }
    .strength > div { height:100%; width:0; background: var(--danger); transition: width .25s, background .25s; }

    /* Alerts */
    .toast { position: fixed; right: 20px; top: 20px; background: var(--success); color: white; padding: .75rem 1rem; border-radius: 10px; box-shadow: var(--card-hover-shadow); display:none; z-index: 100; }
    .toast.error { background: var(--danger); }

    /* Responsive */
    @media (max-width: 1100px){ .grid.cards { grid-template-columns: repeat(2, minmax(0,1fr)); } .grid.two{ grid-template-columns: 1fr; } }
    @media (max-width: 700px){ .grid.cards { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <!-- SIDEBAR -->
  <aside id="sidebar" class="sidebar">
    <div class="brand">
      <button id="collapseBtn" class="icon-btn" title="Toggle sidebar"><i class="fa-solid fa-bars"></i></button>
      <span>Admin</span>
    </div>

    <div class="side-section">
      <div class="side-title">Overview</div>
      <a class="nav-item active" href="#overview"><i class="fa-solid fa-gauge"></i><span class="nav-label">Dashboard</span></a>
      <a class="nav-item" href="#users"><i class="fa-solid fa-users"></i><span class="nav-label">Users</span></a>
      <a class="nav-item" href="#reports"><i class="fa-solid fa-chart-line"></i><span class="nav-label">Reports</span></a>
    </div>

    <div class="side-section">
      <div class="side-title">System</div>
      <a class="nav-item" href="#settings"><i class="fa-solid fa-sliders"></i><span class="nav-label">Settings</span></a>
      <a class="nav-item" href="#backups"><i class="fa-solid fa-database"></i><span class="nav-label">Backups</span></a>
    </div>
  </aside>

  <!-- MAIN -->
  <main id="main" class="main">
    <div class="topbar">
      <div class="left-actions">
        <div class="search">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input id="globalSearch" type="text" placeholder="Search users, reports, settings…" />
        </div>
        <button id="quickAdd" class="icon-btn" title="Quick actions"><i class="fa-solid fa-bolt"></i></button>
      </div>
      <div class="right-actions">
        <button id="themeToggle" class="icon-btn" title="Theme"><i class="fa-solid fa-moon"></i></button>
        <div class="dropdown badge">
          <button id="notifBtn" class="icon-btn"><i class="fa-solid fa-bell"></i></button>
          <span class="dot"></span>
          <div id="notifMenu" class="dropdown-menu">
            <?php foreach($notifications as $n): ?>
              <div class="dropdown-item"><strong><?= htmlspecialchars($n['title']) ?></strong><div class="subtext"><?= htmlspecialchars($n['time']) ?></div></div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="dropdown">
          <button id="profileBtn" class="icon-btn"><i class="fa-solid fa-user"></i></button>
          <div id="profileMenu" class="dropdown-menu">
            <div class="dropdown-item">Edit Profile</div>
            <div class="dropdown-item">Manage Sessions</div>
            <div class="dropdown-item">Logout</div>
          </div>
        </div>
      </div>
    </div>

    <!-- STATS CARDS -->
    <section id="overview" class="grid cards">
      <div class="card">
        <h3>Total Users</h3>
        <div class="metric"><?php echo number_format($totals['total_users']); ?></div>
        <div class="subtext mt-1">All registered users</div>
      </div>
      <div class="card">
        <h3>Active (7d)</h3>
        <div class="metric"><?php echo number_format($totals['active_users']); ?></div>
        <div class="subtext mt-1">Logged in last 7 days</div>
      </div>
      <div class="card">
        <h3>New Today</h3>
        <div class="metric"><?php echo number_format($totals['new_signups']); ?></div>
        <div class="subtext mt-1">Sign-ups since midnight</div>
      </div>
      <div class="card">
        <h3>Server Load</h3>
        <div class="metric"><?php echo (int)$totals['server_load']; ?>%</div>
        <div class="subtext mt-1">Mock metric (demo)</div>
      </div>
    </section>

    <!-- CHARTS + ACTIVITY -->
    <section class="grid two mt-4">
      <div class="card">
        <div class="justify-between flex"><h3>User Growth</h3><span class="subtext">Last 12 weeks</span></div>
        <canvas id="growthChart" height="120"></canvas>
      </div>
      <div class="card">
        <div class="justify-between flex"><h3>Active by Hour</h3><span class="subtext">Today</span></div>
        <canvas id="hourlyChart" height="120"></canvas>
      </div>
    </section>

    <section class="grid two mt-4">
      <div class="card">
        <div class="justify-between flex"><h3>Feature Usage</h3><span class="subtext">7-day</span></div>
        <canvas id="featureChart" height="120"></canvas>
      </div>
      <div class="card">
        <div class="justify-between flex"><h3>Recent Activity</h3><button class="btn ghost" id="exportActivity">Export CSV</button></div>
        <div class="mt-2" style="overflow:auto; max-height: 280px;">
          <table id="activityTable">
            <thead><tr><th>ID</th><th>User</th><th>Action</th><th>At</th></tr></thead>
            <tbody>
              <?php if($recentActivity): foreach($recentActivity as $a): ?>
                <tr>
                  <td><?= (int)$a['id'] ?></td>
                  <td><?= htmlspecialchars($a['user_name'] ?? '—') ?></td>
                  <td><?= htmlspecialchars($a['action'] ?? '—') ?></td>
                  <td><?= htmlspecialchars($a['created_at'] ?? '—') ?></td>
                </tr>
              <?php endforeach; else: ?>
                <tr><td colspan="4">No activity yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- QUICK ACTIONS -->
    <section class="card mt-4">
      <div class="justify-between flex">
        <h3>Quick Actions</h3>
      </div>
      <div class="mt-3">
        <button class="btn" onclick="location.href='user_create.php'"><i class="fa-solid fa-user-plus"></i> Add User</button>
        <button class="btn secondary" onclick="location.href='generate_report.php'"><i class="fa-solid fa-file-export"></i> Generate Report</button>
        <button class="btn warning" onclick="location.href='announce.php'"><i class="fa-solid fa-bullhorn"></i> Send Announcement</button>
      </div>
    </section>

    <!-- SETTINGS -->
    <section id="settings" class="card mt-4">
      <div class="tabs">
        <button class="tab active" data-tab="general">General</button>
        <button class="tab" data-tab="notifications">Notifications</button>
        <button class="tab" data-tab="security">Security</button>
        <button class="tab" data-tab="integrations">Integrations</button>
        <button class="tab" data-tab="appearance">Appearance</button>
        <button class="tab" data-tab="reports">Reports</button>
        <button class="tab" data-tab="roles">Roles & Permissions</button>
        <button class="tab" data-tab="backup">Backup & Restore</button>
      </div>

      <!-- General -->
      <div id="panel-general" class="tab-panel active">
        <form id="generalForm">
          <div class="form-group">
            <label for="timezone">Timezone</label>
            <select id="timezone" name="timezone">
              <?php foreach(DateTimeZone::listIdentifiers() as $tz): $sel = ($tz === ($settings['timezone'] ?? 'UTC')) ? 'selected' : ''; ?>
                <option value="<?= htmlspecialchars($tz) ?>" <?= $sel ?>><?= htmlspecialchars($tz) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="date_format">Date Format</label>
            <select id="date_format" name="date_format">
              <?php $df = $settings['date_format'] ?? 'Y-m-d'; ?>
              <option value="Y-m-d" <?= $df==='Y-m-d'?'selected':''; ?>>YYYY-MM-DD</option>
              <option value="m/d/Y" <?= $df==='m/d/Y'?'selected':''; ?>>MM/DD/YYYY</option>
              <option value="d/m/Y" <?= $df==='d/m/Y'?'selected':''; ?>>DD/MM/YYYY</option>
            </select>
          </div>
          <div class="form-group">
            <label for="records_per_page">Records Per Page</label>
            <input type="number" id="records_per_page" name="records_per_page" min="5" max="100" value="<?= (int)($settings['records_per_page'] ?? 25) ?>">
          </div>
          <button class="btn" type="submit">Save Changes</button>
        </form>
      </div>

      <!-- Notifications -->
      <div id="panel-notifications" class="tab-panel">
        <form id="notifForm">
          <label class="check"><input type="checkbox" name="email_notifications" <?= ($settings['email_notifications'] ?? 1)?'checked':''; ?>> Email notifications</label>
          <label class="check"><input type="checkbox" name="browser_notifications" <?= ($settings['browser_notifications'] ?? 1)?'checked':''; ?>> Browser notifications</label>
          <div class="mt-2"><strong>Types</strong></div>
          <label class="check"><input type="checkbox" name="alert_notifications" <?= ($settings['alert_notifications'] ?? 1)?'checked':''; ?>> Critical alerts</label>
          <label class="check"><input type="checkbox" name="update_notifications" <?= ($settings['update_notifications'] ?? 1)?'checked':''; ?>> System updates</label>
          <label class="check"><input type="checkbox" name="report_notifications" <?= ($settings['report_notifications'] ?? 1)?'checked':''; ?>> Weekly reports</label>
          <div class="form-group mt-2">
            <label for="notification_frequency">Frequency</label>
            <select id="notification_frequency" name="notification_frequency">
              <?php $nf = $settings['notification_frequency'] ?? 'instant'; ?>
              <option value="instant" <?= $nf==='instant'?'selected':''; ?>>Instant</option>
              <option value="hourly" <?= $nf==='hourly'?'selected':''; ?>>Hourly Digest</option>
              <option value="daily" <?= $nf==='daily'?'selected':''; ?>>Daily Digest</option>
            </select>
          </div>
          <button class="btn" type="submit">Save Changes</button>
        </form>
      </div>

      <!-- Security -->
      <div id="panel-security" class="tab-panel">
        <form id="securityForm">
          <div class="form-group"><label>Current Password</label><input type="password" name="current_password"></div>
          <div class="form-group"><label>New Password</label><input id="new_password" type="password" name="new_password"><div class="strength mt-1"><div id="strengthFill"></div></div></div>
          <div class="form-group"><label>Confirm New Password</label><input id="confirm_password" type="password" name="confirm_password"></div>
          <label class="check mt-2"><input type="checkbox" name="two_factor_auth" <?= ($settings['two_factor_auth'] ?? 0)?'checked':''; ?>> Enable Two-Factor Authentication</label>
          <div class="hint">When enabled, show a QR and 6-digit code input in a modal (implement in update_password.php).</div>
          <div class="mt-2"><button class="btn" type="submit">Update Password</button></div>
        </form>

        <div class="mt-4">
          <h3>Active Sessions</h3>
          <div class="subtext mt-1">List of sessions/devices currently logged in (populate from DB).</div>
          <table class="mt-2">
            <thead><tr><th>Device</th><th>IP</th><th>Last Active</th><th></th></tr></thead>
            <tbody>
              <tr><td>Chrome on Windows</td><td>192.168.1.20</td><td>10 mins ago</td><td><button class="btn ghost">Logout</button></td></tr>
              <tr><td>Mobile Safari</td><td>10.0.0.5</td><td>2 hrs ago</td><td><button class="btn ghost">Logout</button></td></tr>
            </tbody>
          </table>

          <div class="mt-4">
            <h3>IP Allowlist</h3>
            <div class="subtext">Restrict admin access to specific IPs (store in a table like admin_ip_allowlist).</div>
            <div class="form-group mt-2"><input type="text" id="ip_input" placeholder="e.g. 203.0.113.4"></div>
            <button class="btn ghost" id="add_ip">Add IP</button>
            <div id="ip_list" class="mt-2 subtext">(demo) 203.0.113.4, 198.51.100.7</div>
          </div>
        </div>
      </div>

      <!-- Integrations -->
      <div id="panel-integrations" class="tab-panel">
        <div class="grid three">
          <div class="card">
            <div class="flex justify-between"><strong>Google Analytics</strong><label class="switch"><input type="checkbox" name="ga_integration" <?= ($settings['ga_integration'] ?? 0)?'checked':''; ?>><span class="slider"></span></label></div>
            <div class="subtext mt-2">Connect to send pageview events.</div>
            <button class="btn ghost mt-2">Configure</button>
          </div>
          <div class="card">
            <div class="flex justify-between"><strong>Slack</strong><label class="switch"><input type="checkbox" name="slack_integration" <?= ($settings['slack_integration'] ?? 0)?'checked':''; ?>><span class="slider"></span></label></div>
            <div class="subtext mt-2">Send alerts to channels.</div>
            <button class="btn ghost mt-2">Configure</button>
          </div>
          <div class="card">
            <div class="flex justify-between"><strong>Mailchimp</strong><label class="switch"><input type="checkbox" name="mailchimp_integration" <?= ($settings['mailchimp_integration'] ?? 0)?'checked':''; ?>><span class="slider"></span></label></div>
            <div class="subtext mt-2">Sync subscriber lists.</div>
            <button class="btn ghost mt-2">Configure</button>
          </div>
        </div>
      </div>

      <!-- Appearance -->
      <div id="panel-appearance" class="tab-panel">
        <div class="form-group">
          <label>Theme</label>
          <div>
            <button class="btn ghost" type="button" onclick="setTheme('light')">Light</button>
            <button class="btn ghost" type="button" onclick="setTheme('dark')">Dark</button>
            <button class="btn ghost" type="button" onclick="setTheme('auto')">Auto</button>
          </div>
          <div class="hint">Auto follows system preference.</div>
        </div>
        <div class="form-group mt-2">
          <label>Branding</label>
          <input type="text" placeholder="Dashboard title / welcome message" />
        </div>
        <div class="form-group mt-2">
          <label>Primary Color</label>
          <input type="color" id="primaryColor" value="#4f46e5" />
          <button class="btn ghost" id="applyColor" type="button">Apply</button>
        </div>
      </div>

      <!-- Reports -->
      <div id="panel-reports" class="tab-panel">
        <div class="form-group">
          <label>Schedule Email Reports</label>
          <select id="reportSchedule">
            <option value="off">Off</option>
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
          </select>
        </div>
        <button class="btn">Save</button>
        <button class="btn ghost">Send Test Report</button>
      </div>

      <!-- Roles & Permissions -->
      <div id="panel-roles" class="tab-panel">
        <div class="subtext">Create roles (Admin, Editor, Viewer) and attach permissions. (Back-end needed)</div>
        <table class="mt-2">
          <thead><tr><th>Role</th><th>Permissions</th><th></th></tr></thead>
          <tbody>
            <tr><td>Admin</td><td>All</td><td><button class="btn ghost">Edit</button></td></tr>
            <tr><td>Editor</td><td>Users: read/edit; Reports: read</td><td><button class="btn ghost">Edit</button></td></tr>
            <tr><td>Viewer</td><td>Read-only</td><td><button class="btn ghost">Edit</button></td></tr>
          </tbody>
        </table>
      </div>

      <!-- Backup & Restore -->
      <div id="panel-backup" class="tab-panel">
        <div class="subtext">Trigger on-demand backups and restore points.</div>
        <div class="mt-2">
          <button class="btn" onclick="location.href='backup_now.php'"><i class="fa-solid fa-database"></i> Backup Now</button>
          <button class="btn ghost" onclick="location.href='backups_list.php'"><i class="fa-solid fa-clock-rotate-left"></i> View Backups</button>
        </div>
      </div>
    </section>

    <!-- BACKUPS PAGE (anchor) -->
    <section id="backups" class="card mt-4">
      <div class="justify-between flex"><h3>Recent Backups</h3><button class="btn ghost" onclick="location.href='backups_list.php'">Manage</button></div>
      <table class="mt-2">
        <thead><tr><th>ID</th><th>Created</th><th>Size</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <tr><td>42</td><td>2025-08-12 23:10</td><td>512 MB</td><td>Completed</td><td><button class="btn ghost">Download</button></td></tr>
          <tr><td>41</td><td>2025-08-11 23:05</td><td>509 MB</td><td>Completed</td><td><button class="btn ghost">Download</button></td></tr>
        </tbody>
      </table>
    </section>

    <div id="toast" class="toast">Saved!</div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script>
    // ---- theme & layout ----
    const body = document.body;
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark' || (savedTheme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches)) body.classList.add('dark');

    const themeToggle = document.getElementById('themeToggle');
    themeToggle.addEventListener('click', () => {
      body.classList.toggle('dark');
      localStorage.setItem('theme', body.classList.contains('dark') ? 'dark' : 'light');
    });
    function setTheme(mode){
      if(mode==='dark'){ body.classList.add('dark'); }
      else if(mode==='light'){ body.classList.remove('dark'); }
      else { body.classList.toggle('dark', window.matchMedia('(prefers-color-scheme: dark)').matches); }
      localStorage.setItem('theme', mode);
    }

    const sidebar = document.getElementById('sidebar');
    const main = document.getElementById('main');
    const collapseBtn = document.getElementById('collapseBtn');
    if(localStorage.getItem('collapsed')==='1'){ sidebar.classList.add('collapsed'); main.classList.add('collapsed'); }
    collapseBtn.addEventListener('click', ()=>{
      sidebar.classList.toggle('collapsed');
      main.classList.toggle('collapsed');
      localStorage.setItem('collapsed', sidebar.classList.contains('collapsed') ? '1' : '0');
    });

    // ---- dropdowns ----
    const notifBtn = document.getElementById('notifBtn');
    const notifMenu = document.getElementById('notifMenu');
    const profileBtn = document.getElementById('profileBtn');
    const profileMenu = document.getElementById('profileMenu');
    function toggleMenu(btn, menu){ menu.classList.toggle('show'); }
    notifBtn.addEventListener('click', ()=>toggleMenu(notifBtn, notifMenu));
    profileBtn.addEventListener('click', ()=>toggleMenu(profileBtn, profileMenu));
    document.addEventListener('click', (e)=>{
      if(!notifBtn.contains(e.target) && !notifMenu.contains(e.target)) notifMenu.classList.remove('show');
      if(!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) profileMenu.classList.remove('show');
    });

    // ---- global search (demo action) ----
    document.getElementById('globalSearch').addEventListener('keydown', (e)=>{
      if(e.key==='Enter'){
        e.preventDefault();
        // Navigate to your search endpoint
        location.href = 'search.php?q=' + encodeURIComponent(e.target.value);
      }
    });

    // ---- tabs ----
    document.querySelectorAll('.tab').forEach(t=>{
      t.addEventListener('click', ()=>{
        document.querySelectorAll('.tab').forEach(x=>x.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(x=>x.classList.remove('active'));
        t.classList.add('active');
        document.getElementById('panel-'+t.dataset.tab).classList.add('active');
      });
    });

    // ---- forms ----
    const toast = document.getElementById('toast');
    function showToast(msg, ok=true){ toast.textContent = msg; toast.classList.toggle('error', !ok); toast.style.display='block'; setTimeout(()=>toast.style.display='none', 2000); }

    document.getElementById('generalForm').addEventListener('submit', async (e)=>{
      e.preventDefault();
      const fd = new FormData(e.target);
      const res = await fetch('update_general_settings.php', { method:'POST', body: fd });
      showToast(res.ok ? 'General settings saved' : 'Failed to save', res.ok);
    });

    document.getElementById('notifForm').addEventListener('submit', async (e)=>{
      e.preventDefault();
      const fd = new FormData(e.target);
      const res = await fetch('update_notification_settings.php', { method:'POST', body: fd });
      showToast(res.ok ? 'Notification settings saved' : 'Failed to save', res.ok);
    });

    document.getElementById('securityForm').addEventListener('submit', async (e)=>{
      e.preventDefault();
      const fd = new FormData(e.target);
      if(fd.get('new_password') !== fd.get('confirm_password')){ showToast('Passwords do not match', false); return; }
      const res = await fetch('update_password.php', { method:'POST', body: fd });
      showToast(res.ok ? 'Password updated' : 'Current password incorrect', res.ok);
      if(res.ok) e.target.reset();
    });

    // Password strength
    const newPwd = document.getElementById('new_password');
    const strengthFill = document.getElementById('strengthFill');
    function computeStrength(p){
      let s=0; if(p.length>0)s++; if(p.length>=8)s++; if(/[A-Z]/.test(p))s++; if(/[0-9]/.test(p))s++; if(/[^A-Za-z0-9]/.test(p))s++;
      return s; }
    function setStrengthBar(s){
      const widths = [0,25,50,75,100];
      strengthFill.style.width = widths[s-1]+'%';
      strengthFill.style.background = s>=4? 'var(--success)' : (s>=3? 'var(--info)' : (s>=2? 'var(--warning)' : 'var(--danger)'));
    }
    newPwd.addEventListener('input', ()=> setStrengthBar(computeStrength(newPwd.value)) );

    // IP allowlist demo
    document.getElementById('add_ip').addEventListener('click', ()=>{
      const v = document.getElementById('ip_input').value.trim();
      if(!v) return; const list = document.getElementById('ip_list');
      list.textContent += (list.textContent ? ', ' : '') + v; document.getElementById('ip_input').value='';
      // Persist via fetch to, e.g., update_ip_allowlist.php
    });

    // Apply primary color live
    document.getElementById('applyColor').addEventListener('click', ()=>{
      const color = document.getElementById('primaryColor').value;
      document.documentElement.style.setProperty('--primary', color);
      document.documentElement.style.setProperty('--sidebar-active', color);
    });

    // Export activity CSV
    document.getElementById('exportActivity').addEventListener('click', ()=>{
      const rows = Array.from(document.querySelectorAll('#activityTable tr')).map(tr=>Array.from(tr.children).map(td=>`"${td.innerText.replace(/"/g,'""')}"`).join(','));
      const blob = new Blob([rows.join('\n')], {type:'text/csv'});
      const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download='activity.csv'; a.click();
    });

    // ---- charts (demo data; wire to your API) ----
   
  </script>

<!--
BACKEND NOTES (create these minimal endpoints securely with CSRF + auth):

// update_general_settings.php
<?php /*
require 'db_connect.php'; session_start(); $uid=1; // from session
$tz = $_POST['timezone'] ?? 'UTC'; $df = $_POST['date_format'] ?? 'Y-m-d'; $rpp = max(5,min(100,(int)($_POST['records_per_page']??25)));
$stmt = $conn->prepare("INSERT INTO user_settings (user_id, timezone, date_format, records_per_page) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE timezone=VALUES(timezone), date_format=VALUES(date_format), records_per_page=VALUES(records_per_page)");
$stmt->bind_param('isss',$uid,$tz,$df,$rpp); $stmt->execute(); http_response_code(204);
*/?>

// update_notification_settings.php
<?php /*
require 'db_connect.php'; session_start(); $uid=1; // from session
$fields=['email_notifications','browser_notifications','alert_notifications','update_notifications','report_notifications','notification_frequency'];
$data=[]; foreach($fields as $f){ $data[$f] = isset($_POST[$f]) ? (($_POST[$f]=='on'||$_POST[$f]=='1')?1:$_POST[$f]) : 0; }
$stmt = $conn->prepare("UPDATE user_settings SET email_notifications=?, browser_notifications=?, alert_notifications=?, update_notifications=?, report_notifications=?, notification_frequency=? WHERE user_id=?");
$stmt->bind_param('iiiiisi', $data['email_notifications'],$data['browser_notifications'],$data['alert_notifications'],$data['update_notifications'],$data['report_notifications'],$data['notification_frequency'],$uid); $stmt->execute(); http_response_code(204);
*/?>

// update_password.php
<?php /*
require 'db_connect.php'; session_start(); $uid=1; // from session
$curr = $_POST['current_password'] ?? ''; $new = $_POST['new_password'] ?? '';
// Verify current password
$stmt=$conn->prepare("SELECT password_hash FROM users WHERE id=?"); $stmt->bind_param('i',$uid); $stmt->execute(); $hash=$stmt->get_result()->fetch_assoc()['password_hash']??'';
if(!password_verify($curr,$hash)){ http_response_code(400); exit; }
// Update
$newhash = password_hash($new, PASSWORD_DEFAULT);
$stmt=$conn->prepare("UPDATE users SET password_hash=? WHERE id=?"); $stmt->bind_param('si',$newhash,$uid); $stmt->execute(); http_response_code(204);
*/?>

SUGGESTED TABLES

CREATE TABLE IF NOT EXISTS user_settings (
  user_id INT PRIMARY KEY,
  timezone VARCHAR(64) DEFAULT 'UTC',
  date_format VARCHAR(32) DEFAULT 'Y-m-d',
  records_per_page INT DEFAULT 25,
  email_notifications TINYINT(1) DEFAULT 1,
  browser_notifications TINYINT(1) DEFAULT 1,
  alert_notifications TINYINT(1) DEFAULT 1,
  update_notifications TINYINT(1) DEFAULT 1,
  report_notifications TINYINT(1) DEFAULT 1,
  notification_frequency VARCHAR(16) DEFAULT 'instant',
  two_factor_auth TINYINT(1) DEFAULT 0,
  ga_integration TINYINT(1) DEFAULT 0,
  slack_integration TINYINT(1) DEFAULT 0,
  mailchimp_integration TINYINT(1) DEFAULT 0
);

CREATE TABLE IF NOT EXISTS activity_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_name VARCHAR(120),
  action VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

// Example: record activity
INSERT INTO activity_log (user_name, action) VALUES ('Admin','Logged in');
-->
</body>
</html>
