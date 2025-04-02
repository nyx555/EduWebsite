<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mailer.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();

// Handle status update
if (isset($_POST['lead_id']) && isset($_POST['action'])) {
    $lead_id = (int)$_POST['lead_id'];
    $action = $db->escape($_POST['action']);
    $notes = $db->escape($_POST['notes'] ?? '');
    
    switch ($action) {
        case 'contact':
            $db->query("UPDATE leads SET status = 'contacted', notes = '$notes' WHERE id = $lead_id");
            break;
        case 'enroll':
            // Get lead details
            $result = $db->query("SELECT * FROM leads WHERE id = $lead_id");
            if ($result && $result->num_rows > 0) {
                $lead = $result->fetch_assoc();
                
                // Update status
                $db->query("UPDATE leads SET status = 'enrolled', notes = '$notes' WHERE id = $lead_id");
                
                // Send enrollment confirmation email
                $mail = new PHPMailer(true);
                try {
                    error_log("Attempting to send email to: " . $lead['email']);
                    
                    $mail->isSMTP();
                    $mail->Host = SMTP_HOST;
                    $mail->SMTPAuth = true;
                    $mail->Username = SMTP_USERNAME;
                    $mail->Password = SMTP_PASSWORD;
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = SMTP_PORT;
                    $mail->CharSet = 'UTF-8';

                    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
                    $mail->addAddress($lead['email'], $lead['name']);

                    $mail->isHTML(true);
                    $mail->Subject = 'Welcome to CodeMaster Academy!';
                    
                    // Email template
                    $emailBody = "
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                            <h2 style='color: #333;'>Welcome to CodeMaster Academy!</h2>
                            <p>Dear {$lead['name']},</p>
                            <p>We are excited to confirm your enrollment in our {$lead['course_interest']} course!</p>
                            <p>Your application has been approved, and we're looking forward to having you join our learning community.</p>
                            <p>Here are your next steps:</p>
                            <ol>
                                <li>Complete your registration by visiting our student portal</li>
                                <li>Review the course syllabus and materials</li>
                                <li>Join our student community</li>
                            </ol>
                            <p>If you have any questions, please don't hesitate to contact our support team.</p>
                            <p>Best regards,<br>The CodeMaster Academy Team</p>
                        </div>
                    ";
                    
                    $mail->Body = $emailBody;
                    $mail->AltBody = strip_tags($emailBody);
                    
                    $mail->send();
                    error_log("Email sent successfully to: " . $lead['email']);
                } catch (Exception $e) {
                    error_log("Failed to send enrollment email: " . $mail->ErrorInfo);
                    error_log("SMTP Debug Info: " . $mail->Debugoutput);
                }
            } else {
                error_log("Lead not found with ID: $lead_id");
            }
            break;
        case 'reject':
            $db->query("UPDATE leads SET status = 'not_interested', notes = '$notes' WHERE id = $lead_id");
            break;
    }
}

// Get leads with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$status_filter = isset($_GET['status']) ? $db->escape($_GET['status']) : '';
$where_clause = $status_filter ? "WHERE status = '$status_filter'" : '';

$total_leads = $db->query("SELECT COUNT(*) as count FROM leads $where_clause")->fetch_assoc()['count'];
$total_pages = ceil($total_leads / $per_page);

$leads = $db->query("SELECT * FROM leads $where_clause ORDER BY created_at DESC LIMIT $offset, $per_page");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Form Submissions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        tr:hover {
            background-color: #f8f9fa;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .status-new { background: #e3f2fd; color: #1976d2; }
        .status-contacted { background: #fff3e0; color: #f57c00; }
        .status-enrolled { background: #e8f5e9; color: #388e3c; }
        .status-not_interested { background: #ffebee; color: #d32f2f; }
        .pagination {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .pagination a {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }
        .pagination a:hover {
            background-color: #f8f9fa;
        }
        .pagination .active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .logout-btn {
            padding: 8px 16px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .logout-btn:hover {
            background-color: #c82333;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        .action-btn {
            padding: 4px 8px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.875rem;
            color: white;
        }
        .contact-btn { background-color: #ffa000; }
        .enroll-btn { background-color: #4caf50; }
        .reject-btn { background-color: #f44336; }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 100%;
            max-width: 500px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .modal-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
        }
        .submit-btn { background-color: #4caf50; }
        .cancel-btn { background-color: #9e9e9e; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Form Submissions</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <div class="filters">
            <a href="?status=" class="btn <?php echo !$status_filter ? 'active' : ''; ?>">All</a>
            <a href="?status=new" class="btn <?php echo $status_filter === 'new' ? 'active' : ''; ?>">New</a>
            <a href="?status=contacted" class="btn <?php echo $status_filter === 'contacted' ? 'active' : ''; ?>">Contacted</a>
            <a href="?status=enrolled" class="btn <?php echo $status_filter === 'enrolled' ? 'active' : ''; ?>">Enrolled</a>
            <a href="?status=not_interested" class="btn <?php echo $status_filter === 'not_interested' ? 'active' : ''; ?>">Not Interested</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Course Interest</th>
                    <th>Status</th>
                    <th>Submission Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($lead = $leads->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($lead['id']); ?></td>
                        <td><?php echo htmlspecialchars($lead['name']); ?></td>
                        <td><?php echo htmlspecialchars($lead['email']); ?></td>
                        <td><?php echo htmlspecialchars($lead['course_interest']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $lead['status']; ?>">
                                <?php echo ucfirst($lead['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y g:i A', strtotime($lead['created_at'])); ?></td>
                        <td>
                            <?php if ($lead['status'] === 'new'): ?>
                                <div class="action-buttons">
                                    <button class="action-btn contact-btn" onclick="openActionModal(<?php echo $lead['id']; ?>, 'contact')">
                                        Contact
                                    </button>
                                    <button class="action-btn enroll-btn" onclick="openActionModal(<?php echo $lead['id']; ?>, 'enroll')">
                                        Enroll
                                    </button>
                                    <button class="action-btn reject-btn" onclick="openActionModal(<?php echo $lead['id']; ?>, 'reject')">
                                        Reject
                                    </button>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>" 
                       class="<?php echo $page === $i ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Action Modal -->
    <div class="modal" id="actionModal">
        <div class="modal-content">
            <h2>Update Status</h2>
            <form method="POST" action="">
                <input type="hidden" name="lead_id" id="modalLeadId">
                <input type="hidden" name="action" id="modalAction">
                <div class="form-group">
                    <label for="notes">Notes (optional)</label>
                    <textarea name="notes" id="modalNotes" rows="4"></textarea>
                </div>
                <div class="modal-buttons">
                    <button type="submit" class="modal-btn submit-btn">Submit</button>
                    <button type="button" class="modal-btn cancel-btn" onclick="closeActionModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openActionModal(leadId, action) {
            document.getElementById('modalLeadId').value = leadId;
            document.getElementById('modalAction').value = action;
            document.getElementById('modalNotes').value = '';
            document.getElementById('actionModal').classList.add('active');
        }

        function closeActionModal() {
            document.getElementById('actionModal').classList.remove('active');
        }
    </script>
</body>
</html> 