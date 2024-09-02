document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('myChart').getContext('2d');
    let myChart = null;

    // Function to fetch JSON data
    async function fetchData(url) {
        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return await response.json();
        } catch (error) {
            console.error('Failed to fetch data:', error);
            return [];
        }
    }

    async function init() {
        // Fetch the data from the JSON files
        const requestsData = await fetchData('requests.json');
        const donationsData = await fetchData('announcements.json');

        function filterData(startDate, endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);

            // Filter requests
            const filteredRequests = requestsData.filter(request => {
                const createdDate = request.created_at ? new Date(request.created_at) : null;
                const acceptedDate = request.accepted_at ? new Date(request.accepted_at) : null;
                const completedDate = request.completed_at ? new Date(request.completed_at) : null;

                return (
                    (!createdDate || createdDate >= start && createdDate <= end) ||
                    (!acceptedDate || acceptedDate >= start && acceptedDate <= end) ||
                    (!completedDate || completedDate >= start && completedDate <= end)
                );
            });

            // Filter donations
            const filteredDonations = donationsData.flatMap(announcement => {
                return announcement.items.filter(item => {
                    const createdDate = item.citizen_acceptance_date ? new Date(item.citizen_acceptance_date) : null;
                    const completedDate = item.delivery_completion_date ? new Date(item.delivery_completion_date) : null;

                    return (
                        (!createdDate || createdDate >= start && createdDate <= end) ||
                        (!completedDate || completedDate >= start && completedDate <= end)
                    );
                });
            });

            return {
                unacceptedRequests: filteredRequests.filter(req => req.created_at && !req.accepted_at),
                completedRequests: filteredRequests.filter(req => req.completed_at && req.status === 'Completed'),
                unacceptedDonations: filteredDonations.filter(item => !item.citizen_acceptance_date),
                completedDonations: filteredDonations.filter(item => item.delivery_completion_date)
            };
        }

        function calculateMetrics(filteredData) {
            return {
                unacceptedRequestsCount: filteredData.unacceptedRequests.length,
                completedRequestsCount: filteredData.completedRequests.length,
                unacceptedDonationsCount: filteredData.unacceptedDonations.length,
                completedDonationsCount: filteredData.completedDonations.length
            };
        }

        function createChart(metrics) {
            if (myChart) {
                myChart.destroy();
            }

            myChart = new Chart(ctx, {
                type: 'bar', // Set the chart type to 'bar' by default
                data: {
                    labels: ['Unaccepted Requests', 'Completed Requests', 'Unaccepted Donations', 'Completed Donations'],
                    datasets: [{
                        data: [
                            metrics.unacceptedRequestsCount,
                            metrics.completedRequestsCount,
                            metrics.unacceptedDonationsCount,
                            metrics.completedDonationsCount
                        ],
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: false
                            }
                        },
                        y: {
                            title: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Initial display logic
        const initialFilteredData = filterData('2024-01-01', '2024-12-31');
        const initialMetrics = calculateMetrics(initialFilteredData);
        createChart(initialMetrics);

        // Form submission logic
        document.getElementById('filterForm').addEventListener('submit', function (event) {
            event.preventDefault();

            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            const filteredData = filterData(startDate, endDate);
            const metrics = calculateMetrics(filteredData);
            createChart(metrics);
        });
    }

    init();
});
