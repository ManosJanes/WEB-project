var adminInventory = [];
var rescuerInventory = [];

// Function to get the rescuer ID
function getRescuerId() {
    return $.ajax({
        url: 'get_rescuer_id.php',
        type: 'GET'
    }).then(function (rescuerId) {
        return parseInt(rescuerId);
    }).catch(function (error) {
        console.error('Failed to fetch rescuer ID:', error);
        throw error;
    });
}

// Function to fetch admin's inventory
function fetchAdminInventory() {
    return $.ajax({
        url: 'items.json',
        type: 'GET',
        dataType: 'json'
    }).then(function (data) {
        adminInventory = data.items;
        // Ensure quantities are numbers
        adminInventory.forEach(item => {
            item.details.forEach(detail => {
                if (detail.detail_name === 'Quantity') {
                    detail.detail_value = parseInt(detail.detail_value);
                }
            });
        });
        displayAdminInventory();
    }).catch(function (error) {
        console.error('Failed to fetch admin inventory:', error);
        throw error;
    });
}

// Function to display admin's inventory
function displayAdminInventory() {
    var adminTableBody = $('#admin-data-output');
    adminTableBody.html('');

    $.each(adminInventory, function (index, item) {
        var quantity = item.details.find(detail => detail.detail_name === 'Quantity').detail_value;
        if (quantity > 0) {
            var row = adminTableBody[0].insertRow();

            $(row.insertCell(0)).html('<input type="checkbox" class="admin-checkbox" data-item-id="' + item.id + '">');
            $(row.insertCell(1)).text(item.name);
            $(row.insertCell(2)).text(item.category);
            $(row.insertCell(3)).text(quantity);
        }
    });
}

// Function to fetch rescuer inventory and display it
function fetchRescuerInventory() {
    return getRescuerId().then(function (rescuerId) {
        return $.ajax({
            url: 'rescuer.json',
            type: 'GET',
            dataType: 'json'
        }).then(function (data) {
            rescuerInventory = data.items;
            // Ensure quantities are numbers
            rescuerInventory.forEach(item => {
                item.details.forEach(detail => {
                    if (detail.detail_name === 'Quantity') {
                        detail.detail_value = parseInt(detail.detail_value);
                    }
                });
            });
            displayRescuerInventory(rescuerId);
        });
    }).catch(function (error) {
        console.error('Failed to fetch rescuer inventory:', error);
        throw error;
    });
}

// Function to display rescuer's inventory
function displayRescuerInventory(loggedInRescuerId) {
    var rescuerTableBody = $('#rescuer-data-output');
    rescuerTableBody.html('');

    rescuerInventory.forEach(function (item) {
        if (item.rescuerId === loggedInRescuerId) {
            var row = rescuerTableBody[0].insertRow();

            $(row.insertCell(0)).html('<input type="checkbox" class="rescuer-checkbox" data-item-id="' + item.id + '">');
            $(row.insertCell(1)).text(item.name);
            $(row.insertCell(2)).text(item.category);

            var quantityDetail = item.details.find(detail => detail.detail_name === 'Quantity');
            $(row.insertCell(3)).text(quantityDetail ? quantityDetail.detail_value : '');
        }
    });
}

