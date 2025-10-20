<?php ob_start(); ?>

<div class="container mx-auto px-4 py-8 max-w-7xl">
  <div class="mb-10 text-center">
    <h1 class="text-4xl font-bold text-gray-900 mb-3">Repository Layout Comparison</h1>
    <p class="text-lg text-gray-600 max-w-3xl mx-auto">Compare the current repository layout with the proposed improved version</p>
  </div>
  
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Current Version -->
    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Current Layout</h2>
        <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-medium rounded-full">Before</span>
      </div>
      
      <div class="space-y-6">
        <!-- Current Header -->
        <div class="text-center mb-6">
          <h3 class="text-xl font-semibold text-gray-800 mb-2">Thesis Repository</h3>
          <p class="text-gray-600">Browse and access approved undergraduate theses from STAI Sultan Abdurrahman.</p>
        </div>
        
        <!-- Current Filter Section -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
          <h4 class="font-medium text-gray-800 mb-3 flex items-center">
            <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
            </svg>
            Filter Theses
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
            <div class="md:col-span-5">
              <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
              <input type="text" placeholder="Search by title, author..." class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
            </div>
            <div class="md:col-span-3">
              <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
              <select class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                <option>All Years</option>
                <option>2023</option>
                <option>2022</option>
              </select>
            </div>
            <div class="md:col-span-3">
              <label class="block text-sm font-medium text-gray-700 mb-1">Program</label>
              <select class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                <option>All Programs</option>
                <option>Computer Science</option>
                <option>Business</option>
              </select>
            </div>
            <div class="md:col-span-12 flex justify-end space-x-2 pt-2">
              <button class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm">Filter</button>
              <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm">Clear</button>
            </div>
          </div>
        
        <!-- Current Theses List -->
        <div class="space-y-4">
          <h4 class="font-medium text-gray-800">Theses List</h4>
          <div class="border border-gray-200 rounded-lg divide-y divide-gray-200">
            <div class="p-4 hover:bg-gray-50">
              <div class="font-medium">Smith, John. (2023). <span class="text-blue-600">Machine Learning Applications</span>. S1 Thesis, STAI Sultan Abdurrahman.</div>
              <div class="text-sm text-gray-500 mt-1 flex flex-wrap items-center gap-2">
                <span>Computer Science</span>
                <span>NIM: 123456789</span>
              </div>
            </div>
            <div class="p-4 hover:bg-gray-50">
              <div class="font-medium">Doe, Jane. (2022). <span class="text-blue-600">Business Strategy Analysis</span>. S1 Thesis, STAI Sultan Abdurrahman.</div>
              <div class="text-sm text-gray-500 mt-1 flex flex-wrap items-center gap-2">
                <span>Business</span>
                <span>NIM: 987654321</span>
              </div>
            <div class="p-4 hover:bg-gray-50">
              <div class="font-medium">Brown, Robert. (2023). <span class="text-blue-600">Data Visualization Techniques</span>. S1 Thesis, STAI Sultan Abdurrahman.</div>
              <div class="text-sm text-gray-500 mt-1 flex flex-wrap items-center gap-2">
                <span>Computer Science</span>
                <span>NIM: 456789123</span>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Current Empty Space Visualization -->
        <div class="mt-8 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
          <h4 class="font-medium text-yellow-800 mb-2 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.33-2.694-1.333-3.464 0L3.34 16c-.77 1.33.192 3 1.732 3z"></path>
            </svg>
            Unused Space
          </h4>
          <p class="text-yellow-700 text-sm">Notice the empty space on the right side due to the narrow container width and single-column layout.</p>
        </div>
      </div>
    
    <!-- Improved Version -->
    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Improved Layout</h2>
        <span class="px-3 py-1 bg-green-10 text-green-800 text-sm font-medium rounded-full">After</span>
      </div>
      
      <div class="space-y-6">
        <!-- Improved Header -->
        <div class="text-center mb-6">
          <h3 class="text-xl font-semibold text-gray-800 mb-2">Thesis Repository</h3>
          <p class="text-gray-600">Browse and access approved undergraduate theses from STAI Sultan Abdurrahman.</p>
        </div>
        
        <!-- Improved Filter Section -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
          <h4 class="font-medium text-gray-800 mb-3 flex items-center">
            <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
            </svg>
            Filter Theses
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
              <input type="text" placeholder="Title, author, keywords..." class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
              <select class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                <option>All Years</option>
                <option>2023</option>
                <option>2022</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Program</label>
              <select class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                <option>All Programs</option>
                <option>Computer Science</option>
                <option>Business</option>
              </select>
            </div>
            <div class="flex items-end">
              <div class="flex space-x-2 w-full">
                <button class="flex-1 px-3 py-2 bg-blue-600 text-white rounded-md text-sm">Filter</button>
                <button class="px-3 py-2 bg-gray-200 text-gray-700 rounded-md text-sm">Clear</button>
              </div>
            </div>
          </div>
        
        <!-- Improved Theses List -->
        <div class="space-y-4">
          <h4 class="font-medium text-gray-800">Theses List</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
              <div class="font-medium text-sm">Smith, John. (2023). <span class="text-blue-600">Machine Learning Applications</span>. S1 Thesis, STAI Sultan Abdurrahman.</div>
              <div class="text-xs text-gray-500 mt-2 flex flex-wrap items-center gap-2">
                <span>Computer Science</span>
                <span>NIM: 123456789</span>
              </div>
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
              <div class="font-medium text-sm">Doe, Jane. (2022). <span class="text-blue-600">Business Strategy Analysis</span>. S1 Thesis, STAI Sultan Abdurrahman.</div>
              <div class="text-xs text-gray-500 mt-2 flex flex-wrap items-center gap-2">
                <span>Business</span>
                <span>NIM: 987654321</span>
              </div>
            </div>
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
              <div class="font-medium text-sm">Brown, Robert. (2023). <span class="text-blue-600">Data Visualization Techniques</span>. S1 Thesis, STAI Sultan Abdurrahman.</div>
              <div class="text-xs text-gray-500 mt-2 flex flex-wrap items-center gap-2">
                <span>Computer Science</span>
                <span>NIM: 456789123</span>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Improvement Benefits -->
        <div class="mt-8 p-4 bg-green-50 border border-green-20 rounded-lg">
          <h4 class="font-medium text-green-800 mb-2 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Improvements
          </h4>
          <ul class="text-green-700 text-sm list-disc pl-5 space-y-1">
            <li>Wider container (max-w-7xl) utilizes more screen space</li>
            <li>Multi-column grid layout (1→3 columns) fills empty areas</li>
            <li>Compact filter section with better organization</li>
            <li>Card-based design with hover effects for better UX</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
  
  <div class="mt-10 text-center">
    <a href="<?= url('submission/repository') ?>" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
      </svg>
      Back to Repository
    </a>
  </div>
</div>

<?php
$title = 'Repository Layout Comparison - University Thesis Submission System';
$content = ob_get_clean();
require __DIR__ . '/main.php';
?>