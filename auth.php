<?php
// StreamHive - Authenticatie (login, registratie, logout)
session_start();

// Zet JSON response header
header("Content-Type: application/json; charset=utf-8");

// Laad database verbinding
require_once "database.php";

// Verwerk CORS preflight verzoeken
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    http_response_code(200);
    exit();
}

// Bepaal welke actie uit te voeren
$action = isset($_GET["action"]) ? $_GET["action"] : null;

if (!$action) {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $action = isset($_POST["action"]) ? $_POST["action"] : null;
    }
}

// Controleer login status (GET or POST)
if ($action === "check") {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");

    if (isset($_SESSION["user_id"])) {
        $user = $userModel->getUserById($_SESSION["user_id"]);
        if ($user) {
            echo json_encode([
                "loggedIn" => true,
                "user" => [
                    "id" => $user["id"],
                    "name" => $user["name"],
                    "email" => $user["email"],
                    "role" => $user["role"],
                ],
            ]);
        } else {
            echo json_encode(["loggedIn" => false]);
        }
    } else {
        echo json_encode(["loggedIn" => false]);
    }
    exit();
}

// Verwerk logout
if ($action === "logout") {
    session_destroy();
    echo json_encode([
        "success" => true,
        "message" => "Succesvol uitgelogd!",
    ]);
    exit();
}

// Verwerk inloggen
if ($action === "login") {
    $email = isset($_POST["email"]) ? $_POST["email"] : "";
    $password = isset($_POST["password"]) ? $_POST["password"] : "";

    if (empty($email) || empty($password)) {
        echo json_encode([
            "success" => false,
            "message" => "Email en wachtwoord zijn verplicht!",
        ]);
        exit();
    }

    // Verifieer gebruiker gegevens
    $user = $userModel->authenticateUser($email, $password);

    if ($user) {
        // Zet sessie variabelen
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["user_name"] = $user["name"];
        $_SESSION["user_email"] = $user["email"];
        $_SESSION["user_role"] = $user["role"];

        echo json_encode([
            "success" => true,
            "message" => "Succesvol ingelogd!",
            "user" => [
                "id" => $user["id"],
                "name" => $user["name"],
                "email" => $user["email"],
                "role" => $user["role"],
            ],
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Email of wachtwoord is incorrect!",
        ]);
    }
    exit();
}

// Verwerk registratie
if ($action === "register") {
    $name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
    $email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
    $password = isset($_POST["password"]) ? $_POST["password"] : "";
    $password_confirm = isset($_POST["password_confirm"])
        ? $_POST["password_confirm"]
        : "";
    $role = "user";

    // Valideer invoer
    if (empty($name) || empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Alle velden zijn verplicht!",
        ]);
        exit();
    }

    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Wachtwoord moet minimaal 6 karakters lang zijn!",
        ]);
        exit();
    }

    if ($password !== $password_confirm) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Wachtwoorden komen niet overeen!",
        ]);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Email adres is niet geldig!",
        ]);
        exit();
    }

    // Controleer of gebruiker al bestaat
    if ($userModel->userExists($email)) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "Email adres is al geregistreerd!",
        ]);
        exit();
    }

    // Maak nieuwe gebruiker aan
    $result = $userModel->createUser($name, $email, $password, $role);

    if ($result) {
        echo json_encode([
            "success" => true,
            "message" => "Account succesvol aangemaakt! Je kunt nu inloggen.",
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" =>
                "Er is een fout opgetreden bij het aanmaken van het account. Controleer de database verbinding.",
        ]);
    }
    exit();
}

http_response_code(400);
echo json_encode([
    "success" => false,
    "message" => "Ongeldig verzoek!",
]);
?>
