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
	$col['NAME'] = ivo_str($_POST['brigada']);
	if(strlen($col['NAME'])>80) $err='Team name can be no longer than 80 symbols';
	elseif($col['NAME']=='') $err='Missing name of team';
	else
	{
		if($err=='')
		{
			if($_REQUEST['edit_1']) $query = 'UPDATE BRIGADA SET '.IVO_update($col,'IVO').' WHERE ID='.$_REQUEST['edit_1'];
				else $query = 'INSERT INTO BRIGADA '.IVO_insert($col,'IVO');
		 	$result = mysql_query($query,$conn);
		 	$a = mysql_errno($conn);
		 	if($a)
		 	{
		 		if($a == 1062) $err='Duplicate team name';
			 		else trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
		 	}
		 	else
		 	{
		 		if($_REQUEST['edit_1']) $query = 'UPDATE BRIGADA SET CHANGED=NOW(),CHANGER='.$user->login['ID'].' WHERE ID='.$_REQUEST['edit_1'];
		 			else $query = 'UPDATE BRIGADA SET CREATED=NOW(),WRITER='.$user->login['ID'].' WHERE ID='.mysql_insert_id();
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
	// check if color is used in any order
	$query = 'SELECT COUNT(*) FROM PERSON WHERE BRIGADA='.$id;
 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
 	$a = mysql_result($result,0,0);

 	if($a) $err='Can not delete non-empty team';
 	else
 	{
		$query = 'DELETE FROM BRIGADA WHERE ID='.$id;
	 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
 	}
}

	if($b = @file_get_contents($tmpdir.'/temp/brigada.htm'))
	{
		$b = str_replace('{HEADER}',@file_get_contents($tmpdir.'/temp/header.htm'),$b);
		$b = str_replace('{FOOTER}',@file_get_contents($tmpdir.'/temp/footer.htm'),$b);
		if($err!='') $z = 'alert("'.$err.'");';
			else $z = '';
		$b = str_replace('<!--{ERROR}-->',$z,$b);
		$b = str_replace('{PREP}',WEBDIR,$b);
		MakeMenu($b);

		$query = 'SELECT B.ID,B.NAME TITLE,W.NAME AVTOR,CREATED,U.NAME UPDATER,CREATED FROM BRIGADA B LEFT JOIN USER W ON WRITER=W.ID LEFT JOIN USER U ON CHANGER=U.ID ORDER BY B.NAME';
	 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
	 	$z = '';
		while($row = mysql_fetch_array($result,MYSQL_ASSOC))
		{
			$z.= '<tr';
			if($row['ID']==$_REQUEST['edit_1']) $z.=' bgcolor="'.COL_EDIT.'"';
			$z.= '><td align="center">'.$row['ID'].'</td><td>&nbsp;'.$row['TITLE'].'&nbsp;</td>
			  <td align="center">
			  <a href="'.WEBDIR.'/brigada.php?edit_1='.$row['ID'].'" onClick="javascript: blur();"><img src="'.WEBDIR.'/images/editicon.gif" border="0" align="absmiddle" width="12" height="12"></a>&nbsp;&nbsp;';
			//$z.= '<a href="'.WEBDIR.'/brigada.php?del_1='.$row['ID'].'" onClick="javascript: blur(); return FinalConfirm(\'Do you really want to delete this color ?\');"><img src="'.WEBDIR.'/images/stop.gif" border="0" align="absmiddle" width="16" height="16"></a>';
			$z.= '</td><td>'.($row['AVTOR']!='' ? $row['AVTOR'] : '&nbsp;').'</td><td>'.((int)$row['CREATED'] ? ADate($row['CREATED'],'-') : '&nbsp;').'</td>
			  <td>'.($row['UPDATER']!='' ? $row['UPDATER'] : '&nbsp;').'</td><td>'.((int)$row['CHANGED'] ? ADate($row['CHANGED'],'-') : '&nbsp;').'</td></tr>'.chr(13).chr(10);
		}
		$b = str_replace('<tr><td>{ITEM_1}</td></tr>',$z,$b);
		if($_REQUEST['edit_1'])
		{
			$query = 'SELECT * FROM BRIGADA WHERE ID='.$_REQUEST['edit_1'];
		 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
	   	if(mysql_num_rows($result)) $item = mysql_fetch_array($result,MYSQL_ASSOC);
			$b = str_replace('{EDIT_1}','EDIT',$b);
		}
		else $b = str_replace('{EDIT_1}','ADD',$b);
		$b = str_replace('{EDITID1}',$_REQUEST['edit_1'],$b);
		$b = str_replace('{BRIGADA}',$item['NAME'],$b);

		echo $b;
	}
	else die('Could not find template - brigada.htm');
?>