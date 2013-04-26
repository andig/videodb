<?php
/**
 * Swedish language file
 *
 * @package Language
 * @author  Nisse Hellberg <nisse@riotdesign.com>
 * @version $Id: se.php,
 */

$lang['encoding']            = "utf-8";

$lang['audiocodec']          = "Ljud-Codec";				//??
$lang['borrowto']            = "Utlånad till";
$lang['cast']                = "Skådespelare";
$lang['country']             = "Land";
$lang['coverurl']            = "Omslags-URL";				//??
$lang['dimension']           = "Upplösning";
$lang['director']            = "Regissör";
$lang['diskid']              = "DiskID";
$lang['filedate']            = "Fildatum";
$lang['filename']            = "Filnamn";
$lang['filesize']            = "Filstorlek";
$lang['genre']               = "Genre";					
$lang['genres']              = "Genre";					//??
$lang['keywords']            = "Keywords";				//??
$lang['language']            = "Språk";
$lang['length']              = "längd";					//??
$lang['less']                = "färre";
$lang['more']                = "mer";
$lang['plot']                = "Handling";
$lang['runtime']             = "Speltid";
$lang['subtitle']            = "Undertitel";
$lang['title']               = "Titel";
$lang['tvepisode']           = "TV-Avsnitt";
$lang['seen']                = "Sett";					//??
$lang['videocodec']          = "Video-Codec";
$lang['year']                = "Årtal";
$lang['yes']                 = "Ja";
$lang['comment']             = "Kommentar";
$lang['mediatype']           = "Mediatyp";
$lang['save']                = "Spara";
$lang['coverupload']         = "Ladda upp omslag";			//
$lang['visit']               = "Besök";					//??
$lang['date']                = "Datum";
$lang['fetchtime']           = "hämtningstid";				//
$lang['cachesize']           = "Cachestorlek";
$lang['cacheexpired']        = "Utgången";				//??
$lang['cachecleanup']        = "Rensa";					//??
$lang['cacheempty']          = "Töm Cache";
$lang['okay']                = "Ok";

#
# Multiuser
#

$lang['enterusername']       = "Skriv in ditt användarnamn och lösenord för att fortsätta.";
$lang['stayloggedin']        = "Jag vill alltid vara inloggad från den här datorn.";
$lang['login']               = "logga in";
$lang['logout']              = "logga ut";
$lang['owner']               = "Ägare";
$lang['loggedinas']          = "Du är inloggad som";

$lang['username']            = "Användarnamn";
$lang['password']            = "Lösenord";
$lang['permissions']         = "Rättigheter";
$lang['perm_admin']          = "Administration";
$lang['perm_writeall']       = "Modifiera andras filmer";
$lang['perm_write']          = "Modifiera egna filmer";
$lang['perm_adult']          = "Tillåt vuxenfilmer";						//??
$lang['update']              = "ändra";								//??
$lang['create']              = "skapa";
$lang['existingusers']       = "Nuvarande användare";						//??
$lang['createuser']          = "Skapa ny användare";
$lang['email']               = "E-post";
$lang['borrowask']           = "Fråga om möjlighet till lån";					//??

$lang['msg_usercreated']     = "Användare har blivit skapad";
$lang['msg_permpassupd']     = "Rättigheterna och lösenord har blivit uppdaterade";
$lang['msg_permupd']         = "Rättigheterna har blivit uppdaterade";
$lang['msg_userdel']         = "Användare har tagits bort";
$lang['msg_loggedoff']       = "Du har loggats ut";
$lang['msg_invalidchar']     = "Felaktiga tecken i användarnamnet";
$lang['msg_loginfailed']     = "Inloggningen misslyckades";
$lang['msg_borrowasksubject']= "[VideoDB] Förfrågan om lån av %title%";
$lang['msg_borrowaskmail']   = "Användare %user% (%usermail%) vill låna \"%title%\" (DiskID %diskid%) av dig.\n\nVideo-URL: %url%\n\n\n\n-- \nBrevet skickades av VideoDB";
$lang['msg_borrowaskok']     = "Din förfrågan har skickats till filmens ägare. Du borde få besked snart.";
$lang['msg_borrowaskfail']   = "Att skicka din förfrågan misslyckades. Du bör istället själv kontakta ägaren av filmen.";

