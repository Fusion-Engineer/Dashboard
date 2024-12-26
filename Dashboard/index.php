<?php
// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'Dashboard'
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
    <style>
        .sidebar {
            background-color: rgb(27, 27, 27);
            padding: 20px;
            border-right: 1px solid #dee2e6;
        }

        .profile-section {
            text-align: center;
        }

        .profile-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .chart-container {
            margin-bottom: 20px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .main-content {
            padding: 20px;
            background-color: #eef2f7;
        }

        .dashboard-title {
            margin-bottom: 30px;
        }

        .sidebar h6 {
            color: white;
        }

        .sidebar h5 {
            font-family: 'Orbitron', sans-serif;
            font-size: 24px;
            color: #034FFC;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .sidebar h3 {
            color: white;
            font-size: 16px;
        }

        .line {
            border: none;
            height: 3px;
            background-color:rgb(255, 255, 255);
            margin: 10px 0;
        }

        .interpretation-guide {
            font-size: 16px;
        }
        
        .bg-purple {
            background-color: #6f42c1;
        }

        .bg-mar{
            background-color:rgb(72, 2, 2);
        }
        
        .badge {
            min-width: 60px;
            margin: 2px 0;
        }
        
        .list-unstyled {
            margin-bottom: 0.5rem;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <div class="profile-section">
                    <img src="channels4_profile.jpg" alt="Profile" class="profile-image">
                    <h5>Fusion Engineer</h5>
                    <h3>Administrator</h3>
                </div>
                <hr class="line">

                <div class="mt-4">
                    <h6>Refresh Interval</h6>
                    <select class="form-select" id="refreshInterval">
                        <option value="30000">30 Seconds</option>
                        <option value="60000">1 Minute</option>
                        <option value="300000">5 Minutes</option>
                        <option value="600000">10 Minutes</option>
                        <option value="3600000" selected>1 Hour</option>
                    </select>
                </div>

                <div class="mt-4">
                    <h6>Quick Actions</h6>
                    <div class="list-group mb-3">
                        <a href="#" class="list-group-item list-group-item-action" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i> Refresh Data
                        </a>
                    </div>
                </div>
                <hr class="line">

                <!-- New Data Interpretation Section -->
                <div class="mt-4">
                    <h6>Data Interpretation</h6>
                    <div class="interpretation-guide small" style="color: #ffffff;">
                        <div class="mb-3">
                            <strong>Ozone:</strong>
                            <ul class="list-unstyled ms-2">
                                <li><span class="badge bg-success">0 - 0.054</span> Good</li>
                                <li><span class="badge bg-warning text-dark">0.055 - 0.070</span> Moderate</li>
                                <li><span class="badge bg-info">0.071 - 0.085</span> Sensitive</li>
                                <li><span class="badge bg-danger">0.086 - 0.105</span> Unhealthy</li>
                                <li><span class="badge bg-purple">0.106 - 0.200</span> Very Unhealthy</li>
                                <li><span class="badge bg-mar">> 0.201</span> Hazardous</li>
                            </ul>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Humidity:</strong>
                            <ul class="list-unstyled ms-2">
                                <li><span class="badge bg-warning text-dark">0-29%</span> Low</li>
                                <li><span class="badge bg-success">30-60%</span> Normal</li>
                                <li><span class="badge bg-info">61-100%</span> High</li>
                            </ul>
                        </div>

                        <div class="mb-3">
                            <strong>Temperature:</strong>
                            <ul class="list-unstyled ms-2">
                                <li><span class="badge bg-info">0 - 4.4</span> Cold</li>
                                <li><span class="badge bg-success">4.5 - 9.4</span> Moderate</li>
                                <li><span class="badge bg-warning text-dark">9.5 - 12.4</span> Sensitive Groups</li>
                                <li><span class="badge bg-danger">	12.5 - 15.4</span> Unhealthy</li>
                                <li><span class="badge bg-purple">15.5 - 30.4</span> Very Unhealthy</li>
                                <li><span class="badge bg-mar">> 30.5</span> Hazardous</li>
                            </ul>
                        </div>

                        <div class="mb-3">
                            <strong>CO:</strong>
                            <ul class="list-unstyled ms-2">
                                <li><span class="badge bg-info">0-15°C</span> Good</li>
                                <li><span class="badge bg-success">16-30°C</span> Comfortable</li>
                                <li><span class="badge bg-warning text-dark">31-45°C</span> Hot</li>
                                <li><span class="badge bg-danger">46°C+</span> Very Hot</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <hr class="line">
            </div>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="dashboard-title">
                    <h2><i class="fas fa-chart-line"></i> Air Quality Dashboard</h2>
                    <p class="text-muted">Real-time air quality monitoring system</p>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Make PHP data available to JavaScript
        const latest_data = <?php echo json_encode($latest_data); ?>;
        const chartData = <?php echo json_encode($data); ?>;

        function createLineChart(canvasId, label, data, color) {
            const ctx = document.getElementById(canvasId).getContext('2d');
            const fieldMapping = {
                'PM2.5': 'pm25',
                'PM10': 'pm10',
                'Ozone': 'ozone',
                'Humidity': 'humidity',
                'Temperature': 'temperature',
                'CO': 'co'
            };

            return new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(item => new Date(item.created_at).toLocaleString()),
                    datasets: [{
                        label: label,
                        data: data.map(item => item[fieldMapping[label]]),
                        borderColor: color,
                        fill: false
                    }]
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
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Create combined chart with only latest value
            const ctxCombined = document.getElementById('combinedChart').getContext('2d');
            new Chart(ctxCombined, {
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

            // Create individual line charts
            createLineChart('pm25Chart', 'PM2.5', chartData, 'rgb(255, 99, 132)');
            createLineChart('pm10Chart', 'PM10', chartData, 'rgb(54, 162, 235)');
            createLineChart('ozoneChart', 'Ozone', chartData, 'rgb(75, 192, 192)');
            createLineChart('humidityChart', 'Humidity', chartData, 'rgb(153, 102, 255)');
            createLineChart('temperatureChart', 'Temperature', chartData, 'rgb(255, 159, 64)');
            createLineChart('coChart', 'CO', chartData, 'rgb(255, 99, 132)');
        });

        // Handle refresh interval
        document.getElementById('refreshInterval').addEventListener('change', function() {
            const interval = parseInt(this.value);
            clearInterval(window.refreshTimer);
            window.refreshTimer = setInterval(() => location.reload(), interval);
        });

        // Initial refresh timer
        window.refreshTimer = setInterval(() => location.reload(), 3600000);

        function testConnection() {
            fetch('get_filtered_data.php?test=1')
                .then(response => response.json())
                .then(data => {
                    console.log('Test response:', data);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function filterByDate(date) {
            if (!date) return;
            console.log('Attempting to filter by date:', date);

            fetch('get_filtered_data.php?date=' + date)
                .then(response => {
                    console.log('Raw response:', response);
                    return response.text(); // Change to text() to see raw response
                })
                .then(text => {
                    console.log('Response text:', text);
                    try {
                        const data = JSON.parse(text);
                        console.log('Parsed data:', data);
                        if (data && data.length > 0) {
                            updateCharts(data);
                        } else {
                            alert('No data found for selected date');
                        }
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        alert('Error parsing response');
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('Error fetching data');
                });
        }

        function filterByTime(timeRange) {
            if (!timeRange) return;

            fetch(`get_filtered_data.php?timeRange=${timeRange}`)
                .then(response => response.json())
                .then(data => {
                    updateCharts(data);
                });
        }

        function filterByMonth(month) {
            if (!month) return;

            fetch(`get_filtered_data.php?month=${month}`)
                .then(response => response.json())
                .then(data => {
                    updateCharts(data);
                });
        }

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
    </script>
</body>

</html>
