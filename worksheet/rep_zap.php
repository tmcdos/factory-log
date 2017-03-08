<style>
<!--
.editable
{
	border: 0px solid; 
	text-align: right; 
	font-family:Tahoma;
}
-->
</style>
<?php

// Payroll for salary

	$a = setlocale(LC_TIME,"en_US");
	list($m,$y) = preg_split('#[:/\.-]#',$_POST['mesec']);
	$beg_m = '01-'.$_POST['mesec'];
	$end_m = date('t-'.$_POST['mesec'],mktime(0,0,0,$m,1,$y));

	$query = 'SELECT NOMER,PERSON.NAME,PREKOR,SUM(BROI*PARI) FROM RABOTA LEFT JOIN PERSON ON NOMER=PERSON.ID LEFT JOIN OPERATION ON OPERAT=OPERATION.ID WHERE 
	  DATA BETWEEN "'.GDate($beg_m).'" AND "'.GDate($end_m).'"';
	if(is_array($_POST['chek']) AND count($_POST['chek'])>0) 
	{
	  $inx = implode(',',array_keys($_POST['chek']));
	  $query.=' AND NOT OPERAT IN ('.$inx.')';
	  $q = 'SELECT NAME FROM OPERATION WHERE ID IN ('.$inx.') ORDER BY NAME';
  	$res = mysql_query($q) or trigger_error($q.'<br>'.mysql_error(),E_USER_ERROR);
  	while($red = mysql_fetch_array($res,MYSQL_NUM)) $opera[] = $red[0];
  	mysql_free_result($res);
	}
	$query.=' GROUP BY NOMER ORDER BY PERSON.NAME';
	$result = mysql_query($query) or trigger_error($query.'<br>'.mysql_error(),E_USER_ERROR);
	echo '<center>Payroll for <b>'.strftime('%B %Y',mktime(0,0,0,$m,1,$y)).'</b>';
	if(is_array($opera)) echo ' excluding<br>'.implode(', ',$opera);
	echo '</center><br>';

	echo '<TABLE ALIGN="CENTER" BORDER="1" CELLSPACING="0" CELLPADDING="2" BORDERCOLOR="black" style="font-size:10pt">
    <TR BGCOLOR="#CCCCCC">
	  <TH>No.</TH>
	  <TH>Full name</TH>
	  <TH>Nickname</TH>
	  <TH>Salary</TH>
	  <TH>Advance</TH>
	  <TH>Loan</TH>
	  <TH>Other deductions</TH>
	  <TH>Final sum</TH>
	  <TH>Signature</TH>
	  </TR>'.chr(13).chr(10);
	
	while ($row = mysql_fetch_array($result,MYSQL_NUM))
	{
		echo '<tr><td align="right">'.$row[0].'</td><td>'.$row[1].'</td><td align="center">'.($row[2]!='' ? $row[2] : '&nbsp;').'</td>
		  <td align="right"><b>'.number_format($row[3],2,'.','').'</td>';
		//echo '<td align="right"><input type="text" name="advance_'.$row[0].'" size="10" class="editable"></td>';
		//echo '<td align="right"><input type="text" name="loan_'.$row[0].'" size="10" class="editable"></td>';
		//echo '<td align="right"><input type="text" name="other_'.$row[0].'" size="10" class="editable"></td>';
		echo '<td align="right">&nbsp;</td>
		  <td align="right">&nbsp;</td>
		  <td align="right">&nbsp;</td>
		  <td align="center">&nbsp;</td>';
		//echo '<td style="color:white">Място за подпис</td>';
		echo '<td>&nbsp;</td></tr>'.chr(13).chr(10);
		$tot += $row[3];
	}
	echo '<tr bgcolor="yellow" style="font-weight:bold"><td colspan="3" align="right">Общо:</td><td align="right">'.number_format($tot,2,"."," ").'</td>
	  <td colspan="5">&nbsp;</td></tr></TABLE>'.chr(13).chr(10);

?>