<?php
/**
 * Refetch and overwrite all movie information by according engine
 * 
 * (c) 2005 GPL'd
 *
 * @package Contrib
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @meta	ACCESS:PERM_ADMIN
 */

chdir('..');
require_once './core/functions.php';
require_once './core/custom.php';
require_once './core/security.php';
require_once './engines/engines.php';
 
// check for localnet
localnet_or_die(); 

// multiuser permission check
permission_or_die(PERM_WRITE);



if (!check_permission(PERM_ADMIN)) {
?>	
	<html>
	<head>
	    <title>Convert fetch engine (dvdpalace&lt;-&gt;dvdb)</title>
	    <meta http-equiv="refresh" content="0; URL=../index.php">
		<META http-equiv="Content-Style-Type" content="text/html">
	</head>
	<body>
	</body>
	</html>
<?php
} else {
	
	if (isset($submit) && $submit == "Yes") {
		
		if ($user != '0') $WHERES = " WHERE owner_id = '".$user."'";
		else $WHERES = '';
	
		$CLIENTERRORS = array();
		$CLIENTOKS = array();
		$diskid=0;
		
		switch ($direction) 
		{
			case 'dvdpalace2dvdb':
				$from_engine = 'dvdpalace';
				$to_engine = 'dvdb';
				break;

			case 'dvdb2dvdpalace':
				$from_engine = 'dvdb';
				$to_engine = 'dvdpalace';
				break; 		
			
			default:
				exit;
		}
		
		require_once('./engines/'.$from_engine.'.php');
		require_once('./engines/'.$to_engine.'.php');
		
		$func = $from_engine.'ImdbIdPrefixes';
	    $from_prefixes = $func();
	    $var = $to_engine.'IdPrefix';
	    global $$var;
	    $to_prefix = $$var;

		// get list movies in DB
	    $SQL = 'SELECT * FROM '.TBL_DATA.$WHERES;
	    $result = runSQL($SQL);
	    
		foreach ($result as $video)
	    {
	    	if (engineGetEngine($video['imdbID']) != $from_engine) continue;
	    	
    		foreach ($from_prefixes as $from_prefix) 
    		{
    			$newImdbId = preg_replace('/^'.$from_prefix.'/', $to_prefix, $video['imdbID']);
    			if ($newImdbId != $video['imdbID']) break;
    		}

    		$UPDATE = "UPDATE ".TBL_DATA." SET imdbID = '".$newImdbId."' WHERE id = ".$video['id'];
    		if (runSQL($UPDATE) === false) $CLIENTERRORS[] = $video['title']." (".$video['imdbID']."=>".$newImdbId.")";
		    else $CLIENTOKS[] = $video['title']." (".$video['imdbID']."=>".$newImdbId.")";
	    }
	?>
		<html>
		<head>
		    <title>Convert fetch engine (dvdpalace&lt;-&gt;dvdb)</title>
		</head>
		<body>
			<h1>Report</h1><p>  
			<h2>ERROR:</h2><p>
			<?php foreach ($CLIENTERRORS as $error) { ?>
				<?php echo $error ?>						
				<br>
			<?php } ?>		
			
			<h2>SUCCESS:</h2><p>
			<?php foreach ($CLIENTOKS as $ok) { ?>
				<?php echo $ok ?>
				<br>	
			<?php } ?>				
		</body>
		</html>
	<?php
	} elseif (isset($submit) && $submit == "LOAD") {	
	?>
		<html>
		<head>
		    <title>Convert fetch engine (dvdpalace&lt;-&gt;dvdb)</title>
		    <link rel="stylesheet" href="../<?php echo $config['style'] ?>" type="text/css" />
		</head>
		<body class="tablemenu" >
			<form method="post" action="<?php echo $_SERVER['PHP_SELF']?>" target="mainFrame">
				<TABLE width="100%" border="0" cellspacing="0" cellpadding="0">
					<TR>
						<TD width="30" nowrap="nowrap">&nbsp;</td>
						<TD align="left" valign="top" class="logo" style="font-size:16px;font-style:normal;float:left;"></br>
							Convert movies for this User:
							<select name="user">
								<option value="0" selected="selected">All Users</option>
								<?php 
								$SQL = 'SELECT name, id FROM '.TBL_USERS;
	    						$result = runSQL($SQL);
								foreach ($result as $user) 
								{ 
									print '<option value="'.$user['id'].'">'.$user['name']."</option>\n";
								}
								?>
							</select>
						</TD>
						<TD align="right" valign="top" width="14"><a href="javascript:parent.location.href='../index.php';"><img src="./images/close.gif" width="14" height="14" alt="" border="0" /></a></TD>
					</TR>
					<TR>
						<TD width="30" nowrap="nowrap">&nbsp;</td>
						<TD colspan="2" align="left" valign="top" class="logo" style="font-size:16px;font-style:normal;float:left;"><br />
							dvdpalace &gt; dvdb <input type="radio" name="direction" value="dvdpalace2dvdb" checked="checked"/><br>
							dvdb &gt; dvdpalace <input type="radio" name="direction" value="dvdb2dvdpalace" /><br>
						</TD>
					</TR>
					<TR>
						<TD width="30" nowrap="nowrap">&nbsp;</td>
						<TD colspan="2" align="left" valign="top" class="logo" style="font-size:16px;font-style:normal;float:left;"><br />
							This is only a Imdb-ID field update - not a refetch! To refetch the information use an other contrib tool!<br>
						</TD>
					</TR>
					<TR>
						<TD width="30" nowrap="nowrap">&nbsp;</td>
						<TD colspan="2" align="left" valign="top" class="logo" style="font-size:16px;font-style:normal;float:left;"><br />
							<strong>Are you shure to know what you are doing?</strong>
							<input type="submit" name="submit" value="Yes">
							<input type="button" value="No" onClick="parent.location.href='../index.php';">
						</TD>
					</TR>
				</TABLE>
			</form>	
		</body>
		</html>
	<?php 
	} else { 
	?>
		<html>
		<head>
		<title>Convert fetch engine (dvdpalace&lt;-&gt;dvdb)</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		</head>
		
		<frameset name="fs1" rows="200,*" frameborder="NO" border="0" framespacing="0">
		  <frame name="topFrame" scrolling="NO" noresize src="<?php echo $_SERVER['PHP_SELF']?>?submit=LOAD"> 
		  <frame name="mainFrame" src="">
		</frameset>
		
		<noframes> 
		<body>Please use a browser which supports frames!</body>
		</noframes> 
		</html>
	<?php 
	} 
}
?>	
