<?php
/**
 * Dutch language file
 *
 * @package Language
 * @author  Kees de Bruin <kees@kdbruin.net>
 * @author  Wout Keunen <wout DOT keunen AT gmail DOT com>
 * @version $Id: nl.php,v 2.22 2013/03/16 10:10:07 andig2 Exp $
 */

$lang['encoding']            = "utf-8";

$lang['audiocodec']          = "Audio Codec";
$lang['borrowto']            = "geleend aan";
$lang['cast']                = "Cast";
$lang['country']             = "Land";
$lang['coverurl']            = "Cover URL";
$lang['dimension']           = "Afmeting";
$lang['director']            = "Regisseur";
$lang['diskid']              = "DiskID";
$lang['filedate']            = "Bestandsdatum";
$lang['filename']            = "Bestandsnaam";
$lang['filesize']            = "Bestandsgrootte";
$lang['genre']               = "Genre";
$lang['genres']              = "Genres";
$lang['keywords']            = "Trefwoorden";
$lang['language']            = "Taal";
$lang['length']              = "Lengte";
$lang['less']                = "minder";
$lang['more']                = "meer";
$lang['plot']                = "Plot";
$lang['runtime']             = "Duur";
$lang['subtitle']            = "Subtitel";
$lang['title']               = "Titel";
$lang['tvepisode']           = "TV aflevering";
$lang['seen']                = "Gezien";
$lang['videocodec']          = "Video Codec";
$lang['year']                = "Jaar";
$lang['yes']                 = "Ja";
$lang['no']                  = "Nee";
$lang['comment']             = "Commentaar";
$lang['mediatype']           = "Mediatype";
$lang['save']                = "Opslaan";
$lang['back']                = "Terug";
$lang['coverupload']         = "Cover Upload";
$lang['visit']               = "Bezoeken";
$lang['input_browse']        = "Bladeren";
$lang['date']                = "Datum";
$lang['fetchtime']           = "Ophaalduur";
$lang['cachesize']           = "Cache grootte";
$lang['cacheexpired']        = "Verlopen";
$lang['cachecleanup']        = "Opschonen";
$lang['cacheempty']          = "Cache leegmaken";
$lang['okay']                = "Ok";
$lang['asactor']             = "als";
$lang['displayed']           = "weergegeven";
$lang['add_another']         = "Nieuwe toevoegen";
$lang['rating']              = 'Waardering';
$lang['extid']               = 'Extern Id';
$lang['bytes']               = 'Bytes';

#
# Edit
#

$lang['main_details']        = 'Eigenschappen';
$lang['media_details']       = 'Mediagegevens';
$lang['description_details'] = 'Omschrijving';
$lang['file_details']        = 'Bestandsinformatie';
$lang['custom_details']      = 'Eigen velden';
$lang['cancel']        		 = 'Annuleren';
$lang['create_']             = 'Maken:';

#
# Show
#

$lang['synopsis']			 = 'Synopsis';
$lang['purchase']            = 'Kopen';
$lang['torrents']            = 'Torrents';
$lang['showcast']            = 'Bekijk acteurs';
$lang['hidecast']            = 'Verberg acteurs';

#
# Search
#

$lang['target']              = 'Doel';

#
# Multiuser
#

$lang['enterusername']       = "Geef gebruikersnaam en wachtwoord om door te gaan.";
$lang['stayloggedin']        = "Blijf aangemeld op deze computer";
$lang['login']               = "Aanmelden";
$lang['logout']              = "Afmelden";
$lang['owner']               = "Eigenaar";
$lang['loggedinas']          = "Je bent aangemeld als";

$lang['username']            = "Gebruikersnaam";
$lang['password']            = "Wachtwoord";
$lang['permissions']         = "Rechten";
$lang['perm_admin']          = "Administratie";
$lang['perm_readall']        = "Bekijk alle";
$lang['perm_writeall']       = "Wijzig video's van anderen";
$lang['perm_write']          = "Wijzig eigen video's";
$lang['perm_adult']          = "Laat 18+ films zien";
$lang['write']               = "Films aanpassen";
$lang['read']                = "Films bekijken";
$lang['update']              = "aanpassen";
$lang['create']              = "aanmaken";
$lang['action']              = "Actie";
$lang['existingusers']       = "Bestaande Gebruikers";
$lang['selecteduser']     	 = "Geselecteerde gebruiker";
$lang['permforuser']         = "Rechten voor gebruiker";
$lang['permtouser']          = "Rechten gerelateerd aan gebruiker";
$lang['createuser']          = "Maak een nieuwe gebruiker";
$lang['email']               = "E-Mail";
$lang['borrowask']           = "Vraag te leen";

