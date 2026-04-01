<?php
$conn = new mysqli('localhost', 'root', '', 'mims');

$check_active = mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Active'");
if (mysqli_num_rows($check_active) == 0) {
    mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES (1, 'Active')");
    echo "Added 'Active' status<br>";
}

$check_approved = mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Approved'");
if (mysqli_num_rows($check_approved) == 0) {
    mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES (2, 'Approved')");
    echo "Added 'Approved' status<br>";
}

$check_pending = mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Pending'");
if (mysqli_num_rows($check_pending) == 0) {
    mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES (4, 'Pending')");
    echo "Added 'Pending' status<br>";
}

$check_rejected = mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Rejected'");
if (mysqli_num_rows($check_rejected) == 0) {
    mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES (3, 'Rejected')");
    echo "Added 'Rejected' status<br>";
}

$check_partial = mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Partial'");
if (mysqli_num_rows($check_partial) == 0) {
    mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES (5, 'Partial')");
    echo "Added 'Partial' status<br>";
}

$check_due_date = mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Due Date'");
if (mysqli_num_rows($check_due_date) == 0) {
    mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES (6, 'Due Date')");
    echo "Added 'Due Date' status<br>";
}

$check_up_to_date = mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Up to Date'");
if (mysqli_num_rows($check_up_to_date) == 0) {
    mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES (7, 'Up to Date')");
    echo "Added 'Up to Date' status<br>";
}

$check_closed = mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Closed'");
if (mysqli_num_rows($check_closed) == 0) {
    mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES (-3, 'Closed')");
    echo "Added 'Closed' status<br>";
}

$check_declined = mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Declined'");
if (mysqli_num_rows($check_declined) == 0) {
    mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES (-1, 'Declined')");
    echo "Added 'Declined' status<br>";
}

echo "<h3>All account statuses have been set up!</h3>";
echo "<h4>Current statuses in database:</h4>";
$result = mysqli_query($conn, "SELECT * FROM account_status ORDER BY account_status_number");
echo "<table border='1'><tr><th>Number</th><th>Name</th></tr>";
while($row = mysqli_fetch_assoc($result)) {
    echo "<tr><td>" . $row['account_status_number'] . "</td><td>" . $row['account_status_name'] . "</td></tr>";
}
echo "</table>";

mysqli_close($conn);
?>
