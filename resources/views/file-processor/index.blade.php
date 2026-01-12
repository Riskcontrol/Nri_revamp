<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AI Business Report Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .upload-area {
            border: 2px dashed #cbd5e1;
            transition: all 0.3s ease;
        }

        .upload-area.dragover {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }

        .spinner {
            border: 3px solid #f3f4f6;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto px-4 py-12 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-12">
            <div class="inline-block p-3 bg-blue-600 rounded-full mb-4">
                <i class="fas fa-chart-line text-white text-3xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-gray-800 mb-2">AI Business Report Generator</h1>
            <p class="text-gray-600">Upload your Excel/CSV file to generate comprehensive business reports</p>
        </div>

        <!-- Success Message -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg mb-6 fade-in">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-3 text-xl"></i>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <!-- Error Message -->
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg mb-6 fade-in">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-3 text-xl"></i>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg mb-6 fade-in">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-circle mr-3 text-xl"></i>
                    <span class="font-semibold">Please fix the following errors:</span>
                </div>
                <ul class="list-disc list-inside ml-8">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Main Upload Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="p-8">
                <form action="{{ route('file-processor.process') }}" method="POST" enctype="multipart/form-data"
                    id="uploadForm">
                    @csrf

                    <!-- Upload Area -->
                    <div class="upload-area rounded-xl p-12 text-center cursor-pointer mb-6" id="uploadArea">
                        <input type="file" name="data_file" id="fileInput" class="hidden" accept=".xlsx,.csv,.xls"
                            required>

                        <div id="uploadPrompt">
                            <i class="fas fa-cloud-upload-alt text-6xl text-blue-500 mb-4"></i>
                            <h3 class="text-xl font-semibold text-gray-700 mb-2">Drop your file here or click to browse
                            </h3>
                            <p class="text-gray-500 text-sm">Supports Excel (.xlsx, .xls) and CSV files (Max 10MB)</p>
                        </div>

                        <div id="fileInfo" class="hidden">
                            <i class="fas fa-file-excel text-6xl text-green-500 mb-4"></i>
                            <h3 class="text-xl font-semibold text-gray-700 mb-2" id="fileName"></h3>
                            <p class="text-gray-500 text-sm" id="fileSize"></p>
                            <button type="button" onclick="clearFile()"
                                class="mt-3 text-red-500 hover:text-red-700 text-sm">
                                <i class="fas fa-times-circle mr-1"></i> Remove file
                            </button>
                        </div>
                    </div>

                    <!-- Process Button -->
                    <div class="flex justify-center">
                        <button type="submit" id="submitBtn"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 px-12 rounded-lg shadow-lg transform transition hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                            <i class="fas fa-magic mr-2"></i>
                            <span id="btnText">Generate AI Report</span>
                        </button>
                    </div>
                </form>

                <!-- Processing Indicator -->
                <div id="processingIndicator" class="hidden mt-8 text-center">
                    <div class="spinner mx-auto mb-4"></div>
                    <p class="text-gray-600 font-medium">Processing your file...</p>
                    <p class="text-gray-500 text-sm mt-2">This may take a few moments</p>
                </div>
            </div>

            <!-- Info Section -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-8 border-t">
                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    What this tool does:
                </h3>
                <ul class="space-y-2 text-gray-700">
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 mr-3 mt-1"></i>
                        <span>Analyzes risk indicators and generates comprehensive business reports</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 mr-3 mt-1"></i>
                        <span>Identifies affected industries and impact levels</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 mr-3 mt-1"></i>
                        <span>Provides business advisory and risk assessments</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 mr-3 mt-1"></i>
                        <span>Finds similar news articles for context</span>
                    </li>
                </ul>

                <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm text-yellow-800">
                        <i class="fas fa-lightbulb mr-2"></i>
                        <strong>Tip:</strong> Ensure your spreadsheet contains columns like 'risk_indicator',
                        'weekly_summary', and 'source_link' for best results.
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-gray-600 text-sm">
            <p>Powered by AI • Your data is processed securely and not stored</p>
        </div>
    </div>

    <script>
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const uploadPrompt = document.getElementById('uploadPrompt');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const uploadForm = document.getElementById('uploadForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const processingIndicator = document.getElementById('processingIndicator');

        // Click to upload
        uploadArea.addEventListener('click', () => fileInput.click());

        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelect();
            }
        });

        // File input change
        fileInput.addEventListener('change', handleFileSelect);

        function handleFileSelect() {
            const file = fileInput.files[0];
            if (file) {
                const sizeInMB = (file.size / (1024 * 1024)).toFixed(2);
                fileName.textContent = file.name;
                fileSize.textContent = `Size: ${sizeInMB} MB`;

                uploadPrompt.classList.add('hidden');
                fileInfo.classList.remove('hidden');
                submitBtn.disabled = false;
            }
        }

        function clearFile() {
            fileInput.value = '';
            uploadPrompt.classList.remove('hidden');
            fileInfo.classList.add('hidden');
            submitBtn.disabled = true;
        }

        // Form submission with download detection
        uploadForm.addEventListener('submit', function(e) {
            if (!fileInput.files[0]) {
                e.preventDefault();
                alert('Please select a file first');
                return;
            }

            // Show processing indicator
            submitBtn.disabled = true;
            btnText.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
            processingIndicator.classList.remove('hidden');

            // Set a cookie to detect when download completes
            document.cookie = "downloadStarted=0; path=/";

            // Check for download completion
            const downloadTimer = setInterval(function() {
                const cookies = document.cookie.split(';');
                for (let cookie of cookies) {
                    if (cookie.trim().startsWith('downloadComplete=')) {
                        // Download completed
                        clearInterval(downloadTimer);

                        // Reset UI
                        submitBtn.disabled = false;
                        btnText.innerHTML = '<i class="fas fa-magic mr-2"></i>Generate AI Report';
                        processingIndicator.classList.add('hidden');

                        // Clear file
                        clearFile();

                        // Show success message
                        showSuccessMessage('File processed and downloaded successfully!');

                        // Clear the cookie
                        document.cookie = "downloadComplete=0; path=/; max-age=0";
                        break;
                    }
                }
            }, 1000); // Check every second

            // Timeout after 5 minutes
            setTimeout(function() {
                clearInterval(downloadTimer);
                if (submitBtn.disabled) {
                    submitBtn.disabled = false;
                    btnText.innerHTML = '<i class="fas fa-magic mr-2"></i>Generate AI Report';
                    processingIndicator.classList.add('hidden');
                    showErrorMessage('Processing took too long. Please try again or contact support.');
                }
            }, 300000); // 5 minutes
        });

        // Function to show success message
        function showSuccessMessage(message) {
            const successDiv = document.createElement('div');
            successDiv.className =
                'bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg mb-6 fade-in fixed top-4 right-4 z-50 max-w-md shadow-lg';
            successDiv.innerHTML = `
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-3 text-xl"></i>
                        <span>${message}</span>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-green-700 hover:text-green-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            document.body.appendChild(successDiv);

            // Auto remove after 5 seconds
            setTimeout(() => {
                successDiv.remove();
            }, 5000);
        }

        // Function to show error message
        function showErrorMessage(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className =
                'bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg mb-6 fade-in fixed top-4 right-4 z-50 max-w-md shadow-lg';
            errorDiv.innerHTML = `
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-3 text-xl"></i>
                        <span>${message}</span>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-red-700 hover:text-red-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            document.body.appendChild(errorDiv);

            // Auto remove after 10 seconds
            setTimeout(() => {
                errorDiv.remove();
            }, 10000);
        }

        // Initial state
        submitBtn.disabled = true;
    </script>
</body>

</html>