$lang['msg_usercreated']     = "Gebruiker aangemaakt";
$lang['msg_usernotcreated']  = "Kon gebruiker niet aanmaken";
$lang['msg_permpassupd']     = "Rechten en Wachtwoord aangepast";
$lang['msg_permupd']         = "Rechten aangepast";
$lang['msg_userdel']         = "Gebruiker verwijderd";
$lang['msg_loggedoff']       = "Je bent nu afgemeld";
$lang['msg_invalidchar']     = "Verkeerd karakter in gebruikersnaam";
$lang['msg_loginfailed']     = "Afmelden niet gelukt";
$lang['msg_borrowasksubject']= "[VideoDB] Verzoek voor het lenen van %title%";
$lang['msg_borrowaskmail']   = "Gebruiker %user% (%usermail%) wil de film \"%title%\" (DiskID %diskid%) van jou lenen.\n\nVideo-URL: %url%\n\n\n\n-- \nVerzonder door VideoDB";
$lang['msg_borrowaskok']     = "Je verzoek is verstuurd naar de eigenaar van de film. Hij zal binnenkort contact met je opnemen..";
$lang['msg_borrowaskfail']   = "Je verzoek kan om wat voor reden dan ook niet verzonden worden. Je zult zelf contact moeten opnemen met de eigenaar van de film.";
$lang['msg_cachecleared']    = "Cache leeggemaakt.";

#
# Phrases
#

$lang['delete_movie']		 = "Je gaat deze video verwijderen. Weet je zeker dat je door wilt gaan?";
$lang['delete_user']		 = "Je gaat deze gebruiker verwijderen. Weet je zeker dat je door wilt gaan?";
$lang['deleted']             = "Video is verwijderd.";

$lang['available']           = "is beschikbaar.";
$lang['genre_desc']          = "Zoek films alleen in deze genres";
$lang['keywords_desc']       = "Trefwoorden worden gezocht in Titels, Subtitels, Regisseur, Plot en Cast. Je kunt AND, NOT en OR gebruiken.";
$lang['lentto']              = "is uitgeleend aan";
$lang['listallfiles']        = "toon alle bestanden";
$lang['look_imdb']           = "probeer ontbrekende gegevens uit de IMDB te halen";
$lang['really_del']          = "Echt verwijderen";
$lang['returned']            = "Het is teruggebracht";
$lang['notavail']            = "<span class=\"notavail\">NIET BESCHIKBAAR</span><br />uitgeleend aan";
$lang['curlent']             = "Uitgeleend:";
$lang['curlentfrom']         = "Momenteel geleent van eigenaar";
$lang['fieldselect']         = "Zoek alleen in deze velden";
$lang['selectall']           = "Selecteer alles";
$lang['selectnone']          = "Selecteer niks";

#
# Menue
#

$lang['menu']                = "Menu";
$lang['browse']              = "overzicht";
$lang['export']              = "exporteren";
$lang['manage']              = "beheren";
$lang['options']             = "instellingen";
$lang['borrow']              = "lenen";
$lang['delete']              = "verwijder";
$lang['edit']                = "aanpassen";
$lang['perm']                = "rechten";
$lang['copy']                = "kopieëren";
$lang['n_e_w']               = "nieuw";
$lang['random']              = "willekeurig";
$lang['search']              = "zoek";
$lang['statistics']          = "statistieken";
$lang['view']                = "toon";
$lang['setup']               = "configuratie";
$lang['tools']               = "Tools";
$lang['contrib']             = "toevoegingen (Contrib)";
$lang['imdbbrowser']         = "IMDB Online";
$lang['help']                = "help";
$lang['profile']             = "profiel";
$lang['users']             	 = "gebruikers";
$lang['filter']              = "filter";

#
# Media player (boxee) controls
#

$lang['play']               = "Afspelen";

#
# Radio-Buttons
#

$lang['radio_all']           = "alles";
$lang['radio_new']           = "nieuw";
$lang['radio_showtv']        = "toon TV aflevering";
$lang['radio_unseen']        = "niet gezien";
$lang['radio_wanted']        = "verlanglijst";

$lang['radio_look_ignore']   = "overslaan";
$lang['radio_look_lookup']   = "toevoegen";
$lang['radio_look_overwrite']= "overschrijven";
$lang['radio_look_caption']  = "Data Gegevens";

$lang['quicksearch']         = "Snel zoeken";
$lang['working']             = "Bezig...";

