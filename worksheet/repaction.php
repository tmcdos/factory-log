<?php
include('./INC/DBASE.PHP');
include('./INC/logon.php');
session_start();
$user = &$_SESSION['user'];

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Report</title>
<meta http-equiv="Content-type" content="text/html;charset=windows-1251">
<link rel="stylesheet" type="text/css" href="<?php echo WEBDIR; ?>/skin/normal.css">
</head>
<body>
<?php
if($user->logout <= time())
{
	loger('Session timeout ['.$user->login['NAME'].']');
	@session_destroy();
	echo '<script language="JavaScript" type="text/javascript">';
	echo 'window.close();</script>';
}
else
{
	// set TIMESTAMP to logout
	$user->logout = time() + SESS_LEN;

  if (isset($_POST['cmdKarta'])) include('rep_karta.php');
 	if (isset($_POST['cmdRab'])) include('rep_rab.php');
  if (isset($_POST['cmdOper'])) include('rep_oper.php');
  if (isset($_POST['cmdNova'])) include('rep_nova.php');
  if (isset($_POST['cmdZap'])) include('rep_zap.php');
}
?>
</body>
</html>
