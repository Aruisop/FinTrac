<?php
session_start();
require 'db_config.php';

if (!isset($_SESSION["user_id"])) {
    die(json_encode(["success" => false, "message" => "Unauthorized"]));
}

$user_id = $_SESSION["user_id"];
$stmt = $conn->prepare("SELECT description, amount, type FROM transactions WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$transactions = [];

while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}

echo json_encode(["success" => true, "transactions" => $transactions]);
$stmt->close();
$conn->close();
?>
