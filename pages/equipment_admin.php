<!-- Equipment Admin Section -->
<div id="equipment-admin-section" class="registration-section" style="display:none; height:100%; overflow-y:auto; padding:20px;">
    <h1 style="text-align:center; margin-bottom:25px;">⚙️ Equipment Administration</h1>

    <!-- Add Equipment Card -->
    <div style="background:#fff; padding:20px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.08); margin-bottom:25px; max-width:600px; margin:auto;">
        <h2 style="margin-top:0;">➕ Add New Equipment</h2>
        <form method="POST" style="display:flex; flex-direction:column; gap:12px;">
            <input type="hidden" name="add_equipment" value="1">

            <label><strong>Name</strong></label>
            <input type="text" name="name" required style="padding:8px; border:1px solid #ccc; border-radius:6px;">

            <label><strong>Description</strong></label>
            <textarea name="description" style="padding:8px; border:1px solid #ccc; border-radius:6px; min-height:60px;"></textarea>

            <label><strong>Quantity</strong></label>
            <input type="number" name="quantity" min="1" required style="padding:8px; border:1px solid #ccc; border-radius:6px;">

            <button type="submit" style="background:#28a745; color:#fff; padding:10px; border:none; border-radius:6px; cursor:pointer; font-weight:bold; transition:0.3s;">
                ➕ Add Equipment
            </button>
        </form>
    </div>

    <!-- Inventory Card -->
    <div style="background:#fff; padding:20px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.08); max-width:900px; margin:auto;">
        <h2 style="margin-top:0; margin-bottom:15px;">📦 Equipment Inventory</h2>
        <table style="width:100%; border-collapse:collapse; text-align:left;">
            <thead>
                <tr style="background:#f5f5f5;">
                    <th style="padding:10px; border-bottom:2px solid #ddd;">Name</th>
                    <th style="padding:10px; border-bottom:2px solid #ddd;">Description</th>
                    <th style="padding:10px; border-bottom:2px solid #ddd;">Quantity</th>
                    <th style="padding:10px; border-bottom:2px solid #ddd;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM equipment");
                while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td style="padding:10px; border-bottom:1px solid #eee;"><?= htmlspecialchars($row['name']) ?></td>
                    <td style="padding:10px; border-bottom:1px solid #eee;"><?= htmlspecialchars($row['description']) ?></td>
                    <td style="padding:10px; border-bottom:1px solid #eee;"><?= $row['quantity'] ?></td>
                    <td style="padding:10px; border-bottom:1px solid #eee;">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="delete_equipment" value="<?= $row['id'] ?>">
                            <button type="submit" onclick="return confirm('Delete this equipment?')" 
                                style="background:#dc3545; color:#fff; padding:6px 10px; border:none; border-radius:6px; cursor:pointer; font-size:0.9em;">
                                🗑️ Delete
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
