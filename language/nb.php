<?php
/**
 * Norwegian (bokmal) language file
 *
 * @package Language
 * @author  Axel <videodb@glemsk.net>
 * @version $Id: nb.php,v 1.7 2013/03/16 10:10:07 andig2 Exp $
 */

$lang['encoding']            = "utf-8";

$lang['audiocodec']          = "Lydkodek";
$lang['borrowto']            = "Lån ut til";
$lang['cast']                = "Skuespillere";
$lang['country']             = "Land";
$lang['coverurl']            = "Cover URL";
$lang['dimension']           = "Videostørrelse";
$lang['director']            = "Regissør";
$lang['diskid']              = "DiskID";
$lang['filedate']            = "Fildato";
$lang['filename']            = "Filnavn";
$lang['filesize']            = "Filstørrelse";
$lang['genre']               = "Sjanger";
$lang['genres']              = "Sjangere";
$lang['keywords']            = "Nøkkelord";
$lang['language']            = "Språk";
$lang['length']              = "Lengde";
$lang['less']                = "mindre";
$lang['more']                = "mer";
$lang['plot']                = "Handling";
$lang['runtime']             = "Spilletid";
$lang['subtitle']            = "Undertekst";
$lang['title']               = "Tittel";
$lang['tvepisode']           = "TV-episode";
$lang['seen']                = "Sett";
$lang['videocodec']          = "Videokodek";
$lang['year']                = "År";
$lang['yes']                 = "Ja";
$lang['comment']             = "Kommentar";
$lang['mediatype']           = "Mediatype";
$lang['save']                = "Lagre";
$lang['coverupload']         = "Last opp cover";
$lang['visit']               = "Besøk";
$lang['date']                = "Dato";
$lang['fetchtime']           = "Lastet ned på";
$lang['cachesize']           = "Størrelse på lokallager";
$lang['cacheexpired']        = "Utgått";
$lang['cachecleanup']        = "Rydd opp i lokallager";
$lang['cacheempty']          = "Tøm lokallager";
$lang['okay']                = "Ok";
$lang['asactor']              = "som";
$lang['displayed']            = "vist";
$lang['add_another']          = "Legg til ";

#
# Multiuser
#

$lang['enterusername']       = "Vennligst skriv inn ditt brukernavn og passord for å fortsette.";
$lang['stayloggedin']        = "Forbli pålogget på denne maskinen";
$lang['login']               = "logg inn";
$lang['logout']              = "logg ut";
$lang['owner']               = "Eier";
$lang['loggedinas']          = "Du er logget inn som";

$lang['username']            = "Brukernavn";
$lang['password']            = "Passord";
$lang['permissions']         = "Tilgangsnivåer";
$lang['perm_admin']          = "Administrasjon";
$lang['perm_writeall']       = "Endre andres filmer";
$lang['perm_write']          = "Endre egne filmer";
$lang['perm_adult']          = "Se filmer med voksent innhold";
$lang['update']              = "oppdater";
$lang['create']              = "opprett";
$lang['existingusers']       = "Eksisterende brukere";
$lang['createuser']          = "Opprett ny bruker";
$lang['email']               = "E-post";
$lang['borrowask']           = "Spør om å få låne";

$lang['msg_usercreated']     = "Brukeren er opprettet";
$lang['msg_permpassupd']     = "Brukerens tilgangsnivå og passord er oppdatert";
$lang['msg_permupd']         = "Brukerens tilgangsnivå er oppdatert";
$lang['msg_userdel']         = "Bruker slettet";
$lang['msg_loggedoff']       = "Du er logget av";
$lang['msg_invalidchar']     = "Ugyldige tegn i brukernavnet";
$lang['msg_loginfailed']     = "Innlogging mislyktes";
$lang['msg_borrowasksubject']= "[VideoDB] Forespørsel om å låne filmen %title%";
$lang['msg_borrowaskmail']   = "Brukeren %user% (%usermail%) ønsker å låne filmen \"%title%\" (DiskID %diskid%) fra deg.\n\nVideo-URL: %url%\n\n\n\n-- \nSendt av VideoDB";
$lang['msg_borrowaskok']     = "Din forespørsel ble sendt til eieren av filmen. Du bør høre fra ham/henne snart.";
$lang['msg_borrowaskfail']   = "Sendingen av forespørselen mislyktes. Du bør kontakte eieren av filmen på egenhånd.";

