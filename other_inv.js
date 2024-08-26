$(document).ready(function() {
    // Function to display inventory
    function displayInventory(inventory) {
        const tableBody = $('#rescuer-data-output');
        tableBody.empty(); // Clear any existing content

        inventory.forEach(item => {
            const details = item.details.map(detail => `${detail.detail_name}: ${detail.detail_value}`).join(", ");
            const row = `
                <tr>
                    <td>${item.name}</td>
                    <td>${item.category}</td>
                    <td>${details}</td>
                    <td>${item.rescuerId}</td>
                </tr>
            `;
            tableBody.append(row);
        });
    }

    // Fetch the JSON data
    $.getJSON('rescuer.json', function(data) {
        displayInventory(data.items);
    });
});
