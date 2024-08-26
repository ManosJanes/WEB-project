document.addEventListener("DOMContentLoaded", function () {
    fetch('requests.json')
        .then(response => response.json())
        .then(data => populateRequests(data))
        .catch(error => console.error('Error fetching requests:', error));
});

function populateRequests(requests) {
    const userId = document.getElementById('userId').value;
    const container = document.getElementById('requestsContainer');

    requests.forEach(request => {
        if (request.user_id == userId) {
            const requestDiv = document.createElement('div');
            requestDiv.className = 'request-item';
            requestDiv.innerHTML = `
                <h2>Request ID: ${request.request_id}</h2>
                <p>Item ID: ${request.item_id}</p>
                <p>People Count: ${request.people_count}</p>
                <p>Status: ${request.status}</p>
                <p>Accepted At: ${request.accepted_at ? request.accepted_at : 'Not accepted yet'}</p>
                <p>Completed At: ${request.completed_at ? request.completed_at : 'Not completed yet'}</p>
                ${request.status === 'Pending' ? `<button onclick="cancelRequest('${request.request_id}')">Cancel</button>` : ''}
            `;
            container.appendChild(requestDiv);
        }
    });
}

function cancelRequest(requestId) {
    fetch('cancel_request.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ request_id: requestId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Request cancelled successfully!');
            location.reload();
        } else {
            alert('Failed to cancel request: ' + data.message);
        }
    })
    .catch(error => console.error('Error cancelling request:', error));
}
