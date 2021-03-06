<?php
include("conf.php");

define('DELIM','-');
define('SESS_LEN','3600'); // Count of seconds before automatic logout
define('PLOG','/system.log');
define('COL_EDIT','FFFFFF');
define('COL_DEL','FFD1E5');
define('COL_NEW','A5E5BA'); //new event background
define('COL_OLD','F7BEAD'); //inactive request background 

define('PREG_FIND_RECURSIVE', 1);
define('PREG_FIND_DIRMATCH', 2);
define('PREG_FIND_FULLPATH', 4);
define('PREG_FIND_NEGATE', 8);
define('PREG_FIND_DIRONLY', 16);
define('PREG_FIND_RETURNASSOC', 32);
// PREG_FIND_RECURSIVE   - go into subdirectorys looking for more files
// PREG_FIND_DIRMATCH    - return directorys that match the pattern also
// PREG_FIND_DIRONLY     - return only directorys that match the pattern (no files)
// PREG_FIND_FULLPATH    - search for the pattern in the full path (dir+file)
// PREG_FIND_NEGATE      - return files that don't match the pattern
// PREG_FIND_RETURNASSOC - Instead of just returning a plain array of matches,
//                         return an associative array with file stats
// to use more than one simply seperate them with a | character


$conn = mysql_connect('localhost',DATABASE_USER,DATABASE_PASSWORD) or die ('Cannot connect to MySQL!');
$db = mysql_select_db(DATABASE_NAME,$conn);
mysql_query('SET NAMES cp1251');

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
//header ('Expires: '.gmdate('D, d M Y H:i:s', time() + 3600).' GMT');
header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); 
header ('Cache-Control: no-cache, must-revalidate');  // HTTP/1.1
header ('Pragma: no-cache');                          // HTTP/1.0

function myErrorHandler ($errno, $errstr, $errfile, $errline, $vars)
{
  // Only handle the errors specified by the error_reporting directive or function
  // Ensure that we should be displaying and/or logging errors
  //if ( ! ($errno & error_reporting ()) || ! (ini_get ('display_errors') || ini_get ('log_errors'))) return;
  if(($errno & (E_NOTICE | E_STRICT)) OR error_reporting()==0) return;

  // define an assoc array of error string
  // in reality the only entries we should
  // consider are 2,8,256,512 and 1024
  $errortype = array (
    1   =>  'Error',
    2   =>  'Warning',
    4   =>  'Parsing Error',
    8   =>  'Notice',
    16  =>  'Core Error',
    32  =>  'Core Warning',
    64  =>  'Compile Error',
    128 =>  'Compile Warning',
    256 =>  'User Error',
    512 =>  'User Warning',
    1024=>  'User Notice',
    2048=>  'Strict Mode',
    4096=>  'Recoverable Error'
    );
  $s = "</table></table></table><br>\n<b>".$errortype[$errno]."</b><br>\n$errstr<br><br>\n\n# $errline, $errfile";
  $s2 = "\n".$errortype[$errno]."\n$errstr\n\n# $errline, $errfile";
	$MAXSTRLEN = 300;
	$s .= '<pre>';
	$a = debug_backtrace();
	//array_shift($a);
	$traceArr = array_reverse($a);
	$tabs = 1;
	if(count($traceArr)) foreach($traceArr as $arr)
	{
		if($arr['function']=='myErrorHandler') continue;
		$Line = (isset($arr['line'])? $arr['line'] : "unknown");
		$File = (isset($arr['file'])? str_replace($_GLOBALS['tmpdir'],'',$arr['file']) : "unknown");
		$s.= "\n<br>";
		$s2.= "\n";
    for ($i=0; $i < $tabs; $i++)
		{
		  $s .= '#';
		  $s2.= '#';
		}
		$s.= ' <b>'.$Line.'</b>, <font color=blue>'.$File."</font>\n<br>";
		$s2.= ' '.$Line.', '.$File."\n";
    for ($i=0; $i < $tabs; $i++)
		{
		  $s .= ' ';
		  $s2.= ' ';
		}
		$tabs ++;
		$s .= ' ';
		$s2.= ' ';
    if (isset($arr['class']))
		{
		  $s .= $arr['class'].'.';
		  $s2.= $arr['class'].'.';
		}
		$args = array();
		if(!empty($arr['args'])) foreach($arr['args'] as $v)
		{
			if (is_null($v)) $args[] = 'NULL';
			elseif (is_array($v)) $args[] = 'Array['.sizeof($v).']'.(sizeof($v)<=5 ? substr(serialize($v),0,$MAXSTRLEN) : '');
			elseif (is_object($v)) $args[] = 'Object:'.get_class($v);
			elseif (is_bool($v)) $args[] = $v ? 'true' : 'false';
			else
      {
				$v = (string) @$v;
				$str = htmlspecialchars(substr($v,0,$MAXSTRLEN));
				//$str = htmlspecialchars($v);
				if (strlen($v) > $MAXSTRLEN) $str .= '...';
				$args[] = "\"".$str."\"";
			}
		}
    if(isset($arr['function']))
		{
		  $s .= $arr['function'].'('.implode(', ',$args).')';
		  $s2.= $arr['function'].'('.implode(', ',$args).')';
		}
    else
		{
		  $s .= '[PHP Kernel] ('.implode(', ',$args).')';
		  $s2.= '[PHP Kernel] ('.implode(', ',$args).')';
		}
	}
	$m = mysql_errno();
	if($m)
	{
	  $s.= chr(13).'<br> MySQL error: '.$m;
	  $s2.= chr(13).' MySQL error: '.$m;
	}
	$s2.= chr(13).' Referrer: '.$_SERVER['HTTP_REFERRER'];
	$s.= '</pre>';
	if($m==2006) return true; // server gone away
	echo $s;
  die; 
}

