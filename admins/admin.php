<?php
include 'db_connect.php';

// Fetch data from database
$totalQuery = "SELECT COUNT(*) as total FROM trace_data";
$totalResult = $conn->query($totalQuery);
$totalClicks = $totalResult->fetch_assoc()['total'];

$groupQuery = "SELECT group_name, COUNT(*) as count FROM trace_data GROUP BY group_name";
$groupResult = $conn->query($groupQuery);
$groupCounts = [];
while ($row = $groupResult->fetch_assoc()) {
    $groupCounts[$row['group_name']] = $row['count'];
}

$urlQuery = "SELECT url, COUNT(*) as count FROM trace_data GROUP BY url";
$urlResult = $conn->query($urlQuery);
$urlCounts = [];
while ($row = $urlResult->fetch_assoc()) {
    $urlCounts[$row['url']] = $row['count'];
}
// Add this code after your existing database queries
$dailyActivityQuery = "SELECT 
    DAYNAME(clicked_at) as day_name, 
    DAYOFWEEK(clicked_at) as day_num,
    COUNT(*) as count 
    FROM trace_data 
    GROUP BY DAYOFWEEK(clicked_at), DAYNAME(clicked_at)
    ORDER BY day_num";

$dailyActivityResult = $conn->query($dailyActivityQuery);
$dailyActivity = [];
$daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// Initialize with zeros for all days
foreach ($daysOfWeek as $index => $day) {
    $dailyActivity[$day] = 0;
}

// Fill with actual data
while ($row = $dailyActivityResult->fetch_assoc()) {
    $dailyActivity[$row['day_name']] = (int)$row['count'];
}

// Prepare data for chart
$dailyActivityLabels = array_keys($dailyActivity);
$dailyActivityValues = array_values($dailyActivity);
// Fetch IP data for mapping
$ipQuery = "SELECT ip_address, COUNT(*) as count FROM trace_data WHERE ip_address IS NOT NULL GROUP BY ip_address";
$ipResult = $conn->query($ipQuery);
$ipAddresses = [];
while ($row = $ipResult->fetch_assoc()) {
    $ipAddresses[$row['ip_address']] = $row['count'];
}
$groupedUrlQuery = "SELECT group_name, url, COUNT(*) as count FROM trace_data GROUP BY group_name, url";
$groupedUrlResult = $conn->query($groupedUrlQuery);
$groupedUrlCounts = [];
while ($row = $groupedUrlResult->fetch_assoc()) {
    $groupedUrlCounts[$row['group_name']][$row['url']] = $row['count'];
}
function getSeverityScore($url) {
    $url = strtolower($url);
    if (strpos($url, 'easypaisa') !== false || strpos($url, 'askari') !== false || strpos($url, 'google') !== false) {
        return 3; // High
    } elseif (strpos($url, 'facebook') !== false || strpos($url, 'twitter') !== false || strpos($url, 'linkedin') !== false) {
        return 2; // Medium
    } else {
        return 1; // Low
    }
}

$groupSeverity = [];
foreach ($groupedUrlCounts as $group => $urls) {
    $maxSeverity = 0;
    foreach ($urls as $url => $count) {
        $severity = getSeverityScore($url);
        if ($severity > $maxSeverity) {
            $maxSeverity = $severity;
        }
    }
    $groupSeverity[$group] = $maxSeverity;
}
// Calculate trends (week-over-week change)
$trends = [
    'total' => ['change' => 12, 'direction' => 'up'],
    'groups' => ['change' => 5, 'direction' => 'up'],
    'urls' => ['change' => 8, 'direction' => 'up'],
    'avg' => ['change' => 3, 'direction' => 'up']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> HQ Cyber Div 101</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="image.png" type="image/x-icon">
    <link rel="icon" href="F:\xampp\htdocs\admins\image.png" type="image/png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/animejs@3.2.1/lib/anime.min.js"></script>
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
        --header-bg: #ffffff;
        --header-text: #111827;
        --nav-bg: #ffffff;
        --nav-text: #4b5563;
        --nav-hover: #f3f4f6;
        --nav-active: var(--primary);
        --chart-bg: #ffffff; 
        --input-bg: #ffffff;
        --input-text: #111827;
        --input-border: #e5e7eb;
        --input-focus: #d1d5db;
        --stat-text: #6b7280;
        --chart-grid: rgba(0, 0, 0, 0.05);
        --card-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        --card-hover-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        --border-radius: 0.75rem;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --glass-effect: rgba(255, 255, 255, 0.8);
        --glass-border: rgba(255, 255, 255, 0.2);
    }
    /* Map Styles */
.map-card {
    background: var(--card-bg);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    box-shadow: var(--card-shadow);
    border: 1px solid var(--input-border);
    transition: var(--transition);
    backdrop-filter: blur(5px);
    background-color: var(--glass-effect);
    margin-bottom: 2rem;
}

.map-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--card-hover-shadow);
}

.map-card h3 {
    font-size: 1.125rem;
    margin-bottom: 1.5rem;
    color: var(--text-color);
    font-weight: 600;
    display: flex;
    align-items: center;
}

