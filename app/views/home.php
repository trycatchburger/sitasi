<?php
ob_start();

// Fetch recent submissions for homepage preview
require_once __DIR__ . '/../../vendor/autoload.php';
use App\Models\Submission;

try {
    $submissionModel = new Submission();
    
    // Check if there's a search query
    $search = $_GET['search'] ?? '';
    
    if (!empty($search)) {
        // Search for recent submissions
        $recentSubmissions = $submissionModel->searchRecentApproved($search, 6);
    } else {
        // Get recent approved submissions
        $recentSubmissions = $submissionModel->findRecentApproved(6);
    }
} catch (Exception $e) {
    $recentSubmissions = [];
    $search = '';
}
?>

<?php
// Fetch recent journal submissions for homepage preview
try {
    $recentJournals = $submissionModel->findRecentApprovedJournals(6);
} catch (Exception $e) {
    $recentJournals = [];
}
?>

<?php include __DIR__ . '/hero.php'; ?>
<?php include __DIR__ . '/alur.php'; ?>
<?php include __DIR__ . '/unggah.php'; ?>
  

<style>
  html {
    scroll-behavior: smooth;
    scroll-padding-top: 300px;
  }
</style>



  <!-- Recent Theses Section -->
  <section class="w-full bg-white rounded-xl shadow-md p-6 mt-4">
  <div class="mb-10 text-center">
    
    <h2 class="text-3xl font-extrabold text-green-900 mb-2">Data Terbaru</h2>
    <p class="text-gray-600 text-sm">Jelajahi koleksi tugas akhir dan karya ilmiah yang baru disetujui</p>
  </div>

  <!-- Search Form -->
  <div class="mb-10 flex justify-center">
    <form method="GET" action="<?= url() ?>" class="flex w-full max-w-2xl shadow-sm rounded-full overflow-hidden border border-gray-300 bg-white">
      <input 
        type="text" 
        name="search" 
        value="<?= htmlspecialchars($search ?? '') ?>" 
        placeholder="Cari berdasarkan judul atau penulis..." 
        class="flex-grow px-6 py-3 text-sm focus:outline-none focus:ring-0"
      />
      <button 
        type="submit" 
        class="bg-green-900 text-white px-6 hover:bg-green-600 transition flex items-center justify-center"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <span class="ml-2 font-medium">Cari</span>
      </button>
    </form>
  </div>

  <!-- Recent List -->
<?php if (!empty($recentSubmissions)): ?>
  <div class="border-t border-b border-gray-200 py-4">
    <ul class="divide-y divide-gray-100">
      <?php foreach ($recentSubmissions as $submission): ?>
        <?php
          $nameParts = explode(' ', htmlspecialchars($submission['nama_mahasiswa']));
          $firstName = $nameParts[0];
          $lastName = count($nameParts) > 1 ? end($nameParts) : $firstName;
          $formattedName = $lastName . ', ' . $firstName;

          // Ambil tipe (kolom submission_type)
          $tipe = strtolower(trim($submission['submission_type'] ?? 'bachelor'));

          // Tentukan teks tipe dan buat label tipe yang berwarna hanya untuk kata tipe
          if ($tipe === 'bachelor') {
            $typeLabel = '<span class="text-green-700 font-semibold">Skripsi</span>';
            $jenisKarya = $typeLabel . ', STAIN Sultan Abdurrahman Kepulauan Riau.';
          } elseif ($tipe === 'master') {
            $typeLabel = '<span class="text-blue-700 font-semibold">Tesis</span>';
            $jenisKarya = $typeLabel . ', STAIN Sultan Abdurrahman Kepulauan Riau.';
          } elseif ($tipe === 'journal') {
            $typeLabel = '<span class="text-orange-700 font-semibold">Jurnal Ilmiah</span>';
            $jenisKarya = $typeLabel . ', STAIN Sultan Abdurrahman Kepulauan Riau.';
          } else {
            $typeLabel = '<span class="text-gray-700 font-semibold">Karya Ilmiah</span>';
            $jenisKarya = $typeLabel . ', STAIN Sultan Abdurrahman Kepulauan Riau.';
          }

          // Bentuk sitasi (sama seperti sebelumnya)
          $citation = $formattedName . '. (' . htmlspecialchars($submission['tahun_publikasi']) . '). ' .
                      '<a href="' . url('submission/detail/' . $submission['id']) . '" class="text-green-900 font-semibold hover:text-green-600 hover:underline">' .
                      htmlspecialchars($submission['judul_skripsi']) . '</a>. ' .
                      $jenisKarya;
        ?>
        <li class="py-4">
          <div class="text-gray-800 text-sm leading-relaxed">
            <?= $citation ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>



  <!-- Theses List -->


    <!-- View More -->
    <div class="mt-6 text-center">
      <a href="<?= url('submission/repository') ?>" class="inline-block px-5 py-2 border border-green-900 text-green-900 rounded-full hover:bg-green-600 hover:text-white transition">
        Lihat Semua
      </a>
    </div>

</section>

<!-- Success Popup -->
<?php if (isset($_SESSION['submission_success']) && $_SESSION['submission_success']): ?>
<div id="successPopup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
    <div class="text-center">
      <svg class="mx-auto h-12 w-12 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0118 0z" />
      </svg>
      <h3 class="mt-4 text-xl font-medium text-gray-900">Submission Successful!</h3>
      <p class="mt-2 text-gray-600">Your submission has been successfully submitted.</p>
      <div class="mt-6">
        <button id="closePopup" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none">
          OK
        </button>
      </div>
    </div>
  </div>

  <script>
    // Close popup when button is clicked
    document.getElementById('closePopup').addEventListener('click', function() {
      document.getElementById('successPopup').style.display = 'none';
    });
    
    // Close popup when clicking outside of it
    document.getElementById('successPopup').addEventListener('click', function(e) {
      if (e.target === this) {
        this.style.display = 'none';
      }
    });
  </script>
  <?php 
  // Remove the session variable after showing the popup
  unset($_SESSION['submission_success']);
  endif;
  ?>
</div>

  <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="fixed bottom-6 right-6 z-50 bg-green-600 hover:bg-green-700 text-white p-3 rounded-full shadow-lg" title="Back to Top">
    ↑
  </button>


<?php
$title = 'Sitasi | Portal Unggah Skripsi Mandiri';
$content = ob_get_clean();
require __DIR__ . '/main.php';
?>