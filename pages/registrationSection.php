<?php
// registrationSection.php
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = md5($_POST['password']); // demo only; use password_hash in prod
    $role = $conn->real_escape_string($_POST['role']);
    $unit = $conn->real_escape_string($_POST['unit']);

    // Check if unit already exists
    $check = $conn->query("SELECT id FROM users WHERE unit='$unit'");
    if ($check->num_rows > 0) {
        $error = "A user account for this unit already exists.";
    } else {
        $sql = "INSERT INTO users (username, password, role, unit) 
                VALUES ('$username', '$password', '$role', '$unit')";
        if ($conn->query($sql)) {
            $success = "✅ User created successfully.";
        } else {
            $error = "❌ Error: " . $conn->error;
        }
    }
}
?>

<!-- Registration Section -->
<div id="registration-section" class="registration-section" style="display:none; height:90%; overflow-y:auto; padding:20px;">
    <h1 style="margin-bottom:20px;">👤 User Registration</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <input type="hidden" name="create_user" value="1">

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="staff">Staff</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div class="form-group">
                <label>Unit</label>
                <input type="text" name="unit" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Create User</button>
                <button type="reset" class="btn-secondary">Clear</button>
            </div>
        </form>
    </div>
</div>
