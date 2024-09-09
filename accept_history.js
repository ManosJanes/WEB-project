document.addEventListener("DOMContentLoaded", function () {
    fetchUserId()
        .then(userId => fetch('announcements.json')
            .then(response => response.json())
            .then(data => populateAcceptedAnnouncements(data, userId))
            .catch(error => console.error('Error fetching announcements:', error))
        )
        .catch(error => console.error('Error fetching user ID:', error));
});

function fetchUserId() {
    return fetch('get_user_id.php')
        .then(response => response.json())
        .then(data => {
            if (data.user_id) {
                return data.user_id;
            } else {
                throw new Error(data.error || 'Unknown error fetching user ID');
            }
        });
}

function populateAcceptedAnnouncements(announcements, userId) {
    const container = document.getElementById('announcementsContainer');

    announcements.forEach(announcement => {
        const acceptedItems = announcement.items.filter(item => item.citizen_id === userId);

        if (acceptedItems.length > 0) {
            const announcementDiv = document.createElement('div');
            announcementDiv.className = 'announcement-item';
            announcementDiv.innerHTML = `<h2>Announcement ID: ${announcement.announcement_id}</h2>`;

            acceptedItems.forEach(item => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'item';
                
                // Προσθέτουμε όλα τα επιπλέον πεδία του item
                itemDiv.innerHTML = `
                    <p>Item ID: ${item.item_id}</p>
                    <p>Accepted By (Citizen ID): ${item.citizen_id}</p>
                    <p>Quantity: ${item.quantity || 'N/A'}</p>
                    <p>Citizen Acceptance Date: ${item.citizen_acceptance_date || 'N/A'}</p>
                    <p>Rescuer Acceptance Date: ${item.rescuer_acceptance_date || 'N/A'}</p>
                    <p>Delivery Completion Date: ${item.delivery_completion_date || 'N/A'}</p>
                    <p>Rescuer First Name: ${item.rescuer_first_name || 'N/A'}</p>
                    <p>Rescuer Last Name: ${item.rescuer_last_name || 'N/A'}</p>
                `;

                // Έλεγχος αν το rescuer_acceptance_date είναι null για εμφάνιση του κουμπιού
                if (!item.rescuer_acceptance_date) {
                    const cancelButton = document.createElement('button');
                    cancelButton.textContent = 'Cancel';
                    cancelButton.onclick = function() {
                        cancelAcceptance(announcement.announcement_id, item.item_id, cancelButton);
                    };
                    itemDiv.appendChild(cancelButton);
                }

                announcementDiv.appendChild(itemDiv);
            });

            container.appendChild(announcementDiv);
        }
    });
}



function cancelAcceptance(annId, itemId, buttonElement) {
    fetch('cancel_acceptance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ annId: annId, itemId: itemId })
    })
    .then(response => response.text()) // Use text() to avoid JSON parsing errors
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                alert('Acceptance canceled successfully!');
                buttonElement.closest('.announcement-item').remove();
            } else {
                alert('Failed to cancel acceptance: ' + data.message);
            }
        } catch (error) {
            console.error('Error parsing JSON:', error);
        }
    })
    .catch(error => console.error('Error canceling acceptance:', error));
}
