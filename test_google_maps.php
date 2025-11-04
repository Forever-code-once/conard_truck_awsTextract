<script type="text/javascript">
        var gk_isXlsx = false;
        var gk_xlsxFileLookup = {};
        var gk_fileData = {};
        function filledCell(cell) {
          return cell !== '' && cell != null;
        }
        function loadFileData(filename) {
        if (gk_isXlsx && gk_xlsxFileLookup[filename]) {
            try {
                var workbook = XLSX.read(gk_fileData[filename], { type: 'base64' });
                var firstSheetName = workbook.SheetNames[0];
                var worksheet = workbook.Sheets[firstSheetName];

                // Convert sheet to JSON to filter blank rows
                var jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1, blankrows: false, defval: '' });
                // Filter out blank rows (rows where all cells are empty, null, or undefined)
                var filteredData = jsonData.filter(row => row.some(filledCell));

                // Heuristic to find the header row by ignoring rows with fewer filled cells than the next row
                var headerRowIndex = filteredData.findIndex((row, index) =>
                  row.filter(filledCell).length >= filteredData[index + 1]?.filter(filledCell).length
                );
                // Fallback
                if (headerRowIndex === -1 || headerRowIndex > 25) {
                  headerRowIndex = 0;
                }

                // Convert filtered JSON back to CSV
                var csv = XLSX.utils.aoa_to_sheet(filteredData.slice(headerRowIndex)); // Create a new sheet from filtered array of arrays
                csv = XLSX.utils.sheet_to_csv(csv, { header: 1 });
                return csv;
            } catch (e) {
                console.error(e);
                return "";
            }
        }
        return gk_fileData[filename] || "";
        }
        </script><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Google Maps API Key</title>
    <style>
        #map {
            height: 400px;
            width: 100%;
            border: 1px solid #ccc;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .error {
            color: red;
            display: none;
            margin-top: 10px;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            margin: 10px 0;
        }
        button {
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Test Google Maps API Key</h2>
        <p>Enter your Google Maps API key below and click "Load Map" to test if it works.</p>
        <input type="text" id="apiKey" placeholder="AIzaSyAogsPCQxx261LGlKop6NA8RxVHUYY8lfs" value="<?php echo isset($_POST['apiKey']) ? htmlspecialchars($_POST['apiKey']) : ''; ?>">
        <button onclick="loadMap()">Load Map</button>
        <div id="error" class="error">Failed to load the map. Please check your API key and ensure billing is enabled.</div>
        <div id="map"></div>
    </div>

    <script>
        function loadMap() {
            const apiKey = document.getElementById('apiKey').value.trim();
            const errorDiv = document.getElementById('error');
            const mapDiv = document.getElementById('map');

            if (!apiKey) {
                errorDiv.style.display = 'block';
                errorDiv.textContent = 'Please enter a valid API key.';
                return;
            }

            // Remove any existing script to avoid duplicates
            const oldScript = document.getElementById('googleMapsScript');
            if (oldScript) oldScript.remove();

            // Create a new script element for Google Maps API
            const script = document.createElement('script');
            script.id = 'googleMapsScript';
            script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&callback=initMap`;
            script.async = true;
            script.defer = true;
            script.onerror = () => {
                errorDiv.style.display = 'block';
                mapDiv.style.display = 'none';
            };
            document.body.appendChild(script);
        }

        function initMap() {
            const mapDiv = document.getElementById('map');
            const errorDiv = document.getElementById('error');

            try {
                // Initialize a basic map centered on a default location (e.g., New York)
                const map = new google.maps.Map(mapDiv, {
                    center: { lat: 40.7128, lng: -74.0060 },
                    zoom: 12
                });
                errorDiv.style.display = 'none';
                mapDiv.style.display = 'block';
            } catch (e) {
                errorDiv.style.display = 'block';
                mapDiv.style.display = 'none';
            }
        }
    </script>
</body>
</html>