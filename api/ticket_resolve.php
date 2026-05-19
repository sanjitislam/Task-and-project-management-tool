<?php
define('APP_RUNNING', true);
session_start();

if (!defined('BASE_URL')) {
    define('BASE_URL', '/task_management/');
}

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/ActivityLogger.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../models/SupportTicketModel.php';

header('Content-Type: application/json');

if (!Auth::check() || Auth::role() !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!verify_csrf($input['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$id = (int)($input['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ticket ID']);
    exit;
}

try {
    $model = new SupportTicketModel();
    $ticket = $model->findById($id);

    if (!$ticket) {
        echo json_encode(['success' => false, 'message' => 'Ticket not found']);
        exit;
    }

    // Flip the status
    $newStatus = $ticket['status'] === 'open' ? 'resolved' : 'open';
    $model->setStatus($id, $newStatus);

    ActivityLogger::log(
        $newStatus === 'resolved' ? 'ticket_resolved' : 'ticket_reopened',
        'Ticket #' . $id . ' ' . ($newStatus === 'resolved' ? 'resolved' : 'reopened')
    );

    echo json_encode([
        'success'    => true,
        'new_status' => $newStatus,
        'message'    => 'Ticket ' . ($newStatus === 'resolved' ? 'resolved' : 'reopened')
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>