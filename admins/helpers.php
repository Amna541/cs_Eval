<?php
// Calculate awareness score based on clicks
function calculateAwarenessScore($totalClicks, $totalLinks) {
    if ($totalLinks == 0) return 100; // No links clicked = 100% awareness
    $score = (($totalLinks - $totalClicks) / $totalLinks) * 100;
    return round($score, 2);
}

// Get severity from numeric score
function getSeverityLevel($score) {
    if ($score >= 8) return "High";
    if ($score >= 4) return "Medium";
    return "Low";
}

// Map score to risk level
function getRiskLevel($awarenessScore) {
    if ($awarenessScore >= 80) return "Safe";
    if ($awarenessScore >= 50) return "At Risk";
    return "High Risk";
}
?>
