<?php

session_start();

// Check whether user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Database connection
include "db.php";

$user_id = $_SESSION["user_id"];
$user_name = $_SESSION["user_name"];


// --------------------------------------------------
// GET TOTAL NUMBER OF ITEMS
// --------------------------------------------------

$total_query = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM items
     WHERE user_id = ?"
);

$total_query->bind_param("i", $user_id);

$total_query->execute();

$total_result = $total_query->get_result();

$total = $total_result->fetch_assoc()["total"];

$total_query->close();


// --------------------------------------------------
// GET ALL ITEMS
// --------------------------------------------------

$stmt = $conn->prepare(
    "SELECT *
     FROM items
     WHERE user_id = ?
     ORDER BY expiry_date ASC"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();


// --------------------------------------------------
// INITIALIZE COUNTERS
// --------------------------------------------------

$active = 0;
$soon = 0;
$urgent = 0;
$expired = 0;

$items = [];


// Today's date
$today = new DateTime();


// --------------------------------------------------
// CALCULATE EXPIRY STATUS
// --------------------------------------------------

while ($row = $result->fetch_assoc()) {

    $expiry_date = new DateTime($row["expiry_date"]);

    // Calculate difference between today and expiry date
    $difference = $today->diff($expiry_date);

    // Number of days remaining
    $days = (int) $difference->format("%r%a");


    // Determine status

    if ($days < 0) {

        $status = "Expired";

        $expired++;

    } elseif ($days <= 6) {

        $status = "Urgent";

        $urgent++;

    } elseif ($days <= 30) {

        $status = "Expiring Soon";

        $soon++;

    } else {

        $status = "Active";

        $active++;
    }


    // Store calculated information

    $row["status"] = $status;
    $row["days"] = $days;
    // Check custom reminder

if (
    $days >= 0 &&
    $days <= $row["reminder_days"]
) {

    $row["reminder"] = true;

} else {

    $row["reminder"] = false;

}

    $items[] = $row;
}


$stmt->close();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Dashboard - Smart Expiry System
    </title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body class="bg-light">


<!-- ==================================================
     NAVIGATION BAR
================================================== -->

<nav class="navbar navbar-dark bg-primary">

    <div class="container">

        <!-- Project Name -->

        <a
            class="navbar-brand"
            href="dashboard.php"
        >
            Smart Expiry System
        </a>


        <!-- User Details -->

        <div>

            <span class="text-white me-3">

                Welcome,
                <?php
                echo htmlspecialchars($user_name);
                ?>

            </span>


            <a
                href="logout.php"
                class="btn btn-light btn-sm"
            >
                Logout
            </a>

        </div>

    </div>

</nav>



<!-- ==================================================
     MAIN CONTAINER
================================================== -->

<div class="container mt-4">


    <!-- ==================================================
         DASHBOARD HEADER
    ================================================== -->

    <div
        class="d-flex justify-content-between align-items-center mb-4"
    >

        <div>

            <h2>
                Dashboard
            </h2>

            <p class="text-muted">

                Manage your documents,
                subscriptions and renewals.

            </p>

        </div>


        <!-- Add Item Button -->

        <a
            href="add_item.php"
            class="btn btn-primary"
        >

            + Add New Item

        </a>

    </div>



    <!-- ==================================================
         SUMMARY CARDS
    ================================================== -->

    <div class="row g-3 mb-4">


        <!-- TOTAL ITEMS -->

        <div class="col-md-3">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h6 class="text-muted">
                        Total Items
                    </h6>

                    <h2>

                        <?php
                        echo $total;
                        ?>

                    </h2>

                    <p class="mb-0 text-muted">
                        All your records
                    </p>

                </div>

            </div>

        </div>



        <!-- ACTIVE -->

        <div class="col-md-3">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h6 class="text-muted">
                        Active
                    </h6>

                    <h2 class="text-success">

                        <?php
                        echo $active;
                        ?>

                    </h2>

                    <p class="mb-0 text-muted">
                        More than 30 days
                    </p>

                </div>

            </div>

        </div>



        <!-- EXPIRING SOON -->

        <div class="col-md-3">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h6 class="text-muted">
                        Expiring Soon
                    </h6>

                    <h2 class="text-info">

                        <?php
                        echo $soon;
                        ?>

                    </h2>

                    <p class="mb-0 text-muted">
                        Within 30 days
                    </p>

                </div>

            </div>

        </div>



        <!-- URGENT -->

        <div class="col-md-3">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h6 class="text-muted">
                        Urgent
                    </h6>

                    <h2 class="text-warning">

                        <?php
                        echo $urgent;
                        ?>

                    </h2>

                    <p class="mb-0 text-muted">
                        Within 7 days
                    </p>

                </div>

            </div>

        </div>

    </div>



    <!-- ==================================================
         ALERTS
    ================================================== -->


    <!-- URGENT ALERT -->

    <?php if ($urgent > 0): ?>

        <div class="alert alert-warning">

            <strong>
                ⚠️ Urgent:
            </strong>

            You have

            <?php
            echo $urgent;
            ?>

            item(s) expiring within 7 days.

            Please renew them soon.

        </div>

    <?php endif; ?>



    <!-- EXPIRING SOON ALERT -->

    <?php if ($soon > 0): ?>

        <div class="alert alert-info">

            <strong>
                🔔 Reminder:
            </strong>

            You have

            <?php
            echo $soon;
            ?>

            item(s) expiring within 30 days.

        </div>

    <?php endif; ?>



    <!-- EXPIRED ALERT -->

    <?php if ($expired > 0): ?>

        <div class="alert alert-danger">

            <strong>
                ❌ Expired:
            </strong>

            You have

            <?php
            echo $expired;
            ?>

            expired item(s).

            Please renew them.

        </div>

    <?php endif; ?>



    <!-- ==================================================
         ITEMS TABLE
    ================================================== -->

    <div class="card shadow-sm">

        <div class="card-body">


            <div
                class="d-flex justify-content-between align-items-center mb-3"
            >

                <h4 class="mb-0">
                    My Expiry Items
                </h4>


                <span class="text-muted">

                    <?php
                    echo $total;
                    ?>

                    item(s)

                </span>

            </div>



            <!-- TABLE -->

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">


                    <!-- TABLE HEADER -->

                    <thead class="table-light">

                        <tr>

                            <th>
                                Item Name
                            </th>

                            <th>
                                Category
                            </th>

                            <th>
                                Expiry Date
                            </th>

                            <th>
                                Cost
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>



                    <!-- TABLE BODY -->

                    <tbody>


                    <?php if (count($items) > 0): ?>


                        <?php foreach ($items as $item): ?>


                            <tr>


                                <!-- ITEM NAME -->

                                <td>

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $item["item_name"]
                                        );
                                        ?>

                                    </strong>

                                    <?php if (!empty($item["notes"])): ?>

                                        <br>

                                        <small class="text-muted">

                                            <?php
                                            echo htmlspecialchars(
                                                $item["notes"]
                                            );
                                            ?>

                                        </small>

                                    <?php endif; ?>

                                </td>



                                <!-- CATEGORY -->

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $item["category"]
                                    );
                                    ?>

                                </td>



                                <!-- EXPIRY DATE -->

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $item["expiry_date"]
                                    );
                                    ?>


                                    <br>


                                    <?php if ($item["days"] < 0): ?>

                                        <small class="text-danger">

                                            Expired

                                        </small>


                                    <?php elseif ($item["days"] == 0): ?>

                                        <small class="text-danger">

                                            Expires today

                                        </small>


                                    <?php elseif ($item["days"] == 1): ?>

                                        <small class="text-warning">

                                            1 day remaining

                                        </small>


                                    <?php else: ?>

                                        <small class="text-muted">

                                            <?php
                                            echo $item["days"];
                                            ?>

                                            days remaining

                                        </small>

                                    <?php endif; ?>

                                </td>



                                <!-- COST -->

                                <td>

                                    ₹<?php

                                    echo htmlspecialchars(
                                        $item["cost"]
                                    );

                                    ?>

                                </td>



                                <!-- STATUS -->

                                <td>


                                    <?php if (
                                        $item["status"] == "Expired"
                                    ): ?>

                                        <span
                                            class="badge bg-danger"
                                        >
                                            🔴 Expired
                                        </span>


                                    <?php elseif (
                                        $item["status"] == "Urgent"
                                    ): ?>

                                        <span
                                            class="badge bg-warning text-dark"
                                        >
                                            🟠 Urgent
                                        </span>


                                    <?php elseif (
                                        $item["status"] == "Expiring Soon"
                                    ): ?>

                                        <span
                                            class="badge bg-info text-dark"
                                        >
                                            🟡 Expiring Soon
                                        </span>


                                    <?php else: ?>

                                        <span
                                            class="badge bg-success"
                                        >
                                            🟢 Active
                                        </span>

                                    <?php endif; ?>


                                </td>
                                <?php if ($item["reminder"]): ?>

    <br>

    <span class="badge bg-danger mt-1">
        🔔 Reminder
    </span>

