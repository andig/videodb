<?php
/**
 * Add a DVD/Video to VideoDB via barcode
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

$didigits = $GLOBALS['config']['diskid_digits'];
if (empty($didigits)) $didigits = 4;
 
// change this if you have some fancy naming style
$NEXTUSERID = "SELECT lpad(max(diskid)+1, ".$didigits.", '0') AS max FROM ".TBL_DATA.' WHERE diskid NOT REGEXP "[^0-9]"';
 
// check for localnet
localnet_or_die(); 

// multiuser permission check
permission_or_die(PERM_WRITE,$_COOKIE['VDBuserid']);


if (isset($_GET['process']) && $_GET['process'] != "") {

	// fetch Media-Types from DB
    $SELECT = 'SELECT id, name
               FROM '.TBL_MEDIATYPES.'
           ORDER BY name';
    $result = runSQL($SELECT);
    foreach($result as $row)
    {
        $mediatypes[$row['id']] = $row['name'];
    }
    
	$notFound = -1;
	if (isset($_GET['barcode']) && $_GET['barcode'] != "")
	{
		$data = dvdbSearch($_GET['barcode'],'ean');

		if (count($data) > 0) {
			// assign automatic disk id
			if (($config['autoid']) && (empty($diskid)))
			{
				$result = runSQL($NEXTUSERID);
				$data[0]['diskid'] = $result[0]['max'];
			}
			$url = "../edit.php?save=1&lookup=1&diskid=".$data[0]['diskid']."&mediatype=$bcMediatypeId&imdbID=".$data[0]['id'];
			$specialJsCode = "parent.mainFrame.location.href='$url';";
			$notFound = 0;
		} else $notFound = 1;
	}
?>

	<html>
	<head>
	    <title>Add movie by DVDB.de barcode</title>
	    <link rel="stylesheet" href="../<?php echo $config['style'] ?>" type="text/css" />
	</head>
	<body class="tablemenu" >
		<TABLE width="100%" border="0" cellspacing="0" cellpadding="0">
			<TR>
				<TD align="left" valign="top">
					<form name="addbarcode" method="get" action="<?php echo $_SERVER['PHP_SELF']?>">
						<span class="logo" style="font-size:16px;font-style:normal;float:left;">Barcode:</span>
						<?php 
							if ($notFound > 0) $textFieldStyle='style="background-color:red;"';
							elseif ($notFound == 0) $textFieldStyle='style="background-color:green;"';
						?>
						<input type="text" <?php echo $textFieldStyle ?> name="barcode" size="20" value="<?php echo $barcode ?>">
						<select name="bcMediatypeId">
						<?php 
							// set default to DVD
							if (empty($bcMediatypeId)) $bcMediatypeId = $config['mediadefault'];
							
							foreach(array_keys($mediatypes) as $mediatypeId)
				   			{
				   				if ($mediatypeId == $bcMediatypeId) $selected = ' selected="selected"'; else $selected = "";
				   				print '<option'.$selected.' value="'.$mediatypeId.'">'.$mediatypes[$mediatypeId]."</option>\n";
				    		}
						?>	 
						</select>
						<input type="submit" name="submit" value="Add"><input type="hidden" name="process" value="1">
						<?php
							if ($notFound == 1)
							{
								print '<span class="logo" style="font-size:16px;font-style:normal;float:left;">Sorry - Barcode not found!</span>';
							}
							elseif ($notFound == 2)
							{
								if ($matches[1] == "0") print '<span class="logo" style="font-size:16px;font-style:normal;float:left;">Sorry - Barcode not found!</span>';
								else print '<span class="logo" style="font-size:16px;font-style:normal;float:left;">No distinct match found (found '.$matches[1].' matches)!</span>';
							}
						?>
					</form>
					<script language="JavaScript">
					<!--
						document.addbarcode.barcode.focus();
						document.addbarcode.barcode.select();
						<?php echo $specialJsCode ?> 
					//-->
					</script>
				</TD>
				<TD align="right" valign="top" width="14"><a href="javascript:parent.location.href='../index.php';"><img src="./images/close.gif" width="14" height="14" alt="" border="0" align="middle" /></a></TD>
			</TR>
		</TABLE>	
	</body>
	</html>
<?
} else { // Frameset
?>
	<html>
	<head>
	<title>Add movie by DVDB.de barcode</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	</head>
	
	<frameset name="fs1" rows="25,*" frameborder="NO" border="0" framespacing="0">
	  <frame name="topFrame" scrolling="NO" noresize src="<?php echo $_SERVER['PHP_SELF']?>?process=1"> 
	  <frame name="mainFrame" src="../index.php">
	</frameset>
	
	<noframes> 
	<body>Please use a browser which supports frames!</body>
	</noframes> 
	</html>
<?
}
?>