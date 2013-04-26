<?php
/**
 * Borrow / return movie via barcode
 * 
 * (c) 2005 GPL'd
 *
 * @package Contrib
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * 
 */
chdir('..');
require_once './core/session.php';
require_once './core/functions.php';
require_once './core/genres.php';
require_once './core/custom.php';
require_once './core/security.php';
 
 
// check for localnet
localnet_or_die(); 

// multiuser permission check
permission_or_die(PERM_WRITE, $_COOKIE['VDBuserid']);

$SELECT = 'SELECT opt FROM '.TBL_CONFIG." WHERE LOWER(opt) LIKE 'custom_type' AND value = 'barcode'";
$result = runSQL($SELECT);
if (count($result)>0) $customFieldName = preg_replace('/type/','',$result[0]['opt']);

if (count($result) == 0) {
?>	
	<html>
		<head>
			<title>Borrow / return movie via barcode</title>
			<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		</head>
		<body>Please select &qt;barcode&qt; as a custom field in the &qt;configuration&qt; tab.</body>
	</html>
<?
} elseif (isset($_GET['process']) && $_GET['process'] != "") {
	$notFound=-1;
	
	if ($_GET['process'] == "BORROW") {

		$who = addslashes(trim($_GET['borrowText']));
		$barcode = trim($_GET['barcode']);
		
		if ($who == '') {
			$notFound=2;
		} elseif ($barcode == '' || preg_match('/[^0-9]+/',$barcode)) { 
			$notFound=1;
		} else {
			$result = runSQL('SELECT diskid, '.$customFieldName.' AS barcode
                      FROM '.TBL_DATA.'
                 LEFT JOIN '.TBL_USERS.'
                        ON '.TBL_DATA.'.owner_id = '.TBL_USERS.'.id
                     WHERE '.TBL_USERS.".name = '".addslashes($_COOKIE['VDBusername'])."'".'
                             AND '.TBL_DATA.'.'.$customFieldName." LIKE '%".$barcode."'");
				
		    foreach($result as $row)
		    {
		    	// missing zeros at the beginning?
		    	if (($lenDiff = strlen($row['barcode'])-strlen($barcode))>0) {
		    		// If there is a rotten apple - just skip  
		    		if (preg_match('/[^0]+/',substr($row['barcode'],0,$lenDiff))) {
		    			continue;	
		    		}
		    	}
		    	
		    	$DELETE = 'DELETE FROM '.TBL_LENT.' WHERE diskid = '.addslashes($row['diskid']);
				$INSERT = 'INSERT '.TBL_LENT." SET who = '".addslashes($who)."', diskid = '".addslashes($row['diskid'])."'";
				runSQL($DELETE,false);
				runSQL($INSERT);
				$specialJsCode = "parent.mainFrame.location.href='../borrow.php';";
				$notFound=0;
		    }
		}
	} else if ($_GET['process'] == "RETURN") {

		$barcode = trim($_GET['barcode']);
		
		if ($barcode == '' || preg_match('/[^0-9]+/',$barcode)) { 
			$notFound=1;
		} else {

			$result = runSQL('SELECT diskid, '.$customFieldName.' AS barcode 
                      FROM '.TBL_DATA.'
                 LEFT JOIN '.TBL_USERS.'
                        ON '.TBL_DATA.'.owner_id = '.TBL_USERS.'.id
                     WHERE '.TBL_USERS.".name = '".addslashes($_COOKIE['VDBusername'])."'".'
                             AND '.TBL_DATA.'.'.$customFieldName." LIKE '%".$barcode."'");

			foreach($result as $row)
		    {
		    	// missing zeros at the beginning?
		    	if (($lenDiff = strlen($row['barcode'])-strlen($barcode))>0) {
		    		// If there is a rotten apple - just skip  
		    		if (preg_match('/[^0]+/',substr($row['barcode'],0,$lenDiff))) {
		    			continue;	
		    		}
		    	}
		    	
		    	$DELETE = 'DELETE FROM '.TBL_LENT.' WHERE diskid = '.addslashes($row['diskid']);
				runSQL($DELETE);
				$specialJsCode = "parent.mainFrame.location.href='../borrow.php';";
				$notFound=0;
		    }
		}
	}
?>

	<html>
	<head>
	    <title>Borrow / return movie via barcode</title>
	    <link rel="stylesheet" href="../<?php echo $config['style'] ?>" type="text/css" />
	    <script language="JavaScript">
			<!--
				function changed() {
					if (document.barcodeForm.process.value == "BORROW") {
						document.getElementsByName('borrowTextDesc')[0].style.display='';
						document.barcodeForm.barcode.focus();
						document.barcodeForm.barcode.select();
					} else { // RETURN
						document.getElementsByName('borrowTextDesc')[0].style.display='none;';
						document.barcodeForm.barcode.focus();
						document.barcodeForm.barcode.select();
					}
				}		
				
				function bgmonitor(field) {
					if (field.defaultValue != field.value) {
						document.barcodeForm.barcode.style.backgroundColor='';	
						document.barcodeForm.borrowText.style.backgroundColor='';
					}
				}
			//-->
		</script>
	</head>
	<body class="tablemenu" >
		<TABLE width="100%" border="0" cellspacing="0" cellpadding="0">
			<TR>
				<TD align="left" valign="top" class="logo" style="font-size:16px;font-style:normal;float:left;">
					<form name="barcodeForm" method="get" action="<?php echo $_SERVER['PHP_SELF']?>">
						<select name="process" onChange="changed();">
							<option value="BORROW" <?php if ($process=="BORROW" || $process=="LOAD") {?>selected<?php }?>>borrow</option>
							<option value="RETURN" <?php if ($process=="RETURN") {?>selected<?php }?>>return</option>
						</select>
						barcode:
						<?php 
							if ($notFound == 1) $textFieldStyleB='style="background-color:red;"';
							elseif ($notFound == 2) $textFieldStyleT='style="background-color:red;"';
							elseif ($notFound == 0) {
								$textFieldStyleB='style="background-color:green;"';
								$textFieldStyleT='style="background-color:green;"';
							}
						?>
						<input type="text" <?php echo $textFieldStyleB ?> name="barcode" size="20" onkeydown="bgmonitor(this);" value="<?php echo $barcode ?>">
						<span name="borrowTextDesc">
						to:
						<input type="text" <?php echo $textFieldStyleT ?> name="borrowText" size="20" onkeydown="bgmonitor(this);"  value="<?php echo $borrowText ?>">
						</span>
						<input type="submit" name="submit" value="OK">
					</form>
					<script language="JavaScript">
					<!--
						changed();
						<?php if ($notFound != 2) { ?>
							document.barcodeForm.barcode.focus();
							document.barcodeForm.barcode.select();
						<?php } else { ?>
							document.barcodeForm.borrowText.focus();
							document.barcodeForm.borrowText.select();
						<?php } ?>
						<?php echo $specialJsCode ?> 
					//-->
					</script>
				</TD>
				<TD align="right" valign="top" width="14"><a href="javascript:parent.location.href='../index.php';"><img src="./images/close.gif" width="14" height="14" alt="" border="0" /></a></TD>
			</TR>
		</TABLE>	
	</body>
	</html>
<?
} else { // Frameset
?>
	<html>
	<head>
	<title>Borrow / return movie via barcode</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	</head>
	
	<frameset name="fs1" rows="25,*" frameborder="NO" border="0" framespacing="0">
	  <frame name="topFrame" scrolling="NO" noresize src="<?php echo $_SERVER['PHP_SELF']?>?process=LOAD"> 
	  <frame name="mainFrame" src="../borrow.php">
	</frameset>
	
	<noframes> 
	<body>Please use a browser which supports frames!</body>
	</noframes> 
	</html>
<?
}
?>