$lang['filter_any']          = '<alle>';
$lang['filter_available']    = '<beschikbaar>';

#
# Trailers
#

$lang['trailer_search']  	 = "Trailer zoeken...";
$lang['trailer_show']  	     = "Bekijk trailers";

#
# Statistics
#

$lang['averagefilesize']     = "gemiddelde bestandsgrootte";
$lang['averageruntime']      = "gemiddelde duur";
$lang['languages']           = "Talen";
$lang['multiple']            = "<small>(meerdere genres<br />per video mogelijk)</small>";
$lang['numberdisks']         = "Aantal disks";
$lang['totalfiles']          = "Totaal bestanden";
$lang['totalruntime']        = "Totaal duur";
$lang['totalseen']           = "Totaal gezien";
$lang['totalsize']           = "Totaal grootte";
$lang['tv_episodes']         = "TV afleveringen";
$lang['videobygen']          = "Videos per Genre";
$lang['videobyvcodec']       = "Video codecs";
$lang['videobyacodec']       = "Audio codecs";
$lang['videobymedia']        = "Mediatypes";
$lang['statistics_for']      = "Statistieken voor";

#
# Lookup Popup
#

$lang['l_search']            = "Zoek";
$lang['l_select']            = "Selecteer de gezochte titel:";
$lang['l_nothing']           = "Niets gevonden.";
$lang['l_selfsearch']        = "Zelf zoeken";
$lang['aka']                 = "Aka";

#
# Config Help
#

$lang['opt_general']          = 'Algemeen';
$lang['opt_custom']           = 'Gebruikersvelden';
$lang['opt_engines']          = 'Gegevensaanbieders';
$lang['opt_security']         = 'Netwerk en Beveiliging';
$lang['opt_caching']          = 'Caching';
$lang['opt_apikeys']       	  = 'API sleutels';

$lang['enable']              = "aanzetten";

