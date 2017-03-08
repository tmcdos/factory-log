<?php
include('./INC/DBASE.PHP');
include('./INC/logon.php');
session_start();
$user = &$_SESSION['user'];

$role = $user->login['ROLE'];
if($user->logout <= time())
{
	loger('Session timeout ['.$user->login['NAME'].']');
	@session_destroy();
	header('Location:'.WEBDIR.'/login.php');
	die;
}
else
{
	// set TIMESTAMP to logout
	$user->logout = time() + SESS_LEN;
}

	if($b = @file_get_contents($tmpdir.'/temp/report.htm'))
	{
		$b = str_replace('{HEADER}',@file_get_contents($tmpdir.'/temp/header.htm'),$b);
		$b = str_replace('{FOOTER}',@file_get_contents($tmpdir.'/temp/footer.htm'),$b);
		if($err!='') $z = 'alert("'.$err.'");';
			else $z = '';
		$b = str_replace('<!--{ERROR}-->',$z,$b);
		$b = str_replace('{PREP}',WEBDIR,$b);
		MakeMenu($b);

		// Report 1
		$z = loadItems('PERSON','NAME',0,' ','','NAME');
		$b = str_replace('<option value="0">{RABOTNIK}</option>',$z,$b);
		// Report 2
		$z = loadItems('ORDERS','PROJECT',0,'- All -','','PROJECT');
		$b = str_replace('<option value="0">{ORDERS}</option>',$z,$b);
		// Report 4
		$z = loadItems('BRIGADA','NAME',0,'- All -','','NAME');
		$b = str_replace('<option value="0">{BRIGADA}</option>',$z,$b);
		
		// Vedomost - zarabotki filter
		$query = 'SELECT ID,NAME FROM OPERATION ORDER BY NAME';
  	$result = mysql_query($query) or trigger_error($query.'<br>'.mysql_error(),E_USER_ERROR);
  	$z = '';
  	while ($row = mysql_fetch_array($result,MYSQL_NUM))
  	  $z.='<tr><td align="center"><input type="checkbox" name="chek['.$row[0].']" value="1"></td>
  	    <td>'.$row[1].'</td></tr>'.chr(13).chr(10);
		$b = str_replace('<tr><td>{OPERA}</td></tr>',$z,$b);
		
		echo $b;
	}
	else die('Could not find template - report.htm');
?>