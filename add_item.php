<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION["user_id"];

    $item_name = trim($_POST["item_name"]);
    $category = trim($_POST["category"]);
    $expiry_date = $_POST["expiry_date"];
    $cost = $_POST["cost"];
    $notes = trim($_POST["notes"]);
    $reminder_days = $_POST["reminder_days"];

    // Check required fields
    if (empty($item_name) || empty($category) || empty($expiry_date)) {

        $message = "Please fill in all required fields.";

    } else {

       $stmt = $conn->prepare(
    "INSERT INTO items
    (user_id, item_name, category, expiry_date, cost, notes, reminder_days)
    VALUES (?, ?, ?, ?, ?, ?, ?)"
);

       $stmt->bind_param(
    "isssdsi",
    $user_id,
    $item_name,
    $category,
    $expiry_date,
    $cost,
    $notes,
    $reminder_days
);
        if ($stmt->execute()) {

            header("Location: dashboard.php");
            exit();

        } else {

            $message = "Failed to add item. Please try again.";

        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Item - Smart Expiry System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">


<!-- Navigation -->

<nav class="navbar navbar-dark bg-primary">

    <div class="container">

        <a class="navbar-brand" href="dashboard.php">
            Smart Expiry System
        </a>

        <a href="logout.php" class="btn btn-light btn-sm">
            Logout
        </a>

    </div>

</nav>


<!-- Main Content -->

<div class="container mt-5">

    <div class="row justify-content-center">

        <div class="col-md-7">

            <div class="card shadow">

                <div class="card-body p-4">

                    <h2 class="text-center mb-4">
                        Add New Item
                    </h2>


                    <?php if (!empty($message)): ?>

                        <div class="alert alert-danger">

                            <?php
                            echo htmlspecialchars($message);
                            ?>

                        </div>

                    <?php endif; ?>


                    <form method="POST" action="">


                        <!-- Item Name -->

                        <div class="mb-3">

                            <label class="form-label">
                                Item Name *
                            </label>

                            <input
                                type="text"
                                name="item_name"
                                class="form-control"
                                placeholder="Example: Driving Licence"
                                required
                            >

                        </div>


                        <!-- Category -->

                        <div class="mb-3">

                            <label class="form-label">
                                Category *
                            </label>

                            <select
                                name="category"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Category
                                </option>

                                <option value="Document">
                                    Document
                                </option>

                                <option value="Subscription">
                                    Subscription
                                </option>

                                <option value="Warranty">
                                    Warranty
                                </option>

                                <option value="Membership">
                                    Membership
                                </option>

                                <option value="Other">
                                    Other
                                </option>

                            </select>

                        </div>


                        <!-- Expiry Date -->

                        <div class="mb-3">

                            <label class="form-label">
                                Expiry Date *
                            </label>

                            <input
                                type="date"
                                name="expiry_date"
                                class="form-control"
                                required
                            >

                        </div>


                        <!-- Cost -->

                        <div class="mb-3">

                            <label class="form-label">
                                Cost / Renewal Amount
                            </label>

                            <input
                                type="number"
                                name="cost"
                                class="form-control"
                                placeholder="Example: 649"
                                step="0.01"
                                min="0"
                            >

                        </div>
<!-- Reminder Days -->

<div class="mb-3">

    <label class="form-label">
        Remind Me Before
    </label>

    <select
        name="reminder_days"
        class="form-select"
    >

        <option value="7">
            7 Days Before
        </option>

        <option value="15">
            15 Days Before
        </option>

        <option value="30" selected>
            30 Days Before
        </option>

        <option value="60">
            60 Days Before
        </option>

    </select>

</div>

                        <!-- Notes -->

                        <div class="mb-3">

                            <label class="form-label">
                                Notes
                            </label>

                            <textarea
                                name="notes"
                                class="form-control"
                                rows="4"
                                placeholder="Example: Renew online before expiry"
                            ></textarea>

                        </div>


                        <!-- Buttons -->

                        <div class="d-flex gap-2">

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Save Item
                            </button>

                            <a
                                href="dashboard.php"
                                class="btn btn-secondary"
                            >
                                Cancel
                            </a>

                        </div>


                    </form>

                </div>

            </div>

        </div>

    </div>

</div>


</body>

</html>