<?php
session_start();
require_once "database.php";

$redirectAfterDelete = isset($_POST["redirect"]);

function respondDelete($ok, $msg, $code = 200, $redir = false)
{
    if ($redir) {
        header("Location: videos.php");
        exit();
    }

    http_response_code($code);
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode(["success" => $ok, "message" => $msg]);
    exit();
}

if (!isset($_SESSION["user_id"])) {
    respondDelete(false, "Niet ingelogd.", 401, $redirectAfterDelete);
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respondDelete(false, "Ongeldige methode.", 405, $redirectAfterDelete);
}

$videoId = isset($_POST["video_id"]) ? (int) $_POST["video_id"] : 0;

if ($videoId <= 0) {
    respondDelete(
        false,
        "Geen geldige video geselecteerd.",
        400,
        $redirectAfterDelete,
    );
}

try {
    $stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ? LIMIT 1");
    $stmt->execute([$videoId]);
    $videoData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$videoData) {
        respondDelete(false, "Video niet gevonden.", 404, $redirectAfterDelete);
    }

    $isOwner = (int) $videoData["user_id"] === (int) $_SESSION["user_id"];
    $isAdmin =
        isset($_SESSION["user_role"]) && $_SESSION["user_role"] === "admin";

    if (!$isOwner && !$isAdmin) {
        respondDelete(
            false,
            "Je hebt geen rechten om deze video te verwijderen.",
            403,
            $redirectAfterDelete,
        );
    }

    $stmt = $pdo->prepare("DELETE FROM comments WHERE video_id = ?");
    $stmt->execute([$videoId]);

    $stmt = $pdo->prepare("DELETE FROM video_likes WHERE video_id = ?");
    $stmt->execute([$videoId]);

    $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
    $stmt->execute([$videoId]);

    if ($stmt->rowCount() < 1) {
        respondDelete(
            false,
            "Fout bij het verwijderen uit de database.",
            500,
            $redirectAfterDelete,
        );
    }

    $filePath = $videoData["file_path"];
    $absolutePath = __DIR__ . DIRECTORY_SEPARATOR . $filePath;

    if (!empty($filePath) && is_file($absolutePath) && !unlink($absolutePath)) {
        error_log("Videobestand kon niet worden verwijderd: " . $absolutePath);
    }

    respondDelete(
        true,
        "Video succesvol verwijderd!",
        200,
        $redirectAfterDelete,
    );
} catch (PDOException $e) {
    error_log("Database error tijdens verwijderen video: " . $e->getMessage());
    respondDelete(
        false,
        "Serverfout bij het verwijderen van de video.",
        500,
        $redirectAfterDelete,
    );
}
?>
