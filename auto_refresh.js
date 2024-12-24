document.addEventListener('DOMContentLoaded', () => {
    const refreshTimeDropdown = document.getElementById('refreshTime');

    // Function to set auto-refresh
    function setAutoRefresh(interval) {
        // Clear any existing interval
        if (window.autoRefreshInterval) {
            clearInterval(window.autoRefreshInterval);
        }
        // Set new interval
        window.autoRefreshInterval = setInterval(() => {
            location.reload();
        }, interval);
    }

    // Set default refresh interval to 1 hour (3600000 ms)
    setAutoRefresh(3600000);

    // Event listener to update refresh time based on user selection
    refreshTimeDropdown.addEventListener('change', (event) => {
        const selectedInterval = parseInt(event.target.value, 10);
        setAutoRefresh(selectedInterval);
    });
});
