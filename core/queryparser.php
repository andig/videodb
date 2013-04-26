<?php
/**
 * Searchquerystring Parser
 *
 * Parses a search query into it's tokens kann detect + - AND OR NOT Operators
 * and Phrases.
 *
 * @todo    remove german errorstrings, maybe handle umlauts and stuff...
 *
 * @package Search
 * @author  Andreas Gohr    <a.gohr@web.de>
 * @author  Andreas Götz	<cpuidle@gmx.de>
 * @version $Id: queryparser.php,v 1.9 2007/12/19 18:42:11 andig2 Exp $
 */


/**
 * Querystringparser
 *
 * Parses a querystring into a datastructure
 *
 * @param   string  $query    Querystring
 * @param   string  &$errors  Stringreference to write errors back
 * @return  array             parsed querytokens
 */
function queryparser($query, &$errors)
{
    $query	= trim($query);
/*
    // filter forbidden characters
    if (preg_match('/[^\w \.\(\)"\'*]/',$query))
    {
        $errors .= "Nicht erlaubte Zeichen wurden ignoriert\n";
        $query = preg_replace('/[^\w \.\(\)"\'*]/','',$query);
    }
*/

    $ops    = array();
    $struct = array();
    $tokens	= tokenizer($query);

    // look through tokens
    while ($current = array_shift($tokens))
    {
        if (preg_match('/^(AND|OR|NOT)$/i', $current)) 
        {  
            // token is operator
            $ops[] = strtoupper($current);
        }
        else
        {                                      
            // token is searchword
            if (!count($ops)) 
            {
                // empty operator counts as AND
                $ops[] = 'AND';
            }
            
            // clean invalid operators
            $cleanops = cleanoperators($ops, $errors);

            // check wildcards
            $wild = '';
            if (substr($current,0,1) == '*') $wild .= 'l';
            if (substr($current, -1) == '*') $wild .= 'r';

            $current    = str_replace('*', '', $current);
            $struct[]   = array('ops'      => $cleanops,
                                'token'    => $current,
                                'wildcard' => $wild);
            $ops = array();
        }
    }
    return $struct;
}

/**
 * Querystring tokenizer
 *
 * Parse string into array of tokens. 
 * Honors literal expressions enclosed by "",
 * converts +/- to AND and NOT
 *
 * @param   string    Querystring
 * @return  array     All tokens of the Strings
 */
function tokenizer($qstring)
{
    // replace +/- with AND and NOT
    $qstring = ' '.$qstring; // for following regexps
    $qstring = preg_replace('/(\s)-(\S)/',  '\1NOT \2', $qstring);
    $qstring = preg_replace('/(\s)\+(\S)/', '\1AND \2', $qstring);
    $qstring = trim($qstring);

    $tokens  = array();
    $current = '';
    $sep     = '\s';

    for ($i=0; $i < strlen($qstring); $i++)
    {
        $char = $qstring{$i};

        // match current separator?
        if (preg_match("/$sep/", $char))
        {
            $current = trim($current);
            if (!empty($current) AND ((str_replace('*', '', $current)) != '')) 
            {
                // add non-empty token
                $tokens[] = $current;
            }
            $current    = '';
            $sep		= '\s';
        }

        // begin literal expression?
        elseif ($char == '"') 
        {
            $sep = '"';
        }
        
        // normal token character
        else 
        {
            $current .= $char;
        }
    }

    // add remaining token
    $current = trim($current);
    if (!empty($current) AND ((str_replace('*','',$current))!='')) 
    {
        $tokens[] = $current;
    }

    return $tokens;
}

/**
 * Operator cleaning
 *
 * removes illogical operator combinations...
 *
 * @param   array   Operators
 * @param   string  Stringreference to write errors back
 * @return  string  cleaned Operators
 */
function cleanoperators($ops, &$errors)
{
    $newops = array();

    // make unique
    $ops = array_unique($ops);
    
    // sort
    if (in_array('AND', $ops))  $newops[] = 'AND';
    if (in_array('OR', $ops))   $newops[] = 'OR';
    if (in_array('NOT', $ops))  $newops[] = 'NOT';

    // join
    $opstr = join(' ', $newops);

    // clean unnormal conditions
    if (strstr($opstr, 'AND OR')) 
    {
        $errors	.="Die logische Verknüpfung 'AND OR' ist nicht erlaubt und wurde in 'OR' umgewandelt.\n";
        $opstr	 = str_replace('AND OR', 'OR', $opstr);
    }
    if (strstr($opstr, 'OR NOT')) 
    {
        $errors	.="Die logische Verknüpfung 'OR NOT' ist nicht erlaubt und wurde in 'AND' umgewandelt.\n";
        $opstr	 = str_replace('OR NOT', 'AND', $opstr);
    }
    if ($opstr == 'NOT') 
    {
        $opstr = 'AND NOT';
    }

    return $opstr;
}

?>