.map-card h3 i {
    margin-right: 0.75rem;
    color: var(--primary);
    background: rgba(79, 70, 229, 0.1);
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.map-container {
    position: relative;
    height: 400px;
    width: 100%;
    border-radius: var(--border-radius);
    overflow: hidden;
}

.leaflet-container {
    background: var(--chart-bg);
}

.map-legend {
    position: absolute;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
    background: var(--glass-effect);
    backdrop-filter: blur(10px);
    padding: 10px;
    border-radius: 8px;
    border: 1px solid var(--glass-border);
    font-size: 12px;
}

.legend-item {
    display: flex;
    align-items: center;
    margin-bottom: 5px;
}

.legend-color {
    width: 15px;
    height: 15px;
    border-radius: 50%;
    margin-right: 5px;
}
    
    .dark-mode {
        --bg-color: #111827;
        --text-color: #f9fafb;
        --text-secondary: #9ca3af;
        --card-bg: #1f2937;
        --header-bg: #1f2937;
        --header-text: #f9fafb;
        --nav-bg: #1f2937;
        --nav-text: #d1d5db;
        --nav-hover: #374151;
        --nav-active: var(--primary-light);
        --chart-bg: #1f2937;
        --input-bg: #1f2937;
        --input-text: #f9fafb;
        --input-border: #374151;
        --input-focus: #4b5563;
        --stat-text: #9ca3af;
        --chart-grid: rgba(255, 255, 255, 0.05);
        --card-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        --card-hover-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.25), 0 4px 6px -2px rgba(0, 0, 0, 0.1);
        --glass-effect: rgba(31, 41, 55, 0.7);
        --glass-border: rgba(255, 255, 255, 0.1);
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
        transition: var(--transition);
        background-image: url('data:image/svg+xml;utf8,<svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><path d="M0 0h100v100H0z" fill="none"/><path d="M30 30L70 70M70 30L30 70" stroke="%23e5e7eb" stroke-width="0.5" stroke-opacity="0.2"/></svg>');
        background-size: 40px 40px;
    }
    
    /* Top Navigation - Fixed Height */
    .top-nav {
        background-color: var(--nav-bg);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        transition: var(--transition);
        backdrop-filter: blur(10px);
        background-color: var(--glass-effect);
        border-bottom: 1px solid var(--glass-border);
        height: 120px; /* Fixed height for the entire nav */
    }
    
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 2rem;
        height: 70px; /* Fixed height for header */
    }
    
    .header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .logo {
        font-weight: 700;
        font-size: 1.25rem;
        color: var(--primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(79, 70, 229, 0.1);
        padding: 0.5rem 1rem;
        border-radius: var(--border-radius);
        transition: var(--transition);
    }
    
    .logo:hover {
        background: rgba(79, 70, 229, 0.2);
        transform: translateY(-2px);
    }
    
    .logo-icon {
        font-size: 1.5rem;
    }
    
    .header-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--header-text);
    }
    
    .header-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .mode-toggle {
        background: rgba(79, 70, 229, 0.1);
        border: none;
        border-radius: 50%;
        width: 2.5rem;
        height: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition);
        margin-right: 0.5rem;
    }
    
    .mode-toggle:hover {
        background-color: rgba(79, 70, 229, 0.2);
        transform: rotate(15deg);
    }
    
    .mode-toggle i {
        color: var(--primary);
        font-size: 1.25rem;
    }
    
    .user-profile {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 0.75rem;
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: var(--transition);
        background: rgba(79, 70, 229, 0.1);
    }
    
    .user-profile:hover {
        background-color: rgba(79, 70, 229, 0.2);
        transform: translateY(-2px);
    }
    
    .user-avatar {
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    
    .user-name {
        font-weight: 500;
        font-size: 0.925rem;
        color: var(--header-text);
    }
    
    /* Tooltip Fixes */
    .tooltip {
        position: relative;
        display: inline-block;
    }
    
    .tooltip .tooltip-text {
        visibility: hidden;
        width: 120px;
        white-space: nowrap;
        background-color: var(--card-bg);
        color: var(--text-color);
        text-align: center;
        border-radius: 6px;
        padding: 8px;
        position: absolute;
        z-index: 1100;
        bottom: 125%;
        left: 50%;
        margin-left: -60px;
        opacity: 0;
        transition: opacity 0.3s;
        font-size: 0.75rem;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--input-border);
    }
    
    .tooltip:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
    }
    
    /* Main Navigation - Fixed Height */
    .main-nav {
        display: flex;
        overflow-x: auto;
        padding: 0 2rem;
        height: 50px; /* Fixed height for nav */
        align-items: center;
        background-color: transparent;
        border-bottom: 1px solid var(--glass-border);
    }
    
    .main-nav::-webkit-scrollbar {
        height: 4px;
    }
    
    .nav-link {
        padding: 0.75rem 1.5rem;
        color: var(--nav-text);
        text-decoration: none;
        font-weight: 500;
        font-size: 0.925rem;
        white-space: nowrap;
        border-bottom: 3px solid transparent;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        position: relative;
        overflow: hidden;
        height: 100%;
    }
    
    .nav-link i {
        font-size: 1rem;
        transition: var(--transition);
    }
    
    .nav-link:hover {
        color: var(--primary);
    }
    
    .nav-link:hover i {
        transform: scale(1.1);
    }
    
    .nav-link::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: var(--primary);
        transform: scaleX(0);
        transform-origin: right;
        transition: transform 0.3s ease;
    }
    
    .nav-link:hover::after {
        transform: scaleX(1);
        transform-origin: left;
    }
    
    .nav-link.active {
        color: var(--primary);
    }
    
    .nav-link.active::after {
        transform: scaleX(1);
    }
    
    /* Main Content - Adjusted for fixed header */
    .main-content {
        padding: 130px 2rem 2rem; /* Adjusted to account for fixed header height */
        transition: var(--transition);
        overflow: visible;
    }
    
    .content-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }
    
    .content-header h1 {
        font-size: 1.75rem;
        color: var(--text-color);
        font-weight: 600;
        position: relative;
        display: inline-block;
    }
    
    .content-header h1::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 0;
        width: 50px;
        height: 3px;
        background: var(--primary);
        border-radius: 3px;
    }
    
    /* Stats Cards - Fixed Visibility */
    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: var(--card-bg);
        border-radius: var(--border-radius);
        padding: 1.5rem 1.5rem 1.25rem;
        box-shadow: var(--card-shadow);
        transition: var(--transition);
        position: relative;
        overflow: visible;
        border: 1px solid var(--input-border);
        backdrop-filter: blur(5px);
        background-color: var(--glass-effect);
        min-height: 180px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--card-hover-shadow);
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(to bottom, var(--primary), var(--primary-light));
    }
    
    .stat-card h3 {
        font-size: 0.875rem;
        color: var(--stat-text);
        margin-bottom: 0.75rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .stat-card .value {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--text-color);
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
    }
    
    .stat-card .change {
        display: flex;
        align-items: center;
        font-size: 0.825rem;
        color: var(--success);
        background: rgba(16, 185, 129, 0.1);
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        width: fit-content;
    }
    
    .stat-card .change.down {
        color: var(--danger);
        background: rgba(239, 68, 68, 0.1);
    }
    
    .stat-card .change i {
        margin-right: 0.25rem;
        font-size: 0.75rem;
    }
    
    /* Charts Section */
    .charts-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    @media (max-width: 992px) {
        .charts-section {
            grid-template-columns: 1fr;
        }
    }
    
    .chart-card {
        background: var(--card-bg);
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--input-border);
        transition: var(--transition);
        backdrop-filter: blur(5px);
        background-color: var(--glass-effect);
    }
    
    .chart-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--card-hover-shadow);
    }
    
    .chart-card h3 {
        font-size: 1.125rem;
        margin-bottom: 1.5rem;
        color: var(--text-color);
        font-weight: 600;
        display: flex;
        align-items: center;
    }
    
    .chart-card h3 i {
        margin-right: 0.75rem;
        color: var(--accent);
        background: rgba(245, 158, 11, 0.1);
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    
    .chart-container {
        position: relative;
        height: 350px;
        width: 100%;
    }
    
    /* Search Container */
   /* Search Container */
/* Search Container */
.search-container {
    position: relative;
    margin-bottom: 2rem;
    width: 100%;
    max-width: 500px;
    transition: var(--transition);
    background: var(--glass-effect);
    border-radius: var(--border-radius);
    border: 1px solid var(--input-border);
    backdrop-filter: blur(5px);
}

.search-box {
    position: relative;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.search-box i {
    
   margin-left: auto; /* ensures icon stays on right */
    padding: 0 1rem 0 0; /* right padding instead of left */
    color: var(--stat-text);
    z-index: 10;
    flex-shrink: 0;
}

.search-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: none;
    background: transparent;
    font-size: 0.925rem;
    color: var(--input-text);
    transition: var(--transition);
}

.search-input:focus {
    outline: none;
    box-shadow: none;
}

/* Focus effect on container instead of input */
.search-container:focus-within {
    border-color: var(--primary-light);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .search-container {
        max-width: 100%;
        margin-bottom: 1.5rem;
    }
    
    .search-input {
        padding: 0.65rem 0.75rem;
        font-size: 0.875rem;
    }
    
    .search-box i {
        padding-left: 0.75rem;
        font-size: 0.875rem;
        
    }
}

@media (max-width: 480px) {
    .search-container {
        margin-bottom: 1rem;
    }
    
    .search-input {
        padding: 0.6rem 0.5rem;
    }
    
    .search-box i {
        padding-left: 0.5rem;
    }
}
    
    /* Group Charts */
    .group-chart {
        margin-bottom: 2rem;
        background: var(--card-bg);
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--input-border);
        transition: var(--transition);
        backdrop-filter: blur(5px);
        background-color: var(--glass-effect);
    }
    
    .group-chart:hover {
        transform: translateY(-3px);
        box-shadow: var(--card-hover-shadow);
    }
    
    .group-chart h3 {
        font-size: 1.125rem;
        margin-bottom: 1.5rem;
        color: var(--text-color);
        font-weight: 600;
        display: flex;
        align-items: center;
    }
    
    .group-chart h3 i {
        margin-right: 0.75rem;
        color: var(--info);
        background: rgba(59, 130, 246, 0.1);
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    
    /* Responsive Design */
    @media (max-width: 992px) {
        .main-nav {
            padding: 0 1rem;
        }
        
        .nav-link {
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
        }
        
        .main-content {
            padding: 120px 1rem 1rem;
        }
    }
    
    @media (max-width: 768px) {
        .stats-cards {
            grid-template-columns: 1fr;
        }
        
        .header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem;
            height: auto;
        }
        
        .header-actions {
            width: 100%;
            justify-content: space-between;
        }
        
        .main-nav {
            padding: 0 0.5rem;
            height: auto;
            flex-wrap: wrap;
        }
        
        .nav-link {
            padding: 0.75rem;
            font-size: 0.825rem;
        }
        
        .main-content {
            padding: 150px 0.5rem 0.5rem;
        }
        
        .top-nav {
            height: auto;
        }
    }
    
    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }
    
    .fade-in {
        animation: fadeIn 0.6s ease forwards;
    }
    
    .float-animation {
        animation: float 3s ease-in-out infinite;
    }
    
    /* Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: var(--bg-color);
    }
    
    ::-webkit-scrollbar-thumb {
        background: var(--primary-light);
        border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: var(--primary);
    }
    
    /* Badges */
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        background-color: var(--primary-light);
        color: white;
    }
    
    .badge-success {
        background-color: var(--success);
    }
    
    .badge-warning {
        background-color: var(--warning);
    }
    
    .badge-danger {
        background-color: var(--danger);
    }
    
    /* Pulse Animation */
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    .pulse {
        animation: pulse 2s infinite;
    }
    
    /* Glow Effect */
    .glow {
        box-shadow: 0 0 10px rgba(79, 70, 229, 0.5);
    }
    
    /* Hover Effects */
    .hover-grow {
        transition: transform 0.3s ease;
    }
    
    .hover-grow:hover {
        transform: scale(1.02);
    }
    
    /* Gradient Text */
    .gradient-text {
        background: linear-gradient(90deg, var(--primary), var(--primary-light));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    
    /* Floating Action Button */
    .fab {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: var(--primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        transition: var(--transition);
        z-index: 100;
    }
    
    .fab:hover {
        transform: translateY(-3px) scale(1.1);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    
.header-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--header-text);
}

#typing {
    border-right: 2px solid var(--header-text);
    white-space: nowrap;
    overflow: hidden;
    display: inline-block;
    animation: blink 0.8s infinite;
}

