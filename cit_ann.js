document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM fully loaded and parsed.');
    fetchAnnouncements();
});

function fetchAnnouncements() {
    console.log('Fetching announcements...');
    fetch('announcements.json')
        .then(response => response.json())
        .then(data => {
            console.log('Announcements data fetched:', data);
            populateAnnouncements(data);
        })
        .catch(error => {
            console.error('Error fetching announcements:', error);
        });
}

function populateAnnouncements(announcements) {
    console.log('Populating announcements:', announcements);
    const container = document.getElementById('announcementsContainer');
    container.innerHTML = '';  // Clear previous data

    announcements.forEach(announcement => {
        const announcementDiv = document.createElement('div');
        announcementDiv.className = 'announcement-item';
        announcementDiv.innerHTML = `<h2>Announcement ID: ${announcement.announcement_id}</h2>`;

        announcement.items
            .filter(item => item.citizen_id === null) // Only items that haven't been accepted by a citizen
            .forEach(item => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'item';
                itemDiv.innerHTML = `
                    <p>Item ID: ${item.item_id}</p>
                    <input type="number" id="quantity_${announcement.announcement_id}_${item.item_id}" placeholder="Enter quantity" min="1">
                    <button class="accept-button" data-announcement-id="${announcement.announcement_id}" data-item-id="${item.item_id}">Accept</button>
                `;
                announcementDiv.appendChild(itemDiv);
            });

        container.appendChild(announcementDiv);
    });

    // Attach event listeners
    attachEventListeners();
}

function attachEventListeners() {
    console.log('Attaching event listeners...');
    document.querySelectorAll('.accept-button').forEach(button => {
        button.addEventListener('click', (event) => {
            const announcement_id = event.target.getAttribute('data-announcement-id');
            const item_id = event.target.getAttribute('data-item-id');
            const quantityInput = document.getElementById(`quantity_${announcement_id}_${item_id}`);
            const quantity = quantityInput ? quantityInput.value : null;

            console.log('Accept button clicked:', {
                announcement_id,
                item_id,
                quantity
            });

            acceptItem(announcement_id, item_id, quantity);
        });
    });
}

function acceptItem(announcement_id, item_id, quantity) {
    const createdAt = new Date().toISOString();

    console.log('Accepting item:', {
        announcement_id,
        item_id,
        quantity,
        citizen_acceptance_date: createdAt
    });

    if (!quantity || quantity <= 0) {
        alert('Please enter a valid quantity.');
        return;
    }

    fetch('accept_ann.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            announcement_id: announcement_id,
            item_id: item_id,
            quantity: quantity,
            citizen_acceptance_date: createdAt
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Item acceptance response:', data);
        if (data.success) {
            alert('Item accepted successfully!');
            // Refresh UI immediately
            fetchAnnouncements();
        } else {
            alert('Failed to accept item: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error accepting item:', error);
    });
}
