document.addEventListener("DOMContentLoaded", function() {
    // Highlight the active page link (your existing code)
    const currentPath = window.location.pathname;
    const sidebarLinks = document.querySelectorAll('.sidebar a');

    sidebarLinks.forEach(link => {
        if (link.getAttribute('href') === currentPath.substring(currentPath.lastIndexOf('/'))) {
            link.classList.add('active');
        }
    });

    // Update the notification icon for the "Opened Tickets" link
    const notificationIcon = document.getElementById('notification-icon');

    if (openedTicketsCount > 0) {
        notificationIcon.textContent = openedTicketsCount; // Set the count
        notificationIcon.style.display = 'inline-block'; // Show the icon
    }
});

//for the success pop up
function remove_feedback() {
    const successMessage = document.getElementById('success-message');
    if (successMessage) {
        successMessage.style.display = 'none';
    }
}

setTimeout(function() {
    document.getElementById('success-message').style.display = 'none';
}, 10000); // Hide after 5 seconds

setTimeout(function() {
    let successMessage = document.getElementById('success-message');
    if (successMessage) {
        successMessage.style.display = 'none';
    }
}, 5000);

// Hide the modal on page load
window.onload = function() {
    document.getElementById('confirmModal').style.display = 'none';
};

// Function to show the modal when the cancel button is clicked
function showConfirmModal() {
    // Show the modal
    document.getElementById('confirmModal').style.display = 'flex';
}

// Function to handle the 'Yes' or 'No' button in the modal
function confirmCancel(isConfirmed) {
    // Hide the modal
    document.getElementById('confirmModal').style.display = 'none';

    // If user clicked 'Yes'
    if (isConfirmed) {
        // Reset the form or take any other action
        document.querySelector('.requisition-form').reset();
    }
}