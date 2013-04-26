<?php
/**
 * Hungarian language file
 *
 * @package Language
 * @author  Nandor Horgos <hn@integra.hu>
 * @version $Id: hu.php,v 1.8 2013/03/16 10:10:07 andig2 Exp $
 */

$lang['encoding']            = "utf-8";

$lang['audiocodec']          = "Hang-kodek";
$lang['borrowto']            = "Kölcsönzés";
$lang['cast']                = "Szereposztás";
$lang['country']             = "Ország";
$lang['coverurl']            = "Borító-URL";
$lang['dimension']           = "Képméret";
$lang['director']            = "Rendezõ";
$lang['diskid']              = "LemezID";
$lang['filedate']            = "Fájldátum";
$lang['filename']            = "Fájlnév";
$lang['filesize']            = "Fájlméret";
$lang['genre']               = "Mûfaj";
$lang['genres']              = "Mûfajok";
$lang['keywords']            = "Kulcsszavak";
$lang['language']            = "Nyelv";
$lang['length']              = "Hossz";
$lang['less']                = "kevesebb";
$lang['more']                = "több";
$lang['plot']                = "Cselekmény";
$lang['runtime']             = "Lejátszási idõ";
$lang['subtitle']            = "Alcím";
$lang['title']               = "Cím";
$lang['tvepisode']           = "TV-Epizód";
$lang['seen']                = "Láttam";
$lang['videocodec']          = "Videó-kodek";
$lang['year']                = "Év";
$lang['yes']                 = "Igen";
$lang['comment']             = "Vélemény";
$lang['mediatype']           = "Adathordozó";
$lang['save']                = "Ment";
$lang['coverupload']         = "Borító-feltöltés";
$lang['visit']               = "Látogatás";
$lang['date']                = "Dátum";
$lang['fetchtime']           = "Letöltési idõ";
$lang['cachesize']           = "Cache méret";
$lang['cacheexpired']        = "Lejárt";
$lang['cachecleanup']        = "Takarít";
$lang['cacheempty']          = "Cache ürítés";
$lang['okay']                = "Mehet";

#
# Multiuser
#

$lang['enterusername']       = "A folytatáshoz írja be felhasználónevét és jelszavát.";
$lang['stayloggedin']        = "Maradjon bejelentkezve errõl a géprõl";
$lang['login']               = "belépés";
$lang['logout']              = "kilépés";
$lang['owner']               = "Tulajdonos";
$lang['loggedinas']          = "Bejelentkezve:";

$lang['username']            = "Felhasználónév";
$lang['password']            = "Jelszó";
$lang['permissions']         = "Jogosultság";
$lang['perm_admin']          = "Adminisztráció";
$lang['perm_writeall']       = "Mások filmjeinek módosítása";
$lang['perm_write']          = "Saját filmek módosítása";
$lang['perm_adult']          = "Felnõtt filmek megtekintése";
$lang['update']              = "módosítás";
$lang['create']              = "létrehozás";
$lang['existingusers']       = "Létezõ felhasználók";
$lang['createuser']          = "Új felhasználó létrehozása";
$lang['email']               = "E-Mail";
$lang['borrowask']           = "Kölcsönkér";

$lang['msg_usercreated']     = "Felhasználó létrehozva";
$lang['msg_permpassupd']     = "Jogosultságok és jelszó módosítva";
$lang['msg_permupd']         = "Jogosultságok módosítva";
$lang['msg_userdel']         = "Felhasználó törölve";
$lang['msg_loggedoff']       = "Sikeres kilépés";
$lang['msg_invalidchar']     = "Nem megengedett karakterek a felhasználónévben";
$lang['msg_loginfailed']     = "Sikertelen belépés";
$lang['msg_borrowasksubject']= "[VideoDB] Kölcsönzési kérelem: %title%";
$lang['msg_borrowaskmail']   = "%user% (%usermail%) szeretné a \"%title%\" címû, (LemezID: %diskid%) filmet kölcsönkérni Öntõl.\n\nVideo-URL: %url%\n\n\n\n-- \nSent by VideoDB";
$lang['msg_borrowaskok']     = "Kérését elküdtük a film tulajdonosának.";
$lang['msg_borrowaskfail']   = "Kérésének továbbítása sikertelen volt. Vegye fel a kapcsolatot közvetlenül a film tulajdonosával.";

#
# Phrases
#

$lang['available']           = "elérhetõ.";
$lang['deleted']             = "A filmet törölték.";
$lang['genre_desc']          = "Gyerekzár";
$lang['keywords_desc']       = "Minden megjelölt mezõben kerestem a kulcsszavakat. Kereséskor használhatja az AND, NOT és OR feltételeket.";
$lang['lentto']              = "kölcsönadva ";
$lang['listallfiles']        = "minden fájl listázása";
$lang['look_imdb']           = "A hiányzó adatok keresése IMDB-ben";
$lang['really_del']          = "Tényleg törli";
$lang['returned']            = "Visszahozták";
$lang['notavail']            = "<span class=\"notavail\">NEM ELÉRHETÕ</span><br />kölcsönadva:";
$lang['curlent']             = "Pillanatnyilag kölcsönadva:";
$lang['fieldselect']         = "Keresõmezõk szûkítése";
$lang['selectall']           = "Mindet kijelöli";

