<?php
/**
 * PDF Export functions
 *
 * Allows exporting movies to PDF
 * Requires FPDF libaray (http://www.fpdf.org)
 *
 * @package Core
 * @link    http://www.fpdf.org
 * @author  Andreas Götz    <cpuidle@gmx.de>
 * @version $Id: pdf.php,v 1.36 2013/03/15 16:42:46 andig2 Exp $
 */

require_once './core/functions.php';
require_once './core/cache.php';
require_once './core/export.core.php';
require_once './engines/engines.php';
require_once './core/VariableStream.class.php';

define('FPDF', './vendor/setasign/fpdf');
define('FPDF_FONTPATH', FPDF.'/font/');

require_once FPDF.'/fpdf.php';
require_once './lib/fpdf2file/fpdf2file.php';

/**
 * Copied from FPDF tutorial 3
 * enhanced with memory image creation for gif->png conversion
 *
 * @link    http://www.fpdf.org/?go=script&id=45
 */
class PDF extends FPDF2File
{
	var $B;
	var $I;
	var $U;
	var $HREF;
	var $GDCount = 0;
	var $Scale   = 0;       // dont rescale images

	function FPDF2File($orientation='P', $unit='mm', $format='A4')
	{
		//Call parent constructor
		$this->FPDF($orientation,$unit,$format);
		//Initialization
		$this->B=0;
		$this->I=0;
		$this->U=0;
		$this->HREF='';
	}

	function GDImage($file, $x, $y, $w=0, $h=0, $im, $link='', $type='png')
	{
		// ouput the GD image $im
		ob_start();
		$func = 'image'.$type;  // image creation function according to type
		$func($im);
		$data = ob_get_contents();
		ob_end_clean();

		// create file-unique variable name to not duplicate images for pdf
		$file = (empty($file)) ? $this->GDCount++ : preg_replace('/[\s\/\.]/', '_', $file);
		$name = 'pdf_image_'.$file;
		$GLOBALS[$name] = $data;

		// call Image using in-memory PNG file
		parent::Image('var://'.$name, $x, $y, $w, $h, $type, $link);
	}

	function Image($file, $x=null, $y=null, $w=0, $h=0, $ext='', $link='')
	{
		global $config;

		$image_types = array(1 => 'gif', 2 => 'jpg', 3 => 'png', 6 => 'bmp');

		list($width, $height, $ext, $attr) = getimagesize($file);
		$ext = $image_types[$ext];

		// find image loading function
		switch($ext)
		{
			case 'jpg': $func = 'jpeg'; break;
			case 'bmp': $func = 'wbmp'; break;
			default:    $func = $ext;
		}
		$func = 'imagecreatefrom'.$func;

		// check if loading functions exists (especially for gif support)
		$im = (function_exists($func)) ? $func($file) : imagecreatetruecolor(1,1);

		// scaling requested?
		if ($this->Scale)
		{
			$this->max_width  = round($this->Scale * $config['pdf_image_max_width']);
			$this->max_height = round($this->Scale * $config['pdf_image_max_height']);

			$scale   = min($this->max_width/$width, $this->max_height/$height);
			$thumb_x = round($width * $scale);
			$thumb_y = round($height * $scale);
		}

		// scaling requied?
		if (($this->Scale) && (($thumb_x != $width) || ($thumb_y != $height)))
		{
			// create white truecolor image (in case original is transparent)
			$target = imagecreatetruecolor($thumb_x, $thumb_y);
			$white  = imagecolorallocate($target, 255, 255, 255);
			imagefilledrectangle($target, 0, 0, $thumb_x, $thumb_y, $white);
			imagecopyresampled($target, $im, 0,0, 0,0, $thumb_x,$thumb_y, $width,$height);
			$this->GDImage($file, $x, $y, $w, $h, $target, $link, 'jpeg');  // change to png if you receive acrobat errors
			imagedestroy($target);
		}
		elseif ($ext == 'gif') {
			// pdf doesn't support interlaced images
			if (imageinterlace($im))
			{
				// claim non-interlaced image
				imageinterlace($im, false);
			}

			$this->GDImage($file, $x, $y, $w, $h, $im, $link);
		}
		else {
			parent::Image($file, $x, $y, $w, $h, $ext, $link);
		}

		imagedestroy($im);
	}

	function VerifyFont($font, $mode = '')
	{
		$default_fonts  = array('Arial', 'Courier', 'Helvetica', 'Times');

		if (!in_array($font, $default_fonts))
		{
			if ($mode)
			$this->AddFont($font, 'B', strtolower($font).'b.php');
			else
			$this->AddFont($font);
		}
	}