<?php endif; ?>



                                <!-- ACTION BUTTONS -->

                                <td>


                                    <!-- EDIT -->

                                    <a
                                        href="edit_item.php?id=<?php echo $item["id"]; ?>"
                                        class="btn btn-sm btn-primary mb-1"
                                    >
                                        Edit
                                    </a>


                                    <!-- RENEW -->

                                    <a
                                        href="renew_item.php?id=<?php echo $item["id"]; ?>"
                                        class="btn btn-sm btn-success mb-1"
                                    >
                                        Renew
                                    </a>


                                    <!-- DELETE -->

                                    <a
                                        href="delete_item.php?id=<?php echo $item["id"]; ?>"
                                        class="btn btn-sm btn-danger mb-1"
                                        onclick="return confirm('Are you sure you want to delete this item?');"
                                    >
                                        Delete
                                    </a>


                                </td>


                            </tr>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <!-- NO ITEMS -->

                        <tr>

                            <td
                                colspan="6"
                                class="text-center py-5"
                            >

                                <h5>
                                    No expiry items found
                                </h5>

                                <p class="text-muted">

                                    Start by adding your first
                                    document or subscription.

                                </p>


                                <a
                                    href="add_item.php"
                                    class="btn btn-primary"
                                >

                                    + Add Your First Item

                                </a>

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>

                </table>

            </div>

        </div>

    </div>


</div>



<!-- Bootstrap JavaScript -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>