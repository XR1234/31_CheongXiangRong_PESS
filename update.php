<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Police Emergency Service System</title>
<?php
if (isset($_POST['btnUpdate'])){
	require_once 'db_config.php';
	
	$mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
	
	if ($mysqli->connect_errno){
		die("Failed to connect to MySQL: ".$mysqli->connect_errno);
	}
	
	$sql = "UPDATE patrolcar SET patrolcarStatusId = ? WHERE patrolcarId = ? ";
	
	if (!($stmt = $mysqli->prepare($sql))) {
		die("Failed preperation: ".$mysqli->errno);
	}
	
	if (!$stmt->bind_param('ss', $_POST['patrolcarStatus'], $_POST['patrolcarId'])){
		die("Parameters bind failed: ".$stmt->errno);
	}
	
	if (!$stmt->execute()) {
		die("Update patrolcar table failed: ".$stmt->errno);
	}
	
	if ($_POST["patrolcarStatus"] == '4'){
		
		$sql = "UPDATE dispatch SET timeArrived = NOW() WHERE timeArrived is NULL AND patrolcarId = ?";
		
		if (!($stmt = $mysqli->prepare($sql))) {
			die("Failed preperation: ".$mysqli->errno);
		}
		
		if (!$stmt->bind_param('s', $_POST['patrolcarId'])){
			die("Parameters bind failed: ".$stmt->errno);
		}
		
		if (!$stmt->execute()) {
			die("Update dispatch table failed: ".$stmt->errno);
		}
		
	} else if ($_POST["patrolcarStatus"] == '3'){
		
		$sql = "SELECT incidentId FROM dispatch WHERE timeCompleted IS NULL and patrolcarId = ?";
		
		if (!($stmt = $mysqli->prepare($sql))) {
			die("Failed preperation: ".$mysqli->errno);
		}
		
		if (!$stmt->bind_param('s', $_POST['patrolcarId'])){
			die("Parameters bind failed: ".$stmt->errno);
		}
		
		if (!$stmt->execute()) {
			die("Failed execution ".$stmt->errno);
		}
		
		if (!($resultset = $stmt->get_result())) {
			die("Getting result set failed: ".$stmt->errno);
		}
		
		$incidentId;
		
		while ($row = $resultset->fetch_assoc()) {
			$incidentId = $row['incidentId'];
		}
		
		$sql = "UPDATE dispatch SET timeCompleted = NOW() WHERE timeCompleted is NULL and patrolcarId = ?";
		
		if (!($stmt = $mysqli->prepare($sql))) {
			die("Failed preperation: ".$mysqli->errno);
		}
		
		if (!$stmt->bind_param('s', $_POST['patrolcarId'])){
			die("Parameters bind failed: ".$stmt->errno);
		}
		
		if (!$stmt->execute()) {
			die("Update dispatch table failed: ".$stmt->errno);
		}
		
		$sql = "UPDATE incident SET incidentStatusId = '3' WHERE incidentId = '$incidentId' AND NOT EXISTS (SELECT * FROM dispatch WHERE timeCompleted IS NULL AND incidentId = '$incidentId')";
		
		if (!($stmt = $mysqli->prepare($sql))) {
			die("Failed preperation 11: ".$mysqli->errno);
		}
		
		if (!$stmt->execute()) {
			die("Update dispatch table failed: ".$stmt->errno);
		}
		
		$resultset->close();
	}
	
	$stmt->close();
	
	$mysqli->close();
	
	?>
	
<script>window.location="logcall.php";</script>

<?php } ?>

</head>

<body>
<?php require_once 'nav.php'; ?>
<?php
if (!isset($_POST["btnSearch"])){
?>
	
<form name="form1" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?> ">
<table width="50%" border="0" align="center" cellpadding="4" cellspacing="4">
<tr></tr>
<tr>
<td>Patrol Car ID :</td>
<td><input type="text" name="patrolcarId" id="patrolcarId"></td>
<td><input type="submit" name="btnSearch" id="btnSearch" value="Search"></td>
</tr>
</table>
</form>
<?php }
else
{
	require_once'db_config.php';
	
	$mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
	
	if ($mysqli->connect_errno) {
		die("Failed to connect to MySQL: ".$mysqli->connect_errno);
	}
	
	$sql = "SELECT * FROM patrolcar WHERE patrolcarId = ?";
	
	if (!($stmt = $mysqli->prepare($sql))){
		die("Failed preperation: ".$mysqli->errno);
	}
	
	if (!$stmt->bind_param('s', $_POST['patrolcarId'])){
		die("Parameters bind failed: ".$stmt->errno);
	}
	
	if (!$stmt->execute()) {
		die("Failed execution: ".$stmt->errno);
	}
	
	if (!($resultset = $stmt->get_result())) {
		die("Getting result set failed: ".$stmt->errno);
	}
	
	if ($resultset->num_rows == 0){
	?>
	<script>window.location="update.php";</script>
	<?php }
	
	$patrolcarId;
	$patrolcarStatusId;
	
	while ($row = $resultset->fetch_assoc()) {
		$patrolcarId = $row['patrolcarId'];
		$patrolcarStatusId = $row['patrolcarStatusId'];
	}
	
	$sql = "SELECT * FROM patrolcar_status";
	if (!($stmt = $mysqli->prepare($sql))) {
		die("Failed preperation: ".$mysqli->errno);
	}
	
	if (!$stmt->execute()) {
		die("Failed execution: ".$stmt->errno);
	}
	
	if (!($resultset = $stmt->get_result())) {
		die("Getting result set failed: ".$stmt->errno);
	}
	
	$patrolcarStatusArray;;
	
	while ($row = $resultset->fetch_assoc()) {
		$patrolcarStatusArray[$row['statusId']] = $row['statusDesc'];
	}
	
	$stmt->close();
	
	$resultset->close();
	
	$mysqli->close();
?>
	
<form name="form2" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?> ">

	<table width="50%" border="0" align="center" cellpadding="4" cellspacing="4">
<tr></tr>
<tr>
<td>ID :</td>
<td><?php echo $patrolcarId ?>
<input type="hidden" name="patrolcarId" id="patrolcarId" value="<?php echo $patrolcarId ?>">	
</td>
</tr>
<tr>
<td>Status :</td>
<td><select name="patrolcarStatus" id="patrolcarStatus">
<?php foreach( $patrolcarStatusArray as $key => $value){ ?>
<option value="<?php echo $key ?>"
<?php if ($key==$patrolcarStatusId) {?> selected="selected"
<?php }?>
>
	<?php echo $value ?>
	</option>
<?php } ?>
</select></td>
</tr>
<tr>
<td><input type="reset" name="btnCancel" id="btnCancel" value="Reset"></td>
<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="btnUpdate" id="btnUpdate" value="Update">
</td>
</tr>
</table>
</form>
<?php } ?>
</body>
</html>