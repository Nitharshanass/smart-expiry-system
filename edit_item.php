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


// Get existing item
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


// Update item
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $item_name = trim($_POST["item_name"]);
    $category = trim($_POST["category"]);
    $expiry_date = $_POST["expiry_date"];
    $cost = $_POST["cost"];
    $notes = trim($_POST["notes"]);


    if (
        empty($item_name) ||
        empty($category) ||
        empty($expiry_date)
    ) {

        $message = "Please fill in all required fields.";

    } else {

        $update = $conn->prepare(
            "UPDATE items
             SET item_name = ?,
                 category = ?,
                 expiry_date = ?,
                 cost = ?,
                 notes = ?
             WHERE id = ? AND user_id = ?"
        );

        $update->bind_param(
            "sssdsii",
            $item_name,
            $category,
            $expiry_date,
            $cost,
            $notes,
            $item_id,
            $user_id
        );

        if ($update->execute()) {

            header("Location: dashboard.php");
            exit();

        } else {

            $message = "Failed to update item.";

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

    <title>Edit Item - Smart Expiry System</title>

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

        <a href="logout.php" class="btn btn-light btn-sm">
            Logout
        </a>

    </div>

</nav>


<div class="container mt-5">

    <div class="row justify-content-center">

        <div class="col-md-7">

            <div class="card shadow">

                <div class="card-body p-4">

                    <h2 class="text-center mb-4">
                        Edit Item
                    </h2>


                    <?php if (isset($message)): ?>

                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($message); ?>
                        </div>

                    <?php endif; ?>


                    <form method="POST">


                        <div class="mb-3">

                            <label class="form-label">
                                Item Name *
                            </label>

                            <input
                                type="text"
                                name="item_name"
                                class="form-control"
                                value="<?php echo htmlspecialchars($item["item_name"]); ?>"
                                required
                            >

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Category *
                            </label>

                            <select
                                name="category"
                                class="form-select"
                                required
                            >

                                <option value="Document"
                                    <?php if ($item["category"] == "Document") echo "selected"; ?>>
                                    Document
                                </option>

                                <option value="Subscription"
                                    <?php if ($item["category"] == "Subscription") echo "selected"; ?>>
                                    Subscription
                                </option>

                                <option value="Warranty"
                                    <?php if ($item["category"] == "Warranty") echo "selected"; ?>>
                                    Warranty
                                </option>

                                <option value="Membership"
                                    <?php if ($item["category"] == "Membership") echo "selected"; ?>>
                                    Membership
                                </option>

                                <option value="Other"
                                    <?php if ($item["category"] == "Other") echo "selected"; ?>>
                                    Other
                                </option>

                            </select>

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Expiry Date *
                            </label>

                            <input
                                type="date"
                                name="expiry_date"
                                class="form-control"
                                value="<?php echo htmlspecialchars($item["expiry_date"]); ?>"
                                required
                            >

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Cost / Renewal Amount
                            </label>

                            <input
                                type="number"
                                name="cost"
                                class="form-control"
                                step="0.01"
                                min="0"
                                value="<?php echo htmlspecialchars($item["cost"]); ?>"
                            >

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Notes
                            </label>

                            <textarea
                                name="notes"
                                class="form-control"
                                rows="4"
                            ><?php echo htmlspecialchars($item["notes"]); ?></textarea>

                        </div>


                        <div class="d-flex gap-2">

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Update Item
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