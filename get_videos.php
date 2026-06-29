<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

require_once "database.php";

try {
    $stmt = $pdo->query("
        SELECT
            v.*,
            u.name AS author_name,
            (SELECT COUNT(*) FROM video_likes vl WHERE vl.video_id = v.id) AS likes_count,
            (SELECT COUNT(*) FROM comments c WHERE c.video_id = v.id) AS comments_count
        FROM videos v
        JOIN users u ON v.user_id = u.id
        ORDER BY v.upload_date DESC
    ");

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    error_log('Kon video\'s niet ophalen: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => 'Kon video\'s niet ophalen uit de database.']);
}
?>
