<?php 
session_start();
error_reporting(0);
include('includes/config.php');
include('includes/checklogin.php');
check_login();
?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="theme-color" content="#3e454c">
    <link rel="icon" type="image/png" href="../img/fav2.png">
    <title>Hostel Summary</title>
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-social.css">
    <link rel="stylesheet" href="css/bootstrap-select.css">
    <link rel="stylesheet" href="css/fileinput.min.css">
    <link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script type="text/javascript" src="js/jquery-1.11.3-jquery.min.js"></script>
    <script type="text/javascript" src="js/validation.min.js"></script>
</head>
<body>
    <?php include('includes/header.php'); ?>
    <div class="ts-main-content">
        <?php include('includes/sidebar.php'); ?>
        <div class="content-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <h2 class="page-title">Hostel Summary</h2>
                        <div class="panel panel-default">
                            <div class="panel-heading">Hostel Summary</div>
                            <div class="panel-body">
                                <table id="zctb" class="display table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Hostel Name</th>
                                            <th>Total Rooms</th>
                                            <th>Hostel Capacity</th>
                                            <th>Occupied Seats</th>
                                            <th>Empty Seats</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            // Initialize variables
                                            $totalRooms = 0;
                                            $totalCapacity = 0;
                                            $totalOccupied = 0;
                                            $totalEmpty = 0;

                                            // Query to get hostel details
                                            $query = "SELECT 
                                                        h.hostelName, 
                                                        h.active, 
                                                        COUNT(r.hostelName) AS room_count,
                                                        SUM(r.seater) AS HostelCapacity,
                                                        COUNT(rg.roomId) AS Occupied
                                                    FROM 
                                                        hostel h 
                                                    LEFT JOIN 
                                                        rooms r 
                                                    ON 
                                                        r.hostelName = h.hostelName 
                                                    LEFT JOIN 
                                                        registration rg 
                                                    ON 
                                                        r.id = rg.roomId AND rg.status = 'verified' 
                                                    GROUP BY 
                                                        h.hostelName";
                                                    
                                            $stmt = $mysqli->prepare($query);
                                            $stmt->execute();
                                            $stmt->bind_result($hostelName, $active, $TotalRooms, $HostelCapacity, $OccupiedRooms);

                                            // Loop through the results
                                            while ($stmt->fetch()) {
                                                $buttonText = $active ? 'Set Inactive' : 'Set Active';
                                                $buttonClass = $active ? 'btn-success' : 'btn-warning';
                                                $EmptyRooms = $HostelCapacity - $OccupiedRooms;

                                                // Accumulate totals
                                                $totalRooms += $TotalRooms;
                                                $totalCapacity += $HostelCapacity;
                                                $totalOccupied += $OccupiedRooms;
                                                $totalEmpty += $EmptyRooms;

                                                echo "<tr style='font-weight: 700;'>
                                                        <td>$hostelName</td>
                                                        <td>$TotalRooms</td>
                                                        <td>$HostelCapacity</td>
                                                        <td style='color: limegreen'>$OccupiedRooms</td>
                                                        <td style='color: tomato'>$EmptyRooms</td>
                                                        <td>
                                                            <button class='btn $buttonClass toggle-status' id='toggle-status-btn' data-hostel='$hostelName' data-occupied='$OccupiedRooms' data-active='$active' onclick='changeActive(this)'>$buttonText</button>
                                                        </td>
                                                    </tr>";
                                            }

                                            $stmt->close();
                                        ?>
                                        <tfoot>
                                            <tr>
                                                <th style="font-weight: 700;">Total</th>
                                                <th id="totalRooms"><?php echo $totalRooms ?></th>
                                                <th id="totalCapacity"><?php echo $totalCapacity ?></th>
                                                <th id="totalOccupied"><?php echo $totalOccupied ?></th>
                                                <th id="totalEmpty"><?php echo $totalEmpty ?></th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div> 	
            </div>
        </div>
    </div>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap-select.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.dataTables.min.js"></script>
    <script src="js/dataTables.bootstrap.min.js"></script>
    <script src="js/Chart.min.js"></script>
    <script src="js/fileinput.js"></script>
    <script src="js/chartData.js"></script>
    <script src="js/main.js"></script>
    <script>
        function changeActive(val) {
            var btn = val;
            var hostelName = btn.getAttribute('data-hostel');
            var occupied = parseInt(btn.getAttribute('data-occupied'));
            var active = parseInt(btn.getAttribute('data-active'));
            
            // Prevent status change if there are occupied seats
            if (occupied > 0) {
                alert('Status cannot be changed. There are occupied seats.');
                return;
            }

            $.ajax({
                url: 'toggle_status.php',
                type: 'POST',
                data: { hostelName: hostelName },
                success: function(response) {
                    try {
                        // Trim any whitespace and check if the response is a number
                        response = response.trim();
                        var newStatus = parseInt(response);

                        if (!isNaN(newStatus)) {
                            // Update the button text and class based on the new status
                            var buttonText = newStatus ? 'Set Inactive' : 'Set Active';
                            var buttonClass = newStatus ? 'btn-success' : 'btn-warning';

                            btn.textContent = buttonText;
                            btn.classList.remove('btn-success', 'btn-warning');
                            btn.classList.add(buttonClass);

                            // Update the button's data-active attribute
                            btn.setAttribute('data-active', newStatus);

                            alert('The hostel is now ' + (newStatus ? 'Active' : 'Inactive') + '.');
                        } else {
                            alert('Error: Invalid response from server.');
                        }
                    } catch (e) {
                        console.error('Error processing response:', e);
                        alert('Error: Unable to update status.');
                    }
                },
                error: function() {
                    alert('Error: Unable to update status.');
                }
            });
        }

        // function changeActive(val) {
        //     var btn = val;
        //     console.log(val);
        //     // Alternatively, using the dataset property
        //     var hostelName = btn.dataset.hostel;
        //     var occupied = btn.dataset.occupied;
        //     var active = btn.dataset.active;

        //     // console.log('Hostel Name:', hostelName);
        //     // console.log('Occupied Rooms:', occupied);
        //     // console.log('Active Status:', active);

        //     if (occupied > 0) {
        //         alert('Status cannot be changed. There are occupied seats.');
        //         return;
        //     }

        //     $.ajax({
        //         url: 'toggle_status.php',
        //         type: 'POST',
        //         data: {hostelName: hostelName},
        //         success: function(response) {
        //             // let temp = Number(response);
        //             // console.log(temp);
        //             console.log(typeof(response));
        //             console.log(response);
        //             if (active === '0') {
        //                 btn.textContent = 'Set Inactive'; // Update the button text
        //                 btn.classList.remove('btn-warning'); // Remove 'btn-warning' class
        //                 btn.classList.add('btn-success'); // Add 'btn-success' class
        //                 btn.setAttribute('data-active', '1'); // Update the data-active attribute
        //                 alert('The hostel is now Active.');
        //             } else if(active === '1') {
        //                 btn.textContent = 'Set Active'; // Update the button text
        //                 btn.classList.remove('btn-success'); // Remove 'btn-success' class
        //                 btn.classList.add('btn-warning'); // Add 'btn-warning' class
        //                 btn.setAttribute('data-active', '0'); // Update the data-active attribute
        //                 alert('The hostel is now Inactive.');
        //             }else{
        //                 alert('Unable to update status.');
        //             }
        //         },
        //         error: function() {
        //             alert('Error: Unable to update status.');
        //         }
        //     });
        // }

    </script>
</body>
</html>