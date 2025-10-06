<?php
/* ===========================
   INSERT / UPDATE / FETCH LOGIC
   =========================== */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';

$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'staff';
$username = $_SESSION['username'] ?? '';

/* ---------------------------
   1. NEW TICKET (Staff)
   --------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_ticket'])) {
    $title        = $conn->real_escape_string($_POST['title']);
    $concern_type = $conn->real_escape_string($_POST['concern_type']);
    $description  = $conn->real_escape_string($_POST['description']);

    $user_id   = $_SESSION['user_id'];   // numeric user ID
    $username  = $_SESSION['username'];  // for ticket_number

    // Date parts for ticket number
    $datePart = date("mdy");   // mmddyy
    $today    = date("Y-m-d");

    // Count tickets today by this user
    $res = $conn->query("SELECT COUNT(*) as cnt 
                         FROM tickets 
                         WHERE DATE(created_at) = '$today' 
                           AND created_by = '$user_id'");
    $row = $res->fetch_assoc();
    $seq = str_pad($row['cnt'] + 1, 3, "0", STR_PAD_LEFT);

    // Ticket number: username-mmddyy-### 
    $ticket_number = $username . "-" . $datePart . "-" . $seq;

    // Insert ticket
    $stmt = $conn->prepare("
        INSERT INTO tickets (ticket_number, title, concern_type, description, status, created_by, created_at)
        VALUES (?, ?, ?, ?, 'Open', ?, NOW())
    ");
    $stmt->bind_param("ssssi", $ticket_number, $title, $concern_type, $description, $user_id);

    if ($stmt->execute()) {
        $stmt->close();

        // ✅ DepEd Google Account creation/reset
        if ($concern_type === "Deped Account Creation" || $concern_type === "Deped Account Reset Password") {
            $school_id   = $conn->real_escape_string($_POST['school_id']);
            $first_name  = $conn->real_escape_string($_POST['first_name']);
            $last_name   = $conn->real_escape_string($_POST['last_name']);
            $designation = $conn->real_escape_string($_POST['designation']);
            $alt_email   = isset($_POST['alt_email']) ? $conn->real_escape_string($_POST['alt_email']) : '';
            $deped_email = $conn->real_escape_string($_POST['deped_email']);
            $new_reset   = $conn->real_escape_string($_POST['new_reset']); // "New Account" or "Reset Account"
            $contact_num = isset($_POST['contact_num']) ? $conn->real_escape_string($_POST['contact_num']) : '';

            $stmt2 = $conn->prepare("
                INSERT INTO google_accounts (ticket_number, school_id, first_name, last_name, designation, alt_email, deped_email, new_reset, contact_num) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt2->bind_param("sssssssss", $ticket_number, $school_id, $first_name, $last_name, $designation, $alt_email, $deped_email, $new_reset, $contact_num);
            if (!$stmt2->execute()) {
                error_log("Google account insert failed: " . $stmt2->error);
            }
            $stmt2->close();
        }

        // Redirect with popup ticket number
        header("Location: index.php?msg=TicketCreated&ticket_number=" . urlencode($ticket_number));
        exit;
    } else {
        die("Error inserting ticket: " . $stmt->error);
    }
}

/* ---------------------------
   2. UPDATE TICKET (Admin)
   --------------------------- */
if (isset($_POST['update_ticket']) && $role === 'admin') {
    $id = isset($_POST['ticket_id']) ? intval($_POST['ticket_id']) : 0;
    $status = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : '';
    $assigned_to = isset($_POST['assigned_to']) ? $conn->real_escape_string($_POST['assigned_to']) : '';

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE tickets SET status=?, assigned_to=? WHERE id=?");
        if (!$stmt) { die("Prepare failed: " . $conn->error); }
        $stmt->bind_param("ssi", $status, $assigned_to, $id);
        $stmt->execute();
        $stmt->close();

        header("Location: ../index.php?msg=TicketUpdated");
        exit;
    } else {
        die("Invalid ticket ID.");
    }
}
/* ---------------------------
   3. STAFF: Request Borrow
   --------------------------- */
if (isset($_POST['request_borrow'])) {
    $equipmentIds = $_POST['equipment_ids'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];

    foreach ($equipmentIds as $equipment_id) {
        $equipment_id = intval($equipment_id);
        $quantity = intval($quantities[$equipment_id] ?? 1);

        // Get total quantity
        $stmt = $conn->prepare("SELECT quantity FROM equipment WHERE id=?");
        $stmt->bind_param("i", $equipment_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$res) continue; // skip invalid equipment

        $totalQty = $res['quantity'];

        // Check overlapping approved requests
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(quantity),0) as borrowed
            FROM borrow_requests
            WHERE equipment_id = ?
              AND status = 'Approved'
              AND NOT (end_date < ? OR start_date > ?)
        ");
        $stmt->bind_param("iss", $equipment_id, $start, $end);
        $stmt->execute();
        $overlap = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $borrowed = $overlap['borrowed'];
        $remaining = $totalQty - $borrowed;

        if ($quantity > 0 && $quantity <= $remaining) {
            // Insert borrow request
            $stmt = $conn->prepare("
                INSERT INTO borrow_requests (user_id, equipment_id, quantity, start_date, end_date, status)
                VALUES (?, ?, ?, ?, ?, 'Pending')
            ");
            $stmt->bind_param("iiiss", $user_id, $equipment_id, $quantity, $start, $end);
            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: index.php?msg=BorrowRequested&section=equipment");
    exit;
}

/* ---------------------------
   4. ADMIN: Approve / Reject / Return
   --------------------------- */
if ($role === 'admin') {
    if (isset($_POST['approve_borrow'])) {
        $id = intval($_POST['request_id']);
        $stmt = $conn->prepare("UPDATE borrow_requests SET status='Approved' WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        header("Location: index.php?msg=BorrowApproved&section=equipment");
        exit;
    }

    if (isset($_POST['reject_borrow'])) {
        $id = intval($_POST['request_id']);
        $stmt = $conn->prepare("UPDATE borrow_requests SET status='Rejected' WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        header("Location: index.php?msg=BorrowRejected&section=equipment");
        exit;
    }

    if (isset($_POST['return_borrow'])) {
        $id = intval($_POST['request_id']);
        $stmt = $conn->prepare("UPDATE borrow_requests SET status='Returned' WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        header("Location: index.php?msg=BorrowReturned&section=equipment");
        exit;
    }
}

/* ---------------------------
   5. ADMIN: Add Equipment
   --------------------------- */
if (isset($_POST['add_equipment']) && $role === 'admin') {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $quantity = intval($_POST['quantity']);

    $stmt = $conn->prepare("INSERT INTO equipment (name, description, quantity) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $name, $description, $quantity);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?msg=EquipmentAdded&section=equipment");
    exit;
}

/* ---------------------------
   6. FETCH DATA
   --------------------------- */
$tickets = $conn->query("SELECT * FROM tickets ORDER BY created_at DESC");
$equipmentData = $conn->query("SELECT * FROM equipment ORDER BY name ASC");

?>
