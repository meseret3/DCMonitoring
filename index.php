<!DOCTYPE html>
<html lang="en">
<head>
    <title>Data Center Monitoring</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #1c1c1c;
            color: #f1f1f1;
            padding: 30px;
        }

        .title {
            text-align: center;
            margin-bottom: 30px;
        }

        h2 {
            font-size: 2.5rem;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 20px;
        }

        .status-container, .camera-status {
            flex: 1 1 30%;
            min-width: 300px;
        }

        .data-box, .camera-status-container {
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 12px;
            background-color: #2b2b2b;
            font-size: 1.3rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.4);
        }

        .status {
            padding: 12px;
            border-radius: 10px;
            font-size: 1.1rem;
            margin-top: 10px;
        }

        .status-ok {
            background-color: #28a745;
            color: white;
        }

        .status-down {
            background-color: #dc3545;
            color: white;
        }

        .temperature, .humidity {
            background-color: green;
        }

        ul {
            padding-left: 20px;
        }

        li {
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="title">
        <h2>Data Center Monitoring</h2>
    </div>

    <div class="container">
        <!-- Temperature and Humidity -->
        <div class="status-container">
            <div id="temperature-box" class="data-box temperature">Loading Temperature...</div>
            <div id="humidity-box" class="data-box humidity">Loading Humidity...</div>
            <div id="server-status-box" class="data-box status-ok">
                Loading server statuses...
            </div>
        </div>

        <!-- Telecom Status -->
        <div class="status-container">
            <div id="ethio-container" class="data-box">
                <h4>EthioTelecom</h4>
                <div id="ethio-status" class="status">Checking...</div>
            </div>
            <div id="safaricom-container" class="data-box">
                <h4>Safaricom</h4>
                <div id="safaricom-status" class="status">Checking...</div>
            </div>
        </div>

        <!-- Camera Status -->
        <div class="camera-status">
            <div id="camera-status" class="camera-status-container status-ok">
                <strong>No cameras are down</strong>
            </div>
        </div>

        <!-- Server Status -->
        
    </div>

    <script>
        function updateData() {
            $.ajax({
                url: 'getdata.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var temperature = parseFloat(response.temperature);
                    $('#temperature-box').text('🌡️ Temperature: ' + temperature + '°C');
                    $('#temperature-box').css('background-color', temperature > 30 ? '#dc3545' : '#28a745');

                    var humidity = parseFloat(response.humidity);
                    $('#humidity-box').text('💧 Humidity: ' + humidity + '%');
                    $('#humidity-box').css('background-color', humidity > 65 ? '#dc3545' : '#28a745');

                    var ethioStatus = response.EthioTelecom_Status || 'Unknown';
                    $('#ethio-status').text(ethioStatus);
                    $('#ethio-status').css('background-color', ethioStatus === 'Online' ? '#28a745' : '#dc3545');

                    var safaricomStatus = response.Safaricom_Status || 'Unknown';
                    $('#safaricom-status').text(safaricomStatus);
                    $('#safaricom-status').css('background-color', safaricomStatus === 'Online' ? '#28a745' : '#dc3545');
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching sensor data:', error);
                }
            });
        }

        function checkCameraStatus() {
            fetch('checkmk-project/checkmk.php')
                .then(response => response.json())
                .then(data => {
                    const statusDiv = document.getElementById('camera-status');
                    if (data.down.length === 0) {
                        statusDiv.className = "camera-status-container status-ok";
                        statusDiv.innerHTML = "<strong>No cameras are down</strong>";
                    } else {
                        statusDiv.className = "camera-status-container status-down";
                        let downList = data.down.map(name => `<div class="camera-name">${name}</div>`).join('');
                        statusDiv.innerHTML = `<strong>The following cameras are down:</strong><br>${downList}`;
                    }
                })
                .catch(error => {
                    console.error("Error fetching camera status:", error);
                });
        }

        function checkServerStatus() {
            fetch('server_status.php')
                .then(response => response.json())
                .then(data => {
                    const box = document.getElementById('server-status-box');
                    const servers = data.servers;
                    const offline = servers.filter(s => !s.online);
                    let html = '';

                    if (offline.length === 0) {
                        html = `<strong>✅ No servers are offline</strong>`;
                        box.className = "data-box status-ok";
                    } else {
                        html = `<strong>⚠️ Offline Servers:</strong><ul>`;
                        offline.forEach(s => {
                            html += `<li>${s.name} (${s.ip})</li>`;
                        });
                        html += `</ul>`;
                        box.className = "data-box status-down";
                    }

                    box.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error fetching server status:', error);
                });
        }

        updateData();
        checkCameraStatus();
        checkServerStatus();

        setInterval(updateData, 5000);
        setInterval(checkCameraStatus, 10000);
        setInterval(checkServerStatus, 10000);
    </script>
</body>
</html>
