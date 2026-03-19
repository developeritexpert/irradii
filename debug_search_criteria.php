<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=irradi_db', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Find the user ID for rahuloffice@sagmetic.com
$stmt = $pdo->prepare("SELECT id FROM tbl_users WHERE username = ?");
$stmt->execute(['rahuloffice@sagmetic.com']);
$userId = $stmt->fetchColumn();

if (!$userId) {
    echo "User not found by username, checking all users with saved searches...\n";
    $userId = $pdo->query("SELECT user_id FROM tbl_saved_searches LIMIT 1")->fetchColumn();
}

if (!$userId) die("No user with saved searches found.\n");

echo "Using User ID: $userId\n\n";

$stmt = $pdo->prepare("SELECT id, name, created_at FROM tbl_saved_searches WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$userId]);
$searches = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($searches as $s) {
    echo "\n--- #{$s['id']}: \"{$s['name']}\" ---\n";
    
    $stmt2 = $pdo->prepare("SELECT attr_name, attr_value FROM tbl_saved_search_criteria WHERE saved_search_id = ? ORDER BY id");
    $stmt2->execute([$s['id']]);
    $criteria = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($criteria as $c) {
        $val = @unserialize($c['attr_value']);
        if ($val === false && $c['attr_value'] !== 'b:0;') {
            $val = $c['attr_value'];
        }
        $display = is_array($val) ? json_encode($val) : (string)$val;
        echo "  {$c['attr_name']} = {$display}\n";
    }
}
