<?php
	/**
	 * A php implementation of Kev's magic excel spreadsheet
	 *
	 * Author: Rob
	 *
	 */
   ob_start();
   session_start();
   // error_reporting(E_ALL);
   // ini_set("display_errors", 1);

	$msg = '';
	include 'db.php';

	$msg = "";

	// Get load shedding stage
	if (empty($_GET['Stage'] )) 
		$Stage = 1;
	else 
		$Stage = $_GET['Stage'];
	
	// get the Suburb (zone) maps onto Group
	if (empty($_GET['ZoneID'] )) 
		$ZoneID = 1;
	else 
		$ZoneID = $_GET['ZoneID'];

	// Get current Group
	$GroupID = 0;
	$sql = "SELECT GroupID FROM zones where ZoneID = ".$ZoneID; 
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		$row = $result->fetch_assoc();
		$GroupID = $row["GroupID"];
	}

?>
<html lang = "en">   
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <title>Load Sheddy Web</title>
  <link href = "css/bootstrap.min.css" rel = "stylesheet">      
  <style>
	body {
		padding-top: 40px;
		padding-bottom: 40px;
	//background-color: #ADABAB;
	}

	div {
		width:auto;
	}
  </style>
</head>
<body>
   <div class="container">

      <!-- Main component -->
      <div class="">
         <form class = "form-main" role = "form" method = "get">

			
		<div class="form-group">
		  <label for="Stage">Stage:</label>
		  <select class="form-control" id="Stage" name="Stage" onchange="this.form.submit()">
		  <?php 
				$arr = array(1, 2, 3, 4, 5, 6, 7);
				foreach ($arr as &$value) {
					echo '<option value='.$value;
					if ($value == $Stage) {
						echo ' SELECTED';
					}
					echo'> Stage: ' .$value.'</option>';
				}
			?>		  
		  </select>
		</div>


		  <?php 
			$sql = "SELECT ZoneID, GroupID, substring(ZoneName,1,100) ZoneName FROM zones order by ZoneName"; // XXX calculate substring length based on screen width
			$result = $conn->query($sql);
			//echo 'Zones='.$result->num_rows.'<br>';
			?>			
		<div class="form-group">
		  <label for="ZoneID">Suburb:</label>
		  <select class="form-control" id="ZoneID" name="ZoneID" onchange="this.form.submit()">
			<?php 
				if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					echo '<option value='.$row["ZoneID"];
					if ($row["ZoneID"] == $ZoneID) {
						echo ' SELECTED';
					}
					echo'>'.$row["ZoneName"].' = '.$row["GroupID"].'</option>';
				}
			}
			?>
		  </select>
		</div>

		<div>
		  <label>Schedule:</label>
		  <table class="table table-hover" id="scheduleTable" border=1>
		  <tr><td>Day</td><td>Start</td><td>End</td><td>Stage</td></tr>
		  <?php 
			$sql = "SELECT * from schedule where GroupID = ".$GroupID." and Stage <= ".$Stage." order by day asc"; 
			$result = $conn->query($sql);
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					echo "<tr>";
					echo "<td>".$row["Day"]."</td>";
					echo "<td>".$row["StartTime"]."</td>";
					echo "<td>".$row["EndTime"]."</td>";
					echo "<td>".$row["Stage"]."</td>";
					echo "</td></tr>";

				}
			}
			?>
			</table>
		</div>		

      </div><!-- Main component -->
    </div> <!-- /container -->
	
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.js"></script>
   
   </body>
</html>
