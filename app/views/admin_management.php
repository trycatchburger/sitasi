<?php ob_start(); ?>

<div class="px-4 py-4">
  <div class="mb-4">
    <h1 class="text-2xl font-bold text-gray-800">Manajemen Admin</h1>
    <p class="text-gray-600 mt-1">Kelola Data Admin</p>
  </div>
  
  <?php if (isset($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
      <p><?php echo htmlspecialchars($error); ?></p>
    </div>
  <?php endif; ?>
  
  <div class="mb-6 flex flex-wrap gap-2">
    <a href="<?= url('admin/create') ?>" class="btn btn-primary text-white">
      <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 018 0zM3 20a6 6 0 1112 0v1H3v-1z"></path>
      </svg>
      Buat Admin Baru
    </a>
     <a href="<?= url('admin/importDataAnggota') ?>" class="btn btn-primary text-white flex items-center px-4 py-2 rounded-lg shadow-sm hover:bg-blue-600 transition">
      <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 018 0zM3 20a6 6 0 1112 0v1H3v-1z"></path>
      </svg>
      Import Data Anggota
    </a>
  </div>
  
  <div class="card">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Pengguna</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat Pada</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php if (empty($admins)): ?>
            <tr>
              <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                No admins found.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($admins as $admin): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($admin['username']) ?></div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <?= format_datetime($admin['created_at'], 'd M Y H:i') ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                    <form action="<?= url('admin/deleteAdmin') ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this admin user?');">
                      
                      <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                      <button type="submit" class="btn btn-danger btn-sm">
                        <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete
                      </button>
                    </form>
                  <?php else: ?>
                    <span class="text-gray-40">Pengguna Saat ini</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>


<?php
$title = 'Manajemen Admin | Portal Unggah Skripsi Mandiri';
$content = ob_get_clean();
require __DIR__ . '/main.php';
?>
