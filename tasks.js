function updateTask(action, id, type) {
    let quantity = 0;

    if (action === 'complete' && type === 'request') {
        quantity = prompt("Please enter the quantity to complete:");
        if (quantity === null) {
            return; // User cancelled the prompt
        }
        quantity = parseInt(quantity);
        if (isNaN(quantity) || quantity <= 0) {
            alert('Please enter a valid quantity.');
            return;
        }
    }

    checkDistance(id, type)
    .then(distance => {
        if (distance <= 50) {
            return fetch('complete_task.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: id, type: type, quantity: quantity })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Task completed successfully!');
                    location.reload();
                } else {
                    alert('Failed to complete task: ' + data.message);
                }
            });
        } else {
            alert('The rescuer is too far from the citizen to complete the task.');
        }
    })
    .catch(error => console.error('Error:', error));
}

function handleAnnouncementCompletion(id) {
    checkDistance(id, 'announcement')
    .then(distance => {
        if (distance <= 50) {
            return fetch('complete_task.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: id, type: 'announcement', quantity: 0 })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Announcement completed successfully!');
                    location.reload();
                } else {
                    alert('Failed to complete announcement: ' + data.message);
                }
            });
        } else {
            alert('The rescuer is too far from the citizen to complete the announcement.');
        }
    })
    .catch(error => console.error('Error:', error));
}

function checkDistance(id, type) {
    return fetch('distance_task.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: id, type: type })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            return data.distance;
        } else {
            throw new Error(data.message);
        }
    });
}

function cancelTask(action,id, type) {
            return fetch('cancel_task.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: id, type: type })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Task cancelled successfully!');
                    location.reload();
                } else {
                    alert('Failed to cancel task: ' + data.message);
                }
            });
}