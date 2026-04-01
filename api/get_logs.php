<?php
date_default_timezone_set('Asia/Manila');
require_once 'database/db_connection.php';
header('Content-Type: application/json');

$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$user_type_filter = isset($_GET['user_type']) ? $_GET['user_type'] : '';
$last_time = isset($_GET['last_time']) ? $_GET['last_time'] : '';

$where = "1=1";
if($search) {
    $search_escaped = mysqli_real_escape_string($conn, $search);
    $where .= " AND (username LIKE '%$search_escaped%' OR action LIKE '%$search_escaped%' OR description LIKE '%$search_escaped%')";
}
if($filter) {
    $filter_escaped = mysqli_real_escape_string($conn, $filter);
    $where .= " AND action = '$filter_escaped'";
}
if($user_type_filter) {
    $user_type_escaped = mysqli_real_escape_string($conn, $user_type_filter);
    $where .= " AND user_type = '$user_type_escaped'";
}
if($date_from) {
    $date_from_escaped = mysqli_real_escape_string($conn, $date_from);
    $where .= " AND DATE(created_at) >= '$date_from_escaped'";
}
if($date_to) {
    $date_to_escaped = mysqli_real_escape_string($conn, $date_to);
    $where .= " AND DATE(created_at) <= '$date_to_escaped'";
}
if($last_time) {
    $last_time_escaped = mysqli_real_escape_string($conn, $last_time);
    $where .= " AND created_at > '$last_time_escaped'";
}

// Get recent logs ordered by newest first
$retrieve = mysqli_query($conn, "SELECT * FROM activity_logs WHERE $where ORDER BY id DESC LIMIT 20");

$logs = [];
$max_time = $last_time;

while ($result = mysqli_fetch_assoc($retrieve)) {
    $created_at = $result['created_at'];
    if($max_time == '' || $created_at > $max_time) $max_time = $created_at;
    
    $user_type = $result['user_type'] ?? 'unknown';
    $type_badge = 'bg-secondary';
    if($user_type == 'admin') $type_badge = 'bg-danger';
    elseif($user_type == 'customer') $type_badge = 'bg-success';
    
    $badge_class = 'bg-secondary';
    if(stripos($result['action'], 'Login') !== false) $badge_class = 'bg-success';
    elseif(stripos($result['action'], 'Logout') !== false) $badge_class = 'bg-warning';
    elseif(stripos($result['action'], 'Create') !== false || stripos($result['action'], 'Add') !== false) $badge_class = 'bg-info';
    elseif(stripos($result['action'], 'Update') !== false) $badge_class = 'bg-primary';
    elseif(stripos($result['action'], 'Delete') !== false || stripos($result['action'], 'Reject') !== false) $badge_class = 'bg-danger';
    
    $logs[] = [
        'id' => $result['id'],
        'created_at' => date('M d, Y h:i:s A', strtotime($created_at)),
        'raw_time' => $created_at,
        'username' => htmlspecialchars($result['username'] ?? 'System'),
        'user_type' => ucfirst($user_type),
        'type_badge' => $type_badge,
        'action' => htmlspecialchars($result['action']),
        'badge_class' => $badge_class,
        'description' => htmlspecialchars($result['description'] ?? ''),
        'ip_address' => $result['ip_address'] ?? ''
    ];
}

echo json_encode(['logs' => $logs, 'max_time' => $max_time]);
