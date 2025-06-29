<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Test - Smart Cabinet</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre {
            background: #f8f8f8;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        button {
            background: #007cba;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
        }
        button:hover {
            background: #005a87;
        }
    </style>
</head>
<body>
    <h1>🔧 Smart Cabinet API Test</h1>
    
    <div class="test-section">
        <h2>1. Test GET Request (Fetch Valid PINs)</h2>
        <button onclick="testGetPins()">Test GET /cabinet_api.php</button>
        <div id="getResult"></div>
    </div>

    <div class="test-section">
        <h2>2. Test POST Request (Log Access)</h2>
        <button onclick="testPostAccess()">Test POST /cabinet_api.php</button>
        <div id="postResult"></div>
    </div>

    <div class="test-section">
        <h2>3. Current System Status</h2>
        <div id="statusResult"></div>
    </div>

    <script>
        async function testGetPins() {
            const resultDiv = document.getElementById('getResult');
            resultDiv.innerHTML = '<p class="info">Testing GET request...</p>';
            
            try {
                const response = await fetch('cabinet_api.php');
                const data = await response.text();
                
                resultDiv.innerHTML = `
                    <p class="success">✅ GET request successful!</p>
                    <p><strong>Status:</strong> ${response.status}</p>
                    <p><strong>Response:</strong></p>
                    <pre>${data}</pre>
                `;
            } catch (error) {
                resultDiv.innerHTML = `
                    <p class="error">❌ GET request failed!</p>
                    <p><strong>Error:</strong> ${error.message}</p>
                `;
            }
        }

        async function testPostAccess() {
            const resultDiv = document.getElementById('postResult');
            resultDiv.innerHTML = '<p class="info">Testing POST request...</p>';
            
            try {
                const formData = new FormData();
                formData.append('pin_code', '1234');
                formData.append('status', 'Granted');
                
                const response = await fetch('cabinet_api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.text();
                
                resultDiv.innerHTML = `
                    <p class="success">✅ POST request successful!</p>
                    <p><strong>Status:</strong> ${response.status}</p>
                    <p><strong>Response:</strong></p>
                    <pre>${data}</pre>
                `;
            } catch (error) {
                resultDiv.innerHTML = `
                    <p class="error">❌ POST request failed!</p>
                    <p><strong>Error:</strong> ${error.message}</p>
                `;
            }
        }

        async function checkStatus() {
            const resultDiv = document.getElementById('statusResult');
            
            try {
                // Check if files exist
                const response = await fetch('cabinet_api.php');
                const data = await response.text();
                
                let statusHtml = '<p class="success">✅ API endpoint is accessible</p>';
                
                // Try to parse JSON to see if it's valid
                try {
                    const pins = JSON.parse(data);
                    statusHtml += `<p class="success">✅ JSON response is valid</p>`;
                    statusHtml += `<p><strong>Number of valid PINs:</strong> ${pins.length}</p>`;
                    statusHtml += `<p><strong>PINs:</strong> ${pins.join(', ')}</p>`;
                } catch (e) {
                    statusHtml += `<p class="error">❌ JSON parsing failed: ${e.message}</p>`;
                }
                
                resultDiv.innerHTML = statusHtml;
            } catch (error) {
                resultDiv.innerHTML = `
                    <p class="error">❌ Cannot access API endpoint</p>
                    <p><strong>Error:</strong> ${error.message}</p>
                    <p><strong>Possible issues:</strong></p>
                    <ul>
                        <li>Check if cabinet_api.php exists</li>
                        <li>Verify file permissions</li>
                        <li>Check web server configuration</li>
                    </ul>
                `;
            }
        }

        // Run status check on page load
        window.onload = checkStatus;
    </script>
</body>
</html> 