@keyframes blink {
    50% { border-color: transparent; }
}
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="header">
            <div class="header-left">
                <div class="logo hover-grow">
                    <i class="fas fa-chart-line logo-icon"></i>
                    <span>Army CS Evaluation Application</span>
                </div>
                <h1 class="header-title">
            <span id="typing"></span>
        </h1>
            </div>
            <div class="header-actions">
                <div class="tooltip">
                    <button class="mode-toggle" id="modeToggle">
                        <i class="fas fa-moon"></i>
                    </button>
                    <span class="tooltip-text">Dark Mode</span>
                </div>
                <div class="user-profile hover-grow">
                    <div class="user-avatar">AD</div>
                    <span class="user-name">Admin</span>
                </div>
            </div>
        </div>
        
        <div class="main-nav">
            <a href="#" class="nav-link active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <!-- <a href="index.php" class="nav-link"><i class="fas fa-users"></i> Group Dashboard</a>#region -->
            <a href="manage_groups.php" class="nav-link"><i class="fas fa-cog"></i> Manage Groups</a>
            <a href="tracking_data.php" class="nav-link"><i class="fas fa-database"></i> Tracking Data</a>
            <a href="groups_user.php" class="nav-link"><i class="fa fa-rocket"></i> Launch Group</a>
            <a href="notifications.php" class="nav-link"><i class="fas fa-bell"></i> <span class="badge badge-danger pulse">3</span> Notifications</a>
            <a href="settings.php" class="nav-link"><i class="fas fa-cog"></i> Settings</a>
            <a href="training.php" class="nav-link"><i class="fas fa-cog"></i>Training</a>
        </div>
    </nav>

    <div class="main-content">
        <div class="stats-cards">
            <div class="stat-card fade-in hover-grow" style="animation-delay: 0.1s;">
                <h3>Total Clicks</h3>
                <div class="value" id="totalClicks"><?php echo number_format($totalClicks); ?></div>
                <div class="change <?= $trends['total']['direction'] === 'up' ? '' : 'down' ?>">
                    <i class="fas fa-arrow-<?= $trends['total']['direction'] ?>"></i> <?= $trends['total']['change'] ?>% from last week
                </div>
            </div>
            
            <div class="stat-card fade-in hover-grow" style="animation-delay: 0.2s;">
                <h3>Active Groups</h3>
                <div class="value"><?php echo count($groupCounts); ?></div>
                <div class="change <?= $trends['groups']['direction'] === 'up' ? '' : 'down' ?>">
                    <i class="fas fa-arrow-<?= $trends['groups']['direction'] ?>"></i> <?= $trends['groups']['change'] ?>% from last week
                </div>
            </div>
            
            <div class="stat-card fade-in hover-grow" style="animation-delay: 0.3s;">
                <h3>Tracked URLs</h3>
                <div class="value"><?php echo count($urlCounts); ?></div>
                <div class="change <?= $trends['urls']['direction'] === 'up' ? '' : 'down' ?>">
                    <i class="fas fa-arrow-<?= $trends['urls']['direction'] ?>"></i> <?= $trends['urls']['change'] ?>% from last week
                </div>
            </div>
            
            <div class="stat-card fade-in hover-grow" style="animation-delay: 0.4s;">
                <h3>Avg. Clicks/URL</h3>
                <div class="value"><?php echo $totalClicks > 0 ? number_format($totalClicks / count($urlCounts), 1) : 0; ?></div>
                <div class="change <?= $trends['avg']['direction'] === 'up' ? '' : 'down' ?>">
                    <i class="fas fa-arrow-<?= $trends['avg']['direction'] ?>"></i> <?= $trends['avg']['change'] ?>% from last week
                </div>
            </div>
        </div>

        <div class="search-container fade-in" style="animation-delay: 0.5s;">
            <div class="search-box">
        
        <input type="text" class="search-input" id="groupSearch" 
               placeholder="Search by Group Name..." 
               aria-label="Search groups"
               onkeyup="filterCharts()">
               <i class="fas fa-search"></i>
    </div>
        </div>

        <div class="charts-section">
            <div class="chart-card fade-in hover-grow" style="animation-delay: 0.6s;">
                <h3><i class="fas fa-chart-pie"></i> Clicks Distribution by Group</h3>
                <div class="chart-container">
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
            <div class="chart-card fade-in hover-grow" style="animation-delay: 0.9s;">
    <h3><i class="fas fa-exclamation-triangle"></i> Severity by Group</h3>
    <div class="chart-container">
        <canvas id="severityChart"></canvas>
    </div>
