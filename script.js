const apiUrl = 'https://api.thingspeak.com/channels/1596152/feeds.json?results=20'; 

// Initialize charts for each parameter
const pm25Chart = new Chart(document.getElementById('pm25Chart').getContext('2d'), createChartConfig('PM2.5', 'red'));
const pm10Chart = new Chart(document.getElementById('pm10Chart').getContext('2d'), createChartConfig('PM10', 'blue'));
const ozoneChart = new Chart(document.getElementById('ozoneChart').getContext('2d'), createChartConfig('Ozone', 'green'));
const humidityChart = new Chart(document.getElementById('humidityChart').getContext('2d'), createChartConfig('Humidity', 'purple'));
const temperatureChart = new Chart(document.getElementById('temperatureChart').getContext('2d'), createChartConfig('Temperature', 'orange'));
const coChart = new Chart(document.getElementById('coChart').getContext('2d'), createChartConfig('CO', 'brown'));


const combinedChart = new Chart(document.getElementById('combinedChart').getContext('2d'), createCombinedChartConfig());

function createChartConfig(label, color) {
    return {
        type: 'line',
        data: {
            labels: [], 
            datasets: [{
                label: label,
                data: [],
                borderColor: color,
                fill: false
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: label + ' Data'
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Time'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Values'
                    }
                }
            }
        }
    };
}

function createCombinedChartConfig() {
    return {
        type: 'bar',
        data: {
            labels: [], 
            datasets: [
                {
                    label: 'PM2.5',
                    data: [],
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                },
                {
                    label: 'PM10',
                    data: [],
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                },
                {
                    label: 'Ozone',
                    data: [],
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                },
                {
                    label: 'Humidity',
                    data: [],
                    backgroundColor: 'rgba(153, 102, 255, 0.5)',
                },
                {
                    label: 'Temperature',
                    data: [],
                    backgroundColor: 'rgba(255, 159, 64, 0.5)',
                },
                {
                    label: 'CO',
                    data: [],
                    backgroundColor: 'rgba(255, 206, 86, 0.5)',
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Combined Air Quality Data'
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Time'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Values'
                    }
                }
            }
        }
    };
}

async function fetchAndUpdateCharts() {
    try {
        const response = await fetch(apiUrl);
        const data = await response.json();

        const latestFeed = data.feeds[data.feeds.length - 1];

        console.log('Latest Feed:', latestFeed);

        if (latestFeed) {
            const currentTime = new Date().toLocaleTimeString(); 

            updateChart(pm25Chart, latestFeed.field1, currentTime);
            updateChart(pm10Chart, latestFeed.field2, currentTime);
            updateChart(ozoneChart, latestFeed.field3, currentTime);
            updateChart(humidityChart, latestFeed.field4, currentTime);
            updateChart(temperatureChart, latestFeed.field5, currentTime);
            updateChart(coChart, latestFeed.field6, currentTime);

            updateCombinedChart(latestFeed, currentTime);
        } else {
            console.error('No new data found');
        }
    } catch (error) {
        console.error('Error fetching data:', error);
    }
}

function updateChart(chart, value, time) {
    if (value !== null && typeof value !== 'undefined') {
        chart.data.labels.push(time);
        chart.data.datasets[0].data.push(value);
    } else {
        console.warn('Missing data point for', chart.data.datasets[0].label);
    }

    if (chart.data.labels.length > 10) {
        chart.data.labels.shift();
        chart.data.datasets[0].data.shift();
    }

    chart.update();
}

function updateCombinedChart(latestFeed, time) {

    combinedChart.data.labels.push(time);
    combinedChart.data.datasets[0].data.push(latestFeed.field1 || 0); // PM2.5
    combinedChart.data.datasets[1].data.push(latestFeed.field2 || 0); // PM10
    combinedChart.data.datasets[2].data.push(latestFeed.field3 || 0); // Ozone
    combinedChart.data.datasets[3].data.push(latestFeed.field4 || 0); // Humidity
    combinedChart.data.datasets[4].data.push(latestFeed.field5 || 0); // Temperature
    combinedChart.data.datasets[5].data.push(latestFeed.field6 || 0); // CO


    if (combinedChart.data.labels.length > 2) {
        combinedChart.data.labels.shift();
        combinedChart.data.datasets.forEach(dataset => dataset.data.shift());
    }

    combinedChart.update();
}

setInterval(fetchAndUpdateCharts, 5000);
