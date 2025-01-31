<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'dashboard'
];

$conn = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);

// ThingSpeak API fetching code here (keep your existing API code)...

// Fetch latest record for combined chart
$sql_latest = "SELECT DISTINCT created_at, pm25, pm10, ozone, humidity, temperature, co 
               FROM airqualitydata 
               ORDER BY created_at DESC 
               LIMIT 1";
$result_latest = $conn->query($sql_latest);
$latest_data = $result_latest->fetch_assoc();

// For line charts - fetch last 10 records with distinct timestamps
$sql = "SELECT DISTINCT created_at, pm25, pm10, ozone, humidity, temperature, co 
        FROM airqualitydata 
        GROUP BY created_at 
        ORDER BY created_at DESC 
        LIMIT 20";

$result = $conn->query($sql);

$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Air Quality Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .sidebar {
            background-color: rgb(20, 20, 20);
            padding: 20px;
            border-right: 1px solid #dee2e6;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 250px;
            transition: all 0.3s ease;
            overflow-y: auto;
            z-index: 1000;
        }

        .fields-selection {
            color: white;
        }

        .form-check {
            margin-bottom: 10px;
        }

        .form-check-label {
            color: white;
        }

        .profile-section {
            text-align: center;
            padding: 20px 0;
            margin-bottom: 20px;
            border-radius: 10px;
            background: linear-gradient(145deg, rgba(32, 32, 32, 0.9), rgba(25, 25, 25, 0.9));
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 15px;
            border: 3px solid #2452de;
            padding: 3px;
            background-color: white;
            box-shadow: 0 4px 10px rgba(36, 82, 222, 0.3);
            transition: transform 0.3s ease;
        }

        .profile-image:hover {
            transform: scale(1.05);
        }

        .profile-section h5 {
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            color: #ffffff;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .profile-section h3 {
            color: #2452de;
            font-size: 14px;
            font-weight: 500;
            padding: 5px 15px;
            background: rgba(36, 82, 222, 0.1);
            border-radius: 20px;
            display: inline-block;
            margin-top: 5px;
        }

        .line {
            border: none;
            height: 2px;
            background: linear-gradient(90deg, rgba(36,82,222,0.1) 0%, rgba(36,82,222,1) 50%, rgba(36,82,222,0.1) 100%);
            margin: 25px 0;
        }

        .chart-container {
            position: relative;
            margin: auto;
            width: 100%;
            min-height: 300px;
            margin-bottom: 20px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .main-content {
            padding: 20px;
            background-color: #eef2f7;
            margin-left: 250px;
            transition: margin-left 0.3s ease;
        }

        .sidebar h6 {
            color: white;
        }

        .sidebar h5 {
            font-family: 'Orbitron', sans-serif;
            font-size: 24px;
            color:rgb(255, 255, 255);
            font-weight: 600;
            letter-spacing: 1px;
        }

        .sidebar h3 {
            color: white;
            font-size: 16px;
        }

        .interpretation-guide {
            font-size: 16px;
        }

        .bg-purple {
            background-color: #6f42c1;
        }

        .bg-mar {
            background-color: rgb(72, 2, 2);
        }

        .badge {
            min-width: 60px;
            margin: 2px 0;
        }

        .list-unstyled {
            margin-bottom: 0.5rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .header_left .dashboard-title h2 {
            font-size: 32px;
            color: #2452de;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .header_left .dashboard-title p {
            font-size: 18px;
            margin: 0;
        }

        .header_right .connect .social {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .social-link {
            color: #2452de;
            font-size: 1.2rem;
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: #f8f9fc;
        }

        .social-link:hover {
            color: white;
            background: #2452de;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(78, 115, 223, 0.2);
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .header_left .dashboard-title h2 {
                justify-content: center;
            }
        }

        label{
            color:rgb(255, 255, 255);
        }

        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .chart-container {
                margin-bottom: 15px;
            }
        }

        .navbar-toggler {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1001;
            background: #2452de;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .navbar-toggler i {
            color: white;
        }

        @media (max-width: 991px) {
            .navbar-toggler {
                display: block;
            }

            .header {
                margin-top: 40px;
            }
        }

        @media (max-width: 576px) {
            .form-control, .btn {
                font-size: 14px;
                padding: 0.375rem 0.5rem;
            }

            .profile-section h5 {
                font-size: 18px;
            }

            .profile-section h3 {
                font-size: 12px;
            }

            .profile-image {
                width: 80px;
                height: 80px;
            }
        }

        .sidebar-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        @media (max-width: 991px) {
            .sidebar-backdrop.active {
                display: block;
            }
        }
    </style>
</head>

<body>
    <button class="navbar-toggler" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar" id="sidebar">
                <div class="profile-section">
                    <img src="channels4_profile.jpg" alt="Profile" class="profile-image">
                    <h3>Administrator</h3>
                </div>
                <hr class="line">
                <div class="mt-4">
                    <h6>Refresh Interval ⏱️</h6>
                    <select class="form-control" id="refreshInterval">
                        <option value="60000">1 minute</option>
                        <option value="300000">5 minutes</option>
                        <option value="600000">10 minutes</option>
                        <option value="1800000">30 minutes</option>
                        <option value="3600000" selected>1 hour</option>
                    </select>
                </div>
                <hr class="line">

                <!-- Quick Actions -->
                <div class="mt-4">
                    <h6>Quick Actions 🚀</h6>
                    <button class="btn btn-light mb-2 w-100" id="testConnectionBtn">
                        Test Connection
                    </button>
                    <button class="btn btn-light w-100" onclick="location.reload()">
                        Refresh Data
                    </button>
                </div>

                <!-- Date Range Selection -->
                <div class="mt-4">
                    <h6>Select Date Range 📅</h6>
                    <div class="mb-3">
                        <label>Start Date</label>
                        <input type="date" class="form-control" id="startDate">
                    </div>
                    <div class="mb-3">
                        <label>End Date</label>
                        <input type="date" class="form-control" id="endDate">
                    </div>
                </div>
                <hr class="line">
                <div class="mt-4">
                    <h6>Display Format 📅</h6>
                    <select class="form-control" id="displayFormat">
                        <option value="both">Date & Time</option>
                        <option value="date">Date Only</option>
                        <option value="time" selected>Time Only</option>
                    </select>
                </div>
                <hr class="line">
                <!-- Fields Selection -->
                <div class="mt-4">
                    <h6>Select Fields to Display 📊</h6>
                    <div class="fields-selection">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="pm25" id="pm25Check" checked>
                            <label class="form-check-label" for="pm25Check"> PM2.5</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="pm10" id="pm10Check" checked>
                            <label class="form-check-label" for="pm10Check"> PM10</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="ozone" id="ozoneCheck" checked>
                            <label class="form-check-label" for="ozoneCheck"></i> Ozone</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="co" id="coCheck" checked>
                            <label class="form-check-label" for="coCheck">CO</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="humidity" id="humidityCheck" checked>
                            <label class="form-check-label" for="humidityCheck">Humidity</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="temperature" id="temperatureCheck" checked>
                            <label class="form-check-label" for="temperatureCheck">Temperature</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="header">
                    <div class="header_left">
                        <div class="dashboard-title">
                            <h2><i class="fas fa-chart-line"></i> Air Quality Dashboard</h2>
                            <p class="text-muted">Real-time air quality monitoring system</p>
                        </div>
                    </div>
                    <div class="header_right">
                        <div class="connect">
                            <div class="social">
                                <a href="https://github.com/YOUR-USERNAME/YOUR-REPO" target="_blank" class="social-link">
                                    <i class="fab fa-github"></i>
                                </a>
                                <a href="https://linkedin.com/in/YOUR-PROFILE" target="_blank" class="social-link">
                                    <i class="fab fa-linkedin"></i>
                                </a>
                                <a href="mailto:your.email@example.com" class="social-link">
                                    <i class="fas fa-envelope"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Combined Chart -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="chart-container">
                            <canvas id="combinedChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Individual Charts Grid - 3x2 layout -->
                <div class="row g-3">
                    <!-- First Row -->
                    <div class="col-md-6">
                        <div class="chart-container">
                            <canvas id="pm25Chart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container">
                            <canvas id="pm10Chart"></canvas>
                        </div>
                    </div>

                    <!-- Second Row -->
                    <div class="col-md-6">
                        <div class="chart-container">
                            <canvas id="ozoneChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container">
                            <canvas id="humidityChart"></canvas>
                        </div>
                    </div>

                    <!-- Third Row -->
                    <div class="col-md-6">
                        <div class="chart-container">
                            <canvas id="temperatureChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container">
                            <canvas id="coChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="sidebar-backdrop"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Make PHP data available to JavaScript
        const latest_data = <?php echo json_encode($latest_data); ?>;
        const chartData = <?php echo json_encode($data); ?>;

        document.addEventListener('DOMContentLoaded', function() {
            // Store all chart instances
            let charts = {
                combinedChart: null,
                pm25Chart: null,
                pm10Chart: null,
                ozoneChart: null,
                humidityChart: null,
                temperatureChart: null,
                coChart: null
            };

            let originalTimestamps = {}; // Store original timestamps for each chart

            function formatDateTime(dateString, format) {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) {
                    return dateString; // Return original string if invalid date
                }
                switch(format) {
                    case 'date':
                        return date.toLocaleDateString();
                    case 'time':
                        return date.toLocaleTimeString();
                    default:
                        return date.toLocaleString();
                }
            }

            // Initialize combined chart
            const ctxCombined = document.getElementById('combinedChart').getContext('2d');
            charts.combinedChart = new Chart(ctxCombined, {
                type: 'bar',
                data: {
                    labels: [new Date(latest_data.created_at).toLocaleString()],
                    datasets: [{
                        label: 'PM2.5',
                        data: [latest_data.pm25],
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    },
                    {
                        label: 'PM10',
                        data: [latest_data.pm10],
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    },
                    {
                        label: 'Ozone',
                        data: [latest_data.ozone],
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    },
                    {
                        label: 'Humidity',
                        data: [latest_data.humidity],
                        backgroundColor: 'rgba(153, 102, 255, 0.5)',
                    },
                    {
                        label: 'Temperature',
                        data: [latest_data.temperature],
                        backgroundColor: 'rgba(255, 159, 64, 0.5)',
                    },
                    {
                        label: 'CO',
                        data: [latest_data.co],
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            reverse: true
                        }
                    }
                }
            });

            // Initialize individual charts with stored references
            charts.pm25Chart = createLineChart('pm25Chart', 'PM2.5', chartData, 'rgb(255, 99, 132)');
            charts.pm10Chart = createLineChart('pm10Chart', 'PM10', chartData, 'rgb(54, 162, 235)');
            charts.ozoneChart = createLineChart('ozoneChart', 'Ozone', chartData, 'rgb(75, 192, 192)');
            charts.humidityChart = createLineChart('humidityChart', 'Humidity', chartData, 'rgb(153, 102, 255)');
            charts.temperatureChart = createLineChart('temperatureChart', 'Temperature', chartData, 'rgb(255, 159, 64)');
            charts.coChart = createLineChart('coChart', 'CO', chartData, 'rgb(255, 99, 132)');

            // Handle Date Range Selection
            function updateDateRange() {
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;

                if (startDate && endDate) {
                    fetch(`get_filtered_data.php?start=${startDate}&end=${endDate}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data && data.length > 0) {
                                // Prepare data for charts
                                const labels = data.map(item => new Date(item.created_at).toLocaleString());
                                const chartData = {
                                    pm25: data.map(item => item.pm25),
                                    pm10: data.map(item => item.pm10),
                                    ozone: data.map(item => item.ozone),
                                    humidity: data.map(item => item.humidity),
                                    temperature: data.map(item => item.temperature),
                                    co: data.map(item => item.co)
                                };

                                // Update combined chart
                                charts.combinedChart.data.labels = labels;
                                charts.combinedChart.data.datasets.forEach(dataset => {
                                    const field = dataset.label.toLowerCase().replace('.', '');
                                    dataset.data = chartData[field];
                                });
                                charts.combinedChart.update();

                                // Update individual line charts
                                updateLineChart(charts.pm25Chart, labels, chartData.pm25);
                                updateLineChart(charts.pm10Chart, labels, chartData.pm10);
                                updateLineChart(charts.ozoneChart, labels, chartData.ozone);
                                updateLineChart(charts.humidityChart, labels, chartData.humidity);
                                updateLineChart(charts.temperatureChart, labels, chartData.temperature);
                                updateLineChart(charts.coChart, labels, chartData.co);
                            }
                        })
                        .catch(error => console.error('Error:', error));
                }
            }

            // Helper function to update line charts
            function updateLineChart(chart, labels, data) {
                if (chart) {
                    chart.data.labels = labels;
                    chart.data.datasets[0].data = data;
                    chart.update();
                }
            }

            // Handle Field Selection for Combined Chart
            function updateFieldVisibility() {
                const fields = {
                    'pm25Check': 'PM2.5',
                    'pm10Check': 'PM10',
                    'ozoneCheck': 'Ozone',
                    'humidityCheck': 'Humidity',
                    'temperatureCheck': 'Temperature',
                    'coCheck': 'CO'
                };

                Object.entries(fields).forEach(([checkboxId, label]) => {
                    const isChecked = document.getElementById(checkboxId).checked;
                    const datasetIndex = charts.combinedChart.data.datasets.findIndex(ds => ds.label === label);
                    
                    if (datasetIndex !== -1) {
                        charts.combinedChart.data.datasets[datasetIndex].hidden = !isChecked;
                    }
                });

                charts.combinedChart.update();
            }

            // Add event listeners
            document.getElementById('startDate').addEventListener('change', updateDateRange);
            document.getElementById('endDate').addEventListener('change', updateDateRange);

            // Add event listeners for field selection checkboxes
            document.querySelectorAll('.fields-selection input[type="checkbox"]').forEach(checkbox => {
                checkbox.addEventListener('change', updateFieldVisibility);
            });

            // Handle refresh interval
            document.getElementById('refreshInterval').addEventListener('change', function() {
                const interval = parseInt(this.value);
                clearInterval(window.refreshTimer);
                window.refreshTimer = setInterval(() => location.reload(), interval);
            });

            // Initial refresh timer
            window.refreshTimer = setInterval(() => location.reload(), 3600000);

            document.getElementById('testConnectionBtn').addEventListener('click', function() {
                // Get the button element
                const button = this;
                const originalText = button.innerHTML;
                
                // Show loading state
                button.innerHTML = 'Testing...';
                button.disabled = true;

                fetch('test_connection.php')
                    .then(response => response.json())
                    .then(data => {
                        console.log('Response:', data); // Debug log
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: data.message,
                                timer: 3000
                            });
                        } else {
                            throw new Error(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error); // Debug log
                        Swal.fire({
                            icon: 'error',
                            title: 'Connection Failed',
                            text: error.message || 'Unable to connect to the server'
                        });
                    })
                    .finally(() => {
                        // Reset button state
                        button.innerHTML = originalText;
                        button.disabled = false;
                    });
            });

            function updateCharts(newData) {
                // Update line charts
                createLineChart('pm25Chart', 'PM2.5', newData, 'rgb(255, 99, 132)');
                createLineChart('pm10Chart', 'PM10', newData, 'rgb(54, 162, 235)');
                createLineChart('ozoneChart', 'Ozone', newData, 'rgb(75, 192, 192)');
                createLineChart('humidityChart', 'Humidity', newData, 'rgb(153, 102, 255)');
                createLineChart('temperatureChart', 'Temperature', newData, 'rgb(255, 159, 64)');
                createLineChart('coChart', 'CO', newData, 'rgb(255, 99, 132)');

                // Update combined chart
                updateCombinedChart(newData[0]); // Pass the latest record
            }

            // Modify the event listener for display format changes
            document.getElementById('displayFormat').addEventListener('change', function() {
                const format = this.value;
                
                // Update all charts with new format
                Object.entries(charts).forEach(([chartId, chart]) => {
                    if (chart && chart.data && chart.data.labels) {
                        // Use stored original timestamps
                        if (originalTimestamps[chartId]) {
                            chart.data.labels = originalTimestamps[chartId].map(timestamp => 
                                formatDateTime(timestamp, format)
                            );
                            chart.update();
                        }
                    }
                });
            });

            // Modify your createLineChart function to store original timestamps
            function createLineChart(canvasId, label, data, color) {
                const format = document.getElementById('displayFormat').value;
                const ctx = document.getElementById(canvasId).getContext('2d');
                
                // Store original timestamps
                originalTimestamps[canvasId] = data.map(item => item.created_at);
                
                return new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.map(item => formatDateTime(item.created_at, format)),
                        datasets: [{
                            label: label,
                            data: data.map(item => item[label.toLowerCase().replace('.', '')]),
                            borderColor: color,
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                reverse: true,
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 45
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    boxWidth: 12,
                                    padding: 10
                                }
                            }
                        }
                    }
                });
            }

            // Add this line right after your charts are initialized in the DOMContentLoaded event
            // This will trigger the time-only format when the page loads
            document.getElementById('displayFormat').dispatchEvent(new Event('change'));

            const sidebar = document.querySelector('.sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const backdrop = document.querySelector('.sidebar-backdrop');

            sidebarToggle.addEventListener('click', toggleSidebar);
            backdrop.addEventListener('click', toggleSidebar);

            function toggleSidebar() {
                sidebar.classList.toggle('active');
                backdrop.classList.toggle('active');
            }

            // Close sidebar when window is resized to larger screen
            window.addEventListener('resize', () => {
                if (window.innerWidth > 991) {
                    sidebar.classList.remove('active');
                    backdrop.classList.remove('active');
                }
            });

            // Update Chart.js options for better responsiveness
            Chart.defaults.responsive = true;
            Chart.defaults.maintainAspectRatio = false;
        });
    </script>
</body>

</html>

