<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit();
}
include('db.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "DELETE FROM jobs WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        header('Location: admin_dashboard.php');
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
