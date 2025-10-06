<!-- Edit Ticket Modal -->
<div id="editModal" class="modal-overlay">
    <div class="modal">
        <!-- Header -->
        <div class="modal-header">
            <h2>Edit Ticket</h2>
            <span class="close-btn" onclick="closeModal('editModal')">&times;</span>
        </div>

        <!-- Body -->
        <div class="modal-body">
<form method="POST" id="editTicketForm" action="includes/InsertUpdateFetch.php">
    <!-- Needed to trigger update in PHP -->
    <input type="hidden" name="update_ticket" value="1">
    <input type="hidden" name="ticket_id" id="edit-id">

    <div class="ticket-number">
        Ticket #: <input id="edit-ticket-number" name="ticket_number" readonly>
    </div>

    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

    <div class="form-grid">
        <div>
            <label for="edit-title">Title</label>
            <input type="text" name="title" id="edit-title" readonly>
        </div>
        <div>
            <label for="edit-description">Description</label>
            <textarea name="description" id="edit-description" readonly></textarea>
        </div>
    </div>

    <?php if ($isAdmin): ?>
        <div class="form-grid">
            <div>
                <label for="edit-status">Status</label>
                <select name="status" id="edit-status">
                    <option>Open</option>
                    <option>In Progress</option>
                    <option>Closed</option>
                </select>
            </div>
            <div>
                <label for="edit-assigned_to">Assign To</label>
                <select name="assigned_to" id="edit-assigned_to">
                    <option value="">-- Select Staff --</option>
                    <?php foreach ($personnel as $person): ?>
                        <option value="<?= htmlspecialchars($person) ?>"><?= htmlspecialchars($person) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    <?php endif; ?>
</form>

        </div>

        <!-- Footer -->
        <div class="modal-footer">
            <button type="submit" form="editTicketForm">Update</button>
            <button type="button" class="btn-back" onclick="closeModal('editModal')">Cancel</button>
        </div>
    </div>
</div>