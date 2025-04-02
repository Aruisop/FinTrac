<?php
session_start();
require 'db_config.php';

if (!isset($_SESSION["user_id"])) {
    die(json_encode(["success" => false, "message" => "Unauthorized"]));
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["description"], $data["amount"], $data["type"])) {
    die(json_encode(["success" => false, "message" => "Invalid data"]));
}

$user_id = $_SESSION["user_id"];
$description = $data["description"];
$amount = $data["amount"];
$type = $data["type"];

$stmt = $conn->prepare("INSERT INTO transactions (user_id, description, amount, type) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isds", $user_id, $description, $amount, $type);
$success = $stmt->execute();

echo json_encode(["success" => $success]);
$stmt->close();
$conn->close();
?>