// set to the user defined error handler
$old_error_handler = set_error_handler('myErrorHandler');

function loger($x)
{
global $tmpdir;

	error_log(date('[d-m-Y] (H:i:s) {'.$_SERVER['REMOTE_ADDR'].($_SERVER["HTTP_X_FORWARDED_FOR"]!='' ? ','.$_SERVER["HTTP_X_FORWARDED_FOR"] : '').'} -> ').$x.chr(13).chr(10),3,$tmpdir.PLOG);
}

function ivo_str($z)
{
	return preg_replace('/[ \t]+/',' ',trim(strtr(strip_tags($z),"'".chr(160).chr(92),"`".chr(32).chr(32))));
}

function fnum($n)
{
	return (double)str_replace(',','.',$n);
}

// Remove URL parameters
function recode($a)
{
	$z = strpos($a,'?');
	if($z>1) return substr($a,0,$z);
		else return $a;
}

// Replace & with &amp; if not followed by #
function re_amp($a)
{
	return preg_replace('/\&[^#]/','&amp;',$a);
}

// Show "Not enough permissions" message
function noperm()
{
global $tmpdir;

	$b = @file_get_contents($tmpdir.'/temp/no_perm.htm');
	$b = str_replace('{PREP}',WEBDIR,$b);
	echo $b;
}

// Enumerates array by key/value in order to prepare a string, suitable for SQL INSERT statement
function IVO_insert($a,$c)
{
  $t=''; $p=''; $b='';
	foreach($a as $k=>$v)
  {
    if(isset($v))
    {
      if(is_array($c))
      {
        $check = true;
        foreach($c as $q)
          if($k==$q) $check = false;
      }
  		else
      {
        $check = ($c!=$k);
      }
      if ($check)
  		{
  			$p.=$b.$k;
  			if (is_numeric($v)) $t.=$b.str_replace(',','.',$v);
  			elseif($v==='NULL') $t.=$b.'NULL';
  			else $t.=$b."'".ivo_str($v)."'";
  			$b=', ';
  		}
    }
  }
	return '('.$p.') VALUES ('.$t.')';
}

