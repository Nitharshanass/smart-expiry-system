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


// Get item details
$stmt = $conn->prepare(
    "SELECT * FROM items WHERE id = ? AND user_id = ?"
);

$stmt->bind_param("ii", $item_id, $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows != 1) {
    header("Location: dashboard.php");
    exit();
}

$item = $result->fetch_assoc();

$message = "";


// Update expiry date
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $new_expiry_date = $_POST["expiry_date"];

    if (empty($new_expiry_date)) {

        $message = "Please select a new expiry date.";

    } else {

        $update = $conn->prepare(
            "UPDATE items 
             SET expiry_date = ?
             WHERE id = ? AND user_id = ?"
        );

        $update->bind_param(
            "sii",
            $new_expiry_date,
            $item_id,
            $user_id
        );

        if ($update->execute()) {

            header("Location: dashboard.php");
            exit();

        } else {

            $message = "Renewal failed. Please try again.";

        }

        $update->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Renew Item - Smart Expiry System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-primary">

    <div class="container">

        <a class="navbar-brand" href="dashboard.php">
            Smart Expiry System
        </a>

        <a href="dashboard.php" class="btn btn-light btn-sm">
            Dashboard
        </a>

    </div>

</nav>


<div class="container mt-5">

    <div class="row justify-content-center">

        <div class="col-md-6">

            <div class="card shadow">

                <div class="card-body p-4">

                    <h2 class="text-center mb-4">
                        Renew Item
                    </h2>


                    <div class="mb-3">

                        <strong>Item Name:</strong>

                        <?php
                        echo htmlspecialchars($item["item_name"]);
                        ?>

                    </div>


                    <div class="mb-3">

                        <strong>Current Expiry Date:</strong>

                        <?php
                        echo htmlspecialchars($item["expiry_date"]);
                        ?>

                    </div>


                    <?php if (!empty($message)): ?>

                        <div class="alert alert-danger">

                            <?php
                            echo htmlspecialchars($message);
                            ?>

                        </div>

                    <?php endif; ?>


                    <form method="POST">

                        <div class="mb-3">

                            <label class="form-label">
                                New Expiry Date
                            </label>

                            <input
                                type="date"
                                name="expiry_date"
                                class="form-control"
                                required
                            >

                        </div>


                        <button
                            type="submit"
                            class="btn btn-success"
                        >
                            Renew Now
                        </button>

                        <a
                            href="dashboard.php"
                            class="btn btn-secondary"
                        >
                            Cancel
                        </a>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>