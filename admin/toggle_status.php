<?php
include('includes/config.php');

// if (isset($_POST['hostelName'])) {
//     $hostelName = $_POST['hostelName'];

//     // Get the current status
//     $query = "SELECT active FROM hostel WHERE hostelName = ?";
//     $stmt = $mysqli->prepare($query);
//     $stmt->bind_param('s', $hostelName);
//     $stmt->execute();
//     $stmt->bind_result($active);
//     $stmt->fetch();
//     $stmt->close();

//     // Toggle the status
//     $newStatus = $active ? 0 : 1;

//     // Update the status in the database
//     $updateQuery = "UPDATE hostel SET active = ? WHERE hostelName = ?";
//     $updateStmt = $mysqli->prepare($updateQuery);
//     $updateStmt->bind_param('is', $newStatus, $hostelName);
//     if($updateStmt->execute()){
//         echo $newStatus;
//     }else{
//         echo "Status Not Changed";
//     }
    
//     // Output the new status
//     $updateStmt->close();
// }

if (isset($_POST['hostelName'])) {
    // Get the hostelName from the POST request
    $hostelName = $_POST['hostelName'];

    // Get the current status of the hostel
    $query = "SELECT active FROM hostel WHERE hostelName = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt === false) {
        echo "Error preparing statement: " . $mysqli->error;
        exit;
    }
    
    $stmt->bind_param('s', $hostelName);
    $stmt->execute();
    $stmt->bind_result($active);
    $stmt->fetch();
    $stmt->close();

    // Toggle the status
    $newStatus = $active ? 0 : 1;

    // Update the status in the database
    $updateQuery = "UPDATE hostel SET active = ? WHERE hostelName = ?";
    $updateStmt = $mysqli->prepare($updateQuery);
    if ($updateStmt === false) {
        echo "Error preparing update statement: " . $mysqli->error;
        exit;
    }
    
    $updateStmt->bind_param('is', $newStatus, $hostelName);

    // Check if the update was successful
    if ($updateStmt->execute()) {
        echo $newStatus; // Output the new status if successful
    } else {
        echo "Status Not Changed"; // Error handling
    }
    
    $updateStmt->close();
}

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Get the current status
    $query = "SELECT active FROM guest_rooms WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($active);
    $stmt->fetch();
    $stmt->close();

    // Toggle status
    $newStatus = $active ? 0 : 1;

    // Update status in the database
    $update_stmt = $mysqli->prepare("UPDATE guest_rooms SET active = ? WHERE id = ?");
    $update_stmt->bind_param('ii', $newStatus, $id);
    $update_stmt->execute();
    $update_stmt->close();

    // Return the new status
    echo $newStatus;
} else {
    echo 'Invalid request';
}
?>