<?php 
include('./INC/DBASE.PHP');
include('./INC/logon.php');
session_start();
$user = &$_SESSION['user'];

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

if(isset($_POST['edit1']))
{
	$col['ID'] = fnum($_POST['nomer']);
	$col['PROJECT'] = ivo_str($_POST['project']);
	$col['COUNTRY'] = ivo_str($_POST['country']);
	if(strlen($col['PROJECT'])>50) $err='Order name can be no longer than 50 symbols';
	elseif(strlen($col['COUNTRY'])>35) $err='Country name can be no longer than 35 symbols';
	elseif($col['PROJECT']=='') $err='Missing project name';
	elseif($col['COUNTRY']=='') $err='Missing country name';
	elseif(!$col['ID']) $err='Missing order ID';
	else
	{
		if($err=='')
		{
			if($_REQUEST['edit_1']) $query = 'UPDATE ORDERS SET '.IVO_update($col,'IVO').' WHERE ID='.$_REQUEST['edit_1'];
				else $query = 'INSERT INTO ORDERS '.IVO_insert($col,'IVO');
		 	$result = mysql_query($query,$conn);
		 	$a = mysql_errno($conn);
		 	if($a)
		 	{
		 		if($a == 1062) $err='Duplicate order';
			 		else trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
		 	}
		 	else
		 	{
		 		if($_REQUEST['edit_1']) $query = 'UPDATE ORDERS SET CHANGED=NOW(),CHANGER='.$user->login['ID'].' WHERE ID='.$_REQUEST['edit_1'];
		 			else $query = 'UPDATE ORDERS SET CREATED=NOW(),WRITER='.$user->login['ID'].' WHERE ID='.mysql_insert_id();
			 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
		 	}
		}
		if($err=='')
		{
			unset($col);
			unset($_REQUEST['edit_1']);
		}
	}
}

if($_GET['del_1'])
{
	$id = $_GET['del_1'];
	// check if used in any job log record
	$query = 'SELECT COUNT(*) FROM RABOTA WHERE ORDERID='.$id;
 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
 	$a = mysql_result($result,0,0);

 	if($a) $err='Can not delete - this project is already used in job log';
 	else
 	{
		$query = 'DELETE FROM ORDERS WHERE ID='.$id;
	 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
 	}
}

if($_GET['act_1'])
{
	$id = $_GET['act_1'];
	$query = 'UPDATE ORDERS SET ACTIVE = NOT ACTIVE,CHANGED=NOW(),CHANGER='.$user->login['ID'].' WHERE ID='.$id;
 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
 	header('Location:orders.php');
 	die;
}

	if($b = @file_get_contents($tmpdir.'/temp/orders.htm'))
	{
		$b = str_replace('{HEADER}',@file_get_contents($tmpdir.'/temp/header.htm'),$b);
		$b = str_replace('{FOOTER}',@file_get_contents($tmpdir.'/temp/footer.htm'),$b);
		if($err!='') $z = 'alert("'.$err.'");';
			else $z = '';
		$b = str_replace('<!--{ERROR}-->',$z,$b);
		$b = str_replace('{PREP}',WEBDIR,$b);
		MakeMenu($b);

		$query = 'SELECT O.ID,PROJECT,COUNTRY,O.ACTIVE,W.NAME AVTOR,CREATED,U.NAME UPDATER,CHANGED FROM ORDERS O
			LEFT JOIN USER W ON WRITER=W.ID LEFT JOIN USER U ON CHANGER=U.ID ORDER BY O.ACTIVE DESC,PROJECT,COUNTRY';
	 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
	 	$z = '';
		while($row = mysql_fetch_array($result,MYSQL_ASSOC))
		{
			$z.= '<tr';
			if($row['ID']==$_REQUEST['edit_1']) $z.=' bgcolor="'.COL_EDIT.'"';
			elseif(!$row['ACTIVE']) $z.= ' bgcolor="'.COL_DEL.'"';
			$z.= '><td align="center">'.$row['ID'].'</td><td>&nbsp;'.$row['PROJECT'].'&nbsp;</td><td align="center">
			  <b>'.($row['COUNTRY']!='' ? $row['COUNTRY'] : '&nbsp;').'</b></td>
			  <td align="center"><a href="'.WEBDIR.'/orders.php?act_1='.$row['ID'].'" onClick="javascript: blur();"><img src="'.WEBDIR.'/images/'.($row['ACTIVE'] ? 'v' : 'x').'_serif.gif" border="0" align="absmiddle" width="16" height="16" alt="Check"></a></td>
			  <td align="center">
			  <a href="'.WEBDIR.'/orders.php?edit_1='.$row['ID'].'" onClick="javascript: blur();"><img src="'.WEBDIR.'/images/editicon.gif" border="0" align="absmiddle" width="12" height="12"></a>&nbsp;&nbsp;';
			//$z.= '<a href="'.WEBDIR.'/orders.php?del_1='.$row['ID'].'" onClick="javascript: blur(); return FinalConfirm(\'Do you really want to delete this color ?\');"><img src="'.WEBDIR.'/images/stop.gif" border="0" align="absmiddle" width="16" height="16"></a>';
			$z.= '</td><td>'.($row['AVTOR']!='' ? $row['AVTOR'] : '&nbsp;').'</td><td>'.((int)$row['CREATED'] ? ADate($row['CREATED'],'-') : '&nbsp;').'</td>
			  <td>'.($row['UPDATER']!='' ? $row['UPDATER'] : '&nbsp;').'</td><td>'.((int)$row['CHANGED'] ? ADate($row['CHANGED'],'-') : '&nbsp;').'</td></tr>'.chr(13).chr(10);
		}
		$b = str_replace('<tr><td>{ITEM_1}</td></tr>',$z,$b);
		if($_REQUEST['edit_1'])
		{
			$query = 'SELECT * FROM ORDERS WHERE ID='.$_REQUEST['edit_1'];
		 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
	   	if(mysql_num_rows($result)) $item = mysql_fetch_array($result,MYSQL_ASSOC);
			$b = str_replace('{EDIT_1}','EDIT',$b);
		}
		else $b = str_replace('{EDIT_1}','ADD',$b);
		$b = str_replace('{EDITID1}',$_REQUEST['edit_1'],$b);
		$b = str_replace('{NOMER}',$item['ID'],$b);
		$b = str_replace('{PROJECT}',$item['PROJECT'],$b);
		$b = str_replace('{COUNTRY}',$item['COUNTRY'],$b);
		$b = str_replace('{ACT_0}',$item['ACTIVE']==0 ? 'selected' : '',$b);
		$b = str_replace('{ACT_1}',$item['ACTIVE']!=0 ? 'selected' : '',$b);

		echo $b;
	}
	else die('Could not find template - orders.htm');
?>