#
# Phrases
#

$lang['available']           = "er tilgjengelig.";
$lang['deleted']             = "Videoen ble slettet.";
$lang['genre_desc']          = "Begrens sjangere";
$lang['keywords_desc']       = "Alle merkede felter blir søkt gjennom. Du kan bruke AND, NOT og OR i din spørring.";
$lang['lentto']              = "er lånt ut til";
$lang['listallfiles']        = "list opp alle filer";
$lang['look_imdb']           = "forsøk å hente opp manglende data på IMDB";
$lang['really_del']          = "Vil du virkelig slette";
$lang['returned']            = "Returnert";
$lang['notavail']            = "<span class=\"notavail\">IKKE TILGJENGELIG</span><br />lånt ut til";
$lang['curlent']             = "Gjeldende utlån:";
$lang['fieldselect']         = "Begrens felter";
$lang['selectall']           = "Merk alle";

#
# Menue
#

$lang['browse']              = "utforsk";
$lang['borrow']              = "utlån";
$lang['delete']              = "slett";
$lang['edit']                = "endre";
$lang['copy']                = "kopier";
$lang['n_e_w']               = "ny";
$lang['random']              = "tilfeldig";
$lang['search']              = "søk";
$lang['statistics']          = "statistikk";
$lang['view']                = "vis";
$lang['setup']               = "konfigurasjon";
$lang['imdbbrowser']         = "IMDB på nett";
$lang['help']                = "hjelp";
$lang['profile']             = "profil";

#
# Radio-Buttons
#

$lang['radio_all']           = "alle";
$lang['radio_new']           = "nye";
$lang['radio_showtv']        = "vis TV-episoder";
$lang['radio_unseen']        = "usett";
$lang['radio_wanted']        = "ønskeliste";

$lang['radio_look_ignore']   = "nei";
$lang['radio_look_lookup']   = "legg til manglende";
$lang['radio_look_overwrite']= "overskriv";
$lang['radio_look_caption']  = "Hent opp data";

#
# Statistics
#

$lang['averagefilesize']     = "gjennomsnittlig filstørrelse";
$lang['averageruntime']      = "gjennomsnittlig spilletid";
$lang['languages']           = "Språk";
$lang['multiple']            = "<small>(flere sjangere per<br />video er mulig)</small>";
$lang['numberdisks']         = "disker";
$lang['totalfiles']          = "totalt antall filer";
$lang['totalruntime']        = "total spilletid";
$lang['totalseen']           = "totalt sett";
$lang['totalsize']           = "total størrelse";
$lang['tv_episodes']         = "TV-episoder";
$lang['videobygen']          = "Videoer per sjanger";
$lang['videobyvcodec']       = "Videokodeker";
$lang['videobymedia']        = "Mediatyper";

#
# Lookup Popup
#

$lang['l_search']            = "Søk";
$lang['l_select']            = "Velg riktig tittel:";
$lang['l_nothing']           = "Ingen treff.";
$lang['l_selfsearch']        = "Søk selv";

#
# Config Help
#

$lang['opt_general']          = 'Generelt';
$lang['opt_custom']           = 'Egendefinerte felter';
$lang['opt_engines']          = 'Datakilder';
$lang['opt_security']         = 'Nettverk og sikkerhet';
$lang['opt_caching']          = 'Lokallager';

$lang['enable']              = "aktiv";

