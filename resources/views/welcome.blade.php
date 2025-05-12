<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Product CSV Upload</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="antialiased bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Upload CSV</h1>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center justify-between border-2 border-dashed border-gray-300 p-4 mb-4">
                <span class="text-gray-500">Select CSV file</span>
                <div class="flex space-x-2">
                    <label for="csvFile" class="bg-blue-500 text-white px-4 py-2 rounded cursor-pointer">
                        Choose File
                        <input type="file" id="csvFile" accept=".csv" class="hidden">
                    </label>
                    <button id="submitUpload" class="bg-green-500 text-white px-4 py-2 rounded cursor-pointer" disabled>
                        Upload
                    </button>
                </div>
            </div>
            <p id="selectedFileName" class="text-sm text-gray-600 mb-4"></p>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="px-4 py-2 text-left">Time</th>
                            <th class="px-4 py-2 text-left">File Name</th>
                            <th class="px-4 py-2 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody id="uploadList"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const csvFile = document.getElementById('csvFile');
        const submitUpload = document.getElementById('submitUpload');
        const selectedFileName = document.getElementById('selectedFileName');
        const uploadList = document.getElementById('uploadList');

        csvFile.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                selectedFileName.textContent = `Selected file: ${file.name}`;
                submitUpload.disabled = false;
            } else {
                selectedFileName.textContent = '';
                submitUpload.disabled = true;
            }
        });

        submitUpload.addEventListener('click', async () => {
            const formData = new FormData();
            formData.append('file', csvFile.files[0]);

            try {
                const response = await axios.post('/upload', formData);
                alert('File uploaded successfully');
                fetchUploads();
            } catch (error) {
                alert('Error uploading file');
            }
        });

        async function fetchUploads() {
            try {
                const response = await axios.get('/uploads');
                uploadList.innerHTML = response.data.map(upload => `
                    <tr class="border-b">
                        <td class="px-4 py-2">${formatDate(upload.created_at)}</td>
                        <td class="px-4 py-2">${upload.filename.split('/').pop()}</td>
                        <td class="px-4 py-2" id="status-${upload.id}">${upload.status}</td>
                    </tr>
                `).join('');

                response.data.forEach(upload => {
                    if (upload.status === 'pending' || upload.status === 'processing') {
                        pollStatus(upload.id);
                    }
                });
            } catch (error) {
                console.error('Error fetching uploads', error);
            }
        }

        function pollStatus(id) {
            const statusElement = document.getElementById(`status-${id}`);
            const interval = setInterval(async () => {
                try {
                    const response = await axios.get(`/upload/${id}/status`);
                    statusElement.textContent = response.data.status;
                    if (response.data.status === 'completed' || response.data.status === 'failed') {
                        clearInterval(interval);
                    }
                } catch (error) {
                    console.error('Error polling status', error);
                    clearInterval(interval);
                }
            }, 5000); // Poll every 5 seconds
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffInMinutes = Math.round((now - date) / 60000);

            const formattedDate = date.toLocaleString('en-US', {
                month: 'numeric',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: 'numeric',
                hour12: true
            });

            return `${formattedDate} (${diffInMinutes} minutes ago)`;
        }

        fetchUploads();
    </script>
</body>
</html>