#
# Phrases
#

$lang['available']           = "är tillgänglig.";
$lang['deleted']             = "Posten togs bort.";						//??
$lang['genre_desc']          = "Sök endast i följande genre (träff på enstaka fält)";		//??
$lang['keywords_desc']       = "Söktermerna utförs mot alla valda fält. Du kan använda AND, NOT och OR i din sökning.";	//??
$lang['lentto']              = "är utlånad till";
$lang['listallfiles']        = "lista alla filer";						//??
$lang['look_imdb']           = "försök att hämta saknad information från IMDB";
$lang['really_del']          = "Bekräfta borttagning";
$lang['returned']            = "Återlämnad";
$lang['notavail']            = "<span class=\"notavail\">EJ TILLGÄNGLIG</span><br />utlånad till";
$lang['curlent']             = "För närvarande utlånade:";						//??
$lang['fieldselect']         = "Sök bara i dessa fält";
$lang['selectall']           = "Markera samtliga";

#
# Menue
#

$lang['browse']              = "lista";
$lang['borrow']              = "låna ut";
$lang['delete']              = "ta bort";
$lang['edit']                = "ändra";
$lang['copy']                = "kopiera";
$lang['n_e_w']               = "ny";
$lang['random']              = "slumpa";
$lang['search']              = "sök";
$lang['statistics']          = "statistik";
$lang['view']                = "visa";
$lang['setup']               = "konfiguration";
$lang['imdbbrowser']         = "IMDB Online";
$lang['help']                = "hjälp";
$lang['searchimdb']          = "sök i IMDB";
$lang['profile']             = "användarprofil";

#
# Radio-Buttons
#

$lang['radio_all']           = "alla";
$lang['radio_new']           = "nya";
$lang['radio_showtv']        = "visa TV-Avsnitt";
$lang['radio_unseen']        = "ej sedda";				//??
$lang['radio_wanted']        = "önskelista";

$lang['radio_look_ignore']   = "ignorera";
$lang['radio_look_lookup']   = "lägg till saknad";
$lang['radio_look_overwrite']= "skriv över";
$lang['radio_look_caption']  = "Hämta extern data";

#
# Statistics
#

$lang['averagefilesize']     = "medel filstorlek";			//??
$lang['averageruntime']      = "medel längd";				//??
$lang['languages']           = "Språk";					//??
$lang['multiple']            = "<small>(en film kan<br />tillhöra flera genre)</small>";		//??
$lang['numberdisks']         = "antal skivor";				//??
$lang['totalfiles']          = "antal filer";				//??
$lang['totalruntime']        = "total längd";				//??
$lang['totalseen']           = "totalt sett";				//??
$lang['totalsize']           = "total storlek";				//??
$lang['tv_episodes']         = "TV-Avsnitt";				//??
$lang['videobygen']          = "Filmer uppdelat per genre";				//??
$lang['videobyvcodec']       = "Video-Codecs";				//??
$lang['videobymedia']        = "Mediatyper";				//??

#
# Lookup Popup
#

$lang['l_search']            = "Sök";
$lang['l_select']            = "Välj titel:";
$lang['l_nothing']           = "Hittade ingen titel.";
$lang['l_selfsearch']        = "Sök själv";

#
# Config Help
#

$lang['enable']              = "aktivera";