$lang['help_langn']          = "Språk";
$lang['help_lang']           = "Velg ønsket språkkode her. Hvis ditt språk ikke er tilgjengelig, oversett den engelske filen i <span class=\"example\">language</span>-mappen og send den til VideoDBs forfatter.";
$lang['help_autoidn']        = "Automatisk DiskID";
$lang['help_autoid']         = "VideoDB bruker en DiskID til å identifisere disker. Denne ID-en skiller seg VideoID som automatisk blir lagt til alle filmer i databasen. DiskID-en gjør det mulig å ha flere videoer på én enkel disk, og la alle videoene bli utilgjengelige når du låner bort disken.<br />Dog, hvis du ikke har et eget nummereringsopplegg for dine disker, og bare vil nummerere dem automatisk, kan du du skru på dette valget, og VideoDB vil foreslå neste ID når du legger til en video.";
$lang['help_templaten']      = "Utseende";
$lang['help_template']       = "Dette valget bestemmer utseendet til VideoDB. Hvert utseende kan ha forskjellige stiler (fargeskjemaer). Bare prøv dem og finn den du liker best :-)";
$lang['help_mediadefaultn']  = "Standard mediatype";
$lang['help_mediadefault']   = "Velg hvilken mediatype som skal foreslås når du legger til en ny film.";
$lang['help_langdefaultn']   = "Standard språk";
$lang['help_langdefault']    = "Dette valget er ikke det samme som språkvalget øverst. Dette er språket som er utfylt når du legger til en ny film. La den stå blank dersom du ikke ønsker et standardvalg.";
$lang['help_languageflagsn'] = "Språkflagg";
$lang['help_languageflags']  = "Her kan du velge flaggene som skal være tilgjengelige som JavaScript-knapper for raskt språkvalg når du legger til eller endrer filmer.<br />Maks antall flagg er fem.";
$lang['help_filterdefaultn'] = "Standardfilter";
$lang['help_filterdefault']  = "Siden som vises når du starter VideoDB.";
$lang['help_showtvn']        = "Vis TV-episoder";
$lang['help_showtv']         = "Skru på denne hvis du ønsker at standardvalget skal være at TV-episoder vises når du er inne på utforsk-siden.";
$lang['help_orderallbydiskn']= "Sorter på DiskID";
$lang['help_orderallbydisk'] = "Hvis denne er skrudd på, vil filmlisten når du har valgt \"alle\" være sortert på DiskID istedenfor filmtittel.";
$lang['help_removearticlesn']= "Fjern artikler";
$lang['help_removearticles'] = "Hvis skrudd på, vil artikler bli fjernet fra filmtitlene som legges til. For eksempel vil \"The Angel\" bli lagret som \"Angel, The\".";
$lang['help_customn']        = "Egendefinerte felter";
$lang['help_custom']         = "Du kan definere opptil fire egendefinerte felter for dine filmer. Hvert felt kan lagre opptil 255 tegn, og du kan gi dem navn og velge hvilken plugin som skal brukes for å vise feltet. La navnet stå blanks for å ikke bruke feltet.";
$lang['help_localnetn']      = "Lokalt nettverk";
$lang['help_localnet']       = "Her kan du definere ditt lokale nettverk. Brukere som går inn på VideoDB utenifra nettverket vil ikke kunne gjøre endringer i databasen. Adressen må skrives inn som en såkalt <a href=\"http://www.php.net/manual/en/pcre.pattern.syntax.php\" target=\"_blank\">regular expression</a>, noe som gir mulighet for en mer avansert måte å definere den.<p>Eksempler:<br /><span class=\"example\">^192\.168\.1\.</span> tillater tilgang fra 192.168.1.1 - 192.168.1.254<br /><span class=\"example\">^(192\.168\.1\.|127\.0\.0\.1$)</span> samme som ovenfor, samt den lokale maskinen<br /><span class=\"example\">^192\.168\.1\.22$</span> kun IP 192.168.1.22 har tilgang<br /><span class=\"example\">^(192\.168\.1\.22|127\.0\.0\.1)$</span> samme som ovenfor, samt den lokale maskinen<br /> lar du feltet stå blankt, vil alle ha tilgang<br /></p> Viktig: Hvis du gjør en feil her, kan du risikere å låse deg selv ute fra konfigurasjonssiden!";
$lang['help_imdbOverwriten'] = "IMDB overskriver";
$lang['help_imdbOverwrite']  = "Standardvalget er at data som hentes inn fra IMDB aldri vil overskrive eksisterende data. Hvis du skrur på dette valget, vil data fra IMDB overskrive eksisterende data.";
$lang['help_IMDBagen']       = "Alder på IMDB-lokallager";
$lang['help_IMDBage']        = "VideoDB kan arkivere IMDB-spørringer for å unngå tidkrevende spørringer og redusere lasten på IMDB sine servere. Bruk denne innstillingen til å definere den maksimale alderen (i sekunder) på lokallagrede dokumenter. Standard er fem dager.";
$lang['help_thumbnailn']     = "Små bilder";
$lang['help_thumbnail']      = "Når dette valget er påskrudd, vil en liten versjon av coverbildet vises på utforsk-siden. Hvis det går for tregt, eller er irriterende for øynene, skru det av.";
$lang['help_castcolumnsn']   = "Kolonner: skuespillere";
$lang['help_castcolumns']    = "Dette valget bestemmer hvor mange kolonner som brukes til å vise skuespillerlisten når du ser på detaljene til én enkelt film. Skjermoppløseningen og utseende du har valgt, avgjør hvor mye plass du har til rådighet.";
$lang['help_listcolumnsn']   = "Kolonner: filmliste";
$lang['help_listcolumns']    = "Dette valget brukes foreløbig kun i \"modern\"-utseendet. Det bestemmer hvor mange kolonner som skal brukes på utforsk-siden. Valget \"1\" tilsvarer standardutseendet.";
$lang['help_proxy_hostn']    = "Proxyserver";
$lang['help_proxy_host']     = "Hvis serveren som kjører VideoDB er bak en proxyserver, kan du skrive inn serveradressen her. La valget stå blankt dersom dette ikke er nødvendig.";
$lang['help_proxy_portn']    = "Proxy port";
$lang['help_proxy_port']     = "Skriv inn porten til proxyserveren her. For eksempel <span class=\"example\">8080</span>.";
$lang['help_actorpicsn']     = "Skuespillerbilder";
$lang['help_actorpics']      = "Finn og vis et lite bilde ved siden av hver skuespiller.";
$lang['help_thumbAgen']      = "Nytt søk etter skuespillerbilder";
$lang['help_thumbAge']       = "Dette valget bestemmer hvor ofte (i sekunder) VideoDB skal lete på nytt etter bilder av skuespillere som ikke ble funnet forrige gang. Standard er tre uker.";
$lang['help_shownewn']       = "Nye filmer";
$lang['help_shownew']        = "Antall filmer som vises under valget \"nye\" på utforsk-siden.";
$lang['help_imdbBrowsern']   = "IMDB på nett";
$lang['help_imdbBrowser']    = "Dette gjør det mulig å besøke IMDB gjennom VideoDB, og legge til filmer direkte.";

