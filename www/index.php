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

	// get the Suburb (zone) maps onto Group
	if (empty($_GET['ZoneID'] )) 
		$ZoneID = 1;
	else 
		$ZoneID = $_GET['ZoneID'];

	// Get current Group
	$GroupID = 0;
	$sql = "SELECT GroupID FROM zones where ZoneID = ?"; 
	$stmt = $conn->prepare($sql);
	$stmt->bind_param('s', $name);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($result->num_rows > 0) {
		$row = $result->fetch_assoc();
		$GroupID = $row["GroupID"];
	}

	// Get the schedule period 
	if (empty($_GET['Period'] )) 
		$Period = 32;
	else 
		$Period = $_GET['Period'];

	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, "loadshedding.eskom.co.za/LoadShedding/GetStatus"); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$output = curl_exec($ch); 
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

    # Eskom responds with a single number
    # 1 = No load shedding
    # 2 = Stage 1
    # 3 = Stage 2
    # 4 = Stage 3

	if ($httpcode == 200) {
		if ($output == "1")
			$status = "Not load shedding";
		else {
			$status = "Stage ".(trim($output) - 1);
			// Get load shedding stage
			if (empty($_GET['Stage'] )) 
				$Stage =  (trim($output) - 1);		
		}
	} else {
		$status = "Error getting status";		
	}

	$image = "red-shed.jpg";	
	if ($output == "1") {
		$image = "green-shed.jpg";
	}

	if (empty($Stage)) {
		if (empty($_GET['Stage'] ))
			$Stage = 1;
		else 
			$Stage = $_GET['Stage'];	
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
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-129161061-3"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'UA-129161061-3');
</script>


</head>
<body>
   <div class="container">

		
      <!-- Main component -->
      <div class="">

	<div class="form-group">			
			<h1><img src="images/<?php echo $image ?>" width="100" height="100"  title="Load Sheddy" alt="Load Sheddy" />Tshwane Load Sheddy</h1><br />
			Status: <?php echo $status; ?>
	</div>
	<div class="form-group">			
     <form class = "form-main" role = "form" method = "get">
		 <label for="Stage">Stage</label>

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
		  <label for="ZoneID">Suburb</label>
		  <select class="form-control" id="ZoneID" name="ZoneID" onchange="this.form.submit()">
			<?php 
				if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					echo '<option value='.$row["ZoneID"];
					if ($row["ZoneID"] == $ZoneID) {
						echo ' SELECTED';
					}
					echo'>'.$row["ZoneName"].'</option>';
					
					}
			}
			?>
		  </select>
		  </div>
		  
		  <div><label for="GroupID">
		  <?php
			echo 'Group: '.$GroupID
			?>
		  </div>
		  
		  
		<div class="form-group">
		  <label for="Period">Period</label>  
		  <select class="form-control" id="Period" name="Period" onchange="this.form.submit()">
		  <?php 
				$arr = array("Today", "Next five days", "Upcoming week", "Upcoming fortnight", "Upcoming three weeks", "Upcoming month");
				$vals = array(1, 6, 8, 15, 22, 32);
				$i = 0;
				foreach ($arr as &$value) {
					echo '<option value="'.$vals[$i].'"';
					if ($vals[$i] == $Period) {
						echo ' SELECTED';
					}
					echo'>'.$value.'</option>';
					$i++;
				}


			?>		  
		  </select>
		</div>

		<div>
		  <label>Schedule:</label>
<?php

		  	$currentDay = (int) date("d");
		  	$lastDayThisMonth = (int) date("t");
		  	$wrapAround = false;
		  	$last = $currentDay + $Period -1;
		  	if ($last > $lastDayThisMonth) {
		  		$wrapAround = true;
		  	}

		  	//echo 'currentDay='.$currentDay.' period='.$Period.' last='.$last.' lastDayThisMonth='.$lastDayThisMonth.' wrapAround='.$wrapAround.'<br>';		  
?>		  
		  <table class="table table-hover" id="scheduleTable" border=1>
		  <tr style="font-weight:bold" ><td>Day</td><td>Start</td><td>End</td><td>Stage</td></tr>
		  <?php 

			$sql = "SELECT * from schedule where GroupID = ".$GroupID." and Stage <= ".$Stage." and day >=".$currentDay." and day  <= ".$last." order by day asc"; 
			$result = $conn->query($sql);
			$colors = ['white', 'whitesmoke'];
			$day = $currentDay;
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc() ) {
					echo "<tr style='background-color: ".$colors[$row["Day"]%2].";'>";
					echo "<td>".$row["Day"]."</td>";
					echo "<td>".str_pad($row["StartTime"], 5, "0", STR_PAD_LEFT) ."</td>";
					echo "<td>".str_pad($row["EndTime"], 5, "0", STR_PAD_LEFT) ."</td>";
					echo "<td>".$row["Stage"]."</td>";
					echo "</td></tr>";
					$day++;
					//if ($day >= $Period) break;// the logic of this line assumes 1 row per day (I think) - just let it run to last instead
				}
			}

			// add on the bit of next month if nwrap around needed ( we don't reset $day.. it keeps counting) - removed day because of above assumption
			if ($last > $lastDayThisMonth) {
				$last = $last-$lastDayThisMonth;
				
				$sql = "SELECT * from schedule where GroupID = ".$GroupID." and Stage <= ".$Stage." and day < ".$last."  order by day asc"; 
				$result = $conn->query($sql);
				if ($result->num_rows > 0 && $wrapAround) {
					while($row = $result->fetch_assoc()) {
						echo "<tr>";
						echo "<td>".$row["Day"]."</td>";
						echo "<td>".str_pad($row["StartTime"], 5, "0", STR_PAD_LEFT) ."</td>";
						echo "<td>".str_pad($row["EndTime"], 5, "0", STR_PAD_LEFT) ."</td>";
						echo "<td>".$row["Stage"]."</td>";
						echo "</td></tr>";
						$day++;
						//if ($day >= $Period) break;/ the logic of this line assumes 1 row per day (I think) - just let it run to last instead
					}
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