#
# Menue
#

$lang['browse']              = "tallóz";
$lang['borrow']              = "kölcsönadva";
$lang['delete']              = "töröl";
$lang['edit']                = "szerkeszt";
$lang['copy']                = "másol";
$lang['n_e_w']               = "új";
$lang['random']              = "random";
$lang['search']              = "keres";
$lang['statistics']          = "statisztikák";
$lang['view']                = "részletek";
$lang['setup']               = "beállítások";
$lang['imdbbrowser']         = "IMDB Online";
$lang['help']                = "súgó";
$lang['searchimdb']          = "Keresés IMDB-ben";
$lang['profile']             = "felh.profil";

#
# Radio-Buttons
#

$lang['radio_all']           = "mind";
$lang['radio_new']           = "új";
$lang['radio_showtv']        = "mutasd a TV-Sorozatokat";
$lang['radio_unseen']        = "még nem láttam";
$lang['radio_wanted']        = "kívánságlista";

$lang['radio_look_ignore']   = "mellõz";
$lang['radio_look_lookup']   = "kiegészít";
$lang['radio_look_overwrite']= "felülír";
$lang['radio_look_caption']  = "Adatkeresõ";

#
# Statistics
#

$lang['averagefilesize']     = "átlagos fájlméret";
$lang['averageruntime']      = "átlagos lejátszási idõ";
$lang['languages']           = "Nyelvek";
$lang['multiple']            = "<small>(több mûfajba sorolás<br />filmenként engedélyezve)</small>";
$lang['numberdisks']         = "lemezek száma";
$lang['totalfiles']          = "össz. fájlszám";
$lang['totalruntime']        = "össz. lejátszási idõ";
$lang['totalseen']           = "össz. látták";
$lang['totalsize']           = "össz. méret";
$lang['tv_episodes']         = "TV-Sorozatok";
$lang['videobygen']          = "Mûfajonként";
$lang['videobyvcodec']       = "Kodekenként";
$lang['videobymedia']        = "Adathordozó";

#
# Lookup Popup
#

$lang['l_search']            = "Keresés";
$lang['l_select']            = "Válassza ki a jó címet:";
$lang['l_nothing']           = "Nincs találat.";
$lang['l_selfsearch']        = "Keresd magad";

#
# Config Help
#

$lang['enable']              = "enable";

