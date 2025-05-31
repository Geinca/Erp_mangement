<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'])) {
    try {
        // Verify the leave application belongs to the current user
        $stmt = $pdo->prepare("DELETE la FROM leave_applications la
                              JOIN users u ON la.user_id = u.user_id
                              WHERE la.application_id = ? AND u.username = ? AND la.status = 'pending'");
        $stmt->execute([$_POST['application_id'], $_SESSION['username']]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = "Leave application deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Leave application not found or cannot be deleted!";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error deleting leave application: " . $e->getMessage();
    }
}

header("Location: apply_leave.php");
exit();
?>