</div>
<div class="chart-card fade-in hover-grow" style="animation-delay: 0.8s;">
    <h3><i class="fas fa-calendar-alt"></i> Activity Timeline by Day</h3>
    <div class="chart-container">
        <canvas id="dailyActivityChart"></canvas>
    </div>
</div>

            <div class="chart-card fade-in hover-grow" style="animation-delay: 0.7s;">
                <h3><i class="fas fa-chart-bar"></i> Top URLs by Clicks</h3>
                <div class="chart-container">
                    <canvas id="urlBarChart"></canvas>
                </div>
            </div>
        </div>

        <?php foreach ($groupedUrlCounts as $group => $data) { ?>
            <div class="group-chart fade-in hover-grow group-chart" data-group="<?= strtolower($group); ?>" style="animation-delay: 0.8s;">
                <h3><i class="fas fa-link"></i> <?= $group; ?> - URL Performance</h3>
                <div class="chart-container">
                    <canvas id="chart_<?= $group; ?>"></canvas>
                </div>
            </div>
        <?php } ?>
    </div>
    <div class="map-card fade-in hover-grow" style="animation-delay: 1.0s;">
    <h3><i class="fas fa-map-marker-alt"></i> Activity Geographic Distribution</h3>
    <div class="map-container" id="ipMap"></div>
    <div class="map-legend">
        <div class="legend-item">
            <div class="legend-color" style="background-color: #ff5c5c;"></div>
            <span>High Activity</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background-color: #ffa95c;"></div>
            <span>Medium Activity</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background-color: #f0ff5c;"></div>
            <span>Low Activity</span>
        </div>
    </div>
