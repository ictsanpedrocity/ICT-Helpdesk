<?php
require_once __DIR__ . '/includes/init.php';
$defaultSection = $_GET['section'] ?? 'tickets'; // default Dashboard
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="ICTLogo.png">
    <title>ICT Helpdesk Dashboard</title>
    <!-- Styles -->
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/modalStyle.css">
    <link rel="stylesheet" href="styles/ticket.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Scripts -->
    <script src="scripts/script.js" defer></script>
    <script src="scripts/ticket.js" defer></script>
</head>
<body>
<?php if (isset($_GET['msg']) && $_GET['msg'] === 'TicketCreated' && isset($_GET['ticket_number'])): ?>
<div id="ticketModal" style="
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center;
">
  <div style="background: #fff; padding: 20px; border-radius: 10px; text-align: center; width: 300px;">
    <h3>Ticket Created</h3>
    <p>Your Ticket Number:</p>
    <strong><?php echo htmlspecialchars($_GET['ticket_number']); ?></strong>
    <br><br>
    <button onclick="closeTicketModal()">OK</button>
  </div>
</div>

<script>
function closeTicketModal() {
    document.getElementById('ticketModal').style.display = 'none';
    // Remove msg and ticket_number from URL without reloading
    const url = new URL(window.location);
    url.searchParams.delete('msg');
    url.searchParams.delete('ticket_number');
    window.history.replaceState({}, document.title, url);
}
</script>
<?php endif; ?>
	<!-- Sidebar -->
	<div class="sidebar">
<div class="logo">
    <img src="ICTLogo.png" alt="ICT Logo" style="height:40px;"> <!-- adjust height as needed -->
    <div class="user-info" style="font-size:10px;">
        <?= htmlspecialchars($_SESSION['username']) ?>
    </div>
</div>
		<ul class="menu">
			<li><a href="#" onclick="showSection('tickets'); return false;"><i class="fas fa-home"></i><span class="text">Dashboard</span></a></li>
			<li><a href="#" onclick="showSection('new-ticket'); return false;"><i class="fas fa-plus-circle"></i><span class="text">New Ticket</span></a></li>
			<li><a href="#" onclick="showSection('equipment'); return false;">
				<i class="fas <?= $isAdmin ? 'fa-laptop' : 'fa-handshake' ?>"></i>
				<span class="text"><?= $isAdmin ? 'Equipment Requests' : 'Borrow Equipment' ?></span>
			</a></li>
			<?php if ($isAdmin): ?>
    <li><a href="#" onclick="showSection('equipment-admin-section'); return false;"><i class="fas fa-gear"></i><span class="text">Manage Equipment</span></a></li>
<?php endif; ?>
<?php if ($isAdmin): ?>
    <li><a href="#" onclick="showSection('register'); return false;">
        <i class="fas fa-users-cog"></i><span class="text">Register Users</span>
    </a></li>
<?php endif; ?>
			<li><a href="logout.php"><i class="fas fa-sign-out-alt"></i><span class="text">Logout</span></a></li>
		</ul>
		<div class="toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></div>
	</div>
	<!-- Edit Ticket Modal -->
	<?php include 'pages/editTicketModal.php'; ?>
	<!-- Main Content -->
	<div class="main" style="height:530px; overflow-y: auto;">
		<!-- List of Tickets Section -->
		<?php include 'pages/ticketsTable.php'; ?>
		<!-- New Ticket Section -->
		<?php include 'pages/newTicketForm.php'; ?>
		<!-- Registration Section -->
		<?php if ($isAdmin) include 'pages/registrationSection.php'; ?>
		<!-- Equipment Section -->
		<?php include 'pages/equipmentSection.php'; ?>
		<!-- Equipment Admin Section -->
		<?php if ($isAdmin) include 'pages/equipment_admin.php'; ?>
	</div>
</body>
</html>