$lang['help_langn']          = "Taal";
$lang['help_lang']           = "Selecteer hier jouw landcode. Als jouw taal niet beschikbaar is, vertaal dan het engelse taalbestand in de <span class=\"example\">language</span> folder en stuur dit naar mij.";
$lang['help_autoidn']        = "Automatisch DiskID";
$lang['help_autoid']         = "VideoDB gebruikt een DiskID om media te identificeren. Dit ID is verschillend van het VideoID welke automatisch aan een nieuwe video wordt toegekend. Hierdoor is het mogelijk om meerdere video's op een enkel media te hebben en deze zijn dan allemaal niet meer beschikbaar als het media aan iemand wordt uitgeleend.<br />Als je geen eigen namen aan elk media geeft maar slechts een nummer, dan kan VideoDB een ID voorstellen voor elke toevoeging aan de database. Als je dit wilt, dan moet je deze optie aanzetten.";
$lang['help_templaten']      = "Uiterlijk";
$lang['help_template']       = "Dit bepaald het uiterlijk van de website. Elke keuze kan meerdere kleurenpaletten definieren. Probeer ze uit en kijk welke je het leukst vind :-)";
$lang['help_mediadefaultn']  = "Standaard mediatype";
$lang['help_mediadefault']   = "Selecteer welk mediatype voorgeselecteerd moet worden bij het toevoegen van een nieuwe video.";
$lang['help_langdefaultn']   = "Standaard taalkeuze";
$lang['help_langdefault']    = "Deze instelling is anders dan de taalkeuze hierboven. Dit is de taalkeuze welke voorgeselecteerd wordt bij het toevoegen van een nieuwe video. Laat het veld leeg als je dit niet wilt.";
$lang['help_languageflagsn'] = "Taalvlaggen";
$lang['help_languageflags']  = "Hier kun je de vlaggen selecteren welke als JavaScript knop in het invoer/wijzigen formulier getoond worden. Hiermee kan snel een taalkeuze gemaakt worden.<br />Selecteer niet meer dan vijf vlaggen.";
$lang['help_filterdefaultn'] = "Standaard filter";
$lang['help_filterdefault']  = "Dit is de pagina die wordt geopend wanneer je VideoDB opstart.";
$lang['help_acclangbrowsern']= "Gebruik je browser's Accept-Language";
$lang['help_acclangbrowser'] = "Als je dit aanzet zal videoDB de taal-instellingen van jou browser gebruiken wanneer het de inhoud van achterliggende diensten opvraagt. Als het uitstaat zal het terugvallen naar en-US. Je kan dit gebruiken om bijvoorbeeld bij IMDB de Nederlandse titel terug te krijgen i.p.v. de originele Engelse.";
$lang['help_showtvn']        = "Toon TV afleveringen";
$lang['help_showtv']         = "Als je TV afleveringen wilt zien in het video overzicht dan moet je deze optie aanzetten.";
$lang['help_orderallbydiskn']= "Sorteer volgens DiskID";
$lang['help_orderallbydisk'] = "Als je deze optie aanzet dan wordt de lijst van video's in het \"alles\" overzicht gesorteerd volgens het DiskID in plaats van de titels van de video's.";
$lang['help_removearticlesn']= "Lidwoord verwijderen";
$lang['help_removearticles'] = "Indien geselecteerd, wordt het lidwoord verwijderd van toegevoegde films, v.b. The Angel wordt opgeslagen als Angel, The.";
$lang['help_customn']        = "Gebruikersvelden";
$lang['help_custom']         = "Je kunt tot vier gebruikersvelden definieren voor elke video. Elk veld kan tot 255 karakters bevatten. Je kunt hier elk veld een naam geven en selecteren hoe het veld afgehandeld moet worden. Als het veld leeg is, wordt het genegeerd.";
$lang['help_localnetn']      = "Lokaal netwerk";
$lang['help_localnet']       = "Hier kun je je lokale netwerk definieren. Gebruikers die de VideoDB vanaf een ander netwerk benaderen, kunnen de data niet meer wijzigen. Dit is een <a href=\"http://www.php.net/manual/en/pcre.pattern.syntax.php\" target=\"_blank\">reguliere expressie</a> dus je kunt vrij gecompliceerde netwerkdefinities opgeven.<p>Voorbeelden:<br /><span class=\"example\">^192\.168\.1\.</span> staat 192.168.1.1 - 192.168.1.254 toe<br /><span class=\"example\">^(192\.168\.1\.|127\.0\.0\.1$)</span> hetzelfde als hierboven plus localhost<br /><span class=\"example\">^192\.168\.1\.22$</span> staat alleen IP 192.168.1.22 toe<br /><span class=\"example\">^(192\.168\.1\.22|127\.0\.0\.1)$</span> hetzelfde als hierboven plus localhost<br /> Als je het veld leeg laat, heeft iedereen toegang tot de database.<br /></p> Attentie: Als je hier een fout maakt kun je jezelf buitensluiten van dit configuratiescherm!";
$lang['help_imdbOverwriten'] = "IMDB overschrijven";
$lang['help_imdbOverwrite']  = "Standaard zullen gegevens opgevraagd uit de IMDB geen bestaande gegevens overschrijven. Als je deze optie aanzet, zal dit wil gebeuren.";
$lang['help_IMDBagen']       = "IMDB cache opslagduur";
$lang['help_IMDBage']        = "VideoDB heeft de mogelijkheid om gegevens opgevraagd uit de IMDB te bewaren om zo de netwerkbelasting te verlagen. Hier kun je de duur (in seconden) opgeven hoelang deze gegevens bewaard worden. Standaard is dit vijf dagen.";
$lang['help_thumbnailn']     = "Miniatuur cover plaatjes";
$lang['help_thumbnail']      = "Wanneer deze optie aangezet wordt, zijn verkleinde versies van de covers zichtbaar in het video overzicht. Als dit te langzaam is of te veel afleidt, zet het dan uit.";
$lang['help_castcolumnsn']   = "Cast kolommen";
$lang['help_castcolumns']    = "Deze optie definieerd het aantal kolommen dat gebruikt wordt om de cast weer te geven. Het aantal kolommen is afhankleijk van de schermresolutie.<br />2 kolommen gaat prima bij een resolutie van 1152x864 - voor een lagere resolutie kun je beter 1 kolom gebruiken, bij een hogere resolutie kan een groter aantal gebruikt worden.";
$lang['help_listcolumnsn']   = "Lijst kolommen";
$lang['help_listcolumns']    = "Deze optie wordt momenteel alleen gebruikt door de 'modern' template. Deze optie geeft aan hoeveel kolommen gebruikt worden voor het tonen van het video overzicht. Wanneer 1 als waarde gebruikt wordt, dan wordt het video overzicht getoond zoals in de standaard template.";
$lang['help_proxy_hostn']    = "Proxy server";
$lang['help_proxy_host']     = "Wanneer VideoDB op een machine achter een proxy server draait, kun je hier de naam van de proxy server invullen. Laat het veld leeg als je geen proxy nodig hebt.";
$lang['help_proxy_portn']    = "Proxy poort";
$lang['help_proxy_port']     = "Geef hier het poortnummer van de proxy, bijvoorbeeld <span class=\"example\">8080</span>.";
$lang['help_actorpicsn']     = "Acteur/Actrice plaatjes";
$lang['help_actorpics']      = "Wanneer deze optie gebruikt wordt, dan wordt naast elke acteur en actrice een kleine afbeelding getoond (wanneer deze gevonden kan worden).";
$lang['help_thumbAgen']      = "Acteur/Actrice plaatjes opnieuw ophalen";
$lang['help_thumbAge']       = "Deze optie geeft aan na hoeveel tijd (in seconden) opnieuw geprobeerd moet worden om een plaatje van een acteur/actrice te vinden als dit nog niet gelukt is. This defines when to retry to load actor thumbnails that weren't found last time (in seconds). Standaard is dit 3 weken.";
$lang['help_youtubekeyn']    = "YouTube API sleutel";
$lang['help_youtubekey']     = "Vraag een YouTube developer API sleutel op bij <a href='http://www.youtube.com/my_profile_dev'>http://www.youtube.com/my_profile_dev</a> om YouTube ondersteuning te krijgen.";
$lang['help_shownewn']       = "Aantal nieuwe films";
$lang['help_shownew']        = "Hiermee kun je aangeven hoeveel films getoond worden voor de \"nieuw\" selectie.";
$lang['help_imdbBrowsern']   = "IMDB integratie";
$lang['help_imdbBrowser']    = "Hiermee kun je via VideoDB films bekijken op de IMDB website en deze direct aan VideoDB toevoegen.";

