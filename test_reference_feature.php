<?php
// Test script to verify the reference feature is working correctly

echo "Testing Reference Feature Implementation:\n";
echo "========================================\n\n";

// 1. Test database table exists
echo "1. Checking if user_references table exists...\n";
try {
    require_once __DIR__ . '/app/Models/Database.php';
    $database = \App\Models\Database::getInstance();
    $conn = $database->getConnection();
    
    $result = $conn->query("SHOW TABLES LIKE 'user_references'");
    if ($result && $result->num_rows > 0) {
        echo "   ✅ user_references table exists\n";
    } else {
        echo "   ❌ user_references table does not exist\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error checking database: " . $e->getMessage() . "\n";
}

// 2. Test models exist
echo "\n2. Checking if UserReference model exists...\n";
if (file_exists(__DIR__ . '/app/Models/UserReference.php')) {
    echo "   ✅ UserReference.php model exists\n";
} else {
    echo "   ❌ UserReference.php model does not exist\n";
}

if (file_exists(__DIR__ . '/app/Repositories/UserReferenceRepository.php')) {
    echo "   ✅ UserReferenceRepository.php exists\n";
} else {
    echo "   ❌ UserReferenceRepository.php does not exist\n";
}

// 3. Test controller methods exist
echo "\n3. Checking if SubmissionController has reference methods...\n";
$controllerContent = file_get_contents(__DIR__ . '/app/Controllers/SubmissionController.php');
if (strpos($controllerContent, 'toggleReference') !== false) {
    echo "   ✅ toggleReference method exists\n";
} else {
    echo "   ❌ toggleReference method does not exist\n";
}

if (strpos($controllerContent, 'getReferences') !== false) {
    echo "   ✅ getReferences method exists\n";
} else {
    echo "   ❌ getReferences method does not exist\n";
}

if (strpos($controllerContent, 'checkReference') !== false) {
    echo "   ✅ checkReference method exists\n";
} else {
    echo "   ❌ checkReference method does not exist\n";
}

// 4. Test views exist
echo "\n4. Checking if referensi view exists...\n";
if (file_exists(__DIR__ . '/app/views/referensi.php')) {
    echo "   ✅ referensi.php view exists\n";
} else {
    echo "   ❌ referensi.php view does not exist\n";
}

// 5. Test detail.php has the reference button
echo "\n5. Checking if detail.php has reference button...\n";
$detailContent = file_get_contents(__DIR__ . '/app/views/detail.php');
if (strpos($detailContent, 'addToReferenceBtn') !== false) {
    echo "   ✅ Add to Reference button exists in detail.php\n";
} else {
    echo "   ❌ Add to Reference button does not exist in detail.php\n";
}

// 6. Test journal_detail.php has the reference button
echo "\n6. Checking if journal_detail.php has reference button...\n";
if (file_exists(__DIR__ . '/app/views/journal_detail.php')) {
    $journalDetailContent = file_get_contents(__DIR__ . '/app/views/journal_detail.php');
    if (strpos($journalDetailContent, 'addToReferenceBtn') !== false) {
        echo "   ✅ Add to Reference button exists in journal_detail.php\n";
    } else {
        echo "   ❌ Add to Reference button does not exist in journal_detail.php\n";
    }
} else {
    echo "   ℹ️  journal_detail.php does not exist (this may be normal)\n";
}

// 7. Test navigation has referensi link
echo "\n7. Checking if navigation has Referensi link...\n";
$mainContent = file_get_contents(__DIR__ . '/app/views/main.php');
if (strpos($mainContent, 'url(\'referensi\')') !== false || strpos($mainContent, 'referensi') !== false) {
    echo "   ✅ Referensi link exists in navigation\n";
} else {
    echo "   ❌ Referensi link does not exist in navigation\n";
}

echo "\nTest completed!\n";
echo "\nTo fully test the feature, you would need to:\n";
echo "1. Log in as a user\n";
echo "2. Go to a submission detail page\n";
echo "3. Click 'Tambahkan ke Referensi' button\n";
echo "4. Navigate to 'Referensi' menu to see the saved reference\n";
echo "5. Test removing a reference from the Referensi page\n";