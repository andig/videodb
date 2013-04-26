<?php
/**
 * Collect and Export barcodes for label printing 
 * 
 * (c) 2005 GPL'd
 *
 * @package Contrib
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * 
 */
chdir('..');
require_once './core/functions.php';
require_once './core/genres.php';
require_once './core/custom.php';
require_once './core/security.php';
require_once './engines/dvdb.php'; 
 
// check for localnet
localnet_or_die(); 

if (!($_GET['submit'] == "Export")) {
?>
<html>
<head>
    <title>Export DVDB.de information for LabelWriter</title>
    <link rel="stylesheet" href="../<?php echo $config['style'] ?>" type="text/css" />
</head>
<?php
}

if (isset($_GET['submit']) && $_GET['submit'] == "Delete") { // delete movie list entry
		$new = array();
		if (preg_match('/^(.+) \(([0-9]*)\)$/',$_GET['movielist'],$delMovie)) {
			session_start();
			foreach($_SESSION['VDBexportDP'] as $movie)
			{
				if ($delMovie[1] == $movie[1] && $delMovie[2] == $movie[0]) continue;
				$new[] = $movie;
			}
			$_SESSION['VDBexportDP'] = $new;
		}
		$_GET['process'] = "LOAD";
}

if (isset($_GET['submit']) && $_GET['submit'] == "Clear") { // clear movie list
	session_start();
	$_SESSION['VDBexportDP'] = array();
	$_GET['process'] = "LOAD";
}