$lang['help_langn']          = "Nyelv";
$lang['help_lang']           = "Válassza ki országkódját. Ha anyanyelve nem elérhetõ, akkor a <span class=\"example\">language</span> könyvtárban található angol nyelvi fájlt fordítsa le és küldje el nekem.";
$lang['help_autoidn']        = "Automatikus LemezID";
$lang['help_autoid']         = "A VideoDB alkalmazás a LemezID alapján azonosítja a lemezeket. Ez az ID különbözik a filmekhez automatikusan hozzárendelt FilmID-tõl. Ez teszi lehetõvé a több filmet tartalmazó lemezek kezelését (nem elérhetõ-vé téve mindegyik feilmet a lemez kölcsönzésekor).<br />However if you don't have a fancy namingscheme for your disks and just want to number them, VideoDB can suggest an ID when adding a new video by increasing a number. If you want this enable this option.";
$lang['help_templaten']      = "Sablon";
#$lang['help_template']       = "This sets the design to use. Each template can have multiple styles (colorschemes). Just try them and use the one you like most :-)";
$lang['help_mediadefaultn']  = "Alapértelmezett adathordozó";
#$lang['help_mediadefault']   = "Select which mediatype should be suggested when adding a new video";
$lang['help_langdefaultn']   = "Alapértelmezett nyelv";
#$lang['help_langdefault']    = "This is different from the language setting above. This is the language that will be filled in on default when you add a new video. Leave it blank if you don't like this.";
$lang['help_languageflagsn'] = "Zászlók";
#$lang['help_languageflags']  = "Here you can select the flags which should be available as JavaScript buttons in the edit form for quick language selection.<br />No more than five should be selected.";
$lang['help_filterdefaultn'] = "Alapértelmezett szûrõ";
#$lang['help_filterdefault']  = "This is the page that will be opened when you start VideoDB.";
$lang['help_showtvn']        = "Mutasd a TV Sorozatokat";
#$lang['help_showtv']         = "If you want TV Episodes to be included in the browse view by default enable this option.";
$lang['help_orderallbydiskn']= "LemezID-re rendezve";
#$lang['help_orderallbydisk'] = "If you enable this parameter the list of movies in the \"all\" view will be ordered by DiskID instead of the video titles.";
$lang['help_customn']        = "Egyéni mezõk";
#$lang['help_custom']         = "You may define up to four custom fields for your videos. Each can store up to 255 characters. Here you can give them names and select the pluginhandlers which should be used to display the fields. Leave the name blank to disable them.";
$lang['help_localnetn']      = "Helyi hálózat";
#$lang['help_localnet']       = "Here you can define your local network. Users who access VideoDB from somewhere else can not edit the data anymore. This is a <a href=\"http://www.php.net/manual/en/pcre.pattern.syntax.php\" target=\"_blank\">regular expression</a> so you may even give some complicated term here.<p>Examples:<br /><span class=\"example\">^192\.168\.1\.</span> allows 192.168.1.1 - 192.168.1.254<br /><span class=\"example\">^(192\.168\.1\.|127\.0\.0\.1$)</span> the same as above plus localhost<br /><span class=\"example\">^192\.168\.1\.22$</span> allows IP 192.168.1.22 only<br /><span class=\"example\">^(192\.168\.1\.22|127\.0\.0\.1)$</span> the same as above plus localhost<br /> leaving it blank allows everyone<br /></p> Attention: If you make a mistake here you will lock out your self from the config page!";
$lang['help_imdbOverwriten'] = "IMDB felülírás";
#$lang['help_imdbOverwrite']  = "IMDB-Lookups never replace any existing data by default - If you enable this option lookups will replace previous data.";
$lang['help_IMDBagen']       = "IMDB cache kora";
#$lang['help_IMDBage']        = "VideoDB can cache IMDB queries to avoid unnecessary timeconsuming requests and to lower the load on their servers. You may define the maximum age of local documents here (in seconds). Defaults to 5 days.";
$lang['help_thumbnailn']     = "Indexképek";
#$lang['help_thumbnail']      = "Enabling this shows a tiny version of the coverimage in the browsinglist. If it is to slow or distracts your eyes disable it.";
$lang['help_castcolumnsn']   = "Szereposztás oszlopszám";
#$lang['help_castcolumns']    = "This defines how many columns are used to display the cast list in a movies detail view. It depends on your screen resolution and the chosen template how much space you have.";
$lang['help_listcolumnsn']   = "Lista oszlopszám";
#$lang['help_listcolumns']    = "This is currently only used in the 'modern' template. It defines how many columns are used to show the browsing list. If set to 1 it shows a list like the default template.";
$lang['help_proxy_hostn']    = "Proxy kiszolgáló";
#$lang['help_proxy_host']     = "If the host which VideoDB runs on is behind a proxy server you can give its name here. Leave it blank if you don't need a proxy.";
$lang['help_proxy_portn']    = "Proxy port";
#$lang['help_proxy_port']     = "Give the port of your proxy here eg. <span class=\"example\">8080</span>.";
$lang['help_actorpicsn']     = "Színész indexképek";
#$lang['help_actorpics']      = "This tries to find a tiny image for each actor from the cast and to display it beside the actors name.<br />This is an experimental feature - disable it if you have any problems with it.";
$lang['help_thumbAgen']      = "Színész indexkép újra";
#$lang['help_thumbAge']       = "This defines when to retry to load actor thumbnails that weren't found last time (in seconds). Defaults to 3 weeks.";
$lang['help_shownewn']       = "Új filmek";
#$lang['help_shownew']        = "You can enter the number of movies shown on the \"new\" filter.";
$lang['help_imdbBrowsern']   = "IMDB Böngészõ";
#$lang['help_imdbBrowser']    = "This allows you to browse the IMDB through VideoDB and add movies directly from within their site.";

#$lang['help_multiuser']      = "This option enables the multi user support to use VideoDB with multiple password authenticated users.";
$lang['help_multiusern']     = "Multiuser Support";
#$lang['help_usermanager']    = "Follow this link to add, modify and delete users and their permissions for the multiuser mode.";
$lang['help_usermanagern']   = "User Management";
#$lang['help_denyguest']      = "Enabling this denies access for everyone except authenticated users. (Only applies when multiuser support is enabled)";
$lang['help_denyguestn']     = "No public access";
#$lang['help_adultgenres']    = "Movies in the genres you select here will only be visible to users with 'adult' permissions. Hold down the CTRL-key to select multiple genres. (Only applies when multiuser support is enabled)";
$lang['help_adultgenresn']   = "Adult Genres";
#$lang['help_pageno']   	   = "Number of items to display on each page. Use 0 or leave empty to disable pagination.";
$lang['help_pagenon']        = "Maximum items per page";
$lang['page']                = "Page";
$lang['of']                  = "of";
$lang['records']             = "records";

?>