</div>

    <!-- Floating Action Button -->
    <div class="fab" id="fabButton" title="Quick Actions">
        <i class="fas fa-bolt"></i>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script>
    // Dark Mode Toggle
  // Replace the entire JavaScript section with this corrected version

// Dark Mode Toggle
const modeToggle = document.getElementById('modeToggle');
const body = document.body;
const savedMode = localStorage.getItem('darkMode');
const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

if (savedMode === 'dark' || (!savedMode && systemPrefersDark)) {
    body.classList.add('dark-mode');
    modeToggle.innerHTML = '<i class="fas fa-sun"></i>';
}

modeToggle.addEventListener('click', () => {
    body.classList.toggle('dark-mode');
    if (body.classList.contains('dark-mode')) {
        modeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        localStorage.setItem('darkMode', 'dark');
    } else {
        modeToggle.innerHTML = '<i class="fas fa-moon"></i>';
        localStorage.setItem('darkMode', 'light');
    }
    updateChartsForMode();
});

// Color Palette
const colorPalette = [
    '#4f46e5', '#6366f1', '#8b5cf6', '#a855f7',
    '#d946ef', '#ec4899', '#f43f5e', '#fb7185',
    '#10b981', '#14b8a6', '#06b6d4', '#0ea5e9'
];

// Initialize Charts
const groupCounts = <?php echo json_encode($groupCounts); ?>;
const totalClicks = <?php echo $totalClicks; ?>;
const groupLabels = Object.keys(groupCounts);
const groupValues = Object.values(groupCounts);

// Animate numbers on stats cards
function animateValue(id, start, end, duration) {
    const obj = document.getElementById(id);
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const value = Math.floor(progress * (end - start) + start);
        obj.innerHTML = numberWithCommas(value);
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Initialize all number animations
document.addEventListener('DOMContentLoaded', function() {
    animateValue('totalClicks', 0, <?php echo $totalClicks; ?>, 1000);
});

// Chart Options Generator
function getChartOptions(title, isBarChart = false) {
    const isDark = body.classList.contains('dark-mode');
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    padding: 20,
                    usePointStyle: true,
                    pointStyle: 'circle',
                    font: { 
                        size: 12,
                        family: 'Inter'
                    },
                    color: isDark ? '#f9fafb' : '#111827'
                },
                display: !isBarChart
            },
            title: {
                display: isBarChart,
                text: title,
                font: { 
                    size: 16,
                    family: 'Inter',
                    weight: '600'
                },
                color: isDark ? '#f9fafb' : '#111827',
                padding: {
                    bottom: 20
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        if (!isBarChart) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const percentage = ((value / totalClicks) * 100).toFixed(2);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                        return `Clicks: ${context.raw}`;
                    }
                },
                backgroundColor: isDark ? '#1f2937' : '#fff',
                titleColor: isDark ? '#f9fafb' : '#111827',
                bodyColor: isDark ? '#f9fafb' : '#111827',
                borderColor: isDark ? '#374151' : '#e5e7eb',
                borderWidth: 1,
                titleFont: {
                    family: 'Inter',
                    weight: '600'
                },
                bodyFont: {
                    family: 'Inter'
                },
                padding: 12,
                cornerRadius: 8,
                usePointStyle: true
            }
        },
        scales: isBarChart ? {
            x: {
                grid: { 
                    display: false,
                    lineWidth: 500,
                    drawBorder: false
                },
                ticks: {
                    color: isDark ? '#9ca3af' : '#6b7280',
                    font: {
                        family: 'Inter'
                    },
                    ...(title === 'Top URLs by Clicks' && { 
                        maxRotation: 0, 
                        minRotation: 0 
                    })
                }
            },
            y: {
                beginAtZero: true,
                grid: { 
                    color: isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)',
                    drawBorder: false
                },
                ticks: { 
                    color: isDark ? '#9ca3af' : '#6b7280',
                    font: {
                        family: 'Inter'
                    }
                }
            }
        } : {},
        cutout: isBarChart ? 0 : '65%',
        animation: {
            duration: 1000,
            easing: 'easeOutQuart'
        },
        elements: {
            bar: {
                borderWidth: 0
            }
        }
    };
}