$lang['help_multiuser']      = "Dette valget skrur på flerbrukermodus, som gjør det mulig å benytte VideoDB med ulike brukere som har eget brukernavn og passord.";
$lang['help_multiusern']     = "Flerbrukermodus";
$lang['help_usermanager']    = "Følg denne linken for å legge til, endre og slette brukere og deres tilgangsnivåer.";
$lang['help_usermanagern']   = "Brukeradministrasjon";
$lang['help_denyguest']      = "Når dette valget er skrudd på, vil kun verifiserte brukere ha tilgang til å benytte VideoDB. (Fungerer kun i flerbrukermodus.)";
$lang['help_denyguestn']     = "Ingen offentlig tilgang";
$lang['help_adultgenres']    = "Filmer i sjangerene du velger her, vil kun være synlig for brukere med tilgang til å se filmer med voksent innhold. Bruk ctrl-knappen til å velge flere sjangere. (Fungerer kun i flerbrukermodus.)";
$lang['help_adultgenresn']   = "Voksensjangere";
$lang['help_pageno']   	   = "Antall filmer som vises per side. Skriv inn 0 eller la feltet stå blankt for la alle filmene være på samme side.";
$lang['help_pagenon']        = "Maksimalt antall filmer per side";

$lang['help_engine']         = "Dersom du skrur på denne, vil VideoDB kunne hente data fra %s.";
$lang['help_defaultenginen'] = "Standard datakilde";
$lang['help_defaultengine']  = "Velg standardkilde for henting av filmdata.";
$lang['help_enginegoogle']   = "Google støtter kun bildesøk.";
$lang['help_engexperimental']= "<br/>Merk at støtte for denne datakilden er på prøvestadiet, og derfor kanskje ikke vil fungere skikkelig.";

$lang['page']                = "Side";
$lang['of']                  = "av";
$lang['records']             = "oppføringer";

?>
