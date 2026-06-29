<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Je moet ingelogd zijn om een video te uploaden.",
    ]);
    exit();
}

require_once "database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Ongeldig verzoek."]);
    exit();
}

$title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
$description = isset($_POST["description"]) ? trim($_POST["description"]) : "";

if ($title === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Videotitel is verplicht!",
    ]);
    exit();
}

if (!isset($_FILES["fileToUpload"])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Er is geen videobestand ontvangen.",
    ]);
    exit();
}

$file = $_FILES["fileToUpload"];

if ($file["error"] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => "Het videobestand is te groot voor de server.",
        UPLOAD_ERR_FORM_SIZE => "Het videobestand is te groot voor het formulier.",
        UPLOAD_ERR_PARTIAL => "Het bestand is maar gedeeltelijk geüpload.",
        UPLOAD_ERR_NO_FILE => "Er is geen bestand meegestuurd.",
        UPLOAD_ERR_NO_TMP_DIR => "De tijdelijke uploadmap ontbreekt op de server.",
        UPLOAD_ERR_CANT_WRITE => "Het bestand kon niet naar schijf worden geschreven.",
        UPLOAD_ERR_EXTENSION => "Een PHP-extensie heeft de upload gestopt.",
    ];

    $message = isset($errorMessages[$file["error"]])
        ? $errorMessages[$file["error"]]
        : "Onbekende uploadfout.";
    http_response_code(400);
    echo json_encode(["success" => false, "message" => $message]);
    exit();
}

$allowedExtensions = ["mp4", "webm", "ogg"];
$fileExtension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

if (!in_array($fileExtension, $allowedExtensions, true)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Alleen MP4, WebM en Ogg video bestanden zijn toegestaan.",
    ]);
    exit();
}

$uploadDir = __DIR__ . DIRECTORY_SEPARATOR . "uploads";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$newFileName = uniqid("vid_", true) . "_" . time() . "." . $fileExtension;
$absoluteTargetPath = $uploadDir . DIRECTORY_SEPARATOR . $newFileName;
$relativeTargetPath = "uploads/" . $newFileName;

if (!move_uploaded_file($file["tmp_name"], $absoluteTargetPath)) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Fout bij het opslaan van het videobestand op de server.",
    ]);
    exit();
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO videos (user_id, title, description, file_path) VALUES (?, ?, ?, ?)",
    );
    $stmt->execute([
        $_SESSION["user_id"],
        $title,
        $description,
        $relativeTargetPath,
    ]);
    $videoId = (int) $pdo->lastInsertId();

    echo json_encode([
        "success" => true,
        "message" => "Video succesvol geüpload en opgeslagen!",
        "video_id" => $videoId,
        "file_path" => $relativeTargetPath,
    ]);
} catch (PDOException $e) {
    if (is_file($absoluteTargetPath)) {
        unlink($absoluteTargetPath);
    }

    error_log("Database error tijdens upload: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Er is een serverfout opgetreden bij het opslaan.",
    ]);
}
?>
