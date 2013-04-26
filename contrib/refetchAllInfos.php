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
require_once './core/compatibility.php';
 
// check for localnet
localnet_or_die(); 

// multiuser permission check
permission_or_die(PERM_WRITE);


/**
 * Fetch a list of all editable video fields (keys) 
 * and assign 1 (value) if they should be preselected else 0  
 */
function getFields()
{
	$edit_file = file_get_contents('edit.php');
	$edit_file = preg_replace("/\n/",'',$edit_file);


	if (preg_match('/\$imdb_set_fields\s*=\s*array\s*\((.*?)\)/', $edit_file, $fieldslist) && 
		preg_match('/\$imdb_overwrite_fields.*?array\s*\((.*?)\)/', $edit_file, $overwritelist))
	{	
		$fields     = array_map('trim', split(',', preg_replace("/'/", '', $fieldslist[1])));
		$overwrites = array_map('trim', split(',', preg_replace("/'/", '', $overwritelist[1])));

		$ret = array();
		foreach ($fields as $field)
		{
			$value = (in_array($field, $overwrites)) ? 1 : 0;
            if (preg_match('/custom/', $field)) $value = 0;
			$ret = array_merge ($ret, array($field => $value));
		}

		return $ret;
	}	
}

if (!check_permission(PERM_ADMIN)) {
?>	
	<html>
	<head>
	    <title>Refetch all external engine information</title>
	    <meta http-equiv="refresh" content="0; URL=../index.php">
		<META http-equiv="Content-Style-Type" content="text/html">
	</head>
	<body>
	</body>
	</html>
<?php
} 
else 
{
	
	if (isset($submit) && $submit == "Yes") 
    {
		$contribUrl = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']
			.substr($_SERVER['PHP_SELF'],0,strrpos ( $_SERVER['PHP_SELF'], '/' ));
		$baseUrl = substr($contribUrl,0,strrpos ($contribUrl, '/'));

	    // get list movies in DB
	    $SQL    = 'SELECT * FROM '.TBL_DATA;
        if ($user != '0') $SQL .= ' AND owner_id = '.$user;
	    $result = runSQL($SQL);
	
		$CLIENTERRORS   = array();
		$CLIENTOKS      = array();
		$diskid         = 0;
		
	    foreach ($result as $video)
	    {
	    	$diskid++;
	    	// Filter movies of unselected Users.
//	    	if ($user != '0' && $video['owner_id'] != $user) continue;

	    	// Filter movies of unselected Engines
	    	if ($selectedengine != 'all' && engineGetEngine($video['imdbID']) != $selectedengine) continue;

	    	// new DiskID ?
	    	if (isset($resetDI) && $resetDI == "true")
            {
	    		$didigits = $GLOBALS['config']['diskid_digits'];
				if (empty($didigits)) $didigits = 4;
	    		$newId=sprintf('%0'.$didigits.'d',$diskid);
	    		
	    		// make sure lent table is changed too
	    		$SELECT = "SELECT diskid FROM ".TBL_DATA." WHERE id = ".$video['id'];
	    		$oldDiskId = runSQL($SELECT);
	    		$UPDATE = "UPDATE ".TBL_LENT." SET diskid = 'TMP".$newId."' WHERE diskid = '".$oldDiskId[0]['diskid']."'";
	    		runSQL($UPDATE);
	    		
				$UPDATE = "UPDATE ".TBL_DATA." SET diskid = '".$newId."' WHERE id = ".$video['id'];
				runSQL($UPDATE);
			}
			
            // cannot refetch without external id
	        if (empty($video['imdbID'])) continue;
            
	        set_time_limit(300); // raise per movie execution timeout limit if safe_mode is not set in php.ini
            
			$id     = $video['id'];
			$imdbID = $video['imdbID'];
	        $engine = engineGetEngine($video['imdbID']);
	        
	        $fieldlist = "";
	        foreach (array_keys($_POST) as $param) 
            {
	        	if (preg_match('/^(update_.*)/',$param,$fieldname))
	        	{
	        		$fieldlist .= "&".$fieldname[1]."=1";
	        	}	
	        }
	    	$url = $baseUrl."/edit.php?id=".$id."&engine=".$engine."&save=1&lookup=".$lookup.$fieldlist;
		    $resp = httpClient($url, false, array('cookies' => $_COOKIE, 'no_proxy' => true, 'no_redirect' => true));
		    if (!$resp['success']) 
            {
		    	$CLIENTERRORS[] = $video['title']." (".$video['diskid']."/".engineGetEngine($video['imdbID'])."): ".$resp['error'];
		    }
		    else $CLIENTOKS[] = $video['title']." (".$video['diskid']."/".engineGetEngine($video['imdbID']).")";
	    }
	    
	    if (isset($resetDI) && $resetDI == "true")
        {
			// fix lent table after upper temp. changes
			$SELECT = "SELECT diskid FROM ".TBL_LENT." WHERE diskid like 'TMP%'";
			$lentResult = runSQL($SELECT);
			foreach ($lentResult as $lentRow)
            {
				$diskid = preg_replace('/^TMP/','',$lentRow['diskid']);
				$UPDATE = "UPDATE ".TBL_LENT." SET diskid = '".$diskid."' WHERE diskid = 'TMP".$diskid."'";
				runSQL($UPDATE);	
			}
	    }
	?>
		<html>
		<head>
		    <title>Refetch all external engine information</title>
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
	} 
    elseif (isset($submit) && $submit == "LOAD") 
    {	
	?>
		<html>
		<head>
		    <title>Refetch all external engine information</title>
		    <link rel="stylesheet" href="../<?php echo $config['style'] ?>" type="text/css" />
		</head>
        
		<body style="font-size: 16px">
        
			<form method="post" action="<?php echo $_SERVER['PHP_SELF']?>" target="mainFrame" onSubmit="alert('This may take a LONG time (dependent on movie count and connection speed)!!!');">

            <div>
                <div style="float:right">
                    <a href="javascript:parent.location.href='../index.php';"><img src="./images/close.gif" width="14" height="14" alt="" border="0" /></a>
                </div>
                
                Refetch and overwrite selected fields of movies for this User:
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
            </div>
                
            <div>
                Update fields only for movies fetched by this engine:
                <select name="selectedengine">
                    <option value="all" selected="selected">All Engines</option>
                    <?php 
                    global $config;
                    foreach ($config['engines'] as $engine => $meta) 
                    { 
                        print '<option value="'.$engine.'">'.$meta['name']."</option>\n";
                    }
                    ?>
                </select>
            </div>
                
            <div>
                Data Lookup: 
                <label for="lookup1"><input type="radio" name="lookup" id="lookup1" value="5" checked = "checked" />add missing</label>
                <label for="lookup2"><input type="radio" name="lookup" id="lookup2" value="6" />overwrite</label>
            </div>
                
            <div>
                <table><tr>
                <?
                    $fields_in_a_row = 6;
                    $fields = getFields();
                    $keys   = array_keys($fields);
                    $field_amount = count($keys);
                    for($i = 0; $i < $field_amount; $i++)
                    {
                        $checked = ($fields[$keys[$i]] == 1) ? "checked" : "";
                        if ($i % $fields_in_a_row == 0 && $i != 0) print "</TR><TR>"; 
                        print '<TD nowrap="nowrap"><input type="checkbox" name="update_'.$keys[$i].'" value="1" '.$checked.' />'.$keys[$i].'</TD>';
                    }
                    for ($i = 0; $i < ($fields_in_a_row - ($field_amount % $fields_in_a_row)); $i++) {
                        print '<TD>&nbsp;</TD>';
                    }
                ?>
                </tr></table>
            </div>
                
            <div>
                reset DiskIDs FOR ALL MOVIES AND ALL USERS?<input type="checkbox" name="resetDI" value="true" />
            </div>
                
            <div>
                Are you shure to know what you are doing?
                <input type="submit" name="submit" value="Yes">
                <input type="button" value="No" onClick="parent.location.href='../index.php';">
            </div>
			</form>	
		</body>
		</html>
	<?php
	} else { 
	?>
		<html>
		<head>
		<title>Refetch all external engine information</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		</head>
		
		<frameset name="fs1" rows="260,*" frameborder="NO" border="0" framespacing="0">
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