$lang['help_langn']          = "Språk";
$lang['help_lang']           = "Välj språkkod här. Om ditt språk inte finns tillgängligt kan du översätta den filen för engelska i <span class=\"example\">language</span> katalogen och skicka den till mig.";
$lang['help_autoidn']        = "Automatiskt DiskID";		//??
$lang['help_autoid']         = "VideoDB använder DiskID för att identifiera skivor. Detta ID är inte samma sak som VideoID vilket sätts automatiskt för varje film. På detta sätt kan du ha flera olika filmer på samma skiva och låta alla bli ej tillgängliga när du lånar ut skivan till någon.<br />Om du inte har något utstuderat system för att namnge dina skivor och bara vill numrera dem kan du låta VideoDB föreslå ett ID när du lägger till en film genom att öka på ett nummer. IOm du vill att VideoDB skall göra detta så kryssa i denna ruta..";		//??
$lang['help_templaten']      = "Stilmall";
$lang['help_template']       = "Välj vilken stilmall som skall användas. Varje stilmall kan ha flera olika färgscheman. Det är bara att testa alla och välja den du tycker bäst om :-)";
$lang['help_mediadefaultn']  = "Standardvärde för mediatyp";
$lang['help_mediadefault']   = "Välj vilken mediatyp som skall föreslås när du lägger in en ny film";
$lang['help_langdefaultn']   = "Standardvärde för språk";
$lang['help_langdefault']    = "Detta är inte samma sak som språkinställningen ovan. Detta är det språk som föreslås när du lägger in en ny film. Lämna detta fält tomt om du inte gillar den här funktionen.";
$lang['help_languageflagsn'] = "Språkflaggor";
$lang['help_languageflags']  = "Här kan du välja vilka flaggor som skall finnas som javascriptknappar när du lägger upp nya filmer eller gör ändringar.<br />Du bör inte välja fler än fem stycken.";
$lang['help_filterdefaultn'] = "Standardvärde för filter";
$lang['help_filterdefault']  = "Detta är den sidan som kommer öppnas när du startar VideoDB.";
$lang['help_showtvn']        = "Visa TV-Avsnitt";
$lang['help_showtv']         = "Om du vill visa TV-avsnitt som standard när du listar filmer så skall denna ruta vara ikryssad";
$lang['help_orderallbydiskn']= "Sortera på DiskID";
$lang['help_orderallbydisk'] = "Om du kryssar i det här valet kommer filmerna i \"all\"-listan att sorteras på DiskID istället för titel.";
$lang['help_customn']        = "Egendefinerade fält";
$lang['help_custom']         = "Du kan definera upp till fyra egna fält för dina filmer. Varje fält kan lagra upp till 255 tecken. Här kan du ge dem namn och välja vilken pluginhanterare som skall användas för att visa fälten. Lämna dessa rader blanka för att inte använda fälten.";
$lang['help_localnetn']      = "Lokalt nätverk";
$lang['help_localnet']       = "Har kan du definera ditt lokala nätverk. Användare som ansluter till VideoDB från andra nätverk kommer inte kunna modifiera filmerna längre. Det här är ett <a href=\"http://www.php.net/manual/en/pcre.pattern.syntax.php\" target=\"_blank\">\"regular expression\"</a> fält så du kan tillochmed skriva in komplicerade värden här.<p>Exempel:<br /><span class=\"example\">^192\.168\.1\.</span> tillåter 192.168.1.1 - 192.168.1.254<br /><span class=\"example\">^(192\.168\.1\.|127\.0\.0\.1$)</span> samma som ovan plus localhost<br /><span class=\"example\">^192\.168\.1\.22$</span> tillåter IP 192.168.1.22 only<br /><span class=\"example\">^(192\.168\.1\.22|127\.0\.0\.1)$</span> samma som ovan plus localhost<br /> om du lämnar detta fält blankt ger du access till vilket nät som helst.<br /></p> OBS: Om du gör fel här kan du låsa ute dig själv från konfigurationssidan!";
$lang['help_imdbOverwriten'] = "Skriv över IMDB";
$lang['help_imdbOverwrite']  = "Hämtning från IMDB skriver aldrig över värden som standardinställning - Om du kryssar i denna ruta kommer data som hämtas från IMDB skriva över befintlig data.";
$lang['help_IMDBagen']       = "IMDB cache-ålder";
$lang['help_IMDBage']        = "VideoDB kan cacha IMDB-sökningar för att undvika onödiga och tidskrävande sökningar samt för att undvika belastning på deras servrar. Du kan ställa in den maximala åldern för en lokal kopia här (i sekunder). Standardvärde är 5 dagar.";
$lang['help_thumbnailn']     = "Thumbnails";
$lang['help_thumbnail']      = "Om du har detta påslaget kommer en liten version av omslagsbilden visas i filmlistan. Om det blir för slött eller om det är jobbigt för ögonen kan du stänga av detta.";
$lang['help_castcolumnsn']   = "Antal kolumner för skådespelare";
$lang['help_castcolumns']    = "Det här anger hur många kolumner som används för att visa skådespelarlistan när info om en film visas. Hur mycket plats du har beror på din skärmupplösning samt vilken stilmall du använder.";
$lang['help_listcolumnsn']   = "Antal kolumner för lista";
$lang['help_listcolumns']    = "Detta värdet används för närvarande bara i stilmallen 'modern'. Det anger hur många kolumner som skall användas när du listar filmer. Om du anger 1 visas en likadan lista som stilmallen 'default' använder.";




