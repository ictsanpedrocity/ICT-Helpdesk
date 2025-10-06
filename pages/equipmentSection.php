<?php
$myBorrows = null; 
$userId = $_SESSION['user_id'] ?? null;

// Fetch staff borrowed requests
if ($userId) {
$stmt = $conn->prepare("
    SELECT br.id, br.status, br.quantity, br.start_date, br.end_date, 
           e.name, u.username AS borrower_name
    FROM borrow_requests br
    JOIN equipment e ON br.equipment_id = e.id
    JOIN users u ON br.user_id = u.id
    WHERE br.user_id = ?
    ORDER BY br.start_date DESC
");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $myBorrows = $stmt->get_result();
}
// Fetch approved borrows for calendar blocking (with borrower name)
$approvedBorrows = [];
$res = $conn->query("
    SELECT br.equipment_id, br.start_date, br.end_date, u.username AS borrower_name
    FROM borrow_requests br
    JOIN users u ON br.user_id = u.id
    WHERE br.status = 'Approved'
");
while ($row = $res->fetch_assoc()) {
    $approvedBorrows[$row['equipment_id']][] = [
        'start'        => $row['start_date'],
        'end'          => $row['end_date'],
        'borrower_name'=> $row['borrower_name']
    ];
}

?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div id="equipment-section" class="equipment-section" style="height:90%; overflow-y:auto; padding:20px;">

    <h1 style="text-align:center; margin-bottom:25px;">
        <?= $isAdmin ? '⚙️ Equipment Management' : '📦 Equipment Borrowing' ?>
    </h1>

    <!-- ================= STAFF BORROW FORM ================= -->
    <?php if (!$isAdmin): ?>
    <h2>Available Equipment</h2>
    <form method="POST" id="borrowForm">
        <input type="hidden" name="request_borrow" value="1">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

        <div class="equipment-container">
            <button type="button" class="dropdown-toggle">📦 Select Equipment</button>
            <ul class="styled-list dropdown-list">
                <?php foreach ($equipmentData as $row): ?>
                <li>
                    <div class="list-item-left">
                        <input type="checkbox" name="equipment_ids[]" value="<?= $row['id'] ?>" class="equipment-checkbox">
                        <strong><?= htmlspecialchars($row['name']) ?></strong>
                        <span><?= htmlspecialchars($row['description']) ?></span>
                        <em>Available: <?= $row['quantity'] ?></em>
                    </div>
                    <div class="list-item-right">
                        <label>Qty:</label>
                        <input type="number" name="quantity[<?= $row['id'] ?>]" min="1" max="<?= $row['quantity'] ?>">
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="shared-dates">
            <label><b>Select Date</b> From:</label>
            <input type="text" name="start_date" required>
            <label>To:</label>
            <input type="text" name="end_date" required>
            <button type="submit" class="btn">Request Selected</button>
        </div>
    </form>
    <?php endif; ?>

<!-- ================= STAFF: MY BORROWED EQUIPMENT ================= -->
<?php
$borrowedByDate = [];
if ($myBorrows && $myBorrows->num_rows > 0) {
    while ($b = $myBorrows->fetch_assoc()) {
        $dateKey = $b['start_date'] . ' → ' . $b['end_date'];
        $borrowedByDate[$dateKey][] = [
            'name'     => $b['name'],
            'quantity' => $b['quantity'],
            'status'   => $b['status'],
            'borrower' => $b['borrower_name'] ?? '' // coming from JOIN users.username / users.name
        ];
    }
}
?>

<?php if (!empty($borrowedByDate)): ?>
<h2>📋 My Borrowed Equipment</h2>
<table class="borrowed-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Equipment (Qty)</th>
            <th>Borrower</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($borrowedByDate as $date => $items): ?>
        <tr>
            <td><?= htmlspecialchars($date) ?></td>
            <td>
                <ul class="equipment-list">
                    <?php foreach ($items as $item): ?>
                    <li><?= htmlspecialchars($item['name']) ?> (<?= $item['quantity'] ?>)</li>
                    <?php endforeach; ?>
                </ul>
            </td>
            <td><?= htmlspecialchars($items[0]['borrower']) ?></td>
            <td><?= htmlspecialchars($items[0]['status']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>


<!-- ================= ADMIN: INVENTORY / REQUESTS ================= -->
<?php if ($isAdmin): ?>
    <h2>📦 Inventory Overview</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Equipment</th>
                <th>Description</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($equipmentData as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= $row['quantity'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>📨 Borrow Requests</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Equipment</th>
                <th>Quantity</th>
                <th>From → To</th>
                <th>Status</th>
                <th style="text-align:center;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $requests = $conn->query("
                SELECT br.*, u.username, e.name 
                FROM borrow_requests br
                JOIN users u ON br.user_id = u.id
                JOIN equipment e ON br.equipment_id = e.id
                ORDER BY br.created_at DESC
            ");
            while ($r = $requests->fetch_assoc()):
            ?>
            <tr>
                <td><?= htmlspecialchars($r['username']) ?></td>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td><?= $r['quantity'] ?></td>
                <td><?= $r['start_date'] ?> → <?= $r['end_date'] ?></td>
                <td><?= $r['status'] ?></td>
                <td style="text-align:center;">
                    <?php if ($r['status'] === 'Pending'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="approve_borrow" value="1">
                            <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                            <button type="submit" class="btn success">Approve</button>
                        </form>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="reject_borrow" value="1">
                            <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                            <button type="submit" class="btn danger">Reject</button>
                        </form>
                    <?php elseif ($r['status'] === 'Approved'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="return_borrow" value="1">
                            <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                            <button type="submit" class="btn">Returned</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php endif; ?>

</div>

<style>
/* Admin Tables */
.admin-table {
    width: 100%;
    border-collapse: collapse;
    margin: 15px 0;
    font-size: 14px;
    background: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,.05);
}
.admin-table th, 
.admin-table td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: left;
    vertical-align: middle;
}
.admin-table th {
    background: #f8f9fa;
    font-weight: bold;
}
.admin-table tr:nth-child(even) {
    background: #fafafa;
}
.btn { 
    padding: 6px 12px; 
    border: none; 
    border-radius: 6px; 
    cursor: pointer; 
    background: #007bff; 
    color: white; 
    font-size: 13px;
}
.btn.success { background: #28a745; }
.btn.danger { background: #dc3545; }
.btn:hover { opacity: 0.9; }

/* ----- Tables & Lists ----- */
.borrowed-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
.borrowed-table th, .borrowed-table td {
    border: 1px solid #ccc;
    padding: 8px 12px;
    text-align: left;
    vertical-align: top;
}
.borrowed-table th { background: #f2f2f2; }
.equipment-list { list-style: none; padding: 0; margin: 0; }
.equipment-list li { margin-bottom: 3px; }
.styled-list {
    list-style: none;
    padding: 0;
    margin: 0 0 15px 0;
}
.styled-list li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    border-bottom: 1px solid #eee;
}
.list-item-left, .list-item-right { display: flex; align-items: center; gap: 8px; }
.equipment-container { position: relative; margin-bottom: 15px; }
.dropdown-toggle {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ccc;
    border-radius: 6px;
    background: #f9f9f9;
    cursor: pointer;
    text-align: left;
}
.dropdown-list {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #ccc;
    border-radius: 6px;
    display: none;
}
.shared-dates { display: flex; align-items: center; gap: 10px; margin-top: 10px; }
.shared-dates label { margin: 0; }
.shared-dates input { padding: 4px; }
.btn { padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; background: #007bff; color: white; }
.btn.success { background: #28a745; }
.btn.danger { background: #dc3545; }
</style>
<script>
document.addEventListener("DOMContentLoaded", () => {
    // Raw data from PHP
    const equipmentBlockedRaw = <?= json_encode($approvedBorrows) ?>;

    // Equipment names map (adjust if your $equipmentData variable name differs)
    const equipmentNames = {
        <?php foreach ($equipmentData as $row): ?>
        "<?= $row['id'] ?>": "<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>",
        <?php endforeach; ?>
    };

    // Normalize blocked ranges to date-only strings (YYYY-MM-DD)
    const equipmentBlocked = {};
    Object.keys(equipmentBlockedRaw || {}).forEach(eid => {
        equipmentBlocked[eid] = (equipmentBlockedRaw[eid] || []).map(r => ({
            start: (r.start || "").substring(0,10),
            end:   (r.end   || "").substring(0,10)
        }));
    });

    // Helpers: parse YYYY-MM-DD to local Date, and format Date -> YYYY-MM-DD (local)
    function ymdToDate(ymd) {
        const [y,m,d] = (ymd || "").split("-").map(Number);
        return new Date(y, (m || 1) - 1, d || 1); // local midnight
    }
    function dateToYMD(dt) {
        const y = dt.getFullYear();
        const m = String(dt.getMonth() + 1).padStart(2, "0");
        const d = String(dt.getDate()).padStart(2, "0");
        return `${y}-${m}-${d}`;
    }

// Build a map: dateStr -> { ids: Set, names: Set, borrowers: Set }
const reservedMap = {};
Object.keys(equipmentBlocked).forEach(eid => {
    (equipmentBlockedRaw[eid] || []).forEach(r => {
        if (!r.start || !r.end) return;
        const start = ymdToDate(r.start);
        const end   = ymdToDate(r.end);
        for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
            const key = dateToYMD(d);
            if (!reservedMap[key]) reservedMap[key] = { ids: new Set(), names: new Set(), borrowers: new Set() };
            reservedMap[key].ids.add(String(eid));
            reservedMap[key].names.add(equipmentNames[eid] || `Equipment ${eid}`);
            reservedMap[key].borrowers.add(r.borrower_name || "Unknown");
        }
    });
});

    // Keep current disabled dates as a Set (strings YYYY-MM-DD)
    let disabledDatesSet = new Set();

    // Determine disabled date strings based on currently checked equipment
    function computeDisabledDatesArray() {
        const checkedIds = Array.from(document.querySelectorAll(".equipment-checkbox:checked")).map(cb => cb.value);
        if (checkedIds.length === 0) return []; // nothing selected => don't disable
        const arr = [];
        Object.keys(reservedMap).forEach(dateStr => {
            const idsSet = reservedMap[dateStr].ids;
            // Conflict rule: disable if ANY of the selected equipment is reserved on this date
            const conflict = checkedIds.some(id => idsSet.has(id));
            if (conflict) arr.push(dateStr);
        });
        return arr;
    }

    // Flatpickr onDayCreate uses this function to style and tooltip days
function makeOnDayCreate() {
    return function(dObj, dStr, fp, dayElem) {
        const key = dateToYMD(dayElem.dateObj);
        if (reservedMap[key]) {
            const names = Array.from(reservedMap[key].names).join(", ");
            const borrowers = Array.from(reservedMap[key].borrowers).join(", ");
            // tooltip
            dayElem.setAttribute("title", "Reserved: " + names + " by " + borrowers);

            // Styling
            dayElem.classList.add("reserved-day");
            dayElem.style.borderRadius = "6px";

            if (disabledDatesSet.has(key)) {
                dayElem.style.background = "#f8d7da"; // light red
                dayElem.style.color = "#000";
                dayElem.style.cursor = "not-allowed";
            } else {
                dayElem.style.background = "#fff3cd"; // light yellow
            }
        }
    };
}


    // Initialize pickers (with empty disable; we'll update after)
    const startPicker = flatpickr("input[name='start_date']", {
        dateFormat: "Y-m-d",
        locale: { firstDayOfWeek: 1 },
        disable: [],
        onDayCreate: makeOnDayCreate()
    });
    const endPicker = flatpickr("input[name='end_date']", {
        dateFormat: "Y-m-d",
        locale: { firstDayOfWeek: 1 },
        disable: [],
        onDayCreate: makeOnDayCreate()
    });

    // Update disabled dates on pickers and internal Set
    function updatePickersDisabled() {
        const arr = computeDisabledDatesArray();
        disabledDatesSet = new Set(arr);
        startPicker.set("disable", arr);
        endPicker.set("disable", arr);
        // Flatpickr will re-render days after set().
    }

    // Wire checkbox changes
    document.querySelectorAll(".equipment-checkbox").forEach(cb => {
        cb.addEventListener("change", updatePickersDisabled);
    });

    // Initialize based on any pre-checked boxes
    updatePickersDisabled();

    // Dropdown toggle (unchanged)
    const toggle = document.querySelector(".dropdown-toggle");
    const list = document.querySelector(".dropdown-list");
    if (toggle && list) {
        toggle.addEventListener("click", () => {
            list.style.display = list.style.display === "block" ? "none" : "block";
        });
    }
});
</script>




