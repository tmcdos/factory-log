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

$month = Array('NONE','January','February','March','April','May','June','July','August','September','October','November','December');

if(isset($_POST['edit1']))
{
	$col['NOMER'] = (int)$_POST['nomer'];
	$col['DATA'] = ivo_str($_POST['datum']);
	$col['OPERAT'] = (int)$_POST['opera'];
	$col['BROI'] = fnum($_POST['rabota']);
	$col['ORDERID'] = (int)$_POST['orderid'];
	if(!$col['NOMER']) $err='Missing employee';
	elseif(!$col['OPERAT']) $err='Missing operation';
	elseif(!$col['ORDERID']) $err='Missing order';
	elseif($col['BROI']<=0) $err='Missing work quantity';
	elseif(!ChekDate($col['DATA'])) $err='Invalid date';
	else
	{
		if($err=='')
		{
			$col['DATA'] = GDate($col['DATA']);
			if($_REQUEST['edit_1']) $query = 'UPDATE RABOTA SET '.IVO_update($col,'IVO').' WHERE ID='.$_REQUEST['edit_1'];
				else $query = 'INSERT INTO RABOTA '.IVO_insert($col,'IVO');
		 	$result = mysql_query($query,$conn);
		 	$a = mysql_errno($conn);
		 	if($a)
		 	{
		 		if($a == 1062) $err='Duplicate data';
			 		else trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
		 	}
		 	else
		 	{
		 		if($_REQUEST['edit_1']) $query = 'UPDATE RABOTA SET CHANGED=NOW(),CHANGER='.$user->login['ID'].' WHERE ID='.$_REQUEST['edit_1'];
		 			else $query = 'UPDATE RABOTA SET CREATED=NOW(),WRITER='.$user->login['ID'].' WHERE ID='.mysql_insert_id();
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
	$query = 'DELETE FROM RABOTA WHERE ID='.$id;
 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
}

	if($b = @file_get_contents($tmpdir.'/temp/rabota.htm'))
	{
		$b = str_replace('{HEADER}',@file_get_contents($tmpdir.'/temp/header.htm'),$b);
		$b = str_replace('{FOOTER}',@file_get_contents($tmpdir.'/temp/footer.htm'),$b);
		if($err!='') $z = 'alert("'.$err.'");';
			else $z = '';
		$b = str_replace('<!--{ERROR}-->',$z,$b);
		$b = str_replace('{PREP}',WEBDIR,$b);
		MakeMenu($b);
		
		// show distinct months from database
		$today = date('Ym');
		if($_REQUEST['month']=='') $_REQUEST['month'] = $today;
		$query = 'SELECT DISTINCT DATE_FORMAT(DATA,"%Y%m") AS DAT FROM RABOTA ORDER BY DAT';
	 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
	 	$z = '';
		while($row = mysql_fetch_array($result,MYSQL_NUM))
		{
			$last_data = $row[0];
			$z.= '<a class="whead" '.($last_data==$_REQUEST['month'] ? 'style="background-color:LightSkyBlue"' : '').' href="rabota.php?month='.$last_data.'">&nbsp;'.$month[(int)substr($last_data,4,2)].'&nbsp;&nbsp;'.substr($last_data,0,4).'&nbsp;</a> ';
		}
		if($last_data < $today) $z.= '<a class="whead" style="background-color:LightSkyBlue" href="rabota.php?month='.$today.'">&nbsp;'.$month[(int)substr($today,4,2)].'&nbsp;&nbsp;'.substr($today,0,4).'&nbsp;</a>';
		$b = str_replace('{MONTH_LIST}','<div style="padding:2px;line-height:150%">'.$z.'</div>',$b);
		$b = str_replace('{MONTH_ID}',$_REQUEST['month'],$b);
		$b = str_replace('{CUR_MONT}',$month[(int)substr($_REQUEST['month'],4,2)].'&nbsp;&nbsp;'.substr($_REQUEST['month'],0,4),$b);
		
		$sql_data = substr($_REQUEST['month'],0,4).'-'.substr($_REQUEST['month'],4,2);
		$query = 'SELECT R.*,W.NAME AVTOR,R.CREATED,U.NAME UPDATER FROM RABOTA R
			LEFT JOIN PERSON ON NOMER=PERSON.ID 
			LEFT JOIN ORDERS ON ORDERID=ORDERS.ID 
			LEFT JOIN OPERATION ON OPERAT=OPERATION.ID 
			LEFT JOIN USER W ON R.WRITER=W.ID
			LEFT JOIN USER U ON R.CHANGER=U.ID
			WHERE DATA BETWEEN "'.$sql_data.'-01" AND "'.$sql_data.'-31" 
			ORDER BY DATA,PERSON.NAME,PROJECT,OPERATION.NAME';
	 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
	 	$z = '';
		while($row = mysql_fetch_array($result,MYSQL_ASSOC))
		{
			$z.= '<tr align="center"';
			if($row['ID']==$_REQUEST['edit_1']) $z.=' bgcolor="'.COL_EDIT.'"';
			$z.= '><td>'.a_select('PERSON',$row['NOMER'],'NAME').'</td><td>&nbsp;'.ADate($row['DATA'],'-').'&nbsp;</td><td>';
		  $z.= ($row['OPERAT'] ? a_select('OPERATION',$row['OPERAT'],'NAME') : '&nbsp;').'</td>
			  <td>'.($row['BROI']>0 ? $row['BROI'] : '&nbsp;').'</td>
			  <td>'.($row['ORDERID'] ? a_select('ORDERS',$row['ORDERID'],'PROJECT') : '&nbsp;').'</td>
			  <td><a href="'.WEBDIR.'/rabota.php?edit_1='.$row['ID'].'" onClick="javascript: blur();"><img src="'.WEBDIR.'/images/editicon.gif" border="0" align="absmiddle" width="12" height="12"></a>&nbsp;&nbsp;';
			//$z.= '<a href="'.WEBDIR.'/rabota.php?del_1='.$row[0].'" onClick="javascript: blur(); return FinalConfirm(\'Do you really want to delete this color ?\');"><img src="'.WEBDIR.'/images/stop.gif" border="0" align="absmiddle" width="16" height="16"></a>';
			$z.= '</td><td>'.($row['AVTOR']!='' ? $row['AVTOR'] : '&nbsp;').'</td><td>'.((int)$row['CREATED'] ? ADate($row['CREATED'],'-') : '&nbsp;').'</td>
			  <td>'.($row['UPDATER']!='' ? $row['UPDATER'] : '&nbsp;').'</td><td>'.((int)$row['CHANGED'] ? ADate($row['CHANGED'],'-') : '&nbsp;').'</td></tr>'.chr(13).chr(10);
		}
		$b = str_replace('<tr><td>{ITEM_1}</td></tr>',$z,$b);
		if($_REQUEST['edit_1'])
		{
			$query = 'SELECT * FROM RABOTA WHERE ID='.$_REQUEST['edit_1'];
		 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
	   	if(mysql_num_rows($result)) $item = mysql_fetch_array($result,MYSQL_ASSOC);
			$b = str_replace('{EDIT_1}','EDIT',$b);
		}
		else $b = str_replace('{EDIT_1}','ADD',$b);
		$b = str_replace('{EDITID1}',$_REQUEST['edit_1'],$b);
		$b = str_replace('<option value="0">{NOMER}</option>',loadItems('PERSON','NAME',$item['NOMER'],' ','WHERE ACTIVE','NAME'),$b);
		$b = str_replace('{DATUM}',$_REQUEST['edit_1']>0 ? ADate($item['DATA'],'-') : $_POST['datum'],$b);
		$b = str_replace('<option value="0">{OPERA}</option>',loadItems('OPERATION','NAME',$item['OPERAT'],' ','WHERE ACTIVE','NAME'),$b);
		$b = str_replace('{RABOTA}',$item['BROI'],$b);
		$b = str_replace('<option value="0">{ORDERID}</option>',loadItems('ORDERS','PROJECT',$item['ORDERID'],' ','WHERE ACTIVE','PROJECT'),$b);

		echo $b;
	}
	else die('Could not find template - rabota.htm');
?>