<?php ob_start(); ?>
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Selamat Datang, <?= htmlspecialchars($user['name']) ?></h1>
        <p class="text-gray-60">ID Anggota: <?= htmlspecialchars($user['library_card_number']) ?></p>
        <p class="text-gray-600">Status: <span class="font-semibold"><?= htmlspecialchars($user['status_display']) ?></span></p>
    </div>

    <?php if (isset($_SESSION['potential_submission_matches']) && !empty($_SESSION['potential_submission_matches'])): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.72-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 012 0zm-1-8a1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>We found <?= count($_SESSION['potential_submission_matches']) ?> submission(s) that might belong to you.</strong>
                        <a href="<?= url('user/confirmSubmissions') ?>" class="font-medium underline text-yellow-70 hover:text-yellow-600">
                            Click here to review and confirm
                        </a>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <?php unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-80">Your Submissions</h2>

    </div>

    <?php if (empty($submissions)): ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No submissions yet</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by submitting your first thesis or paper.</p>
            <div class="mt-6">
                <?php
                $submitUrl = '';
                switch($user['status_display']) {
                    case 'Mahasiswa Program Magister':
                        $submitUrl = url('submission/tesis');
                        break;
                    case 'Dosen':
                        $submitUrl = url('submission/jurnal');
                        break;
                    case 'Mahasiswa Program Sarjana':
                    default:
                        $submitUrl = url('submission/skripsi');
                        break;
                }
                ?>
                <a href="<?= $submitUrl ?>" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-70">
                    Submit New
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white shadow-md rounded-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[200px]">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($submissions as $submission): ?>
                    <tr>
                        <td class="px-6 py-4 max-w-xs">
                            <div class="text-sm font-medium text-gray-900 break-words"><?= htmlspecialchars($submission['judul_skripsi']) ?></div>
                            <div class="text-sm text-gray-500"><?= htmlspecialchars($submission['nama_mahasiswa']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-normal">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-10 text-blue-800 break-words">
                                <?php
                                // Determine submission type based on available data
                                $displayType = $submission['submission_type'] ?? '';
                                
                                // If submission_type is empty, try to determine from other fields
                                if (empty($displayType)) {
                                    // Check if this looks like a journal submission based on multiple authors
                                    if (!empty($submission['author_2']) || !empty($submission['author_3']) ||
                                        !empty($submission['author_4']) || !empty($submission['author_5']) ||
                                        !empty($submission['abstract'])) {
                                        $displayType = 'journal';
                                    }
                                    // Check if the user is a Dosen which typically submits journals
                                    elseif (!empty($submission['tipe_member']) && $submission['tipe_member'] === 'Dosen') {
                                        $displayType = 'journal';
                                    }
                                    elseif (!empty($submission['nim'])) {
                                        $displayType = 'bachelor'; // Has NIM, likely a bachelor thesis
                                    } else {
                                        $displayType = 'skripsi'; // Default fallback
                                    }
                                }
                                echo htmlspecialchars(ucfirst($displayType));
                                ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $statusClass = '';
                            switch ($submission['status']) {
                                case 'Diterima':
                                    $statusClass = 'bg-green-100 text-green-800';
                                    break;
                                case 'Ditolak':
                                    $statusClass = 'bg-red-100 text-red-800';
                                    break;
                                case 'Digantikan':
                                    $statusClass = 'bg-yellow-100 text-yellow-800';
                                    break;
                                default:
                                    $statusClass = 'bg-gray-100 text-gray-800';
                            }
                            ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass ?> break-words">
                                <?= htmlspecialchars($submission['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= date('M j, Y', strtotime($submission['created_at'])) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex flex-col">
                                <a href="<?= url('user/submission/' . $submission['id']) ?>" class="text-blue-600 hover:text-blue-90 mb-1 block">View Details</a>
                                <?php if ($submission['status'] === 'Ditolak' || $submission['is_resubmission']): ?>
                                    <a href="<?= url('user/resubmit/' . $submission['id']) ?>" class="text-green-600 hover:text-green-900 block">Resubmit</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
$title = 'User Dashboard | Library System';
$content = ob_get_clean();
require __DIR__ . '/main.php';
?>