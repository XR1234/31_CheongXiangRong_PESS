<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Police Emergency Service System</title>
<link href="style.css" rel="stylesheet" type="text/css">	
</head>

<body>
<script>
function logcallinfo()
{
	var x=document.forms["frmLogCall"]["callerName"].value;
	if (x==null || x=="")
	{
		alert("Caller Name is required.");
		return false;
	}
}
</script>
<?php require 'nav.php'?>
<?php require 'db_config.php';

$mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
if ($mysqli->connect_errno)
{
	die("Failed to connect to MySQL: ".$mysqli->connect_errno);
}
	
$sql = "SELECT * FROM incidenttype";
	
if (!($stmt = $mysqli->prepare($sql)))
{
	die("Failed preperation: ".$mysqli->errno);
}
	
if (!$stmt->execute())
{
	die("Failed execution: ".$stmt->errno);
}
	
if (!($resultset = $stmt->get_result())) {
	die("Getting result set failed: ".$stmt->errno);
}
	
	$incidentType;
	
while ($row = $resultset->fetch_assoc()) {
$incidentType[$row['incidentTypeId']] = $row['incidentTypeDesc'];
}
$stmt->close();
	
$resultset->close();

$mysqli->close();
	
?>
<fieldset>
<legend>Log Call</legend>
<form name="frmLogCall" method="post" action="dispatch.php" onSubmit="return logcallinfo();">
<table width="40%" border="0" align="center" cellpadding="4" cellspacing="4"
<tr>
<td width="50%">Caller's Name</td>
<td width="50%"><input type="text" name="callerName" id="callerName"></td>
</tr>
<tr>
<td width="50%">Contact No :</td>
<td width="50%"><input type="text" name="contactNo" id="contactNo"></td>
</tr>
<tr>
<td width="50%">Location :</td>
<td width="50%"><input type="text" name="incidentLocation" id="incidentLocation"></td>
</tr>
<tr>
<td width="50%">Incident Type :</td>
<td width="50%"><select type="text" name="incidentTypeId" id="incidentTypeId">
<?php foreach ($incidentType as $key=> $value) {?>
<option value="<?php echo $key ?> ">
	<?php echo $value ?> </option>
<?php } ?>
</select>
</td>
</tr>
<tr>
<td width="50%">Description :</td>
<td width="50%"><textarea name="incidentDesc" id="incidentDesc" cols="45" rows="5"></textarea></td>
</tr>
<tr>									  
<td> <input type="reset" name="cancelProcess" id="cancelProcess" value="Reset"</td>	
<td> <input type="submit" name="submitButton" id="submitButton" value="Submit"</td>
<tr>
</table>
</form>
</fieldset>
</body>
</html>