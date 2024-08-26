document.addEventListener("DOMContentLoaded", function () {
    fetch('items.json')
        .then(response => response.json())
        .then(data => populateAllSelectors(data.items))
        .catch(error => console.error('Error fetching items:', error));
});

function populateAllSelectors(items) {
    populateSelector(document.querySelector('.itemSelector'), items);
}

function populateSelector(selector, items) {
    items.forEach(item => {
        const option = document.createElement('option');
        option.value = item.id;
        option.text = item.name;
        selector.add(option);
    });
}

function addNewSelector() {
    const container = document.getElementById('itemSelectorsContainer');
    const newSelector = document.createElement('select');
    newSelector.className = 'itemSelector';

    fetch('items.json')
        .then(response => response.json())
        .then(data => populateSelector(newSelector, data.items))
        .catch(error => console.error('Error fetching items:', error));

    container.appendChild(newSelector);
}

function sendAnnouncement() {
    const selectors = document.querySelectorAll('.itemSelector');
    const announcementItems = Array.from(selectors).map(selector => {
        return {
            item_id: selector.value,
            citizen_id: null,
            quantity: null, // Quantity set by the citizen after accepting
            citizen_acceptance_date: null,
            rescuer_acceptance_date: null,
            delivery_completion_date: null,
            rescuer_first_name: null,
            rescuer_last_name: null
        };
    });

    fetch('save_ann.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            announcement_created_at: new Date().toISOString(), // Current date-time
            items: announcementItems
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Announcement sent successfully!');
        } else {
            alert('Failed to send announcement.');
        }
    })
    .catch(error => console.error('Error sending announcement:', error));
}