$lang['help_multiuser']      = "Deze optie maakt het mogelijk om VideoDB te gebruiken met meerdere personen via eigen gebruikersnamen en wachtwoorden.";
$lang['help_multiusern']     = "Support voor meerdere Gebruikers";
$lang['help_usermanager']    = "Volg deze link om gebruikers en hun rechten toe te voegen, te wijzigen of te verwijderen. Dit is alleen van toepassing wanneer meerdere gebruikers zijn toegestaan.";
$lang['help_usermanagern']   = "Gebruikers Administratie";
$lang['help_permmanager']    = "Volg deze link om gebruikersrechten te beheren.";
$lang['help_permmanagern']   = "Rechten Administratie";
$lang['help_denyguest']      = "Wanneer deze optie aan staat, kunnen alleen gebruikers met de juiste rechten de gegevens benaderen. Dit is alleen van toepassing wanneer meerdere gebruikers zijn toegestaan.";
$lang['help_denyguestn']     = "Geen publieke toegang";
$lang['help_adultgenres']    = "Films in de geselecteerde genres zijn alleen zichtbaar voor gebruikers met 'adult' rechten. Houd de CTRL-toets ingedrukt om meerdere genres te selecteren. (Alleen van toepassing wanneer meerdere gebruikers ondersteund worden)";
$lang['help_adultgenresn']   = "18+ Genres";
$lang['help_pageno']         = "Aantal elementen dat wordt weergegeven per pagina. Gebruik 0 or laat het veld leeg om pagina's te onderdrukken.";
$lang['help_pagenon']        = "Maximaal aantal elementen per pagina";

$lang['help_engine']         = "Deze optie staat videoDB toe data te verkrijgen van %s.";
$lang['help_defaultenginen'] = "Standaard Data Provider";
$lang['help_defaultengine']  = "Selecteer van waar videoDB zijn data standaard zou moeten afhalen.";
$lang['help_enginegoogle']   = "Google ondersteunt enkel het zoeken van afbeeldingen.";
$lang['help_engexperimental']= "<br/>Let op: deze data provider is nog steeds in experimentele fase en zou niet naar verwachting kunnen werken.";

$lang['help_showtoolsn']     = "Bekijk Tools";
$lang['help_showtools']      = "Het tools menu laat de inhoud van de <code>contrib</code> map zien. Deze map bevat wat handige toevoegingen aan videoDB maar zijn niet getest en kunnen gevaarlijk zijn voor wat betreft de veiligheid.";
$lang['help_showcasttogglen'] = "Toon de Acteurs knop";
$lang['help_showcasttoggle'] = "Als je deze aanzet verschijnt er een &lsquo;Bekijk acteurs&rsquo; of &lsquo;Verberg acteurs&rsquo; knop op de details pagina. Het zal die pagina wat sneller maken als je niet altijd geïnteresseerd bent in de volledige acteurs-lijst.";

$lang['page']                = "Pagina";
$lang['of']                  = "van";
$lang['records']             = "elementen";

$lang['warn_noOwner']        = "Data not saved - you have to select an owner first!";

$lang['order']				 = "Sorteren op";

?>