function transferItems() {
    var selectedItems = document.querySelectorAll('.admin-checkbox:checked');
    var quantityInput = parseInt($('#quantityInput').val());

    if (isNaN(quantityInput) || quantityInput <= 0) {
        alert('Please enter a valid quantity');
        return;
    }

    var validTransfer = true;
    selectedItems.forEach(function (checkbox) {
        var itemId = checkbox.getAttribute('data-item-id');
        var item = adminInventory.find(item => item.id === itemId);

        if (item) {
            var adminQuantityDetail = item.details.find(detail => detail.detail_name === 'Quantity');
            if (adminQuantityDetail && adminQuantityDetail.detail_value < quantityInput) {
                alert(`Not enough quantity for item ${item.name}. Available: ${adminQuantityDetail.detail_value}`);
                validTransfer = false;
            }
        }
    });

    if (!validTransfer) {
        return;
    }

    getRescuerId().then(function (rescuerId) {
        selectedItems.forEach(function (checkbox) {
            var itemId = checkbox.getAttribute('data-item-id');
            var item = adminInventory.find(item => item.id === itemId);

            if (item) {
                var transferItem = rescuerInventory.find(i => i.id === item.id && i.rescuerId === rescuerId);
                if (transferItem) {
                    // Update quantity if item exists
                    var transferQuantityDetail = transferItem.details.find(detail => detail.detail_name === 'Quantity');
                    var adminQuantityDetail = item.details.find(detail => detail.detail_name === 'Quantity');
                    if (transferQuantityDetail && adminQuantityDetail) {
                        transferQuantityDetail.detail_value += quantityInput;
                        adminQuantityDetail.detail_value -= quantityInput;
                    }
                } else {
                    // Add new item if it doesn't exist
                    transferItem = {
                        id: item.id,
                        name: item.name,
                        category: item.category,
                        details: item.details.map(detail => ({ ...detail })),
                        rescuerId: rescuerId
                    };

                    var quantityDetail = transferItem.details.find(detail => detail.detail_name === 'Quantity');
                    if (quantityDetail) {
                        quantityDetail.detail_value = quantityInput;
                    } else {
                        transferItem.details.push({ detail_name: 'Quantity', detail_value: quantityInput });
                    }

                    rescuerInventory.push(transferItem);

                    // Update the quantity in the admin inventory
                    var adminQuantityDetail = item.details.find(detail => detail.detail_name === 'Quantity');
                    if (adminQuantityDetail) {
                        adminQuantityDetail.detail_value -= quantityInput;
                    }
                }
            }
        });

        // Optimistically update the UI immediately
        displayAdminInventory();
        displayRescuerInventory(rescuerId);

        // Update both admin and rescuer inventories on the server
        return updateAdminInventory(adminInventory).then(function () {
            return updateRescuerInventory(rescuerInventory, rescuerId);
        }).then(function () {
            return fetchAdminInventory(); // Refresh admin inventory display
        }).then(function () {
            return fetchRescuerInventory(); // Refresh rescuer inventory display
        });
    }).catch(function (error) {
        console.error('Error transferring items:', error);
    });
}

function sendItems() {
    var selectedItems = document.querySelectorAll('.rescuer-checkbox:checked');
    var quantityInput = parseInt($('#quantityInput').val());

    if (isNaN(quantityInput) || quantityInput <= 0) {
        alert('Please enter a valid quantity');
        return;
    }

    var validTransfer = true;
    selectedItems.forEach(function (checkbox) {
        var itemId = checkbox.getAttribute('data-item-id');
        var item = rescuerInventory.find(item => item.id === itemId);

        if (item) {
            var rescuerQuantityDetail = item.details.find(detail => detail.detail_name === 'Quantity');
            if (rescuerQuantityDetail && rescuerQuantityDetail.detail_value < quantityInput) {
                alert(`Not enough quantity for item ${item.name}. Available: ${rescuerQuantityDetail.detail_value}`);
                validTransfer = false;
            }
        }
    });

    if (!validTransfer) {
        return;
    }

    getRescuerId().then(function (rescuerId) {
        selectedItems.forEach(function (checkbox) {
            var itemId = checkbox.getAttribute('data-item-id');
            var item = rescuerInventory.find(item => item.id === itemId);

            if (item) {
                var sendItem = adminInventory.find(i => i.id === item.id);
                if (sendItem) {
                    // Update quantity if item exists
                    var sendQuantityDetail = sendItem.details.find(detail => detail.detail_name === 'Quantity');
                    var rescuerQuantityDetail = item.details.find(detail => detail.detail_name === 'Quantity');
                    if (sendQuantityDetail && rescuerQuantityDetail) {
                        sendQuantityDetail.detail_value += quantityInput;
                        rescuerQuantityDetail.detail_value -= quantityInput;
                    }
                } else {
                    // Add new item if it doesn't exist
                    sendItem = {
                        id: item.id,
                        name: item.name,
                        category: item.category,
                        details: item.details.map(detail => ({ ...detail }))
                    };

                    var quantityDetail = sendItem.details.find(detail => detail.detail_name === 'Quantity');
                    if (quantityDetail) {
                        quantityDetail.detail_value = quantityInput;
                    } else {
                        sendItem.details.push({ detail_name: 'Quantity', detail_value: quantityInput });
                    }

                    adminInventory.push(sendItem);

                    // Update the quantity in the rescuer inventory
                    var rescuerQuantityDetail = item.details.find(detail => detail.detail_name === 'Quantity');
                    if (rescuerQuantityDetail) {
                        rescuerQuantityDetail.detail_value -= quantityInput;
                    }
                }
            }
        });

        // Optimistically update the UI immediately
        displayAdminInventory();
        displayRescuerInventory(rescuerId);

        // Update both rescuer and admin inventories on the server
        return updateRescuerInventory(rescuerInventory, rescuerId).then(function () {
            return updateAdminInventory(adminInventory);
        }).then(function () {
            return fetchAdminInventory(); // Refresh admin inventory display
        }).then(function () {
            return fetchRescuerInventory(); // Refresh rescuer inventory display
        });
    }).catch(function (error) {
        console.error('Error sending items:', error);
    });
}

