<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Upload Homeowners CSV</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }

        .upload-section {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 5px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        input[type="file"] {
            padding: 5px;
        }

        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }

        button:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f8f9fa;
        }

        .error-table {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .hidden {
            display: none;
        }
    </style>
</head>

<body>
    <h1>Homeowner Names</h1>
    <div class="upload-section">
        <h2>Upload CSV File</h2>
        <form id="uploadForm" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="csvFile">CSV File:</label>
                <input type="file" name="file" id="csvFile" accept=".csv,.txt" required>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="csvHasHeader" id="csvHasHeader" checked> File contains header row?
                </label>
            </div>

            <button type="submit" id="submitBtn">Upload</button>
        </form>
    </div>

    <div id="resultsSection" class="hidden">
        <h3>Processed Data</h3>
        <table id="resultsTable">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>First Name</th>
                    <th>Initial</th>
                    <th>Last Name</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <div id="errorsSection" class="hidden">
            <h3>Errors</h3>
            <table class="error-table">
                <thead>
                    <tr>
                        <th>Row</th>
                        <th>Input</th>
                        <th>Error</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById('uploadForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;

            const formData = new FormData();
            formData.append('file', document.getElementById('csvFile').files[0]);
            formData.append('csvHasHeader', document.getElementById('csvHasHeader').checked ? '1' : '0');

            try {
                const response = await fetch('/upload', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    }
                });

                const data = await response.json();

                document.getElementById('resultsSection').classList.remove('hidden');
                document.querySelector('#resultsTable tbody').innerHTML = '';
                document.querySelector('#errorsSection tbody').innerHTML = '';
                document.getElementById('errorsSection').classList.add('hidden');

                if (data.data && data.data.length > 0) {
                    data.data.forEach(person => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${person.title}</td>
                            <td>${person.first_name || ''}</td>
                            <td>${person.initial || ''}</td>
                            <td>${person.last_name}</td>
                        `;
                        document.querySelector('#resultsTable tbody').appendChild(row);
                    });
                }

                if (data.errors && data.errors.length > 0) {
                    document.getElementById('errorsSection').classList.remove('hidden');
                    data.errors.forEach(error => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${error.row}</td>
                            <td>${error.input}</td>
                            <td>${error.message}</td>
                        `;
                        document.querySelector('#errorsSection tbody').appendChild(row);
                    });
                }

                if (data.errors?.file || data.errors?.csvHasHeader) {
                    alert(data.message);
                }

            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred during file processing');
            } finally {
                submitBtn.disabled = false;
            }
        });

        document.getElementById('csvFile').addEventListener('change', function() {
            document.getElementById('submitBtn').disabled = !this.files.length;
        });
    </script>
</body>

</html>