<?php
/**
 * XLS Export functions
 *
 * Allows exporting movies to an Excel list
 * Requires Spreadsheet_Excel_Writer libaray (http://pear.php.net)
 *
 * @package Core
 * @link    http://pear.php.net/package/Spreadsheet_Excel_Writer
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @author  Andreas Götz    <cpuidle@gmx.de>
 * @version $Id: xls.php,v 1.8 2008/01/05 13:50:29 andig2 Exp $
 */

require_once './core/functions.php';
require_once './core/export.core.php';
require_once './engines/engines.php';

#error_reporting(E_ALL^E_NOTICE);
require_once 'Spreadsheet/Excel/Writer.php';

/**
 * Export PDF document
 *
 * @param   string  $where  WHERE clause for SQL statement
 */
function xlsexport($WHERE)
{
    global $config, $lang;

    $text_length    = 256-3;

    // videodb context dir
    $context_dir = preg_replace('/^(.*)\/.*?$/','\\1',$_SERVER["SCRIPT_FILENAME"]);

    // array of temp files wich have to be deleted if workbook is closed
    $del_list = array();

    // make shure we have list with extra fields, even if empty
    $extra_fields = array_map('trim', explode(",", $config['xls_extra_fields']));

    // Creating a workbook
    $workbook = new Spreadsheet_Excel_Writer();
    $workbook->setCustomColor(12, 192,192,192); // Headline
    $workbook->setCustomColor(13, 255,255,200); // Seen
    $workbook->setCustomColor(14, 255,220,220); // Lent
    //$workbook->setCustomColor(15, 0,0,0); // Test

    // sending HTTP headers
    $outputFilename = ($config['xls_output_filename']) ? $config['xls_output_filename'] : 'VideoDB';
    $workbook->send($outputFilename.'.xls');

    // Creating a worksheet
    $sheetTitle = ($config['xls_sheet_title']) ? $config['xls_sheet_title'] : 'VideoDB';
    $worksheet =& $workbook->addWorksheet($sheetTitle);

    // format templates
    $alignLeftFormatNormal   =& $workbook->addFormat();
    $alignLeftFormatLent     =& $workbook->addFormat(array('Pattern' => 1));
    $alignLeftFormatLent     -> setFGColor(14);
    $alignRightFormatNormal  =& $workbook->addFormat(array('Align' => 'right'));
    $alignRightFormatLent    =& $workbook->addFormat(array('Align' => 'right', 'Pattern' => 1));
    $alignRightFormatLent    -> setFgColor(14);
    $alignCenterFormatNormal =& $workbook->addFormat(array('Align' => 'center'));
    $alignCenterFormatLent   =& $workbook->addFormat(array('Align' => 'center', 'Pattern' => 1));
    $alignCenterFormatLent   -> setFgColor(14);
    $titleFormatNormal       =& $workbook->addFormat(array('Bold' => 1));
    $titleFormatUnseen       =& $workbook->addFormat(array('Bold' => 1, 'Pattern' => 1));
    $titleFormatUnseen       -> setFgColor(13);
    $titleFormatLent         =& $workbook->addFormat(array('Bold' => 1, 'Pattern' => 1));
    $titleFormatLent         -> setFgColor(14);

    $plotFormatNormal        =& $workbook->addFormat();
    $plotFormatNormal        -> setTextWrap();
    $plotFormatLent          =& $workbook->addFormat(array('Pattern' => 1));
    $plotFormatLent          -> setTextWrap();
    $plotFormatLent          -> setFgColor(14);

    $headlineFormat          =& $workbook->addFormat(array('Bold' => 1, 'Align' => 'center', 'Pattern' => 1));
    $headlineFormat          ->setFgColor(12);

    $rowindex    = 0;
    $columnindex = 0;

    if ($config['xls_show_headline'])
    {
        $worksheet->setRow(0, 30);
        $rowindex++;
    }

    // get data (see http://pear.php.net/bugs/bug.php?id=1572)
    $result = iconv_array('utf-8', 'iso-8859-1', exportData($WHERE));

    foreach ($result as $row)
    {
        $columnindex = 0;
        set_time_limit(300); // rise per movie execution timeout limit if safe_mode is not set in php.ini

        if (!empty($row['lentto']) && $config['xls_mark_lent']) {
            $alignLeftFormat   = $alignLeftFormatLent;
            $alignCenterFormat = $alignLeftFormatLent;
            $alignRightFormat  = $alignLeftFormatLent;
        }
        else
        {
            $alignLeftFormat   = $alignLeftFormatNormal;
            $alignCenterFormat = $alignLeftFormatNormal;
            $alignRightFormat  = $alignLeftFormatNormal;
        }
        $worksheet->setRow($rowindex, 15, $alignLeftFormat);

        foreach ($extra_fields as $field)
        {
            $isNote = false;
            $walks = 1;
            if (preg_match('/(.+)\((.+)\)/',$field,$matches))
            {
                $field = trim($matches[1]);
                $note  = trim($matches[2]);
                $walks = 2;
            }

            for ($walk = 0;$walk < $walks; $walk++)
            {
                if ($walk == 1)
                {
                    $isNote = true;
                    $field  = $note;
                    $columnindex--;
                }

                // title
                if ($field == "title")
                {
                    // headline
                    if ($config['xls_show_headline'] && $rowindex == 1 && !$isNote)
                        $worksheet->writeString( 0, $columnindex, $lang['title'], $headlineFormat);

                    $title = $row['title'];
                    if ($row['subtitle'])   $title .= ' - '.$row['subtitle'];

                    if ($isNote) $worksheet->writeNote($rowindex, $columnindex++, $lang['title'].":\n".html_entity_decode($title));
                    else {
                        if ($row['seen'] == '0' && $config['xls_mark_unseen']) $format = $titleFormatUnseen;
                        elseif (!empty($row['lentto']) && $config['xls_mark_lent']) $format = $titleFormatLent;
                        else  $format = $titleFormatNormal;
                        if ($rowindex == 1) $worksheet->setColumn($columnindex, $columnindex, 50);
                        $imdb = $row['imdbID'];
                        $link = ($imdb) ? engineGetContentUrl($imdb, engineGetEngine($imdb)) : '';
                        $worksheet->writeUrl($rowindex, $columnindex, $link, html_entity_decode($title), $format);
                    }
                    $columnindex++;
                }

                // plot
                elseif ($field == "plot")
                {
                    // headline
                    if ($config['xls_show_headline'] && $rowindex == 1 && !$isNote)
                        $worksheet->writeString( 0, $columnindex, $lang['plot'], $headlineFormat);

                    if ($isNote) $worksheet->writeNote($rowindex, $columnindex++, leftString(html_entity_decode($row['plot']),$text_length));
                    else
                    {
                        if (!empty($row['lentto']) && $config['xls_mark_lent']) $format = $plotFormatLent;
                        else  $format = $plotFormatNormal;
                        if ($rowindex == 1) $worksheet->setColumn($columnindex, $columnindex, 50);
                        $worksheet->writeString($rowindex, $columnindex++, leftString(html_entity_decode($row['plot']),$text_length), $format);
                    }
                }

                // DiskId
                elseif ($field == "diskid")
                {
                    // headline
                    if ($config['xls_show_headline'] && $rowindex == 1 && !$isNote)
                        $worksheet->writeString( 0, $columnindex, $lang['diskid'], $headlineFormat);

                    if ($isNote) $worksheet->writeNote($rowindex, $columnindex++, $lang['diskid'].":\n".html_entity_decode($row['diskid']));
                    else
                    {
                        if ($rowindex == 1) $worksheet->setColumn($columnindex, $columnindex, 7);
                        $worksheet->writeString($rowindex, $columnindex++, html_entity_decode($row['diskid']), $alignCenterFormat);
                    }
                }

                // add language
                elseif ($field == "language")
                {
                    // headline
                    if ($config['xls_show_headline'] && $rowindex == 1 && !$isNote)
                        $worksheet->writeString( 0, $columnindex, $lang['language'], $headlineFormat);

                    if ($isNote) $worksheet->writeNote($rowindex, $columnindex++, $lang['language'].":\n".html_entity_decode($row['language']));
                    else
                    {
                        if ($rowindex == 1) $worksheet->setColumn($columnindex, $columnindex, 30);
                        $worksheet->writeString($rowindex, $columnindex++, html_entity_decode($row['language']), $alignLeftFormat);
                    }
                }

                // add mediatype
                elseif ($field == "mediatype")
                {
                    // headline
                    if ($config['xls_show_headline'] && $rowindex == 1 && !$isNote)
                        $worksheet->writeString( 0, $columnindex, $lang['mediatype'], $headlineFormat);

                    if ($isNote) $worksheet->writeNote($rowindex, $columnindex++, $lang['mediatype'].":\n".html_entity_decode($row['mediatype']));
                    else
                    {
                        if ($rowindex == 1) $worksheet->setColumn($columnindex, $columnindex, 7);
                        $worksheet->writeString($rowindex, $columnindex++, html_entity_decode($row['mediatype']), $alignLeftFormat);
                    }
                }

                // genres
                elseif ($field == "genres")
                {
                    // headline
                    if ($config['xls_show_headline'] && $rowindex == 1 && !$isNote)
                        $worksheet->writeString( 0, $columnindex, $lang['genres'], $headlineFormat);

                    if (count($row['genres']))
                    {
                        $output_genres = array();
                        foreach ($row['genres'] as $genre)
                        {
                            $output_genres[]= html_entity_decode($genre['name']);
                        }

                        if ($isNote) $worksheet->writeNote($rowindex, $columnindex++, $lang['genres'].":\n".join(", ", $output_genres));
                        else
                        {
                            if ($rowindex == 1) $worksheet->setColumn($columnindex, $columnindex, 20);
                            $worksheet->writeString($rowindex, $columnindex, join(", ", $output_genres), $alignCenterFormat);
                        }
                    }
                    $columnindex++;
                }

                // runtime
                elseif ($field == "runtime")
                {
                    // headline
                    if ($config['xls_show_headline'] && $rowindex == 1 && !$isNote)
                        $worksheet->writeString( 0, $columnindex, $lang['runtime'], $headlineFormat);

                    if ($row['runtime'])
                    {
                        if ($isNote) $worksheet->writeNote($rowindex, $columnindex++, $lang['runtime'].":\n".html_entity_decode($row['runtime']).' min');
                        else
                        {
                            if ($rowindex == 1) $worksheet->setColumn($columnindex, $columnindex, 7);
                            $worksheet->writeString($rowindex, $columnindex, html_entity_decode($row['runtime']).' min', $alignRightFormat);
                        }
                    }
                    $columnindex++;
                }

                // year
                elseif ($field == "year")
                {
                    // headline
                    if ($config['xls_show_headline'] && $rowindex == 1 && !$isNote)
                        $worksheet->writeString( 0, $columnindex, $lang['year'], $headlineFormat);

                    if ($row['year'] != '0000')
                    {
                        if ($isNote) $worksheet->writeNote($rowindex, $columnindex++, $lang['year'].":\n".html_entity_decode($row['year']));
                        else
                        {
                            if ($rowindex == 1) $worksheet->setColumn($columnindex, $columnindex, 7);
                            $worksheet->writeNumber($rowindex, $columnindex, html_entity_decode($row['year']), $alignCenterFormat);
                        }
                    }
                    $columnindex++;
                }

                // owner
                elseif ($field == "owner")
                {
                    // headline
                    if ($config['xls_show_headline'] && $rowindex == 1 && !$isNote)
                        $worksheet->writeString( 0, $columnindex, $lang['owner'], $headlineFormat);

                    if ($isNote) $worksheet->writeNote($rowindex, $columnindex++, $lang['owner'].":\n".html_entity_decode($row['owner']));
                    else
                    {
                        if ($rowindex == 1) $worksheet->setColumn($columnindex, $columnindex, 15);
                        $worksheet->writeString($rowindex, $columnindex++, html_entity_decode($row['owner']), $alignCenterFormat);
                    }
                }

                // lent
                elseif ($field == "lent")
                {
                    // headline
                    if ($config['xls_show_headline'] && $rowindex == 1 && !$isNote)
                        $worksheet->writeString( 0, $columnindex, $lang['lentto'], $headlineFormat);

                    if ($isNote) $worksheet->writeNote($rowindex, $columnindex++, $lang['lentto'].":\n".html_entity_decode($row['lentto']));
                    else
                    {
                        if ($rowindex == 1) $worksheet->setColumn($columnindex, $columnindex, 15);
                        $worksheet->writeString($rowindex, $columnindex++, html_entity_decode($row['lentto']), $alignCenterFormat);
                    }
                }
                // seen
                elseif ($field == "seen")
                {
                    // headline
                    if ($config['xls_show_headline'] && $rowindex == 1 && !$isNote)
                        $worksheet->writeString( 0, $columnindex, $lang['seen'], $headlineFormat);

                    if ($isNote) {
                        if ($row['seen'] == 1) $worksheet->writeNote($rowindex, $columnindex++, html_entity_decode($lang['seen']));
                        else $columnindex++;
                    }
                    else
                    {
                        if ($rowindex == 1) $worksheet->setColumn($columnindex, $columnindex, 2);
                        if ($row['seen'] == 1) $worksheet->writeString($rowindex, $columnindex++, "X", $alignCenterFormat); else $columnindex++;
                    }
                }
                // insertdate
                elseif ($field == "insertdate")
                {
                    // headline
                    if ($config['xls_show_headline'] && $rowindex == 1 && !$isNote)
                        $worksheet->writeString( 0, $columnindex, $lang['date'], $headlineFormat);

                    if ($isNote) $worksheet->writeNote($rowindex, $columnindex++, $lang['date'].":\n".html_entity_decode(preg_replace('/^([0-9]{4}\-[0-9]{2}\-[0-9]{2}).*/','$1',$row['created'])));
                    else
                    {
                        if ($rowindex == 1) $worksheet->setColumn($columnindex, $columnindex, 10);
                        $worksheet->write($rowindex, $columnindex++, html_entity_decode(preg_replace('/^([0-9]{4}\-[0-9]{2}\-[0-9]{2}).*/','$1',$row['created'])), $alignCenterFormat);
                    }
                }

                // custom fields
                elseif(preg_match("/^custom[0-4]$/",$field))
                {
                    // headline
                    if ($config['xls_show_headline'] && $rowindex == 1 && !$isNote)
                        $worksheet->writeString( 0, $columnindex, $config[$field], $headlineFormat);

                    //$row[$field] = html_entity_decode($row[$field]);

                    switch ($config[$field.'type'])
                    {
                        case 'ed2k':
                            if ($isNote) $worksheet->writeNote($rowindex, $columnindex++, $config[$field].":\n".html_entity_decode($row[$field]));
                            else
                            {
                                if ($rowindex == 1) $worksheet->setColumn($columnindex, $columnindex, 12);
                                $worksheet->writeUrl($rowindex, $columnindex++, html_entity_decode($row[$field]), 'ED2K-Link', $alignCenterFormat);
                            }
                            break;
                        case 'language':
                            if ($isNote) $worksheet->writeNote($rowindex, $columnindex++, $config[$field].":\n".html_entity_decode($row[$field]));
                            else
                            {
                                if ($rowindex == 1) $worksheet->setColumn($columnindex, $columnindex, 30);
                                $worksheet->writeString($rowindex, $columnindex++, html_entity_decode($row[$field]), $alignLeftFormat);
                            }
                            break;
                        case 'rating':
                            if ($row[$field]) $rating = html_entity_decode($row[$field]).'/10'; else $rating = "";
                            if ($isNote)
                            {
                                if ($row[$field]) $worksheet->writeNote($rowindex, $columnindex, $config[$field].":\n".$rating);
                                $columnindex++;
                            }
                            else
                            {
                                if ($rowindex == 1) $worksheet->setColumn($columnindex, $columnindex, 7);
                                if ($row[$field]) $worksheet->writeString($rowindex, $columnindex, $rating,$alignCenterFormat);
                                $columnindex++;
                            }
                            break;
                        case 'fsk':
                            if (preg_match("/[0-9]+/",$row[$field]) && !preg_match("/[^0-9]+/",$row[$field]))
                            {
                                $fskstr =  'FSK'.html_entity_decode($row[$field]);
                            }
                            else $fskstr = html_entity_decode($row[$field]);

                            if ($isNote) $worksheet->writeNote($rowindex, $columnindex++, $config[$field].":\n".$fskstr);
                            else
                            {
                                if ($rowindex == 1) $worksheet->setColumn($columnindex, $columnindex, 7);
                                $worksheet->writeString($rowindex, $columnindex++, $fskstr, $alignCenterFormat);
                            }
                            break;
                        case 'barcode':
                            if ($isNote) $worksheet->writeNote($rowindex, $columnindex++, $config[$field].":\n".html_entity_decode($row[$field]));
                            else
                            {
                                if ($rowindex == 1) $worksheet->setColumn($columnindex, $columnindex, 15);
                                $worksheet->writeString($rowindex, $columnindex++, html_entity_decode($row[$field]), $alignCenterFormat);
                            }
                            break;
                        case 'orgtitle':
                            if ($isNote)
                            {
                                if (!empty($row[$field])) $worksheet->writeNote($rowindex, $columnindex, $config[$field]. ":\n".html_entity_decode($row[$field]));
                                $columnindex++;
                            }
                            else
                            {
                                if ($rowindex == 1) $worksheet->setColumn($columnindex, $columnindex, 50);
                                $worksheet->writeString($rowindex, $columnindex++, html_entity_decode($row[$field]), $alignLeftFormat);
                            }
                            break;
                        case 'movix':
                            if ($isNote) $worksheet->writeNote($rowindex, $columnindex++, $config[$field].":\n ".html_entity_decode($row[$field]));
                            else
                            {
                                if ($rowindex == 1) $worksheet->setColumn($columnindex, $columnindex, 7);
                                $worksheet->writeString($rowindex, $columnindex++, html_entity_decode($row[$field]), $alignCenterFormat);
                            }
                            break;
                        case 'mpaa':
                            if ($isNote) $worksheet->writeNote($rowindex, $columnindex++, $config[$field].":\n".html_entity_decode($row[$field]), $alignCenterFormat);
                            else
                            {
                                if ($rowindex == 1) $worksheet->setColumn($columnindex, $columnindex, 12);
                                $worksheet->writeString($rowindex, $columnindex++, html_entity_decode($row[$field]), $alignCenterFormat);
                            }
                            break;
                        case 'bbfc':
                            if ($isNote) $worksheet->writeNote($rowindex, $columnindex++, $config[$field].":\n".html_entity_decode($row[$field]));
                            else
                            {
                                if ($rowindex == 1) $worksheet->setColumn($columnindex, $columnindex, 7);
                                $worksheet->writeString($rowindex, $columnindex++, html_entity_decode($row[$field]), $alignCenterFormat);
                            }
                            break;
                        default: // unknown
                            if ($isNote) $worksheet->writeNote($rowindex, $columnindex++, $config[$field].":\n".html_entity_decode($row[$field]));
                            else
                            {
                                $worksheet->writeString($rowindex, $columnindex++, html_entity_decode($row[$field]), $alignLeftFormat);
                            }
                            break;
                    }
                }

            } //End of walk

        }
        $rowindex++;
    }
    // Let's send the file
    $workbook->close();
}
?>