function updateAdminInventoryWithReceivedItems(itemsToSend) {
    return $.ajax({
        url: 'items.json',
        type: 'GET',
        dataType: 'json'
    }).then(function (data) {
        var currentInventory = data.items;

        itemsToSend.forEach(function (sendItem) {
            var existingItem = currentInventory.find(item => item.id === sendItem.id);

            if (existingItem) {
                var existingQuantityDetail = existingItem.details.find(detail => detail.detail_name === 'Quantity');
                var sendQuantityDetail = sendItem.details.find(detail => detail.detail_name === 'Quantity');

                if (existingQuantityDetail && sendQuantityDetail) {
                    existingQuantityDetail.detail_value += sendQuantityDetail.detail_value;
                }
            } else {
                currentInventory.push(sendItem);
            }
        });

        // Update items.json
        return $.ajax({
            url: 'adm_inv.php',
            type: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({ items: currentInventory })
        }).then(function (responseData) {
            console.log('Admin inventory updated successfully with received items:', responseData);
        }).catch(function (error) {
            console.error('Error updating admin inventory with received items:', error);
        });
    }).catch(function (error) {
        console.error('Failed to fetch current admin inventory:', error);
    });
}

function updateAdminInventory(updatedAdminInventory) {
    return $.ajax({
        url: 'adm_inv.php',
        type: 'POST',
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify({ items: updatedAdminInventory })
    }).then(function (responseData) {
        console.log('Admin inventory updated successfully:', responseData);
    }).catch(function (error) {
        console.error('Error updating admin inventory:', error);
    });
}

function updateRescuerInventory(updatedRescuerInventory, rescuerId) {
    return $.ajax({
        url: 'res_inv.php',
        type: 'POST',
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify({ items: updatedRescuerInventory})
    }).then(function (responseData) {
        console.log('Rescuer inventory updated successfully:', responseData);
    }).catch(function (error) {
        console.error('Error updating rescuer inventory:', error);
    });
}

var distance = null;
var maxDistance = 50; // Define a maximum allowed distance in kilometers

$(document).ready(function () {
    fetchAdminInventory();
    fetchRescuerInventory();

    // Check the distance on page load
    checkDistance();

    $('#transferButton').click(function() {
        if (distance !== null && distance <= maxDistance) {
            transferItems();
        } else {
            alert('You are too far from the admin to transfer items.');
        }
    });

    $('#sendButton').click(function() {
        if (distance !== null && distance <= maxDistance) {
            sendItems();
        } else {
            alert('You are too far from the admin to send items.');
        }
    });
});

// Function to check the distance
function checkDistance() {
    $.ajax({
        url: 'distance.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                distance = response.distance;
                console.log('Distance from admin:', distance);
            } else {
                alert(response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Failed to fetch distance:', error);
        }
    });
}
