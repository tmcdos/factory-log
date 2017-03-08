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
	$col['NAME'] = ivo_str($_POST['ime']);
	$col['PARI'] = fnum($_POST['cena']);
	if(strlen($col['NAME'])>50) $err='Name of operation can be no longer than 50 symbols';
	elseif($col['NAME']=='') $err='Missing name of operation';
	elseif(!$col['PARI']) $err='Missing wage for operation';
	else
	{
		if($err=='')
		{
			if($_REQUEST['edit_1']) $query = 'UPDATE OPERATION SET '.IVO_update($col,'IVO').' WHERE ID='.$_REQUEST['edit_1'];
				else $query = 'INSERT INTO OPERATION '.IVO_insert($col,'IVO');
		 	$result = mysql_query($query,$conn);
		 	$a = mysql_errno($conn);
		 	if($a)
		 	{
		 		if($a == 1062) $err='Duplicate name of operation';
			 		else trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
		 	}
		 	else
		 	{
		 		if($_REQUEST['edit_1']) $query = 'UPDATE OPERATION SET CHANGED=NOW(),CHANGER='.$user->login['ID'].' WHERE ID='.$_REQUEST['edit_1'];
		 			else $query = 'UPDATE OPERATION SET CREATED=NOW(),WRITER='.$user->login['ID'].' WHERE ID='.mysql_insert_id();
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
	// check if used in any job log
	$query = 'SELECT COUNT(*) FROM RABOTA WHERE OPERAT='.$id;
 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
 	$a = mysql_result($result,0,0);

 	if($a) $err='Can not delete - this operation was already used in a job log record';
 	else
 	{
		$query = 'DELETE FROM OPERATION WHERE ID='.$id;
	 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
 	}
 	header('Location:operation.php');
 	die;
}

if($_GET['act_1'])
{
	$id = $_GET['act_1'];
	$query = 'UPDATE OPERATION SET ACTIVE = NOT ACTIVE,CHANGED=NOW(),CHANGER='.$user->login['ID'].' WHERE ID='.$id;
 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
 	header('Location:operation.php');
 	die;
}

	if($b = @file_get_contents($tmpdir.'/temp/operation.htm'))
	{
		$b = str_replace('{HEADER}',@file_get_contents($tmpdir.'/temp/header.htm'),$b);
		$b = str_replace('{FOOTER}',@file_get_contents($tmpdir.'/temp/footer.htm'),$b);
		if($err!='') $z = 'alert("'.$err.'");';
			else $z = '';
		$b = str_replace('<!--{ERROR}-->',$z,$b);
		$b = str_replace('{PREP}',WEBDIR,$b);
		MakeMenu($b);

		$query = 'SELECT O.ID,O.NAME,PARI,O.ACTIVE,W.NAME AVTOR,CREATED,U.NAME UPDATER,CHANGED FROM OPERATION O
			LEFT JOIN USER W ON WRITER=W.ID LEFT JOIN USER U ON CHANGER=U.ID ORDER BY O.ACTIVE DESC,O.NAME';
	 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
	 	$z = '';
		while($row = mysql_fetch_array($result,MYSQL_ASSOC))
		{
			$z.= '<tr';
			if($row['ID']==$_REQUEST['edit_1']) $z.=' bgcolor="'.COL_EDIT.'"';
			elseif(!$row['ACTIVE']) $z.= ' bgcolor="'.COL_DEL.'"';
			$z.= '><td align="center">'.$row['ID'].'</td><td>&nbsp;'.$row['NAME'].'&nbsp;</td><td align="center">
			  <b>'.($row['PARI']>0 ? $row['PARI'] : '&nbsp;').'</b></td>
			  <td align="center"><a href="'.WEBDIR.'/operation.php?act_1='.$row['ID'].'" onClick="javascript: blur();"><img src="'.WEBDIR.'/images/'.($row['ACTIVE'] ? 'v' : 'x').'_serif.gif" border="0" align="absmiddle" width="16" height="16" alt="Check"></a></td>
			  <td align="center">
			  <a href="'.WEBDIR.'/operation.php?edit_1='.$row['ID'].'" onClick="javascript: blur();"><img src="'.WEBDIR.'/images/editicon.gif" border="0" align="absmiddle" width="12" height="12"></a>&nbsp;&nbsp;';
			//$z.= '<a href="'.WEBDIR.'/operation.php?del_1='.$row['ID'].'" onClick="javascript: blur(); return FinalConfirm(\'Do you really want to delete this color ?\');"><img src="'.WEBDIR.'/images/stop.gif" border="0" align="absmiddle" width="16" height="16"></a>';
			$z.= '</td><td>'.($row['AVTOR']!='' ? $row['AVTOR'] : '&nbsp;').'</td><td>'.((int)$row['CREATED'] ? ADate($row['CREATED'],'-') : '&nbsp;').'</td>
			  <td>'.($row['UPDATER']!='' ? $row['UPDATER'] : '&nbsp;').'</td><td>'.((int)$row['CHANGED'] ? ADate($row['CHANGED'],'-') : '&nbsp;').'</td></tr>'.chr(13).chr(10);
		}
		$b = str_replace('<tr><td>{ITEM_1}</td></tr>',$z,$b);
		if($_REQUEST['edit_1'])
		{
			$query = 'SELECT * FROM OPERATION WHERE ID='.$_REQUEST['edit_1'];
		 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
	   	if(mysql_num_rows($result)) $item = mysql_fetch_array($result,MYSQL_ASSOC);
			$b = str_replace('{EDIT_1}','EDIT',$b);
		}
		else $b = str_replace('{EDIT_1}','ADD',$b);
		$b = str_replace('{EDITID1}',$_REQUEST['edit_1'],$b);
		$b = str_replace('{NOMER}',$item['ID'],$b);
		$b = str_replace('{NAME}',$item['NAME'],$b);
		$b = str_replace('{CENA}',$item['PARI'],$b);
		$b = str_replace('{ACT_0}',$item['ACTIVE']==0 ? 'selected' : '',$b);
		$b = str_replace('{ACT_1}',$item['ACTIVE']!=0 ? 'selected' : '',$b);

		echo $b;
	}
	else die('Could not find template - operation.htm');
?>