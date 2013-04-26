<?php
/**
 * Bulgarian language file
 *
 * @package Language
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @author  drJeckyll
 * @version $Id: bg.php,v 2.16 2013/03/16 10:10:07 andig2 Exp $
 */

$lang['encoding']            = "windows-1251";

$lang['audiocodec']          = "Аудио кодек";
$lang['borrowto']            = "даден на";
$lang['cast']                = "Участват";
$lang['country']             = "Страна";
$lang['coverurl']            = "URL на обложката";
$lang['dimension']           = "Размери";
$lang['director']            = "Режисьор";
$lang['diskid']              = "Диск с ID";
$lang['filedate']            = "Дата на файла";
$lang['filename']            = "Име на файла";
$lang['filesize']            = "Големина на файла";
$lang['genre']               = "Жанр";
$lang['genres']              = "Жанрове";
$lang['keywords']            = "Ключови думи";
$lang['language']            = "Език";
$lang['length']              = "Продължителност";
$lang['less']                = "по-малко";
$lang['more']                = "още";
$lang['plot']                = "За филма накратко";
$lang['runtime']             = "Продължителност";
$lang['subtitle']            = "субтитри";
$lang['title']               = "Заглавие";
$lang['tvepisode']           = "TV Епизод";
$lang['seen']                = "Гледан";
$lang['videocodec']          = "Видео кодек";
$lang['year']                = "Година";
$lang['yes']                 = "Да";
$lang['comment']             = "Коментар";
$lang['mediatype']           = "Носител";
$lang['save']                = "Запис";
$lang['coverupload']         = "Upload на обложка";
$lang['visit']               = "посетен";
$lang['date']                = "Дата";
$lang['fetchtime']           = "Изпълнено за";
$lang['cachesize']           = "Големина на кеша";
$lang['cacheexpired']        = "Остарял";
$lang['cachecleanup']        = "Почистване на кеша";
$lang['cacheempty']          = "Изпразни кеша";
$lang['okay']                = "Ок";
$lang['asactor']             = "като";
$lang['displayed']           = "показани";
$lang['add_another']         = "Добави друг";
$lang['action']              = 'Действие';

#
# Multiuser
#

$lang['enterusername']       = "Моля, въведете потребителкото име и паролата си за да продължите.";
$lang['stayloggedin']        = "Остани включен";
$lang['login']               = "Име";
$lang['logout']              = "Изход";
$lang['owner']               = "Собственик";
$lang['loggedinas']          = "Вие сте включен като";

$lang['username']            = "Име";
$lang['password']            = "Парола";
$lang['permissions']         = "Права";
$lang['perm_admin']          = "Администрация";
$lang['perm_writeall']       = "Промени на чужди филми";
$lang['perm_write']          = "Промени на собствените филми";
$lang['perm_adult']          = "Показване на филми за възрастни";
$lang['update']              = "промени";
$lang['create']              = "направи";
$lang['existingusers']       = "Съществуващи потребители";
$lang['createuser']          = "Нов потребител";
$lang['email']               = "E-Mail";
$lang['borrowask']           = "Заявка за заем";

$lang['msg_usercreated']     = "Потребителят е създаден";
$lang['msg_permpassupd']     = "Правата и паролите са променени";
$lang['msg_permupd']         = "Правата са променени";
$lang['msg_userdel']         = "Потребителят е изтрит";
$lang['msg_loggedoff']       = "Бяхте изключен";
$lang['msg_invalidchar']     = "Невалиден символ в името";
$lang['msg_loginfailed']     = "Невалидно име или парола";
$lang['msg_borrowasksubject']= "[VideoDB] Заявка за заем на %title%";
$lang['msg_borrowaskmail']   = "Потребителят %user% (%usermail%) иска да заеме филма \"%title%\" (DiskID %diskid%).\n\nVideo-URL: %url%\n\n\n\n-- \nИзпратено от VideoDB";
$lang['msg_borrowaskok']     = "Заявката ви беше изпратена на собственика. Ще получите отговор скоро.";
$lang['msg_borrowaskfail']   = "Заявката не може да бъде изпратена. Трябва да се свържете със собственика ръчно.";

#
# Phrases
#

$lang['available']           = "е наличен.";
$lang['deleted']             = "Филмът беше изтрит.";
$lang['genre_desc']          = "Търси само филми от тези жанрове (ИЛИ критерии)";
$lang['keywords_desc']       = "Ключовите думи се търсят в Заглавията, Субтитрите, Режисьор, Описание и Артисти. Може да използвате AND, NOT и OR в заявката.";
$lang['lentto']              = "даден на";
$lang['listallfiles']        = "списък на всички файлове";
$lang['look_imdb']           = "потърси липсващите данни в IMDB";
$lang['really_del']          = "Наистина да се изтрие";
$lang['returned']            = "Беше върнат";
$lang['notavail']            = "<span class=\"notavail\">НЕ Е НАЛИЧЕН</span><br />даден на";
$lang['curlent']             = "Понастоящем в:";
$lang['fieldselect']         = "Търси само в тези полета";
$lang['selectall']           = "Избери всички";

