    <!-- New Ticket Section -->
<div id="new-ticket" class="new-ticket">
    <h1>Create New Ticket</h1>
    <form method="POST">
        <input type="hidden" name="create_ticket" value="1">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

        <div class="form-container">
<!-- Left column -->
<div class="form-col">
    <label for="title_input">Title</label>
    <input type="text" name="title" id="title_input">

    <label for="description_input">Details</label>
    <textarea name="description" id="description_input"></textarea>

    <label for="concern_type">Concern Type</label>
    <select id="concern_type" name="concern">
        <option value="">-- Select Concern --</option>
        <option value="Other Concern">Other Concern</option>
    </select>
</div>

            <!-- Right column -->
            <div id="depedFields" class="form-col" style="display:none;">
                <label>School ID</label>
                <input type="text" name="school_id">

                <label>First Name</label>
                <input type="text" name="first_name">

                <label>Last Name</label>
                <input type="text" name="last_name">

                <label>Designation</label>
                <input type="text" name="designation">

                <div id="altEmailField" style="display:none;">
                    <label>Alternate Email</label>
                    <input type="email" name="alt_email">
                </div>

                <div id="depedemail" style="display:none;">
                    <label>DepEd Email</label>
                    <input type="email" name="deped_email">
                </div>

                <label>New Account or Reset Password</label>
                <input type="text" id="newResetField" name="new_reset" readonly>

                <div id="contactField" style="display:none;">
                    <label>Contact Number</label>
                    <input type="text" name="contact_num" pattern="[0-9]{11}" maxlength="11">
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit">Save Ticket</button>
            <button type="button" onclick="showSection('tickets')">Cancel</button>
        </div>
    </form>
</div>
