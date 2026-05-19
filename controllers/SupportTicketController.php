<?php
if (!defined('APP_RUNNING')) die('Direct access not allowed.');

class SupportTicketController extends Controller
{
    /**
     * GET /support_tickets
     * List all tickets with status filter.
     */
    public function index()
    {
        Auth::requireRole('admin');

        $status = trim($_GET['status'] ?? '');

        $model = $this->model('SupportTicketModel');
        $tickets = $model->getAll($status);
        $counts  = $model->countByStatus();

        $this->view('support_tickets/index', [
            'pageTitle' => 'Support Tickets',
            'tickets'   => $tickets,
            'counts'    => $counts,
            'status'    => $status
        ]);
    }

    /**
     * GET  /support_tickets/details/{id}   → show form
     * POST /support_tickets/details/{id}   → save admin notes
     */
    public function details($id = null)
    {
        Auth::requireRole('admin');

        $id = (int)$id;
        if ($id <= 0) {
            set_flash('error', 'Invalid ticket ID.');
            redirect('support_tickets');
        }

        $model = $this->model('SupportTicketModel');
        $ticket = $model->findById($id);

        if (!$ticket) {
            set_flash('error', 'Ticket not found.');
            redirect('support_tickets');
        }

        // ---- POST: save admin notes ----
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (!verify_csrf($_POST['csrf_token'] ?? '')) {
                set_flash('error', 'Invalid form submission.');
                redirect('support_tickets/details/' . $id);
            }

            $notes = trim($_POST['admin_notes'] ?? '');

            if (strlen($notes) > 5000) {
                set_flash('error', 'Notes too long (max 5000 characters).');
                redirect('support_tickets/details/' . $id);
            }

            $model->updateNotes($id, $notes);

            ActivityLogger::log(
                'ticket_note_added',
                'Admin notes updated on ticket #' . $id
            );

            set_flash('success', 'Admin notes saved.');
            redirect('support_tickets/details/' . $id);
        }

        $this->view('support_tickets/view', [
            'pageTitle' => 'Ticket #' . $id,
            'ticket'    => $ticket
        ]);
    }
}
?>