#
# Menue
#

$lang['browse']              = "Списък";
$lang['borrow']              = "На заем";
$lang['delete']              = "Изтриване";
$lang['edit']                = "Редактиране";
$lang['copy']                = "Копирай";
$lang['n_e_w']               = "Нов";
$lang['random']              = "Случаен";
$lang['search']              = "Търсене";
$lang['statistics']          = "Статистики";
$lang['view']                = "Преглед";
$lang['setup']               = "Настройки";
$lang['contrib']             = "Инструменти";
$lang['imdbbrowser']         = "IMDB Онлайн";
$lang['help']                = "Помощ";
$lang['searchimdb']          = "Търсене в IMDB";
$lang['profile']             = "Профил";

#
# Radio-Buttons
#

$lang['radio_all']           = "всички";
$lang['radio_new']           = "нови";
$lang['radio_showtv']        = "TV Епизодите";
$lang['radio_unseen']        = "негледани";
$lang['radio_wanted']        = "желан";

$lang['radio_look_ignore']   = "Игнорирай";
$lang['radio_look_lookup']   = "Добави липсващите";
$lang['radio_look_overwrite']= "Презапиши";
$lang['radio_look_caption']  = "Търсене на данни";

#
# Statistics
#

$lang['averagefilesize']     = "средна големина на файловете";
$lang['averageruntime']      = "средна продължителност";
$lang['languages']           = "Езици";
$lang['multiple']            = "<small>(възможен е повече от<br />един жанр за филм)</small>";
$lang['numberdisks']         = "брой на дисковете";
$lang['totalfiles']          = "общо файлове";
$lang['totalruntime']        = "обща продължителност";
$lang['totalseen']           = "обща гледана продължителност";
$lang['totalsize']           = "обща големина";
$lang['tv_episodes']         = "TV Епизоди";
$lang['videobygen']          = "Филми по жанрове";
$lang['videobyvcodec']       = "Видео кодеци";
$lang['videobymedia']        = "Носители";

#
# Lookup Popup
#

$lang['l_search']            = "Търсене";
$lang['l_select']            = "Изберете отговарящото заглавие:";
$lang['l_nothing']           = "Няма намерен резултат.";
$lang['l_selfsearch']        = "Потърсете сами";

#
# Config Help
#

$lang['opt_general']          = 'Общи';
$lang['opt_custom']           = 'Собствени полета';
$lang['opt_engines']          = 'Доставчици на информация';
$lang['opt_security']         = 'Мрежа и сигурност';
$lang['opt_caching']          = 'Кеш';

$lang['enable']              = "включено";

