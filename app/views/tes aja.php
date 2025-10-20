
    <!-- Tailwind CSS for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Applying the Inter font family */
        body {
            font-family: 'Inter', sans-serif;
        }
        
        /* Styles for the floating information box */
        #floating-info-box {
            position: absolute;
            width: calc(100% - 6rem); /* Adjust width considering parent padding (p-12) */
            background-color: #eff6ff; /* Light blue background */
            border-left: 4px solid #3b82f6; /* Blue left border */
            padding: 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            opacity: 0;
            transform: translateX(-15px) scale(0.98);
            transition: opacity 0.3s ease, transform 0.3s ease, top 0.2s ease-in-out;
            visibility: hidden;
            pointer-events: none; /* Prevent it from blocking mouse events */
            z-index: 10;
        }

        #floating-info-box.visible {
            opacity: 1;
            transform: translateX(0) scale(1);
            visibility: visible;
        }
    </style>

<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4 sm:p-6 lg:p-8">

    <div class="container mx-auto max-w-6xl w-full">
        <div class="bg-white rounded-xl shadow-2xl overflow-hidden md:flex">

            <!-- Left Column: The Form -->
            <div class="md:w-1/2 p-8 md:p-12">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Project Submission</h1>
                <p class="text-gray-600 mb-8">Please fill out the details below to submit your project.</p>

                <form action="#" method="POST" class="space-y-6">
                    <!-- Your Name -->
                    <div class="form-field-group">
                        <label for="userName" class="block text-sm font-semibold text-gray-700 mb-2">Your Name</label>
                        <input type="text" id="userName" name="userName" placeholder="e.g., Jane Doe" class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition"
                        data-info='<h3 class="font-semibold text-gray-700 mb-1">Full Name</h3><p class="text-sm text-gray-600">Please enter your first and last name.</p>'>
                    </div>

                    <!-- Email Address -->
                    <div class="form-field-group">
                        <label for="userEmail" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                        <input type="email" id="userEmail" name="userEmail" placeholder="e.g., jane.doe@example.com" class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition"
                        data-info='<h3 class="font-semibold text-gray-700 mb-1">Contact Email</h3><p class="text-sm text-gray-600">We will use this email for all communication regarding your submission.</p>'>
                    </div>

                    <!-- Project Title Input -->
                    <div class="form-field-group">
                        <label for="projectTitle" class="block text-sm font-semibold text-gray-700 mb-2">Project Title</label>
                        <input type="text" id="projectTitle" name="projectTitle" placeholder="e.g., Q4 Marketing Report" class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition"
                        data-info='<h3 class="font-semibold text-gray-700 mb-1">Project Title Format</h3><p class="text-sm text-gray-600">Keep it concise and descriptive. Avoid using special characters like / ? * &.</p>'>
                    </div>

                    <!-- Project Description Textarea -->
                    <div class="form-field-group">
                        <label for="projectDescription" class="block text-sm font-semibold text-gray-700 mb-2">Project Description</label>
                        <textarea id="projectDescription" name="projectDescription" rows="4" placeholder="Provide a brief summary of your project..." class="form-textarea w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition"
                        data-info='<h3 class="font-semibold text-gray-700 mb-1">About the Description</h3><p class="text-sm text-gray-600">Summarize the key points of your project in 2-3 sentences. This helps us categorize it correctly.</p>'></textarea>
                    </div>

                    <!-- File Upload Input -->
                    <div class="form-field-group">
                        <label for="fileUpload" class="block text-sm font-semibold text-gray-700 mb-2">Upload Your File</label>
                        <input type="file" id="fileUpload" name="fileUpload" class="form-input w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition"
                        data-info='<h3 class="font-semibold text-gray-700 mb-2">File Requirements</h3><ul class="list-disc list-inside text-sm text-gray-600 space-y-1"><li><strong>File Size:</strong> Maximum 10MB.</li><li><strong>Allowed Formats:</strong> PDF, DOCX, ZIP.</li><li><strong>Naming Convention:</strong> Use `ProjectTitle_YourName_YYYYMMDD.ext`.</li></ul>'>
                    </div>
                    
                    <!-- Submit Button -->
                    <div>
                        <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-transform transform hover:scale-105">
                            Submit Project
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Column: Information & Guidelines -->
            <div class="guidelines-column md:w-1/2 bg-gray-50 p-8 md:p-12 relative">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 sticky top-8">Submission Guidelines</h2>
                <div id="floating-info-box">
                    <!-- Dynamic content will be inserted here by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Select the parent containers of the form fields
            const formFieldGroups = document.querySelectorAll('.form-field-group');
            const floatingBox = document.getElementById('floating-info-box');
            const rightColumn = document.querySelector('.guidelines-column');

            if (!floatingBox || !rightColumn || formFieldGroups.length === 0) return;

            formFieldGroups.forEach(group => {
                const input = group.querySelector('input[data-info], textarea[data-info]');
                if (!input) return;

                // When the mouse enters the area of the form field group
                group.addEventListener('mouseenter', () => {
                    const infoText = input.dataset.info;
                    if (infoText) {
                        // 1. Update the box content
                        floatingBox.innerHTML = infoText;

                        // 2. Calculate the position based on the input element
                        const inputRect = input.getBoundingClientRect();
                        const rightColumnRect = rightColumn.getBoundingClientRect();
                        
                        // Position the box vertically aligned with the input field
                        const topPosition = inputRect.top - rightColumnRect.top;
                        
                        // 3. Apply the position and make it visible
                        floatingBox.style.top = `${topPosition}px`;
                        floatingBox.classList.add('visible');
                    }
                });

                // When the mouse leaves the area
                group.addEventListener('mouseleave', () => {
                    floatingBox.classList.remove('visible');
                });
            });
        });
    </script>

</body>
