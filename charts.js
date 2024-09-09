document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('myChart').getContext('2d');
    let myChart = null;

    // Get the current year dynamically
    const currentYear = new Date().getFullYear();
    let initialStartDate = `${currentYear}-01-01`;  // Set the start date to the first day of the current year
    let initialEndDate = `${currentYear}-12-31`;    // Set the end date to the last day of the current year

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
        const requestsData = await fetchData('requests.json');
        const donationsData = await fetchData('announcements.json');

        function filterData(startDate, endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            end.setHours(23, 59, 59, 999); // Ensure the end date includes the full day

            // Filter completed requests: completed_at is within the date range
            const completedRequests = requestsData.filter(request => 
                request.completed_at != null &&
                new Date(request.completed_at) >= start &&
                new Date(request.completed_at) <= end
            );

            // Filter new requests: created_at must be not null and earlier or equal to the end date,
            // completed_at is either null or after the end date, and accepted_at is either null or after the end date
            //Exageration since the completion date will happend AFTER the rescuer acceptance date anyways...
            const newRequests = requestsData.filter(request => 
                request.created_at != null && new Date(request.created_at) <= end &&
                (request.completed_at == null || new Date(request.completed_at) > end) &&
                (request.accepted_at == null || new Date(request.accepted_at) > end)
            );

            // Filtering announcements based on the new logic
            const completedAnnouncements = donationsData.flatMap(announcement => 
                announcement.items.filter(item => 
                    item.delivery_completion_date != null &&
                    new Date(item.delivery_completion_date) >= start &&
                    new Date(item.delivery_completion_date) <= end
                )
            );

            // Updated New Announcements logic with rescuer_acceptance_date condition 
            //Exageration since the completion date will happend AFTER the rescuer acceptance date anyways...
            const newAnnouncements = donationsData.flatMap(announcement => 
                announcement.items.filter(item => 
                    item.citizen_acceptance_date != null && 
                    new Date(item.citizen_acceptance_date) <= end &&
                    (item.delivery_completion_date == null || new Date(item.delivery_completion_date) > end) &&
                    (item.rescuer_acceptance_date == null || new Date(item.rescuer_acceptance_date) > end)
                )
            );

            return {
                completedRequests: completedRequests,
                newRequests: newRequests,
                completedAnnouncements: completedAnnouncements,
                newAnnouncements: newAnnouncements
            };
        }

        function calculateMetrics(filteredData) {
            return {
                completedRequestsCount: filteredData.completedRequests.length,
                newRequestsCount: filteredData.newRequests.length,
                completedAnnouncementsCount: filteredData.completedAnnouncements.length,
                newAnnouncementsCount: filteredData.newAnnouncements.length
            };
        }

        function createChart(metrics) {
            if (myChart) {
                myChart.destroy();
            }

            myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['New Requests', 'Completed Requests', 'New Announcements', 'Completed Announcements'],
                    datasets: [{
                        data: [
                            metrics.newRequestsCount,
                            metrics.completedRequestsCount,
                            metrics.newAnnouncementsCount,
                            metrics.completedAnnouncementsCount
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

        // Initial display logic using the current year as default
        const initialFilteredData = filterData(initialStartDate, initialEndDate);
        const initialMetrics = calculateMetrics(initialFilteredData);
        createChart(initialMetrics);

        // Form submission logic
        document.getElementById('filterForm').addEventListener('submit', function (event) {
            event.preventDefault();

            const startDate = document.getElementById('startDate').value || initialStartDate;
            const endDate = document.getElementById('endDate').value || initialEndDate;

            // Compare with initial dates and reload chart if no changes
            if (startDate === initialStartDate && endDate === initialEndDate) {
                createChart(initialMetrics);  // Reload the chart with the same data
            } else {
                const filteredData = filterData(startDate, endDate);
                const metrics = calculateMetrics(filteredData);
                createChart(metrics);
            }
        });
    }

    init();
});
