<!DOCTYPE html>
<html lang="en">
<head>
    <title>Data Center</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
    <style>
       body {
    font-family: 'Courier New', Courier, monospace;
    background-color: #222;
    color: #FFF;
    padding: 20px;
}

.container {
    display: flex;
    justify-content: space-between; /* Space out columns evenly */
    align-items: flex-start;       /* Align all items at the top */
    margin: 0 auto;
    gap: 20px;                     /* Add spacing between columns */
}

.status-container, .camera-status {
    width: 30%;                    /* Ensure each column takes equal space */
}

.data-box, .camera-status-container {
    margin: 10px 0;
    padding: 20px;
    width: 100%;
    border-radius: 8px;
    background-color: #333;
    font-size: 1.5rem;
    box-sizing: border-box;        /* Include padding in width calculation */
}

.camera-status-container {
    padding: 15px;
    background-color: #333;
    color: white;
    height: 100%;                  /* Ensure full height for alignment */
}

.status {
    padding: 10px;
    border-radius: 8px;
    font-size: 1.2rem;
    margin-top: 10px;
}

.temperature, .humidity {
    background-color: green;
}

.status-ok {
    background-color: #28a745;     /* Green */
}

.status-down {
    background-color: #dc3545;     /* Red */
}



        .title {
        text-align: center;  /* Centers text inside the div */
        margin: 0 auto;      /* Centers the div horizontally */
        width: 100%;         /* Ensures div spans the full width */
    }
    </style>
</head>
<body>
    <div class = "title">
    <h2> Data Center Monitoring</h2>
    </div>
    <div class="container">
    <!-- First Column: Temperature and Humidity -->
    <div class="status-container">
        <div id="temperature-box" class="data-box temperature">Loading Temperature...</div>
        <div id="humidity-box" class="data-box humidity">Loading Humidity...</div>
    </div>

    <!-- Second Column: Telecom Status -->
    <div class="status-container">
        <div id="ethio-container" class="data-box">
            <h3>EthioTelecom</h3>
            <div id="ethio-status" class="status">Checking...</div>
        </div>
        <div id="safaricom-container" class="data-box">
            <h3>Safaricom</h3>
            <div id="safaricom-status" class="status">Checking...</div>
        </div>
    </div>

    <!-- Third Column: Camera Status -->
    <div class="camera-status">
        <div id="camera-status" class="camera-status-container status-ok">
            <strong>No cameras are down</strong>
        </div>
    </div>
</div>


    <script>
        // Fetch temperature, humidity, and telecom statuses
        function updateData() {
            $.ajax({
                url: 'getdata.php', // Correct PHP script path
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    // Update temperature
                    var temperature = parseFloat(response.temperature);
                    $('#temperature-box').text('Temperature: ' + temperature + '°C');
                    $('#temperature-box').css('background-color', temperature > 30 ? 'red' : 'green');

                    // Update humidity
                    var humidity = parseFloat(response.humidity);
                    $('#humidity-box').text('Humidity: ' + humidity + '%');
                    $('#humidity-box').css('background-color', humidity > 65 ? 'red' : 'green');

                    // Update EthioTelecom status
                    var ethioStatus = response.EthioTelecom_Status || 'Unknown';
                    $('#ethio-status').text(ethioStatus);
                    $('#ethio-status').css('background-color', ethioStatus === 'Online' ? 'green' : 'red');

                    // Update Safaricom status
                    var safaricomStatus = response.Safaricom_Status || 'Unknown';
                    $('#safaricom-status').text(safaricomStatus);
                    $('#safaricom-status').css('background-color', safaricomStatus === 'Online' ? 'green' : 'red');
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching data:', error);
                }
            });
        }

        // Fetch camera status
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

        // Initial calls and periodic updates
        updateData();
        checkCameraStatus();
        setInterval(updateData, 5000); // Every 5 seconds
        setInterval(checkCameraStatus, 10000); // Every 10 seconds
    </script>
</body>
</html>