if (isset($_GET['process']) && !($_GET['submit'] == "Export") && ($_GET['process'] == "LOAD" || $_GET['process']== "ADD")) { // topFrame
	
	if ($_GET['process']=="ADD" && isset($_GET['movieid']) && $_GET['movieid'] != "" && preg_replace('/DP[0-9]+/','',$language)=="" ) {
		$data = dvdbData($_GET['movieid']);
		$new = array();
		$new[0] = $data['barcode'];
		if (isset($data['subtitle']) && trim($data['subtitle'])!="") 
			$data['title'] = trim($data['title']) . " - " . trim($data['subtitle']);
		$data['title'] = preg_replace('/"/','\'',$data['title']); // change quotation marks
		$new[1] = $data['title'];
		$new[2] = $_GET['movieid'];
		session_start();
		$_SESSION['VDBexportDP'][] = $new; 
	}
?>
	<body class="tablemenu" onLoad="var i=document.exportForm.movielist.selectedIndex;document.exportForm.movielist.selectedIndex = 0;document.exportForm.movielist.selectedIndex = i;">
		<TABLE width="100%" border="0" cellspacing="0" cellpadding="0">
			<TR>
				<TD align="left" valign="top">
					<form name="exportForm" method="get" action="<?php echo $_SERVER['PHP_SELF']?>" target="mainFrame">
						<span class="logo" style="font-size:16px;font-style:normal;float:left;">Movie title:</span>
						<input type="text" name="moviesearch" size="20" value="<?php echo $search ?>">
						<input type="submit" name="submit" value="Search"><input type="hidden" name="process" value="SEARCH"><input type="hidden" name="movieid" value="">
						<select name="movielist" size="3">
						<?php 
							$keystr = "";
							foreach($_SESSION['VDBexportDP'] as $movie)
				   			{	
				   				$last = $keystr;
				   				$keystr = $movie[1]." (".$movie[0].")";
				   				if ($last != "") print '<option value="'.$last.'">'.$last."</option>\n";
				    		}
				    		if ($keystr != "") print '<option value="'.$keystr.'" selected>'.$keystr."</option>\n";
						?>	 
						</select>
						<input type="submit" name="submit" value="Delete" onClick="document.exportForm.target='topFrame';">
						<input type="submit" name="submit" value="Clear"  onClick="document.exportForm.target='topFrame';">
						<select name="resultsPerLine">
							<option value="1" selected>Movie Barcode (11355)</option>
							<option value="6">Movie Barcode (99012)</option>
						</select>
						<input type="submit" name="submit" value="Export">
					</form>
					<script language="JavaScript">
					<!--
						document.exportForm.moviesearch.focus();
						document.exportForm.moviesearch.select();
					//-->
					</script>
				</TD>
				<TD align="right" valign="top" width="14"><a href="javascript:parent.location.href='../index.php';"><img src="./images/close.gif" width="14" height="14" alt="" border="0" /></a></TD>
			</TR>
		</TABLE>	
	</body>
<?php
} elseif (isset($_GET['process']) && $_GET['process'] == "SEARCH" && !($_GET['submit'] == "Export")) { // mainFrame
	$data = dvdbSearch($_GET['moviesearch']);
	?>
	<BODY>
		<TABLE width="100%" border="0" cellspacing="0" cellpadding="0">
<?php
foreach($data as $key => $movie)
{
	if (@in_array($key,array('encoding'))) continue;
	if ($class=='even') $class='odd'; else $class='even';
?>

			<TR style="line-height : 120%;">
				<TD align="left" class="<?php echo $class ?>" width="10px" nowrap>&nbsp;</TD>
				<TD align="left" class="<?php echo $class ?>"><a href="<?php echo dvdbContentUrl($movie['id'])?>" target="_blank"><?php echo (empty($movie['img'])) ? 'show' : '<img src="'.$movie['img'].'" />' ?></a></TD>
				<TD align="left" class="<?php echo $class ?>"><a href="<?php echo $_SERVER['PHP_SELF'].'?movieid='.$movie['id'].'&process=ADD' ?>" target="topFrame"><img src="../images/add.gif" alt="add to upper list" title="add to upper list" /></a></TD>
				<TD align="left" class="<?php echo $class ?>"><?php echo $movie['title'] ?></TD>
				<TD align="left" class="<?php echo $class ?>" width="10px" nowrap>&nbsp;</TD>
			</TR>
<?php
}
?>
		</TABLE>
	</BODY>	
<?php
} elseif ($_GET['submit'] == "Export") { // mainFrame -> EXPORT
	$dataPerLine = (int)$_GET['resultsPerLine'];
	$saveas = "lableprinter_export.csv";
	$tempfn = tempnam("","");
	$temp = fopen($tempfn, "w");
	$counter=0;

	$separator = "";
	session_start();
	foreach($_SESSION['VDBexportDP'] as $movie)
	{	
		$counter++;
		fwrite($temp,$separator);
				
		// make shure ean code has a lenght of 13 digits
		while (strlen($movie[0]) < 13) $movie[0] = "0".$movie[0];
		
		// cut off the checksum digit at the end
		// lablewriter recalculates the checksum later
		$movie[0] = substr($movie[0],0,-1);
		
		fwrite($temp,"\"".$movie[1]."\";\"".$movie[0]."\""); 
		if ($counter % $dataPerLine == 0) $separator = "\n";
		else $separator = ";";
	}
	fclose($temp);

	$size = filesize($tempfn);
	//ini_set("zlib.output_compression", "Off");
	session_write_close();
	
	set_magic_quotes_runtime(0);
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment;filename=$saveas");
	header("Content-Length: " . (string)$size);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Connection: close");
	$fd = fopen($tempfn, "rb");
	rewind($tempfn);
	fpassthru($fd); // output temp file content
	set_magic_quotes_runtime(get_magic_quotes_gpc());

	unlink($tempfn); // remove temp file
	
} else { // Frameset

	$_SESSION['VDBexportDP'] = array();
?>
	<frameset rows="55,*" frameborder="NO" border="0" framespacing="0">
	  <frame name="topFrame" scrolling="NO" noresize src="<?php echo $_SERVER['PHP_SELF']?>?process=LOAD"> 
	  <frame name="mainFrame" src="">
	</frameset>
	<noframes> 
	<body>Please use a browser which supports frames!</body>
	</noframes> 
<?php
}

if (!($_GET['submit'] == "Export")) {
?>
</html>
<?php } ?>