// Enumerates array by key/value in order to prepare a string, suitable for SQL UPDATE statement
function IVO_update($a,$c)
{
  $t=''; $b='';
	while (list($k,$v) = each($a)) 
  {
    if(isset($v))
    {
      if(is_array($c))
      {
        $check = true;
        foreach($c as $q)
          if($k==$q) $check = false;
      }
  		else
      {
        $check = ($c!=$k);
      }
      if ($check)
  		{
  			if (is_numeric($v)) $t.=$b.$k.'='.str_replace(',','.',$v);
  			elseif($v==='NULL') $t.=$b.$k.'=NULL';
  			else $t.=$b.$k."='".ivo_str($v)."'";
  			$b=', ';
  		}
    }
  }
	return $t;
}

// returns list of records from $TABLE, sorted by column $SORT in ascending/descending $ORDER,
// using $END as optional WHERE clause
// $TABLE can be comma separated list - first element is used as primary key
// $FIELD can be comma separated list, too
function loadItems($table,$field,$current,$empty="",$end="",$sort="",$order="ASC")
{
	$a = substr($table,0,strpos($table,','));
	if($a != '') $a .= '.';
	$query = 'SELECT '.$a."ID ID0,$field FROM $table $end";
 	if($sort!='') $query.= " ORDER BY $sort $order";
	$result = mysql_query($query) or trigger_error($query.'<br>'.mysql_error(),E_USER_ERROR);
	if ($empty!='') $z = '<option value="0">'.$empty.'</option>';
  while ($row = mysql_fetch_array($result,MYSQL_NUM))
	{	
  	if ($row[0] != $current) $a=''; else $a='selected';
 		$z .= '<option '.$a.' value="'.$row[0].'">'.$row[1].'</option>';
 	}
 	return $z;
}

// converts d-m-Y into the only possible for MySQL Y-m-d
function GDate($dft)
{
	if($dft>0)
	{
		list ($d, $m, $y) = preg_split ('#[:/\.-]#', str_replace(" ","",$dft)); 
  	if($y<10) $y='200'.(int)$y;
  	elseif($y<100) $y='20'.(int)$y;
  	elseif($y<1000) $y='2'.(int)$y;
		return $y.'-'.($m<10?"0".(int)$m:$m).'-'.($d<10?"0".(int)$d:$d);
	}
	else return '0000-00-00';
}

// converts from Y-m-d to the usual d-m-Y
function ADate($dft,$lim)
{
	if($dft>0) return substr($dft,8,2).$lim.substr($dft,5,2).$lim.substr($dft,0,4);
		else return '&#160;';
}

// returns a cell from table by column and row
function a_select($tbl,$clause,$field='',$where='')
{
	if ($clause==0) return '&#160;';
	if($field=='') $field=$tbl;
	$query = "SELECT $field FROM $tbl WHERE ";
	if($where=='') $query.='ID='.$clause;
		else $query.=$where;
	$res = mysql_query($query) or trigger_error($query.'<br>'.mysql_error(),E_USER_ERROR);
	if(mysql_num_rows($res)>0) return mysql_result($res,0,0);
		else return '&#160;';
}

// check date validity in d-m-Y
function ChekDate($a)
{
	list ($d, $m, $y) = preg_split ('#[:/\.-]#', str_replace(" ","",$a));
	list($d2,$m2,$y2) = preg_split('/[-]/',strftime('%d-%m-%Y', mktime(0,0,0,$m,$d,$y)));
	if($d<1 OR $d>31) return false;
	if($m<1 OR $m>12) return false;
	if($y<10) return false;
	if($y<100) $y='20'.$y;
	if($y>2020) return false;
	if((int)$d!=(int)$d2 OR (int)$m!=(int)$m2 OR (int)$y!=(int)$y2) return false;
	return true;
}