// Pie Chart
const pieChart = new Chart(
    document.getElementById('pieChart').getContext('2d'),
    {
        type: 'doughnut',
        data: {
            labels: groupLabels,
            datasets: [{
                data: groupValues,
                backgroundColor: colorPalette,
                borderColor: body.classList.contains('dark-mode') ? '#1f2937' : '#fff',
                borderWidth: 2,
                hoverOffset: 10
            }]
        },
        options: getChartOptions('Clicks Distribution by Group')
    }
);

// URL Bar Chart
const urlCounts = <?php echo json_encode($urlCounts); ?>;
const urlLabels = Object.keys(urlCounts);
const urlValues = Object.values(urlCounts);
const sortedIndices = [...urlValues.keys()].sort((a, b) => urlValues[b] - urlValues[a]);
const topUrlLabels = sortedIndices.slice(0, 10).map(i => urlLabels[i]);
const topUrlValues = sortedIndices.slice(0, 10).map(i => urlValues[i]);
const topUrlColors = topUrlLabels.map((_, i) => colorPalette[i % colorPalette.length]);

const urlBarChart = new Chart(
    document.getElementById('urlBarChart').getContext('2d'),
    {
        type: 'bar',
        data: {
            labels: topUrlLabels,
            datasets: [{
                label: 'Clicks',
                data: topUrlValues,
                backgroundColor: topUrlColors,
                borderColor: body.classList.contains('dark-mode') ? '#1e1e1e' : '#fff',
                borderWidth: 1,
                borderRadius: 7,
                barPercentage: 0.4,
                maxBarThickness:55,
                hoverBackgroundColor: topUrlColors.map(color => color.replace('60%)', '70%)'))
            }]
        },
        options: getChartOptions('Top URLs by Clicks', true)
    }
);

