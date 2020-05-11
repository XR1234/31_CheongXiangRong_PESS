<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Police Emergency Service System</title>
</head>
<body>
<?php require 'nav.php'?>
<?php
if (isset($_POST["btnDispatch"]))
{
	require_once 'db_config.php';
	
	$mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
	
	if ($mysqli->connect_errno)
	{
		die("Failed to connect to MySQL: ".$mysqli->connect_errno);
	}
	
	$patrolcarDispatched = $_POST["chkPatrolcar"];
	$numofPatrolcarDispatched = count($patrolcarDispatched);
	
	$incidentStatus;
	if ($numofPatrolcarDispatched > 0) {
		$incidentStatus='2';    //Dispatched
	} else {
		$incidentStatus='1';    //Pending
	}
	
	$sql ="INSERT INTO incident (callerName, contactNo, incidentTypeId, incidentLocation, incidentDesc, incidentStatusId) VALUES (?, ?, ?, ?, ?, ?)";
	
	if (!($stmt = $mysqli->prepare($sql)))
	{
		die("Failed preperation: ".$mysqli->errno);
	}
	
	if (!$stmt->bind_param('ssssss', $_POST['callerName'],
						           $_POST['contactNo'],
						           $_POST['incidentTypeId'],
						           $_POST['incidentLocation'],
						           $_POST['incidentDesc'],
						           $incidentStatus))
		
	{
		die("Parameters bind failed: ".$stmt->errno);
	}
	
	if (!$stmt->execute())
	{
		die("Insert incident table failed: ".$stmt->errno);
	}
	
	$incidentId=mysqli_insert_id($mysqli);
	//echo $incidentId;
	for($i=0; $i < $numofPatrolcarDispatched; $i++)
	{
		
		$sql = "UPDATE patrolcar SET patrolcarStatusId ='1' WHERE patrolcarId = ?";
		
		if (!($stmt = $mysqli->prepare($sql))) {
			die("Failed preperation: ".$mysqli->errno);
		}
		
		if (!$stmt->bind_param('s', $patrolcarDispatched[$i])){
			die("Parameters bind failed: ".$stmt->errno);
		}
		
		if (!$stmt->execute()) {
			die("Update patrolcarstatus table failed: ".$stmt->errno);
		}
		
		$sql = "INSERT INTO dispatch (incidentId, patrolcarId, timeDispatched) VALUES (?, ?, NOW())";
		
		if (!($stmt = $mysqli->prepare($sql))) {
			die("Failed preperation: ".$mysqli->errno);
		}
		
		if (!$stmt->bind_param('ss', $incidentId, $patrolcarDispatched[$i])){
			die("Parameters bind failed ".$stmt->errno);
		}
		
		if (!$stmt->execute()) {
			die("Insert dispatch table failed: ".$stmt->errno);
		}
		
		
	}
	
	$stmt->close();
	$mysqli->close();
	
} ?>
<form name="form1" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?> ">
<table>
<tr>
<td colspan="2">Incident Detail</td>	
</tr>	
<tr>
<td>Caller's Name :</td>
<td><?php echo $_POST['callerName'] ?>
<input type="hidden" name="callerName" id="callerName"
value="<?php echo $_POST['callerName'] ?>"></td>
</tr>
<tr>
<td>Contact No :</td>
<td><?php echo $_POST['contactNo'] ?>
<input type="hidden" name="contactNo" id="contactNo"
value="<?php echo $_POST['contactNo'] ?>"></td>	
</tr>
<tr>
<td>Location :</td>
<td><?php echo $_POST['incidentLocation'] ?>
<input type="hidden" name="incidentLocation" id="incidentLocation"
value="<?php echo $_POST['incidentLocation'] ?>"></td>	
</tr>
<tr>
<td>Incident Type :</td>
<td><?php echo $_POST['incidentTypeId'] ?>
<input type="hidden" name="incidentTypeId" id="incidentTypeId"
value="<?php echo $_POST['incidentTypeId'] ?>"></td>	
</tr>
<tr>
<td>Description :</td>
<td><textarea name="incidentDesc" cols="45"
rows="5" readonly id="incidentDesc"><?php echo $_POST['incidentDesc'] ?></textarea>
<input name="incidentDesc" type="hidden"
id="incidentDesc" value="<?php echo $_POST['incidentDesc'] ?>"</td>
</tr>
</table>
<?php 
require_once'db_config.php';
$mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
if($mysqli->connect_errno) 
{
	die("Failed to connect to MySQL: ".$mysqli->connect_errno);
}

$sql = "SELECT patrolcarId, statusDesc FROM patrolcar JOIN patrolcar_status
ON patrolcar.patrolcarStatusId=patrolcar_status.StatusId
WHERE patrolcar.patrolcarStatusId='2' OR patrolcar.patrolcarStatusId='3'";

	if (!($stmt = $mysqli->prepare($sql)))
	{
		die("Failed preperation: ".$mysqli->errno);
	}
	if (!$stmt->execute())
	{
		die("Failed execution: ".$stmt->errno);
	}
	if(!($resultset = $stmt->get_result()))
	{
		die("Getting result set failed: ".$stmt->errno);
	}
	
	$patrolcarArray;
	
	while  ($row = $resultset->fetch_assoc()) 
	{
		$patrolcarArray[$row['patrolcarId']] = $row['statusDesc'];
	}
	
	$stmt->close();
	$resultset->close();
	$mysqli->close();
	?>
	

<br><br><table border="1" align="center">
<tr>
<td colspan="3">Dispatch Patrolcar Panel</td>
</tr>
<?php
foreach($patrolcarArray as $key=>$value){
?>
<tr>
<td><input type="checkbox" name="chkPatrolcar[]" value="<?php echo $key?>"</td>
<td><?php echo $key ?></td>
<td><?php echo $value ?></td>
</tr>
<?php } ?>
<tr>
<td><input type="reset" name="btnCancel" id="btnCancel" value="Reset"></td>
<td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="btnDispatch" id="btnDispatch" value="Dispatch"></td>
</tr>
</table>
</form>
</body>
</html>