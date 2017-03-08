<?php

// Log of job done per team, order, operation for a time period

	echo '<h4 align="center">Log of job done per team, order, operation for '.($_POST['beg_4'] != $_POST['end_4'] ? 'the period between '.$_POST['beg_4'].' and '.$_POST['end_4'] : $_POST['beg_4']).'</h4>
	  <TABLE ALIGN="CENTER" BORDER="1" CELLSPACING="0" CELLPADDING="2" BORDERCOLOR="black" style="font-size:10pt">
	  <TR BGCOLOR="#CCCCCC">
	  <TH>Team</TH>
	  <TH>Order</TH>
	  <TH>Operation</TH>
	  <TH>Quantity</TH>
	  <TH>Sum</TH>
	  </TR>'.chr(13).chr(10);

	$query = 'SELECT BRIGADA.NAME OTBOR,PROJECT,OPERATION.NAME JOB,SUM(BROI),SUM(BROI*PARI) FROM RABOTA 
	  LEFT JOIN OPERATION ON OPERATION.ID=OPERAT
	  LEFT JOIN ORDERS ON ORDERS.ID=ORDERID
	  LEFT JOIN PERSON ON NOMER=PERSON.ID
	  LEFT JOIN BRIGADA ON BRIGADA.ID=BRIGADA
	  WHERE DATA BETWEEN "'.GDate($_POST['beg_4']).'" AND "'.GDate($_POST['end_4']).'"';
	if($_POST['bri_4']!=0) $query.=' AND BRIGADA='.$_POST['bri_4'];
	if($_POST['ord_4']!=0) $query.=' AND ORDERID='.$_POST['ord_4'];
	$query.=' GROUP BY BRIGADA,ORDERID,OPERAT';
	$result = mysql_query($query) or trigger_error($query.'<br>'.mysql_error(),E_USER_ERROR);
	while ($row = mysql_fetch_array($result,MYSQL_NUM))
	{
		echo '<tr><td>'.$row[0].'</td><td>'.$row[1].'</td><td>'.$row[2].'</td><td align="right">'.round($row[3],2).'</td><td align="right">'.number_format($row[4],2,'.',chr(160)).'</td></tr>'.chr(13).chr(10);
		$suma += $row[4];
	}
	echo '<tr bgcolor="yellow" style="font-weight:bold"><td colspan="4" align="right">TOTAL:</td><td align="right">'.number_format($suma,2,'.',chr(160)).'</td></tr>'.chr(13).chr(10);
	echo '</TABLE>'.chr(13).chr(10);

?>