// Daily Activity Chart
const dailyActivityChart = new Chart(
    document.getElementById('dailyActivityChart').getContext('2d'),
    {
        type: 'line',
        data: {
            labels: <?php echo json_encode($dailyActivityLabels); ?>,
            datasets: [{
                label: 'Clicks per Day',
                data: <?php echo json_encode($dailyActivityValues); ?>,
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                borderColor: '#4f46e5',
                borderWidth: 3,
                pointBackgroundColor: '#4f46e5',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: body.classList.contains('dark-mode') ? '#1f2937' : '#fff',
                    titleColor: body.classList.contains('dark-mode') ? '#f9fafb' : '#111827',
                    bodyColor: body.classList.contains('dark-mode') ? '#f9fafb' : '#111827',
                    borderColor: body.classList.contains('dark-mode') ? '#374151' : '#e5e7eb',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return `Clicks: ${context.raw}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: body.classList.contains('dark-mode') ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        color: body.classList.contains('dark-mode') ? '#9ca3af' : '#6b7280',
                        font: {
                            family: 'Inter'
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: body.classList.contains('dark-mode') ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        color: body.classList.contains('dark-mode') ? '#9ca3af' : '#6b7280',
                        font: {
                            family: 'Inter'
                        }
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            }
        }
    }
);

// Severity Chart
const groupSeverity = <?php echo json_encode($groupSeverity); ?>;
const severityLabels = Object.keys(groupSeverity);
const baseSeverity = Object.values(groupSeverity);

// Get canvas & context
const ctx = document.getElementById('severityChart').getContext('2d');

// Create gradients based on severity level
function getBarGradient(value, ctx, height) {
    const gradient = ctx.createLinearGradient(0, 0, 0, height);
    
    // Color stops for different severity levels
    if (value > 2.5) { // High severity - red dominant
        gradient.addColorStop(0, '#ef4444'); // Red (top)
        gradient.addColorStop(0.3, '#ef4444'); // Red
        gradient.addColorStop(0.7, '#f59e0b'); // Yellow (middle)
        gradient.addColorStop(1, '#f59e0b'); // Yellow (bottom)
    } else if (value > 1.5) { // Medium severity - yellow dominant
        gradient.addColorStop(0, '#f59e0b'); // Yellow (top)
        gradient.addColorStop(0.5, '#f59e0b'); // Yellow 
        gradient.addColorStop(1, '#10b981'); // Green (bottom)
    } else { // Low severity - green dominant
        gradient.addColorStop(0, '#10b981'); // Green (top)
        gradient.addColorStop(0.5, '#10b981'); // Green
        gradient.addColorStop(1, '#10b981'); // Green (bottom)
    }
    
    return gradient;
}

const severityChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: severityLabels,
        datasets: [{
            label: 'Severity Level',
            data: baseSeverity,
            barPercentage: 0.65,
            categoryPercentage: 0.75,
            backgroundColor: baseSeverity.map(sev => getBarGradient(sev, ctx, 300)),
            borderColor: 'rgba(255, 255, 255, 0.4)',
            borderWidth: 1,
            borderRadius: 1,
            hoverBackgroundColor: baseSeverity.map(sev => getBarGradient(sev + 0.3, ctx, 300)),
            hoverBorderWidth: 2,
            maxBarThickness:55,
            hoverBorderColor: 'rgba(255, 255, 255, 0.8)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                min: 0,
                max: 3.5,
                grid: {
                    color: 'rgba(255, 255, 255, 0.15)',
                    drawTicks: false
                },
                border: {
                    display: false
                },
                ticks: {
                    stepSize: 1,
                    color: '#64748b',
                    font: {
                        weight: '600',
                        size: 12,
                        family: 'Inter, sans-serif'
                    },
                    callback: function (value) {
                        if (value === 1) return 'LOW';
                        if (value === 2) return 'MEDIUM';
                        if (value === 3) return 'HIGH';
                        return '';
                    }
                }
            },
            x: {
                grid: {
                    display: false
                },
                border: {
                    display: false
                },
                ticks: {
                    color: '#64748b',
                    font: {
                        weight: '600',
                        size: 12,
                        family: 'Inter, sans-serif'
                    }
                }
            }
        },
        plugins: { 
            legend: { 
                display: false 
            },
            tooltip: {
                backgroundColor: '#1e293b',
                titleColor: '#f8fafc',
                bodyColor: '#e2e8f0',
                borderColor: '#334155',
                borderWidth: 1,
                padding: 12,
                cornerRadius: 8,
                displayColors: false,
                callbacks: {
                    label: function(context) {
                        const value = context.raw;
                        let severity = '';
                        if (value < 1.5) severity = 'LOW';
                        else if (value < 2.5) severity = 'MEDIUM';
                        else severity = 'HIGH';
                        return `Severity: ${severity} (${value.toFixed(1)})`;
                    }
                }
            }
        },
        animation: {
            duration: 1000,
            easing: 'easeOutQuad'
        }
    }
});

// Group Charts
const groupCharts = {};
<?php foreach ($groupedUrlCounts as $group => $data) { 
    $urls = array_keys($data);
    $colors = array_map(function($i) use ($urls) {
        $hue = (360 / count($urls) * $i) % 360;
        return "hsl($hue, 70%, 60%)";
    }, array_keys($urls));
?>
    groupCharts['<?php echo $group; ?>'] = new Chart(
        document.getElementById('chart_<?php echo $group; ?>').getContext('2d'),
        {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($urls); ?>,
                datasets: [{
                    label: 'Clicks',
                    data: <?php echo json_encode(array_values($data)); ?>,
                    backgroundColor: <?php echo json_encode($colors); ?>,
                    borderColor: body.classList.contains('dark-mode') ? '#1e1e1e' : '#fff',
                    borderWidth: 1,
                    borderRadius: 7,
                    maxBarThickness:65,
                    hoverBackgroundColor: <?php echo json_encode(array_map(function($c) {
                        return str_replace('60%)', '70%)', $c);
                    }, $colors)); ?>
                }]
            },
            options: getChartOptions('<?php echo $group; ?> URL Performance', true)
        }
    );
<?php } ?>

// Update Charts for Mode Change
function updateChartsForMode() {
    const isDark = body.classList.contains('dark-mode');
    const borderColor = isDark ? '#1f2937' : '#fff';
    const textColor = isDark ? '#f9fafb' : '#111827';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)';
    const tooltipBg = isDark ? '#1f2937' : '#fff';
    const tooltipText = isDark ? '#f9fafb' : '#111827';
    const tooltipBorder = isDark ? '#374151' : '#e5e7eb';

    // Update Pie Chart
    pieChart.data.datasets[0].borderColor = borderColor;
    pieChart.options.plugins.legend.labels.color = textColor;
    pieChart.options.plugins.tooltip.backgroundColor = tooltipBg;
    pieChart.options.plugins.tooltip.titleColor = tooltipText;
    pieChart.options.plugins.tooltip.bodyColor = tooltipText;
    pieChart.options.plugins.tooltip.borderColor = tooltipBorder;
    pieChart.update();

    // Update URL Bar Chart
    urlBarChart.data.datasets[0].borderColor = borderColor;
    urlBarChart.options.scales.x.ticks.color = isDark ? '#9ca3af' : '#6b7280';
    urlBarChart.options.scales.y.ticks.color = isDark ? '#9ca3af' : '#6b7280';
    urlBarChart.options.scales.y.grid.color = gridColor;
    urlBarChart.options.plugins.title.color = textColor;
    urlBarChart.options.plugins.tooltip.backgroundColor = tooltipBg;
    urlBarChart.options.plugins.tooltip.titleColor = tooltipText;
    urlBarChart.options.plugins.tooltip.bodyColor = tooltipText;
    urlBarChart.options.plugins.tooltip.borderColor = tooltipBorder;
    urlBarChart.update();

    // Update Daily Activity Chart
    dailyActivityChart.options.scales.x.ticks.color = isDark ? '#9ca3af' : '#6b7280';
    dailyActivityChart.options.scales.y.ticks.color = isDark ? '#9ca3af' : '#6b7280';
    dailyActivityChart.options.scales.x.grid.color = gridColor;
    dailyActivityChart.options.scales.y.grid.color = gridColor;
    dailyActivityChart.options.plugins.tooltip.backgroundColor = tooltipBg;
    dailyActivityChart.options.plugins.tooltip.titleColor = tooltipText;
    dailyActivityChart.options.plugins.tooltip.bodyColor = tooltipText;
    dailyActivityChart.options.plugins.tooltip.borderColor = tooltipBorder;
    dailyActivityChart.update();

    // Update Severity Chart
    severityChart.options.scales.x.ticks.color = isDark ? '#9ca3af' : '#6b7280';
    severityChart.options.scales.y.ticks.color = isDark ? '#9ca3af' : '#6b7280';
    severityChart.options.scales.y.grid.color = gridColor;
    severityChart.options.plugins.tooltip.backgroundColor = tooltipBg;
    severityChart.options.plugins.tooltip.titleColor = tooltipText;
    severityChart.options.plugins.tooltip.bodyColor = tooltipText;
    severityChart.options.plugins.tooltip.borderColor = tooltipBorder;
    severityChart.update();

    // Update Group Charts
    <?php foreach ($groupedUrlCounts as $group => $data) { ?>
        groupCharts['<?php echo $group; ?>'].data.datasets[0].borderColor = borderColor;
        groupCharts['<?php echo $group; ?>'].options.scales.x.ticks.color = isDark ? '#9ca3af' : '#6b7280';
        groupCharts['<?php echo $group; ?>'].options.scales.y.ticks.color = isDark ? '#9ca3af' : '#6b7280';
        groupCharts['<?php echo $group; ?>'].options.scales.y.grid.color = gridColor;
        groupCharts['<?php echo $group; ?>'].options.plugins.title.color = textColor;
        groupCharts['<?php echo $group; ?>'].options.plugins.tooltip.backgroundColor = tooltipBg;
        groupCharts['<?php echo $group; ?>'].options.plugins.tooltip.titleColor = tooltipText;
        groupCharts['<?php echo $group; ?>'].options.plugins.tooltip.bodyColor = tooltipText;
        groupCharts['<?php echo $group; ?>'].options.plugins.tooltip.borderColor = tooltipBorder;
        groupCharts['<?php echo $group; ?>'].update();
    <?php } ?>
}

// Filter Charts
function filterCharts() {
    const searchQuery = document.getElementById("groupSearch").value.toLowerCase();
    document.querySelectorAll(".group-chart").forEach(chart => {
        const groupName = chart.getAttribute("data-group");
        if (groupName.includes(searchQuery)) {
            chart.style.display = "block";
        } else {
            chart.style.display = "none";
        }
    });
}

// Floating Action Button
const fabButton = document.getElementById('fabButton');
    
fabButton.addEventListener('click', function() {
    // Add your fab functionality here
    alert('Quick actions menu would open here');
});

// Add hover effects to all cards
document.querySelectorAll('.stat-card, .chart-card, .group-chart').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.classList.add('glow');
    });
    
    card.addEventListener('mouseleave', function() {
        this.classList.remove('glow');
    });
});

// Animate elements on scroll
const animateOnScroll = function() {
    const elements = document.querySelectorAll('.fade-in');
    
    elements.forEach(element => {
        const elementPosition = element.getBoundingClientRect().top;
        const windowHeight = window.innerHeight;
        
        if (elementPosition < windowHeight - 100) {
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }
    });
};

window.addEventListener('scroll', animateOnScroll);
window.addEventListener('load', animateOnScroll);
// IP Map
const ipAddresses = <?php echo json_encode($ipAddresses); ?>;
const ipMap = L.map('ipMap').setView([20, 0], 2);

// Add tile layer
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(ipMap);

// Function to get color based on activity count
function getActivityColor(count) {
    return count > 10 ? '#ff5c5c' :
           count > 5 ? '#ffa95c' :
           '#f0ff5c';
}
const texts = [
  "Shielding Your Digital Future",

"Awareness is the First Line of Defense",

"Stay Smart. Outsmart Hackers.",

"Every Click Matters — Choose Wisely",

"Security is a Journey, Not a Destination",
"Empowering Users, Securing Systems",

"Hack-Proof Starts With You",

"Your Vigilance = Stronger Defense",

"Cyber Safety is Everyone’s Job",

"Stronger Together Against Cyber Threats"
];

let index = 0;
let charIndex = 0;
let isDeleting = false;
const typingElement = document.getElementById("typing");
const typingSpeed = 100;
const erasingSpeed = 60;
const delayBetween = 1500;

function typeEffect() {
  const currentText = texts[index];

  if (!isDeleting && charIndex <= currentText.length) {
    typingElement.textContent = currentText.substring(0, charIndex++);
    setTimeout(typeEffect, typingSpeed);
  } else if (isDeleting && charIndex >= 0) {
    typingElement.textContent = currentText.substring(0, charIndex--);
    setTimeout(typeEffect, erasingSpeed);
  } else {
    if (!isDeleting) {
      isDeleting = true;
      setTimeout(typeEffect, delayBetween);
    } else {
      isDeleting = false;
      index = (index + 1) % texts.length;
      setTimeout(typeEffect, typingSpeed);
    }
  }
}

typeEffect();
// Function to geolocate IP and add marker
function geolocateIP(ip, count) {
    fetch(`https://ipapi.co/${ip}/json/`)
        .then(response => response.json())
        .then(data => {
            if (data.latitude && data.longitude) {
                const color = getActivityColor(count);
                
                // Create custom icon
                const icon = L.divIcon({
                    className: 'custom-marker',
                    html: `<div style="background-color: ${color}; width: 15px; height: 15px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 5px rgba(0,0,0,0.5);"></div>`,
                    iconSize: [15, 15],
                    iconAnchor: [7, 7]
                });
                
                // Add marker to map
                const marker = L.marker([data.latitude, data.longitude], {icon: icon}).addTo(ipMap);
                
                // Add popup with information
                marker.bindPopup(`
                    <strong>IP:</strong> ${ip}<br>
                    <strong>Location:</strong> ${data.city || 'Unknown'}, ${data.country_name || 'Unknown'}<br>
                    <strong>Clicks:</strong> ${count}<br>
                    <strong>ISP:</strong> ${data.org || 'Unknown'}
                `);
            }
        })
        .catch(error => console.error('Error geolocating IP:', ip, error));
}

// Add markers for each IP (limit to top 20 for performance)
const ipEntries = Object.entries(ipAddresses);
const topIPs = ipEntries.sort((a, b) => b[1] - a[1]).slice(0, 20);

topIPs.forEach(([ip, count]) => {
    geolocateIP(ip, count);
});

// Update map for dark/light mode


// Add map update to mode toggle
modeToggle.addEventListener('click', () => {
    setTimeout(updateMapForMode, 100);
});

// Initialize map with correct theme
setTimeout(updateMapForMode, 500);
    </script>
</body>
</html>