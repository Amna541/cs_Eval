<?php
include('db_connect.php'); // Include database connection

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Ensure ID is an integer to prevent SQL injection

    // Prepare the delete statement
    $query = "DELETE FROM trace_data WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) > 0) {
            echo "<script>alert('Record deleted successfully!'); window.location.href='manage_groups.php';</script>";
        } else {
            echo "<script>alert('Error: Could not delete the record.'); window.location.href='manage_groups.php';</script>";
        }
        
        mysqli_stmt_close($stmt);
    } else {
        echo "<script>alert('Error preparing the statement.'); window.location.href='manage_groups.php';</script>";
    }

    mysqli_close($conn);
} else {
    echo "<script>alert('Invalid request.'); window.location.href='manage_groups.php';</script>";
}
?>
