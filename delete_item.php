<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$user_id = $_SESSION["user_id"];

if (!isset($_GET["id"])) {
    header("Location: dashboard.php");
    exit();
}

$item_id = $_GET["id"];

$stmt = $conn->prepare(
    "DELETE FROM items WHERE id = ? AND user_id = ?"
);

$stmt->bind_param("ii", $item_id, $user_id);

$stmt->execute();

$stmt->close();

header("Location: dashboard.php");

exit();

?>