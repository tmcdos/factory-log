<?php

// Job done per employee for a time period

	echo '<h4 align="center">Job done per employee and operation ('.($_POST['ord_2']>0 ? 'order '.a_select('ORDERS',(int)$_POST['ord_2'],'PROJECT') : 'all orders');
	echo ') for '.($_POST['beg_2'] != $_POST['end_2'] ? 'the period between '.$_POST['beg_2'].' and '.$_POST['end_2'] : $_POST['beg_2']).'</h4>';

	// prepare table with person partial sums
	$uname = 'TMP'.time();
	$query = 'CREATE TEMPORARY TABLE '.$uname.' SELECT NOMER,ORDERID,OPERAT,SUM(BROI) SUMA FROM RABOTA WHERE ';
	if($_POST['ord_2']>0) $query.= 'ORDERID='.$_POST['ord_2'].' AND ';
	$query.= 'DATA BETWEEN "'.GDate($_POST['beg_2']).'" AND "'.GDate($_POST['end_2']).'" GROUP BY NOMER,ORDERID,OPERAT';
	$result = mysql_query($query) or trigger_error($query.'<br>'.mysql_error(),E_USER_ERROR);
	// load distinct operations
	$query = 'SELECT DISTINCT OPERAT,NAME,PARI FROM '.$uname.' LEFT JOIN OPERATION ON OPERAT=OPERATION.ID';
	$result = mysql_query($query) or trigger_error($query.'<br>'.mysql_error(),E_USER_ERROR);
	while ($row = mysql_fetch_array($result,MYSQL_NUM)) $opera[$row[0]] = Array($row[1],$row[2]);

	echo '<TABLE ALIGN="CENTER" BORDER="1" CELLSPACING="0" CELLPADDING="2" BORDERCOLOR="black" style="font-size:10pt">
	  <TR BGCOLOR="#CCCCCC">
	  <TH>No.</TH>
	  <TH>Full name</TH>
	  <TH>Nickname</TH>
	  <TH>Order</TH>
	  <TH>Suma</TH>';
	foreach($opera as $v)
		echo '<TH>'.$v[0].'</TH>';
	echo '</TR>'.chr(13).chr(10);
	
	// iterate brigades
	$query = 'SELECT * FROM BRIGADA ORDER BY NAME';
	$result = mysql_query($query) or trigger_error($query.'<br>'.mysql_error(),E_USER_ERROR);
	while ($bri = mysql_fetch_array($result,MYSQL_NUM))
	{
		echo '<tr><td colspan="5" align="center"><b>'.$bri[1].'</td>';
		for($i=0; $i<count($opera); $i++)
			echo '<td>&nbsp;</td>';
		echo '</tr>'.chr(13).chr(10);
		// show persons from this brigade
		$query = 'SELECT * FROM PERSON WHERE BRIGADA='.$bri[0].' ORDER BY NAME';
		$res2 = mysql_query($query) or trigger_error($query.'<br>'.mysql_error(),E_USER_ERROR);
		while ($person = mysql_fetch_array($res2,MYSQL_NUM))
		{
			// iterate orders for this person
			$query = 'SELECT DISTINCT ORDERID,PROJECT FROM '.$uname.' LEFT JOIN ORDERS ON ORDERID=ORDERS.ID WHERE NOMER='.$person[0];
			$res3 = mysql_query($query) or trigger_error($query.'<br>'.mysql_error(),E_USER_ERROR);
			while ($ord = mysql_fetch_array($res3,MYSQL_NUM))
			{
				echo '<tr><td align="right">'.$person[0].'</td><td>'.$person[1].'</td><td align="center">'.($person[2]!='' ? $person[2] : '&nbsp;').'</td><td>'.$ord[1].'</td>';
				// accumulate operations
				$suma = 0;
				$z = '';
				foreach($opera as $k=>$v)
				{
					$query = 'SELECT SUMA FROM '.$uname.' WHERE NOMER='.$person[0].' AND ORDERID='.$ord[0].' AND OPERAT='.$k;
					$res4 = mysql_query($query) or trigger_error($query.'<br>'.mysql_error(),E_USER_ERROR);
					if(mysql_num_rows($res4)) $s = mysql_result($res4,0,0);
						else $s = 0;
					$z.= '<td align="center">'.($s ? $s : '&nbsp;').'</td>';
					$suma += $s * $v[1];
				}
				echo '<td align="right">'.number_format($suma,2,'.',' ').'</td>'.$z.'</tr>'.chr(13).chr(10);
				$brigada += $suma;
			}
		}
		echo '<tr bgcolor="yellow" style="font-weight:bold"><td colspan="4" align="right">Accumulated for the team:</td><td align="right">'.number_format($brigada,2,"."," ").'</td>';
		for($i=0; $i<count($opera); $i++)
			echo '<td>&nbsp;</td>';
		echo '</tr>'.chr(13).chr(10);
		$tot += $brigada;
		$brigada = 0;
	}
	echo '<tr bgcolor="yellow" style="font-weight:bold"><td colspan="4" align="right">TOTAL:</td><td align="right">'.number_format($tot,2,"."," ").'</td>';
	for($i=0; $i<count($opera); $i++)
		echo '<td>&nbsp;</td>';
	echo '</tr>'.chr(13).chr(10);
	echo '</TABLE>'.chr(13).chr(10);

	$query = 'DROP TABLE '.$uname;
	$result = mysql_query($query) or trigger_error($query.'<br>'.mysql_error(),E_USER_ERROR);

?>