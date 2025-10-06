<!-- Tickets Section -->
<div id="tickets-section" class="tickets-section">
    <h1>Helpdesk Tickets</h1>
    <table class="tickets-table">
        <thead>
            <tr>
                <th>Ticket #</th>
                <th>Issue</th>
                <?php if ($isAdmin): ?><th>Details</th><?php endif; ?>
                <th>Status</th>
                <th>Personnel Assigned</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $tickets->fetch_assoc()): ?>
                <tr class="ticket-row">
                    <td><?= htmlspecialchars($row['ticket_number']) ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <?php if ($isAdmin): ?>
                        <td><?= nl2br(htmlspecialchars($row['description'])) ?></td>
                    <?php endif; ?>
                    <td><span class="status <?= strtolower($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                    <td><?= nl2br(htmlspecialchars($row['assigned_to'])) ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td class="actions">
                        <?php if ($isAdmin): ?>
                            <a href="#" class="btn btn-edit" onclick='openEditModal(
                                <?= json_encode($row["id"]) ?>,
                                <?= json_encode($row['ticket_number']) ?>,
                                <?= json_encode($row['title']) ?>,
                                <?= json_encode($row['description']) ?>,
                                <?= json_encode($row['status']) ?>,
                                <?= json_encode($row['assigned_to']) ?>
                            ); return false;'>Edit</a>
                        <a href="includes/delete_ticket.php?id=<?= $row['id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this ticket?')">Delete</a>
                        <?php else: ?>
                            <span class="view-only">View Only</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<style>
/* Tickets Section */
.tickets-section {
    margin: 30px auto;
    max-width: 1200px;
    padding: 20px;
    background-color: #f7f8fa;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.tickets-section h1 {
    margin-bottom: 20px;
    font-size: 28px;
    color: #333;
    text-align: center;
}

/* Table Styling */
.tickets-table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
}

.tickets-table th, 
.tickets-table td {
    padding: 12px 15px;
    text-align: left;
}

.tickets-table thead {
    background-color: #4CAF50;
    color: #fff;
    font-weight: bold;
}

.tickets-table tbody tr {
    border-bottom: 1px solid #e0e0e0;
    transition: background 0.3s;
}

.tickets-table tbody tr:hover {
    background-color: #f1f8e9;
}

/* Status badges */
.status {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 12px;
    font-weight: bold;
    color: #000;
    text-align: center;
    font-size: 0.9em;
}

.status.open { background-color: #f39c12; }     /* Open / pending */
.status.inprogress { background-color: #f2f24b; } /* In Progress */
.status.closed { background-color: #2ecc71; }   /* Closed */
.status.cancelled { background-color: #e74c3c; } /* Cancelled */

/* Action Buttons */
.btn {
    padding: 0px 0px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.9em;
    transition: all 0.2s ease-in-out;
}

.btn-edit {
    background-color: #3498db;
    color: #fff;
    margin: 5px;
	padding: 0px;
}

.btn-edit:hover { background-color: #2980b9; }

.btn-delete {
    background-color: #e74c3c;
    color: #fff;
}

.btn-delete:hover { background-color: #c0392b; }

.view-only {
    font-style: italic;
    color: #999;
}

/* Responsive */
@media (max-width: 992px) {
    .tickets-table th, .tickets-table td {
        padding: 10px;
        font-size: 0.9em;
    }
}

@media (max-width: 768px) {
    .tickets-table thead { display: none; }
    .tickets-table, .tickets-table tbody, .tickets-table tr, .tickets-table td { display: block; width: 100%; }
    .tickets-table tr { margin-bottom: 15px; }
    .tickets-table td {
        text-align: right;
        padding-left: 50%;
        position: relative;
    }
    .tickets-table td::before {
        content: attr(data-label);
        position: absolute;
        left: 15px;
        width: 45%;
        text-align: left;
        font-weight: bold;
        color: #555;
    }
}
</style>