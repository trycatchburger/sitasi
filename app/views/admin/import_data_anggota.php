<?php ob_start(); ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Import Data Anggota</h1>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <?php unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <?php unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <form action="<?= url('admin/prosesImportAnggota') ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

        <div>
            <label for="file_excel" class="block text-sm font-medium text-gray-700 mb-1">Excel File</label>
            <input type="file" name="file_excel" id="file_excel" accept=".xlsx,.xls" required
                   class="form-control w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
        </div>

        <div class="pt-4">
            <button type="submit"
                    class="w-full flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-md transition duration-200">
                Import Data
            </button>
        </div>
    </form>

    <div class="mt-6 text-center">
        <a href="<?= url('admin/adminManagement') ?>" class="text-green-600 hover:text-green-800 text-sm font-medium">
            ← Back to Admin Management
        </a>
    </div>
    
    <!-- Display imported members data -->
    <div class="mt-10">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 space-y-4 md:space-y-0">
            <h2 class="text-xl font-semibold text-gray-800">Imported Members Data</h2>
            <!-- Search Form -->
            <form method="GET" class="flex space-x-2 w-full md:w-auto">
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort ?? 'id_member') ?>">
                <input type="hidden" name="order" value="<?= htmlspecialchars($order ?? 'desc') ?>">
                <input type="hidden" name="page" value="<?= $currentPage ?? 1 ?>">
                <input type="text" name="search" placeholder="Search members..." value="<?= htmlspecialchars($search ?? '') ?>"
                       class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent w-full md:w-64">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-200">
                    Search
                </button>
                <?php if (!empty($search)): ?>
                    <a href="?sort=id_member&order=desc&page=1" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition duration-200">
                        Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>
        <?php if (isset($members) && count($members) > 0): ?>
            <div class="overflow-x-auto bg-white rounded-lg border-gray-200 shadow-sm">
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">
                                <a href="?search=<?= urlencode($search ?? '') ?>&page=<?= $currentPage ?? 1 ?>&sort=id_member&order=<?= ($sort === 'id_member' && $order === 'asc') ? 'desc' : 'asc' ?>" class="flex items-center">
                                    ID
                                    <?php if ($sort === 'id_member'): ?>
                                        <span class="ml-1"><?= $order === 'asc' ? '↑' : '↓' ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/12">
                                <a href="?search=<?= urlencode($search ?? '') ?>&page=<?= $currentPage ?? 1 ?>&sort=nama&order=<?= ($sort === 'nama' && $order === 'asc') ? 'desc' : 'asc' ?>" class="flex items-center">
                                    Name
                                    <?php if ($sort === 'nama'): ?>
                                        <span class="ml-1"><?= $order === 'asc' ? '↑' : '↓' ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/12">
                                <a href="?search=<?= urlencode($search ?? '') ?>&page=<?= $currentPage ?? 1 ?>&sort=prodi&order=<?= ($sort === 'prodi' && $order === 'asc') ? 'desc' : 'asc' ?>" class="flex items-center">
                                    Prodi
                                    <?php if ($sort === 'prodi'): ?>
                                        <span class="ml-1"><?= $order === 'asc' ? '↑' : '↓' ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/12">
                                <a href="?search=<?= urlencode($search ?? '') ?>&page=<?= $currentPage ?? 1 ?>&sort=email&order=<?= ($sort === 'email' && $order === 'asc') ? 'desc' : 'asc' ?>" class="flex items-center">
                                    Email
                                    <?php if ($sort === 'email'): ?>
                                        <span class="ml-1"><?= $order === 'asc' ? '↑' : '↓' ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">
                                <a href="?search=<?= urlencode($search ?? '') ?>&page=<?= $currentPage ?? 1 ?>&sort=no_hp&order=<?= ($sort === 'no_hp' && $order === 'asc') ? 'desc' : 'asc' ?>" class="flex items-center">
                                    Phone
                                    <?php if ($sort === 'no_hp'): ?>
                                        <span class="ml-1"><?= $order === 'asc' ? '↑' : '↓' ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">
                                <a href="?search=<?= urlencode($search ?? '') ?>&page=<?= $currentPage ?? 1 ?>&sort=tipe_member&order=<?= ($sort === 'tipe_member' && $order === 'asc') ? 'desc' : 'asc' ?>" class="flex items-center">
                                    Type
                                    <?php if ($sort === 'tipe_member'): ?>
                                        <span class="ml-1"><?= $order === 'asc' ? '↑' : '↓' ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">
                                <a href="?search=<?= urlencode($search ?? '') ?>&page=<?= $currentPage ?? 1 ?>&sort=member_since&order=<?= ($sort === 'member_since' && $order === 'asc') ? 'desc' : 'asc' ?>" class="flex items-center">
                                    Since
                                    <?php if ($sort === 'member_since'): ?>
                                        <span class="ml-1"><?= $order === 'asc' ? '↑' : '↓' ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">
                                <a href="?search=<?= urlencode($search ?? '') ?>&page=<?= $currentPage ?? 1 ?>&sort=expired&order=<?= ($sort === 'expired' && $order === 'asc') ? 'desc' : 'asc' ?>" class="flex items-center">
                                    Expired
                                    <?php if ($sort === 'expired'): ?>
                                        <span class="ml-1"><?= $order === 'asc' ? '↑' : '↓' ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($members as $member): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-3 text-sm text-gray-900 max-w-xs truncate"><?= htmlspecialchars($member['id_member']) ?></td>
                                <td class="px-3 py-3 text-sm text-gray-900 max-w-xs truncate"><?= htmlspecialchars($member['nama']) ?></td>
                                <td class="px-3 py-3 text-sm text-gray-900 max-w-xs truncate"><?= htmlspecialchars($member['prodi']) ?></td>
                                <td class="px-3 py-3 text-sm text-gray-900 max-w-xs truncate"><?= htmlspecialchars($member['email']) ?></td>
                                <td class="px-3 py-3 text-sm text-gray-900 max-w-xs truncate"><?= htmlspecialchars($member['no_hp']) ?></td>
                                <td class="px-3 py-3 text-sm text-gray-900 max-w-xs truncate"><?= htmlspecialchars($member['tipe_member']) ?></td>
                                <td class="px-3 py-3 text-sm text-gray-900 max-w-xs truncate"><?= htmlspecialchars($member['member_since']) ?></td>
                                <td class="px-3 py-3 text-sm text-gray-900 max-w-xs truncate"><?= htmlspecialchars($member['expired']) ?></td>
                                <td class="px-3 py-3 text-sm font-medium">
                                    <div class="flex flex-col space-y-1 md:flex-row md:space-y-0 md:space-x-1">
                                        <button class="text-blue-600 hover:text-blue-900 text-xs md:text-sm" onclick="editMember('<?= $member['id_member'] ?>')">Edit</button>
                                        <button class="text-yellow-600 hover:text-yellow-900 text-xs md:text-sm" onclick="suspendMember('<?= $member['id_member'] ?>')">Suspend</button>
                                        <button class="text-red-600 hover:text-red-900 text-xs md:text-sm" onclick="deleteMember('<?= $member['id_member'] ?>')">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if (isset($totalPages) && $totalPages > 1): ?>
            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <span class="font-medium"><?= ($currentPage - 1) * 10 + 1 ?></span> to
                    <span class="font-medium"><?= min($currentPage * 10, $totalCount) ?></span> of
                    <span class="font-medium"><?= $totalCount ?></span> results
                </div>
                <div class="flex space-x-2">
                    <?php if ($currentPage > 1): ?>
                        <a href="?search=<?= urlencode($search ?? '') ?>&sort=<?= $sort ?? 'id_member' ?>&order=<?= $order ?? 'desc' ?>&page=<?= $currentPage - 1 ?>" class="px-3 py-1 rounded-md bg-white border border-gray-30 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i >= max(1, $currentPage - 2) && $i <= min($totalPages, $currentPage + 2)): ?>
                            <a href="?search=<?= urlencode($search ?? '') ?>&sort=<?= $sort ?? 'id_member' ?>&order=<?= $order ?? 'desc' ?>&page=<?= $i ?>" class="px-3 py-1 rounded-md <?php echo ($i == $currentPage) ? 'bg-green-600 text-white' : 'bg-white border border-gray-30 text-gray-700 hover:bg-gray-50'; ?> text-sm font-medium"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?search=<?= urlencode($search ?? '') ?>&sort=<?= $sort ?? 'id_member' ?>&order=<?= $order ?? 'desc' ?>&page=<?= $currentPage + 1 ?>" class="px-3 py-1 rounded-md bg-white border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="mt-4 text-sm text-gray-600 text-center">
                Showing <?= count($members) ?> of <?= $totalCount ?> member(s) in the database
            </div>
        <?php else: ?>
            <div class="text-center py-6 text-gray-500">
                No members data found. Please import data using the form above.
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Edit Member Modal -->
    <div id="editMemberModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center pb-3 border-b">
                    <h3 class="text-lg font-semibold text-gray-800">Edit Member</h3>
                    <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-70">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form id="editMemberForm" method="POST" class="mt-4">
                    <input type="hidden" name="id_member" id="edit_id_member" value="">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    
                    <div class="mb-4">
                        <label for="edit_nama" class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="nama" id="edit_nama" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>
                    
                    <div class="mb-4">
                        <label for="edit_prodi" class="block text-sm font-medium text-gray-700">Prodi</label>
                        <input type="text" name="prodi" id="edit_prodi" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>
                    
                    <div class="mb-4">
                        <label for="edit_email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="edit_email" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>
                    
                    <div class="mb-4">
                        <label for="edit_no_hp" class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="text" name="no_hp" id="edit_no_hp" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-50 sm:text-sm">
                    </div>
                    
                    <div class="mb-4">
                        <label for="edit_tipe_member" class="block text-sm font-medium text-gray-700">Member Type</label>
                        <input type="text" name="tipe_member" id="edit_tipe_member" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>
                    
                    <div class="mb-4">
                        <label for="edit_member_since" class="block text-sm font-medium text-gray-700">Member Since</label>
                        <input type="date" name="member_since" id="edit_member_since" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>
                    
                    <div class="mb-4">
                        <label for="edit_expired" class="block text-sm font-medium text-gray-700">Expired</label>
                        <input type="date" name="expired" id="edit_expired" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition duration-200">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-200">
                            Update Member
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editMember(id) {
            // Fetch member data via AJAX
            fetch('<?= url('admin/editMember') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id_member=' + encodeURIComponent(id) + '&csrf_token=<?= $csrf_token ?? '' ?>'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('edit_id_member').value = data.member.id_member;
                    document.getElementById('edit_nama').value = data.member.nama;
                    document.getElementById('edit_prodi').value = data.member.prodi;
                    document.getElementById('edit_email').value = data.member.email;
                    document.getElementById('edit_no_hp').value = data.member.no_hp;
                    document.getElementById('edit_tipe_member').value = data.member.tipe_member;
                    document.getElementById('edit_member_since').value = formatDateForInput(data.member.member_since);
                    document.getElementById('edit_expired').value = formatDateForInput(data.member.expired);
                    
                    document.getElementById('editMemberModal').classList.remove('hidden');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while fetching member data.');
            });
        }
        
        function closeEditModal() {
            document.getElementById('editMemberModal').classList.add('hidden');
        }
        
        // Handle form submission for edit
        document.getElementById('editMemberForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const params = new URLSearchParams(formData).toString();
            
            fetch('<?= url('admin/updateMember') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    closeEditModal();
                    location.reload(); // Reload the page to see updated data
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating member data.');
            });
        });
        
        function suspendMember(id) {
            if (confirm('Are you sure you want to suspend member with ID: ' + id + '?')) {
                fetch('<?= url('admin/suspendMember') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id_member=' + encodeURIComponent(id) + '&csrf_token=<?= $csrf_token ?? '' ?>'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload(); // Reload the page to see updated data
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while suspending member.');
                });
            }
        }
        
        function deleteMember(id) {
            if (confirm('Are you sure you want to delete member with ID: ' + id + '?')) {
                fetch('<?= url('admin/deleteMember') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id_member=' + encodeURIComponent(id) + '&csrf_token=<?= $csrf_token ?? '' ?>'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload(); // Reload the page to see updated data
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting member.');
                });
            }
        }
        
        function formatDateForInput(dateString) {
            if (!dateString || dateString === '000-0-00' || dateString === '000-00-00 00:00:00') {
                return '';
            }
            
            // Parse the date string (could be in various formats)
            const date = new Date(dateString);
            
            // Check if the date is valid
            if (isNaN(date.getTime())) {
                return '';
            }
            
            // Format as YYYY-MM-DD for the date input
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            
            return `${year}-${month}-${day}`;
        }
    </script>
</div>

<?php
$title = 'Import Data Anggota | Admin Dashboard';
$content = ob_get_clean();
require __DIR__ . '/../main.php';
?>