$lang['help_langn']          = "Език";
$lang['help_lang']           = "Изберете кода на езика си. Ако вашият език не е включен преведете английския файл, който се намира в <span class=\"example\">language</span> директорията и го изпратете на автора.";
$lang['help_autoidn']        = "Автоматичен ID на диск";
$lang['help_autoid']         = "VideoDB използва ID за да идентифицира дисковете. Това ID е различно и ви позволява да присвоявате собствена номерация на дисковете си.";
$lang['help_templaten']      = "Шаблони";
$lang['help_template']       = "Избере дизайна който искате да използвате. Всеки дизайн може да използва различни цветови схеми. Пробвайте всички и изберете тази която най-много ви харесва ;'))";
$lang['help_mediadefaultn']  = "Носител по подразбиране";
$lang['help_mediadefault']   = "Изберете кой носител ще бъде използван по подразбиране когато добавяте нов филм.";
$lang['help_langdefaultn']   = "Език по подразбиране";
$lang['help_langdefault']    = "Изберете езика на филма по подразбиране когато добавяте нов филм.";
$lang['help_languageflagsn'] = "Флагове на езиците";
$lang['help_languageflags']  = "Избере кои езици ще се показват като бързи бутони когато добавяте нов филм. Не избирайте повече от 5";
$lang['help_filterdefaultn'] = "Филтър по подразбиране";
$lang['help_filterdefault']  = "Страницата която се показва когато стартирате VideoDB.";
$lang['help_showtvn']        = "Показвай TV Епизодите";
$lang['help_showtv']         = "Ако искате TV Епизодите да се показват в списъка на филмите изберете тази опция";
$lang['help_orderallbydiskn']= "Нареди по ID";
$lang['help_orderallbydisk'] = "Ако изберете тази опция филмите в списъка \"всички\" ще бъдат подредени по ID вместо по азбучен ред.";
$lang['help_removearticlesn']= "Корекция";
$lang['help_removearticles'] = "Изберете тази опция ако избрани заглавия са записани неправилно например Angel, The е записан като The Angel.";
$lang['help_customn']        = "Ваши Полета";
$lang['help_custom']         = "Можете да дефинирате до 4 Ваши Полета. Всяко поле може да съдържа до 255 символа. Тук можете да им дадете имена и тип. Оставете ги празни ако не ги използвате.";
$lang['help_localnetn']      = "Локална мрежа";
$lang['help_localnet']       = "Тук можете да дефинирате достъпа до VideoDB. Потребители които не са включени няма да могат да променят данните. Това е <a href=\"http://www.php.net/manual/en/pcre.pattern.syntax.php\" target=\"_blank\">regex</a> затова можете да използвате сложни описания.<p>Примери:<br /><span class=\"example\">^192\.168\.1\.</span> разрешава на 192.168.1.1 - 192.168.1.254<br /><span class=\"example\">^(192\.168\.1\.|127\.0\.0\.1$)</span> същото плюс localhost<br /><span class=\"example\">^192\.168\.1\.22$</span> разрешава само на IP 192.168.1.22 <br /><span class=\"example\">^(192\.168\.1\.22|127\.0\.0\.1)$</span> същото плюс localhost<br /> оставете го празно за да разрешите на всички<br /></p> ВНИМАНИЕ: Ако направите грешка тук е възможно да загубите достъпа до конфигурационнта страниця!";
$lang['help_imdbOverwriten'] = "IMDB презаписва";
$lang['help_imdbOverwrite']  = "Попълването на данни от IMDB никога не презаписва данните Ви, освен ако не сте избрали тази опция.";
$lang['help_IMDBagen']       = "IMDB живот на кеша";
$lang['help_IMDBage']        = "VideoDB може да кешира заявките към IMDB. Дефинирайте тук живота на кешираните данни (в секунди). По подразбиране 5 дена.";
$lang['help_thumbnailn']     = "Икони";
$lang['help_thumbnail']      = "Избирайки тази опция ще получите малки икони в списъците на филмите.";
$lang['help_castcolumnsn']   = "Колони за артистите";
$lang['help_castcolumns']    = "Тази опция определя броя на колоните при показването на артистите. Ако използвате 1152x864 или по-голяма разделителна способност изберете 2 или повече.";
$lang['help_listcolumnsn']   = "Показвани колони";
$lang['help_listcolumns']    = "Тази опция за сега е валидна само за шаблона 'modern'. Определя колко колони да се показват при списъка. Ако е 1 списъка ще изглежда като шаблона 'default'.";
$lang['help_proxy_hostn']    = "Прокси сървър";
$lang['help_proxy_host']     = "Ако компютъра на който се намира VideoDB е зад прокси сървър задайте адреса му тук.";
$lang['help_proxy_portn']    = "Порт на Прокси сървъра";
$lang['help_proxy_port']     = "Задайте порта на прокси сървъра. Например: <span class=\"example\">8080</span>.";
$lang['help_actorpicsn']     = "Икони за актьори";
$lang['help_actorpics']      = "Ако зададете тази опция VideoDB ще се опита да намери икони за актьорите и да ги покаже до името на съответяетния актьор.";
$lang['help_thumbAgen']      = "Повторно търсене на икона за актьор";
$lang['help_thumbAge']       = "Това определя след колко време ще се направи опит за повторно търсене на икона за актьор, ако таква не е намерена.. По подразбиране 3 седмици..";
$lang['help_shownewn']       = "Нови филми";
$lang['help_shownew']        = "Въведете броя на филмите показвани в категорията \"нови\".";
$lang['help_imdbBrowsern']   = "IMDB браузер";
$lang['help_imdbBrowser']    = "Преглед на IMDB чрез VideoDB...";

$lang['help_multiuser']      = "Тази опция включва многопотребителския режим, така VideoDB може да се използва от различни потребители с различни пароли.";
$lang['help_multiusern']     = "Многопотребителски режим";
$lang['help_usermanager']    = "Щракнете тук за да добавите, промените или изтриете потребители и техните права за многопотребителския режим.";
$lang['help_usermanagern']   = "Настройки на потребителите";
$lang['help_denyguest']      = "Включването на тази опция забранява достъпа на всички, освен потребителите които разполагат с парола за достъп. (Важи само ако е включен многопотребителския режим)";
$lang['help_denyguestn']     = "Публичен достъп забранен";
$lang['help_adultgenres']    = "Филмите от жанровете за възрастни ще се виждат само от потребителите с този статус.";
$lang['help_adultgenresn']   = "Жанрове за възрастни";
$lang['help_pageno']   	   = "Брой филми на една страница. Ако напишете 0 ще виздате всички на една страница.";
$lang['help_pagenon']        = "Брой на страница";

$lang['help_engine']         = "Активирането на тази опция ще позволи на VideoDB да приема данни от %s.";
$lang['help_defaultenginen'] = "Доставчик на информация по подразбиране";
$lang['help_defaultengine']  = "Изберете откъде по подразбиране VideoDB ще приема данни.";
$lang['help_enginegoogle']   = "Google поддържа търсене само на картинки.";
$lang['help_engexperimental']= "<br/>Тази опция още е в процес на разработка и е възможно да не работи както се очаква.";

$lang['help_showtoolsn']     = "Показвай инструментите";
$lang['help_showtools']      = "Тази опция ще активира меню с инструменти от <code>contrib</code> директорията. Съдържанието на тази директория не е напълно тествано.";

$lang['page']                = "Страница";
$lang['of']                  = "от";
$lang['records']             = "записа";

?>
