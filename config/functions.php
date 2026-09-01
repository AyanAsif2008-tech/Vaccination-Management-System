<?php
/**
 * Shared helper functions
 */

function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header("Location: $path");
    exit;
}

function calculate_age($dob) {
    if (!$dob) return '-';
    $birth = new DateTime($dob);
    $today = new DateTime('today');
    $diff  = $birth->diff($today);

    if ($diff->y >= 1) {
        return $diff->y . ' yr' . ($diff->y > 1 ? 's' : '');
    } elseif ($diff->m >= 1) {
        return $diff->m . ' mo' . ($diff->m > 1 ? 's' : '');
    } else {
        return $diff->d . ' day' . ($diff->d != 1 ? 's' : '');
    }
}

function format_date($date) {
    if (!$date) return '—';
    return date('d M Y', strtotime($date));
}

function status_badge($status) {
    $status = $status ?? 'Pending';
    $map = [
        'Pending'              => 'badge-pending',
        'Approved'             => 'badge-approved',
        'Rejected'             => 'badge-rejected',
        'Vaccinated'           => 'badge-completed',
        'Completed'            => 'badge-completed',
        'Not Vaccinated'       => 'badge-rejected',
        'Available'            => 'badge-approved',
        'Out of Stock'         => 'badge-rejected',
        'Limited'              => 'badge-pending',
    ];
    $class = $map[$status] ?? 'badge-pending';
    return '<span class="status-badge ' . $class . '">' . e($status) . '</span>';
}

function flash_set($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function verify_admin_password($input, $stored) {
    // Supports both properly hashed passwords (bcrypt/argon2) and legacy
    // plain-text rows like the sample data shipped in vaccination_system.sql
    $info = password_get_info($stored);
    if (!empty($info['algoName']) && $info['algoName'] !== 'unknown') {
        return password_verify($input, $stored);
    }
    return hash_equals($stored, $input);
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check() {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        die('Invalid request (CSRF check failed). Please go back and try again.');
    }
}

function next_dose_hint($age_group) {
    return $age_group ?: 'All ages';
}