$lang['help_proxy_hostn']    = "Proxyserver";
$lang['help_proxy_host']     = "Om server som VideoDB körs ifrån ligger bekom en proxy kan du skriva in namnet på denna server här. Lämna fältet tomt om du inte behöver någon proxy.";
$lang['help_proxy_portn']    = "Proxyport";
$lang['help_proxy_port']     = "Skriv in proxyserverns port här, t.ex. <span class=\"example\">8080</span>.";
$lang['help_actorpicsn']     = "Skådespelarthumbnails";
$lang['help_actorpics']      = "Denna funktion försöker hämta en liten bild av varje varje skådespelare som är med i en film och visar denna bredvid skådespelarens namn.<br />Detta är en experimentell funktion - använd den inte om du upplever några problem med den påslagen.";
$lang['help_thumbAgen']      = "Återkoll av skådespelarthumbnail";	//???????
$lang['help_thumbAge']       = "Detta ställer in hur ofta en nytt försök att ladda en thumbnail av en skådespelare som inte hittades förra gången skall göras (i sekunder). Standardvärde är 3 veckor.";
$lang['help_shownewn']       = "Nya filmer";
$lang['help_shownew']        = "Du kan ställa in hur många filmer som skall visas när \"new\"-filtret används.";
$lang['help_imdbBrowsern']   = "IMDB Browser";
$lang['help_imdbBrowser']    = "Detta låter dig surfa IMDB genom VideoDB och lägga till filmer direkt från deras site.";

$lang['help_multiuser']      = "Detta val ger dig fleranvändarstöd för att använda VideoDB med flera lösenordsskyddade användare.";
$lang['help_multiusern']     = "Fleranvändarstöd";
$lang['help_usermanager']    = "Klicka på denna länk för att lägga till, ändra på och ta bort användare samt deras rättigheter om fleranvändarstöd är påslaget.";
$lang['help_usermanagern']   = "Användarhantering";
$lang['help_denyguest']      = "Med detta val påslaget kan endast registrerade användare komma åt siten. (Gäller bara om fleranvändarstöd är påslaget)";
$lang['help_denyguestn']     = "Neka publik åtkomst";
$lang['help_adultgenres']    = "Filmer i dessa genres kan bara ses av användare med 'adult'-rättigheter. Håll nere CTRL för att välja fler än en genre. (Gäller bara om fleranvändarstöd är påslaget)";
$lang['help_adultgenresn']   = "Vuxenfilmsgenre";
$lang['help_pageno']   	   = "Antal poster som visas per sida. Skriv in 0 eller lämna blankt om du vill slå av sidfunktionen.";
$lang['help_pagenon']        = "Max antal poster per sida";
$lang['page']                = "Sida";
$lang['of']                  = "av";
$lang['records']             = "poster";

?>
