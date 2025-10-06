	function openModal(id) {
    console.log("Opening modal:", id);
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add("active");
    } else {
        console.warn("Modal not found:", id);
    }
}
function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove("active");
    }
}
function showSection(section) {
    // Hide all first
    document.getElementById('new-ticket').style.display = 'none';
    document.getElementById('tickets-section').style.display = 'none';
    document.getElementById('equipment-section').style.display = 'none';
    
    const regSection = document.getElementById('registration-section');
    if (regSection) regSection.style.display = 'none';

    const equipAdminSection = document.getElementById('equipment-admin-section');
    if (equipAdminSection) equipAdminSection.style.display = 'none';

    // Show the requested one
    if (section === 'tickets') {
        document.getElementById('tickets-section').style.display = 'block';
    } else if (section === 'new-ticket') {
        document.getElementById('new-ticket').style.display = 'block';
    } else if (section === 'equipment') {
        document.getElementById('equipment-section').style.display = 'block';
    } else if (section === 'register') {
        if (regSection) regSection.style.display = 'block';
    } else if (section === 'equipment-admin-section') {
        if (equipAdminSection) equipAdminSection.style.display = 'block';
    }
    // Save last active section
    localStorage.setItem('activeSection', section);
}

window.onload = function () {
    // ✅ Restore last active section
    const lastSection = localStorage.getItem('activeSection') || 'tickets';
    showSection(lastSection);

    // ✅ Fetch concern types
    fetch('get_concern.php')
        .then(response => response.json())
        .then(data => {
            const concernSelect = document.getElementById("concern_type");
            data.forEach(concern => {
                let option = document.createElement("option");
                option.value = concern.concern;
                option.textContent = concern.concern;
                concernSelect.appendChild(option);
            });
        });
};

document.getElementById("concern_type").addEventListener("change", function() {
    let depedFields = document.getElementById("depedFields");
    let altEmailField = document.getElementById("altEmailField");
    let depedemail = document.getElementById("depedemail");
    let contactField = document.getElementById("contactField");
    let newResetField = document.getElementById("newResetField");
	
    let title = document.getElementById("title");
    let description = document.getElementById("description");

    if (this.value === "Deped Account Creation") {
        depedFields.style.display = "block";
        altEmailField.style.display = "block";
        contactField.style.display = "block";
        depedemail.style.display = "none";
        newResetField.value = "New Account";
        title.style.display = "none";
        description.style.display = "none";
    } 
    else if (this.value === "Deped Account Reset Password") {
        depedFields.style.display = "block";
        altEmailField.style.display = "none";
        contactField.style.display = "none";
        depedemail.style.display = "block";
        newResetField.value = "Reset Account";
        title.style.display = "none";
        description.style.display = "none";
    } 
    else {
        depedFields.style.display = "none";
        altEmailField.style.display = "none";
        contactField.style.display = "none";
        title.style.display = "block";
        description.style.display = "block";
        newResetField.value = "";
    }
});