	function WriteHTML($html)
	{
		global $config;

		//HTML parser
		$html   = str_replace("\n",' ',$html);
		$a      = preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
		foreach($a as $i=>$e)
		{
			if($i%2==0)
			{
				//Text
				if($this->HREF)
				$this->PutLink($this->HREF,$e);
				else
				$this->Write((int)$config['pdf_font_size'] / 2.5, $e);
			}
			else
			{
				//Tag
				if($e[0]=='/')
				$this->CloseTag(strtoupper(substr($e,1)));
				else
				{
					//Extract attributes
					$a2=explode(' ',$e);
					$tag=strtoupper(array_shift($a2));
					$attr=array();
					foreach($a2 as $v)
					if(ereg('^([^=]*)=["\']?([^"\']*)["\']?$',$v,$a3))
					$attr[strtoupper($a3[1])]=$a3[2];
					$this->OpenTag($tag,$attr);
				}
			}
		}
	}

	function OpenTag($tag, $attr)
	{
		global $config;

		//Opening tag
		if($tag=='B' or $tag=='I' or $tag=='U')
		$this->SetStyle($tag,true);
		if($tag=='A')
		$this->HREF=$attr['HREF'];
		if($tag=='BR')
		$this->Ln((int)$config['pdf_font_size'] / 2);
	}

	function CloseTag($tag)
	{
		//Closing tag
		if($tag=='B' or $tag=='I' or $tag=='U')
		$this->SetStyle($tag,false);
		if($tag=='A')
		$this->HREF='';
	}

	function SetStyle($tag, $enable)
	{
		//Modify style and select corresponding font
		$this->$tag+=($enable ? 1 : -1);
		$style='';
		foreach(array('B','I','U') as $s)
		if($this->$s>0)
		$style.=$s;
		$this->SetFont('',$style);
	}

	function PutLink($URL, $txt)
	{
		global $config;

		//Put a hyperlink
		$this->SetTextColor(0,0,255);
		$this->SetStyle('U',true);
		$this->Write((int)$config['pdf_font_size'] / 2, $txt, $URL);
		$this->SetStyle('U',false);
		$this->SetTextColor(0);
	}

	/**
     * @author  Olivier <oliver@fpdf.org>
     * @license Freeware 
     */
	function WordWrap(&$text, $maxwidth)
	{
		$text = trim($text);
		if ($text==='')
		return 0;
		$space = $this->GetStringWidth(' ');
		$lines = explode("\n", $text);
		$text = '';
		$count = 0;

		foreach ($lines as $line)
		{
			$words = preg_split('/ +/', $line);
			$width = 0;

			foreach ($words as $word)
			{
				$wordwidth = $this->GetStringWidth($word);
				if ($width + $wordwidth <= $maxwidth)
				{
					$width += $wordwidth + $space;
					$text .= $word.' ';
				}
				else
				{
					$width = $wordwidth + $space;
					$text = rtrim($text)."\n".$word.' ';
					$count++;
				}
			}
			$text = rtrim($text)."\n";
			$count++;
		}
		$text = rtrim($text);
		return $count;
	}
        
        function SaveFile($filename)
        {
            FPDF::Output('D', 'videoDB.pdf', $isUTF8=false);
            readfile($filename);
        }      
}

/**
 * Return image name for representing the media type
 */
function getMediaImage($mediatype)
{
	if (preg_match("/^(DVD([+-]R)?|DivX|CD|VCD|SVCD|VHS|BLU-RAY|AVCHD|HDD|HD-DVD)/i", $mediatype, $matches))
	{
		$type_image = strtolower($matches[1]).'.png';
	}
	else $type_image = '';

	return $type_image;
}

/**
 * Export PDF document
 *
 * @param   string  $where  WHERE clause for SQL statement
 */
