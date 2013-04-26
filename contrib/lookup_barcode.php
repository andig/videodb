<?php
/**
 * Add a DVD/Video to VideoDB via the barcode on the box
 * (c) 2004 GPL'd
 *
 * @package Contrib
 * @author  Andrew Pritchard <videodb@teppic.homeip.net>
 * @version $Id: lookup_barcode.php,v 1.4 2007/09/08 09:17:16 andig2 Exp $
 */
 
    chdir('..');
    require_once('./core/functions.php');
        
	$notFound = 0;
	if (isset($_GET['barcode']))
	{
		// Base URL for the search
		$url = 'http://s1.amazon.co.uk/exec/varzea/sdp/sai-condition/';
		
		// Add our post options and get the data
		$post = 'sdp-sai-asin='.$_GET['barcode'];
		$amazon_data =  httpClient  ($url, 0, $post);
		
		// If it succeeds....
		if ($amazon_data['success'] == 1)
		{
			if (preg_match("/<b class=\"sans\">(.*)<\/b>/", $amazon_data['data'], $matches))
			{
				if ($matches[1] == 'Identify the exact item you&//039;re selling')
				{
					$notFound = 1;
				}
				else
				{
					$media_type = 1;
					$title = urlencode($matches[1]);
					if (preg_match("/http:\/\/www.amazon.co.uk\/exec\/obidos\/ASIN\/(.*)\//", $amazon_data, $matches))
					{
						$asin_number = "ASIN: $matches[1]";
					}
					else
					{
						$asin_number = 'ASIN not found';
					}
					if (preg_match("/alt=\"VHS\"/", $amazon_data['data'], $matches))
					{
						$media_type = 6;
					}
					header("Location: ../edit.php?save=1&lookup=1&title=$title&diskid={$_GET['barcode']}&mediatype=$media_type&subtitle=$asin_number");
				}
			}
			else
			{
				$notFound = 2;
			}
		}
		else
		{
			// Print the error message
			print "Failed to download:<br>\n";
			print $amazon_data['error'];
		}
	}
?>

<html>
<head>
    <title>Add movie by Amazon-UK barcode</title>
</head>
<body>
<h1>Add movie by Amazon-UK barcode</h1>

<form name="addbarcode" method="get" action="<?php echo $_SERVER['PHP_SELF']?>">
<input type="text" name="barcode" size="20">
<input type="submit" name="submit" value="Submit">
<input type="reset" name="reset" value="Reset">
</form>
<script language="JavaScript">
<!--
	document.addbarcode.barcode.focus();
//-->
</script>

<?php
	if ($notFound == 1)
	{
		print "Sorry - your barcode wasn't found at Amazon<br>\n";
		//print_r($amazon_data);
	}
	elseif ($notFound == 2)
	{
		print "No data returned!<br>\n";
		print_r($amazon_data);
	}
?>
</body>
</html>