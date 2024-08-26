document.addEventListener("DOMContentLoaded", function () {
    fetch('items.json')
        .then(response => response.json())
        .then(data => {
            populateCategories(data.items);
        })
        .catch(error => console.error('Error fetching items:', error));
});

function populateCategories(items) {
    const categorySelector = document.getElementById('categorySelector');
    const categories = [...new Set(items.map(item => item.category))];

    categories.forEach(category => {
        const option = document.createElement('option');
        option.value = category;
        option.textContent = category;
        categorySelector.appendChild(option);
    });

    // Populate items for the initial category
    populateItems();
}

function populateItems() {
    const categorySelector = document.getElementById('categorySelector');
    const selectedCategory = categorySelector.value;
    const itemSelector = document.getElementById('itemSelector');

    fetch('items.json')
        .then(response => response.json())
        .then(data => {
            itemSelector.innerHTML = '';

            const filteredItems = data.items.filter(item => item.category === selectedCategory);
            filteredItems.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.name;
                itemSelector.appendChild(option);
            });

            // Check if no items are available for the selected category
            if (filteredItems.length === 0) {
                const option = document.createElement('option');
                option.textContent = 'No items available';
                itemSelector.appendChild(option);
            }
        })
        .catch(error => console.error('Error fetching items:', error));
}

document.getElementById('requestForm').addEventListener('submit', function (event) {
    event.preventDefault();

    const itemId = document.getElementById('itemSelector').value;
    const peopleCount = document.getElementById('peopleCount').value;

    // Get the current date and time
    const createdAt = new Date().toISOString().slice(0, 19).replace('T', ' ');

    fetch('submit_request.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            itemId: itemId,
            peopleCount: peopleCount,
            createdAt: createdAt  // Add createdAt to the request body
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Request submitted successfully!');
        } else {
            alert('Failed to submit request: ' + data.message);
        }
    })
    .catch(error => console.error('Error submitting request:', error));
});