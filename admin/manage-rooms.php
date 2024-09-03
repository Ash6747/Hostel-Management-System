<?php
session_start();
include('includes/config.php');
include('../includes/enc.php');
include('includes/checklogin.php');
check_login();

if (isset($_GET['del'])) {
    $id = intval(decrypt($_GET['del']));
    $adn = "DELETE FROM rooms WHERE id=?";
    $stmt = $mysqli->prepare($adn);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Data Deleted');</script>";
}

$hostel_filter = 'All';
if (isset($_POST['filter'])) {
    $hostel_filter = $_POST['hostel_filter'];
}
?>

<!doctype html>
<html lang="en" class="no-js">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="theme-color" content="#3e454c">
    <link rel="icon" type="image/png" href="../img/fav2.png">
    <title>Manage Rooms</title>
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-social.css">
    <link rel="stylesheet" href="css/bootstrap-select.css">
    <link rel="stylesheet" href="css/fileinput.min.css">
    <link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <?php include('includes/header.php');?>

    <div class="ts-main-content">
        <?php include('includes/sidebar.php');?>
        <div class="content-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <h2 class="page-title" >Manage Rooms</h2>
                        <!-- <h2 class="page-title" style="margin-top: 4%">Manage Rooms</h2> -->
                        <div class="panel panel-default">
                            <div class="panel-heading">All Room Details</div>
                            <div class="panel-body">
                                <div class="action_file">
									<span style="float:left" class="print-hide"><i class="fa fa-print action_icon" aria-hidden="true" onclick="CallPrint()" style="cursor:pointer" title="Print the Report"></i></span>
									<span style="float:left" class="print-hide"><i class="fa-solid fa-file-arrow-down action_icon" onclick="exportExcel()" aria-hidden="true"  style="cursor:pointer" title="Download the Report"></i></span>
								</div>
                                <div id="print-header" style="display:none;">
									<h2>Rooms Status</h2>
								</div>
                                <form style=" display:flex; justify-content:center;" method="post" action="">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="hostel_filter">Select Hostel</label>
                                                <select name="hostel_filter" id="hostel_filter" class="form-control">
                                                    <option value="All" <?php echo ($hostel_filter == 'All') ? 'selected' : ''; ?>>All</option>
                                                    <?php
                                                    $query = "SELECT active, hostelName FROM hostel";
                                                    $stmt2 = $mysqli->prepare($query);
                                                    $stmt2->execute();
                                                    $res = $stmt2->get_result();
                                                    while ($row = $res->fetch_object()) { ?>
                                                        <option style="<?php echo $row->active? "":"color : tomato;";?>" value="<?php echo $row->hostelName; ?>" <?php echo ($hostel_filter == $row->hostelName) ? 'selected' : ''; ?>><?php echo $row->hostelName; ?></option>
                                                    <?php } ?>
                                                </select>
    
                                            </div>
                                            <button type="submit" name="filter" class="btn btn-primary">Filter</button>
                                        </div>
                                    </div>
                                </form>
                                <div class="row" id="print-content">
                                    <table id="zctb" class="display table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th>Sno.</th>
                                                <th>Hostel Name</th>
                                                <th>Gender</th>
                                                <th>Room No.</th>
                                                <th>Total Seats</th>
                                                <th>Fees (PY)</th>
                                                <th>Posting Date</th>
                                                <th>Occupants</th>
                                                <th>Available</th>
                                                <th class="action-column action_col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $query = "SELECT rm.*, COUNT(r.roomId) AS verified_count FROM registration r RIGHT JOIN rooms rm ON r.roomId = rm.id AND r.status = 'verified' ";
                                        $params = array();
                                        $types = '';
    
                                        if ($hostel_filter != 'All') {
                                            $query .= " WHERE rm.hostelName = ?";
                                            $params[] = $hostel_filter;
                                            $types .= 's';
                                        }
                                        $query .= " GROUP BY rm.id ORDER BY rm.id";
                                        $stmt = $mysqli->prepare($query);
                                        if (!empty($types)) {
                                            $stmt->bind_param($types, ...$params);
                                        }
                                        $stmt->execute();
                                        $res = $stmt->get_result();
                                        $cnt = 1;
                                        while ($row = $res->fetch_object()) {
                                            $available_seats = $row->seater - $row->verified_count;
                                        ?>
                                            <tr>
                                                <td><?php echo $cnt; ?></td>
                                                <td><?php echo $row->hostelName; ?></td>
                                                <td><?php echo ucfirst($row->gender); ?></td>
                                                <td><?php echo $row->room_no; ?></td>
                                                <td><?php echo $row->seater; ?></td>
                                                <td><?php echo $row->yearlyFees; ?></td>
                                                <td><?php echo $row->posting_date; ?></td>
                                                <td><?php echo $row->verified_count; ?></td>
                                                <td><?php echo $available_seats; ?></td>
                                                <td class="action-column action_col">
                                                    <a href="edit-room.php?id=<?php echo encrypt($row->id); ?>"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;
                                                    <a href="manage-rooms.php?del=<?php echo encrypt($row->id); ?>" onclick="return confirm('Do you want to delete');"><i class="fa fa-close"></i></a>
                                                </td>
                                            </tr>
                                        <?php
                                            $cnt = $cnt + 1;
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    
                    </div>
                </div>

            

            </div>
        </div>
    </div>

    <!-- Loading Scripts -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap-select.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.dataTables.min.js"></script>
    <script src="js/dataTables.bootstrap.min.js"></script>
    <script src="js/Chart.min.js"></script>
    <script src="js/fileinput.js"></script>
    <script src="js/chartData.js"></script>
    <script src="js/main.js"></script>
    <script language="javascript" type="text/javascript">
        var popUpWin=0;
        function popUpWindow(URLStr, left, top, width, height)
        {
        if(popUpWin)
        {
        if(!popUpWin.closed) popUpWin.close();
        }
        popUpWin = open(URLStr,'popUpWin', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,copyhistory=yes,width='+510+',height='+430+',left='+left+', top='+top+',screenX='+left+',screenY='+top+'');
        }

        function CallPrint() {
			const hidePagination = document.getElementById('zctb_paginate');
			const hideLenght = document.getElementById('zctb_length');
			const hideSearch = document.getElementById('zctb_filter');
			const hideColumn = document.querySelector('.action-column');
			hideColumn.style.display = 'none';
			hidePagination.style.display = 'none';
            hideLenght.style.display = 'none';
            hideSearch.style.display = 'none';
            var printContent = document.getElementById("print-content").innerHTML;
            var printHeader = document.getElementById("print-header").innerHTML;
            var WinPrint = window.open('', '', 'left=0,top=0,width=800,height=900,toolbar=0,scrollbars=0,status=0');
            WinPrint.document.write('<html><head><title>Rooms</title>');
            WinPrint.document.write('<link rel="stylesheet" href="css/bootstrap.min.css">');
            WinPrint.document.write('<style>@media print { .action-column { display: none; } a { color: black; text-decoration: none; } a::after { content: none !important; } }</style>');
            WinPrint.document.write('</head><body>');
            WinPrint.document.write(printHeader);
            WinPrint.document.write(printContent);
            WinPrint.document.write('</body></html>');
            WinPrint.document.close();
            WinPrint.focus();
            WinPrint.print();
			hideColumn.style.display = 'table-cell';
			hideLenght.style.display = 'block';
			hideSearch.style.display = 'block';
			hidePagination.style.display = 'block';
        }
    </script>
    </body>

</html>
