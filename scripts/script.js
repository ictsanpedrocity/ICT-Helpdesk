function openEditModal(id, ticket_number, title, description, status, assigned_to) {
    document.getElementById("edit-id").value = id;
    document.getElementById("edit-ticket-number").value = ticket_number;
    document.getElementById("edit-title").value = title;
    document.getElementById("edit-description").value = description;
    document.getElementById("edit-status").value = status;
    document.getElementById("edit-assigned_to").value = assigned_to;

    openModal("editModal");
}
//document.getElementById("editTicketForm").addEventListener("submit", function() {
//    closeModal("editModal"); // close modal right away
//});
		function toggleSidebar() {
    document.querySelector(".sidebar").classList.toggle("collapsed");
}