//Compare dates - return -1,0,+1
function CompDate($a,$b)
{
	list ($d, $m, $y) = preg_split ('#[:/\.-]#', str_replace(" ","",$a));
	list ($d2, $m2, $y2) = preg_split ('#[:/\.-]#', str_replace(" ","",$b));
	$x = mktime(0,0,0,$m,$d,$y);
	$z = mktime(0,0,0,$m2,$d2,$y2);
	if($x < $z) return -1;
	elseif($x > $z) return 1;
	else return 0;
}

function preg_find($pattern, $start_dir='.', $args=NULL) 
{
  $files_matched = array();
  $fh = opendir($start_dir);
  while (($file = readdir($fh)) !== false) 
  {
    if (strcmp($file, '.')==0 || strcmp($file, '..')==0) continue;
    $filepath = $start_dir . '/' . $file;
    if (preg_match($pattern, ($args & PREG_FIND_FULLPATH) ? $filepath : $file)) 
    {
      $doadd =     is_file($filepath)
               || (is_dir($filepath) && ($args & PREG_FIND_DIRMATCH))
               || (is_dir($filepath) && ($args & PREG_FIND_DIRONLY));
      if ($args & PREG_FIND_DIRONLY && $doadd && !is_dir($filepath)) $doadd = false;
      if ($args & PREG_FIND_NEGATE) $doadd = !$doadd;
      if ($doadd) 
      {
        if ($args & PREG_FIND_RETURNASSOC) // return more than just the filenames
        {
          $fileres = array();
          if (function_exists('stat')) 
          {
            $fileres['stat'] = stat($filepath);
            $fileres['du'] = $fileres['stat']['blocks'] * 512;
          }
          //if (function_exists('fileowner')) $fileres['uid'] = fileowner($filepath);
          //if (function_exists('filegroup')) $fileres['gid'] = filegroup($filepath);
          //if (function_exists('filetype')) $fileres['filetype'] = filetype($filepath);
          //if (function_exists('mime_content_type')) $fileres['mimetype'] = mime_content_type($filepath);
          if (function_exists('dirname')) $fileres['dirname'] = dirname($filepath);
          if (function_exists('basename')) $fileres['basename'] = basename($filepath);
          //if (isset($fileres['uid']) && function_exists('posix_getpwuid ')) $fileres['owner'] = posix_getpwuid ($fileres['uid']);
          $files_matched[$filepath] = $fileres;
        } 
        else array_push($files_matched, $filepath);
      }
    }
    if ( is_dir($filepath) && ($args & PREG_FIND_RECURSIVE) ) $files_matched = array_merge($files_matched, preg_find($pattern, $filepath, $args));
  }
  closedir($fh); 
  return $files_matched;
}

function MakeMenu(&$html)
{
global $conn,$user;

  if($user->login['ID']>0)
  	$x = '<td><a href="'.WEBDIR.'/profile.php" class="menu">&nbsp;My profile&nbsp;</a></td><td>&nbsp;</td><td><a href="'.WEBDIR.'/signout.php" class="menu">&nbsp;LOGOUT&nbsp;</a></td>';
  else $x = '<td><a href="'.WEBDIR.'/login.php" class="menu">&nbsp;LOGIN&nbsp;</a></td>';
  $html = str_replace('<td>{LOG_IN}</td>',$x,$html);

	// Show WELCOME phrase
  if($user->login['ID'])
  {
    $vreme = $user->logout - time();
    if($vreme>0)
    {
			$s = $vreme % 60;
			$n = ($vreme - $s)/60;
			if($n>0) $logout = $n.' min ';
			if($s<10) $s='0'.$s;
			if($s>0) $logout .= $s.' sec';
		}
		else $logout = '0 sec';
  	$z = 'Welcome, &nbsp;'.$user->login['NAME'].'&nbsp;&nbsp;[&nbsp;<a id="timeout">'.$logout.'</a>&nbsp;before logout&nbsp;]';
  	$y = $user->logout - time();
  }
 	else
 	{
 		$z = '';
 		$y = '0';
 	}
  $html = str_replace('{WELCOME}',$z,$html);
  $html = str_replace('{LOGOUT}',$y,$html);

}

?>
