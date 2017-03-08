<?php

// Log of job done per order for a time period

	echo '<h4 align="center">Job done per order and operation for '.($_POST['beg_3'] != $_POST['end_3'] ? 'the period between '.$_POST['beg_3'].' and '.$_POST['end_3'] : $_POST['beg_3']).'</h4>';

	// prepare table with person partial sums
	$uname = 'TMP'.time();
	$query = 'CREATE TEMPORARY TABLE '.$uname.' SELECT ORDERID,OPERAT,SUM(BROI) SUMA FROM RABOTA WHERE 
	  DATA BETWEEN "'.GDate($_POST['beg_3']).'" AND "'.GDate($_POST['end_3']).'" GROUP BY ORDERID,OPERAT';
	$result = mysql_query($query) or trigger_error($query.'<br>'.mysql_error(),E_USER_ERROR);
	// load distinct operations
	$query = 'SELECT DISTINCT OPERAT,NAME,PARI FROM '.$uname.' LEFT JOIN OPERATION ON OPERAT=OPERATION.ID';
	$result = mysql_query($query) or trigger_error($query.'<br>'.mysql_error(),E_USER_ERROR);
	while ($row = mysql_fetch_array($result,MYSQL_NUM)) $opera[$row[0]] = Array($row[1],$row[2]);

	echo '<TABLE ALIGN="CENTER" BORDER="1" CELLSPACING="0" CELLPADDING="2" BORDERCOLOR="black" style="font-size:10pt">
	  <TR BGCOLOR="#CCCCCC">
	  <TH>ID</TH>
	  <TH>Order</TH>
	  <TH>Country</TH>
	  <TH>Sum</TH>';
	foreach($opera as $v)
		echo '<TH>'.$v[0].'</TH>';
	echo '</TR>'.chr(13).chr(10);
	
	// iterate orders for this person
	$query = 'SELECT DISTINCT ORDERID,PROJECT,COUNTRY FROM '.$uname.' LEFT JOIN ORDERS ON ORDERID=ORDERS.ID ORDER BY PROJECT';
	$res3 = mysql_query($query) or trigger_error($query.'<br>'.mysql_error(),E_USER_ERROR);
	while ($ord = mysql_fetch_array($res3,MYSQL_NUM))
	{
		echo '<tr><td align="right">'.$ord[0].'</td><td align="center">'.$ord[1].'</td><td align="center">'.$ord[2].'</td>';
		// accumulate operations
		$suma = 0;
		$z = '';
		foreach($opera as $k=>$v)
		{
			$query = 'SELECT SUMA FROM '.$uname.' WHERE ORDERID='.$ord[0].' AND OPERAT='.$k;
			$res4 = mysql_query($query) or trigger_error($query.'<br>'.mysql_error(),E_USER_ERROR);
			if(mysql_num_rows($res4)) $s = mysql_result($res4,0,0);
				else $s = 0;
			$z.= '<td align="center">'.($s ? $s : '&nbsp;').'</td>';
			$suma += $s * $v[1];
		}
		echo '<td align="right">'.number_format($suma,2,'.',' ').'</td>'.$z.'</tr>'.chr(13).chr(10);
		$tot += $suma;
	}
	echo '<tr bgcolor="yellow" style="font-weight:bold"><td colspan="3" align="right">TOTAL:</td><td align="right">'.number_format($tot,2,"."," ").'</td>';
	for($i=0; $i<count($opera); $i++)
		echo '<td>&nbsp;</td>';
	echo '</tr>'.chr(13).chr(10);
	echo '</TABLE>'.chr(13).chr(10);

	$query = 'DROP TABLE '.$uname;
	$result = mysql_query($query) or trigger_error($query.'<br>'.mysql_error(),E_USER_ERROR);

?>