function pdfexport($WHERE)
{
	global $config;

	$ypos           = $config['pdf_font_size'];     // Match the font size for proper vertical offset
	$page_width     = $config['pdf_page_width'];
	$margin         = $config['pdf_margin'];
	$left_margin    = $config['pdf_left_margin'];
	$right_margin   = $config['pdf_right_margin'];
	$mediaimg_width = $config['pdf_image_media_width'];
	$font_size      = $config['pdf_font_size'];

	$image_height   = $config['pdf_image_height'];
	$image_width    = $config['pdf_image_width'];

	$font_title     = $config['pdf_font_title'];
	$font_plot      = $config['pdf_font_plot'];

	$text_length    = $config['pdf_text_length'];

    $tempfolder     = cache_get_folder('');
    if ($config['cache_pruning']) cache_prune_folder($tempfolder, 3600, false, false, 'videodb*.pdf');
    $filename       = $tempfolder.'videodb'.date('His',time()).'.pdf';

	// setup pdf class
	$pdf = new PDF();
	$pdf->Open($filename);
	$pdf->VerifyFont($font_title);
	$pdf->VerifyFont($font_title, 'B');
	$pdf->VerifyFont($font_plot);
	$pdf->AddPage();
	$pdf->SetRightMargin($right_margin);

	// add downscaling
	if ($config['pdf_scale'])
	{
		$pdf->Scale     = $config['pdf_scale'];
		$pdf->max_width = $config['pdf_image_max_width'];
		$pdf->max_height= $config['pdf_image_max_height'];
	}

	// get data
	$result = iconv_array('utf-8', 'iso-8859-1', exportData($WHERE));

	foreach ($result as $row)
	{
		set_time_limit(300); // rise per movie execution timeout limit if safe_mode is not set in php.ini

		$title = $row['title'];
		if ($row['subtitle']) $title .= ' - '.$row['subtitle'];
		if ($row['diskid'] || $row['mediatype'])
		{
			$title .= ' [';
			if ($row['mediatype']) $title .= $row['mediatype'] . ', ';
			if ($row['diskid'])    $title .= $row['diskid'];
			$title = preg_replace('/, $/', '', $title) . ']';
		}

		// get drilldown url for image
		$imdb = $row['imdbID'];
		$link = ($imdb) ? engineGetContentUrl($imdb, engineGetEngine($imdb)) : '';

		// title
		$pdf->SetFont($font_title, 'B', $font_size);
		$pdf->SetXY($left_margin + $image_width + $margin, $ypos);
		$pdf->Cell(0, 0, $title, 0,1, 'L',0,$link);

		// [muddle] technical details
                unset($tech['Y']);
		if ($row['year']) {
			$tech['Y'] = "Year: ".$row['year'];
		}
                
		unset($tech['V']);
		if ($row['video_width'] and $row['video_height'])
		{
			$vw = $row['video_width'];
			$vh = $row['video_height'];
			$tech['V'] = "Video: ";

			if ($vw>1920) {
				$tech['V'] .= "UHD ".$vw."x".$vh;
			} elseif ($vw>1280) {
				$tech['V'] .= "HD 1080p";
			} elseif ($vw==1280 or $vh==720) {
				$tech['V'] .= "HD 720p";
			} elseif ($vw==720 or $vw==704) {
				$tech['V'] .= "SD ";
				if ($vh==480) {
					$tech['V'] .= "NTSC";
				} elseif ($vh==576) {
					$tech['V'] .= "PAL";
				}	else {
					$tech['V'] .= $vw."x".$vh;
				}
			} else {
				$tech['V'] .= "LORES ".$vw."x".$vh;
			}
		}

		unset($tech['A']);
		if ($row['audio_codec']) {
			$tech['A'] = "Audio: ".$row['audio_codec'];
		}
		
		unset($tech['D']);
		if ($row['created']) {
			$tech['D'] = "Date: ".$row['created'];
		}
		
		$techinfo = implode(", ", $tech);

		$pdf->SetFont($font_title, 'B', $font_size-3);
		$pdf->SetXY($left_margin + $image_width + $margin, $ypos+ 4);
		$pdf->Cell(0, 0, $techinfo, 0,1, 'L',0);

		// plot
		$plot = leftString($row['plot'], $text_length);
		$pdf->SetFont($font_plot, '', $font_size-1);
		$pdf->SetXY($left_margin + $image_width + $margin, $ypos+3 +3);
		$pdf->SetLeftMargin($left_margin + $image_width + $margin);
		$pdf->WriteHTML($plot);

		// image
		$file = getThumbnail($row['imgurl']);
		if (preg_match('/^img.php/', $file)) $file = img();

		// image file present?
		if ($file)
		{
			$pdf->Image($file, $left_margin, $ypos-2, $image_width, $image_height, '', $link);
		}

		// add mediatype image
		if ($type_image = getMediaImage($row['mediatype']))
		{
			$pdf->Image('./images/media/'.$type_image, $page_width - $mediaimg_width - $right_margin, $ypos - 2, $mediaimg_width, 0, '', '');
		}

		// new position
		$ypos += $margin;
		if ($file or $plot)
		{
			$ypos += max($image_height, $font_size);
		}
		else
		{
			$ypos += $font_size;
		}

		if ($ypos > 250)
		{
			$ypos = $config['pdf_font_size'];
			$pdf->AddPage();
		}
	}

        $pdf->SaveFile($filename);

	// get rid of temp file
	@unlink($filename);
}

?>
