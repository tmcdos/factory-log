<?php

// Worksheet of a given employee for a specified time period

	echo '<h4 align="center">Worksheet for <b>'.a_select('PERSON',$_POST['rab_1'],'NAME').' ('.a_select('PERSON',$_POST['rab_1'],'PREKOR').')</b>';
	echo ' for '.($_POST['beg_1'] != $_POST['end_1'] ? 'the period between '.$_POST['beg_1'].' and '.$_POST['end_1'] : $_POST['beg_1']).'</h4>';

	echo '<TABLE ALIGN="CENTER" BORDER="1" CELLSPACING="0" CELLPADDING="2" BORDERCOLOR="black" style="font-size:10pt">
	  <TR BGCOLOR="#CCCCCC">
	  <TH>Date</TH>
	  <TH colspan="2">Work operation</TH>
	  <TH>Quantity</TH>
	  <TH>Cost</TH>
	  <TH>Wage</TH>
	  </TR>'.chr(13).chr(10);
	
	$query = 'SELECT DATE_FORMAT(DATA,"%d-%m-%Y"),OPERAT,NAME,BROI,PARI FROM RABOTA LEFT JOIN OPERATION ON OPERAT=OPERATION.ID WHERE NOMER=';
	$query.= (int)$_POST['rab_1'].' AND DATA BETWEEN "'.GDate($_POST['beg_1']).'" AND "'.GDate($_POST['end_1']).'" ORDER BY DATA,NAME';
	$result = mysql_query($query) or trigger_error($query.'<br>'.mysql_error(),E_USER_ERROR);
	while ($row = mysql_fetch_array($result,MYSQL_NUM))
	{
		echo '<tr><td align="center">'.$row[0].'</td><td align="right">'.$row[1].'</td><td>'.$row[2].'</td><td align="center">'.$row[3].'</td>';
		$s = $row[3]*$row[4];
		$tot += $s;
		echo '<td align="right">'.number_format($row[4],2,'.',' ').'</td><td align="right">'.number_format($s,2,'.',' ').'</td></tr>'.chr(13).chr(10);
	}

	echo '<tr bgcolor="yellow" style="font-weight:bold"><td colspan="5" align="right">TOTAL:</td><td align="right">'.number_format($tot,2,"."," ").'</td></tr>';
	echo '</TABLE>'.chr(13).chr(10);

?>