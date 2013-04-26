<?php
/**
 * test_imdb.php
 *
 * IMDB engine test case
 *
 * @package Test
 * @author Andreas Götz <cpuidle@gmx.de>
 * @version $Id: test_imdb.php,v 1.32 2013/03/01 09:19:04 andig2 Exp $
 */

require_once './core/functions.php';
require_once './engines/engines.php';

class TestIMDB extends UnitTestCase
{
    function TestIMDB()
    {
        parent::__construct();
    }

    function testMovie()
    {
        // Star Wars: Episode I
        // http://imdb.com/title/tt0120915/
        $id = '0120915';
        $data = engineGetData($id, 'imdb');
        $this->assertTrue(sizeof($data) > 0);

        #echo '<pre>';dump($data);echo '</pre>';

        $this->assertEqual($data['istv'], '');
        $this->assertEqual($data[title], 'Star Wars: Episode I');
        $this->assertPattern('#The Phantom Menace|Die dunkle Bedrohung#', $data[subtitle]);
        
        // Since title is delivered by country htis can no longer be tested this way
        $this->assertTrue( strlen($data['subtitle']) > 10 );
        # new test: origtitle
        $this->assertEqual($data['origtitle'], 'Star Wars: Episode I - The Phantom Menace');
        $this->assertEqual($data[year], 1999);
        $this->assertPattern('#http://ia.*imdb.com/.*.jpg#', $data[coverurl]);
        $this->assertEqual($data[mpaa], 'Rated PG for sci-fi action/violence');
        # bbfc no longer appears on main page
        # test disabled
        # $this->assertEqual($data[bbfc], 'U');
        $this->assertTrue($data[runtime] >= 133 && $data[runtime] <= 136);
        $this->assertEqual($data[director], 'George Lucas');
        $this->assertTrue($data[rating] >= 6);
        $this->assertTrue($data[rating] <= 8);
        $this->assertEqual($data[country], 'USA');
        $this->assertEqual($data[language], 'english');
        $this->assertEqual(join(',', $data[genres]), 'Action,Adventure,Fantasy,Sci-Fi');

        # cast tests changed to be independent of order
        $cast = explode("\n", $data['cast']);

        $this->assertTrue( in_array('Liam Neeson::Qui-Gon Jinn::imdb:nm0000553', $cast) );
        $this->assertTrue( in_array('Ewan McGregor::Obi-Wan Kenobi::imdb:nm0000191', $cast) );
        $this->assertTrue( in_array('Natalie Portman::Queen Amidala / Padmé::imdb:nm0000204', $cast) );
        $this->assertTrue( in_array('Anthony Daniels::C-3PO (voice)::imdb:nm0000355', $cast) );
        $this->assertTrue( in_array('Kenny Baker::R2-D2::imdb:nm0048652', $cast) );
        $this->assertTrue( sizeof($cast) > 90 );

        $this->assertPattern('/The evil Trade Federation/', $data[plot]);


#   Array ( [title] => Star Wars: Episode I [subtitle] => The Phantom Menace [year] => 1999 [coverurl] => http://ia.imdb.com/media/imdb/01/I/47/66/60m.jpg [mpaa] => Rated PG for sci-fi action/violence. [bbfc] => U [runtime] => 133 [director] => George Lucas [rating] => 6.3 [country] => USA [language] => english [genres] => Array ( [0] => Action [1] => Adventure [2] => Fantasy [3] => Sci-Fi ) [cast] => Liam Neeson::Qui-Gon Jinn::imdb:nm0000553 Ewan McGregor::Obi-Wan Kenobi::imdb:nm0000191 Natalie Portman::Queen Padmé Amidala::imdb:nm0000204 Jake Lloyd::Anakin Skywalker::imdb:nm0005157 Pernilla August::Shmi Skywalker::imdb:nm0000278 Frank Oz::Yoda::imdb:nm0000568 Ian McDiarmid::Senator Palpatine::imdb:nm0001519 Oliver Ford Davies::Gov. Sio Bibble::imdb:nm0203882 Ray Park::Darth Maul::imdb:nm0661917 Hugh Quarshie::Capt. Panaka::imdb:nm0702934 Ahmed Best::Jar Jar Binks::imdb:nm0078886 Anthony Daniels::C-3PO::imdb:nm0000355 Kenny Baker::R2-D2::imdb:nm0048652 Terence Stamp::Supreme Chancellor Valorum::imdb:nm0000654 Brian Blessed::Boss Nass::imdb:nm0000306 Andrew Secombe::Watto::imdb:nm0781181 Lewis Macleod::Sebulba::imdb:nm0533914 Steve Speirs::Capt. Tarpals::imdb:nm0818648 Silas Carson::Viceroy Nute Gunray/Ki-Adi-Mundi/Lott Dodd/Radiant VII Pilot::imdb:nm0141324 Ralph Brown::Ric Olié::imdb:nm0114460 Celia Imrie::Fighter Pilot Bravo 5::imdb:nm0408309 Benedict Taylor::Fighter Pilot Bravo 2::imdb:nm0852028 Karol Cristina da Silva::Rabé::imdb:nm0196263 Clarence Smith::Fighter Pilot Bravo 3::imdb:nm0807734 Samuel L. Jackson::Mace Windu::imdb:nm0000168 Dominic West::Palace guard::imdb:nm0922035 Liz Wilson::Eirtaé (as Friday 'Liz' Wilson)::imdb:nm0933770 Candice Orwell::Yané::imdb:nm0651425 Sofia Coppola::Saché::imdb:nm0001068 Keira Knightley::Sabé::imdb:nm0461136 Bronagh Gallagher::Radiant VII captain::imdb:nm0302345 John Fensom::TC-14::imdb:nm0271993 Greg Proops::Beed (voice)::imdb:nm0698681 Scott Capurro::Fode::imdb:nm0135750 Margaret Towner::Jira::imdb:nm0870026 Dhruv Chanchani::Kitster::imdb:nm0151268 Oliver Walpole::Seek::imdb:nm0909488 Katie Lucas::Amee (as Jenna Green)::imdb:nm0337964 Megan Udall::Melee::imdb:nm0879818 Hassani Shapi::Eeth Koth::imdb:nm0788434 Gin Clarke::Adi Gallia (as Gin)::imdb:nm0164778 Khan Bonfils::Saesee Tiin::imdb:nm0094416 Michelle Taylor::Yarael Poof::imdb:nm1180158 Michaela Cottrell::Even Piell::imdb:nm0183169 Dipika O'Neill Joti::Depa Billaba::imdb:nm0642086 Phil Eason::Yaddle::imdb:nm0247500 Mark Coulier::Aks Moe::imdb:nm0183418 Lindsay Duncan::TC-14 (voice)::imdb:nm0242026 Peter Serafinowicz::Darth Maul (voice)::imdb:nm0784818 James Taylor::Rune Haako::imdb:nm0852511 Chris Sanders::Daultay Dofine::imdb:nm0761497 Toby Longworth::Sen. Lott Dodd/Gragra (voice)::imdb:nm0519528 Marc Silk::Aks Moe (voice)::imdb:nm0798025 Amanda Lucas::Tey How/Diva Funquita (voice) (as Tyger)::imdb:nm0878741 Amy Allen::Twi'Lek Senatorial Aide (DVD deleted scenes) (uncredited)::imdb:nm1116989 Don Bies::Pod race mechanic (uncredited)::imdb:nm0081636 Trisha Biggar::Orn Free Taa's aide (uncredited)::imdb:nm0081773 Jerome Blake::Rune Haako/Mas Amedda/Oppo Rancisis/Orn Free Taa (uncredited)::imdb:nm0086592 Ben Burtt::Naboo Courier (uncredited)::imdb:nm0123785 Doug Chiang::Flag bearer (uncredited)::imdb:nm0156956 Rob Coleman::Pod race spectator in Jabba's Private Box (uncredited)::imdb:nm0171197 Roman Coppola::Senate guard (uncredited)::imdb:nm0178910 Russell Darling::Naboo Royal Security Guard (uncredited)::imdb:nm0201402 Warwick Davis::Wald/Pod race spectator/Mos Espa Citizen (uncredited)::imdb:nm0001116 C. Michael Easton::Pod race spectator (uncredited)::imdb:nm0247694 Joss Gower::Naboo fighter pilot (uncredited)::imdb:nm0332980 Raymond Griffiths::GONK Droid (uncredited)::imdb:nm0341739 Nathan Hamill::Pod race spectator/Naboo palace guard (uncredited)::imdb:nm0357686 Tim Harrington::Extra (Naboo Security Gaurd) (uncredited)::imdb:nm0364360 Jack Haye::Pod race spectator in Jabba's Private Box (uncredited)::imdb:nm0370849 Nifa Hindes::Ann Gella (uncredited)::imdb:nm0385539 Nishan Hindes::Tann Gella (uncredited)::imdb:nm0385540 John Knoll::Lt. Rya Kirsch (Bravo 4)/Flag bearer (uncredited)::imdb:nm0461306 Kamay Lau::Sei Taria - Senators Aide (uncredited)::imdb:nm2151895 Dan Madsen::Kaadu handler (uncredited)::imdb:nm0535186 Iain McCaig::Orn Free Taa's aide (uncredited)::imdb:nm0564572 Rick McCallum::Naboo courier (uncredited)::imdb:nm0564768 Jeff Olson::Pod race spectator in Jabba's Private Box (uncredited)::imdb:nm0647926 Lorne Peterson::Mos Espa citizen (uncredited)::imdb:nm0677285 Alan Ruscoe::Plo Koon/Bib Foruna/Daultay Dofine (uncredited)::imdb:nm0750603 Steve Sansweet::Naboo courier (uncredited)::imdb:nm0763109 Mike Savva::Naboo Royal Guard (uncredited)::imdb:nm1979766 Christian Simpson::Bravo 6 (uncredited)::imdb:nm0800939 Paul Martin Smith::Naboo courier (uncredited)::imdb:nm0809551 Scott Squires::Naboo speeder driver (uncredited)::imdb:nm0820140 Tom Sylla::Battle Droid (voice) (uncredited)::imdb:nm0843143 Bill Tlusty::Chokk, Jabba's Bodyguard (uncredited)::imdb:nm0864754 Danny Wagner::Mawhonic (uncredited)::imdb:nm0905855 Matthew Wood::Bib Fortuna/Voice of Ody Mandrell (uncredited)::imdb:nm0003214 [plot] => The evil Trade Federation, led by Nute Gunray (Carson) is planning to take over the peaceful world of Naboo. Jedi's Qui-Gon Jinn (Neeson) and Obi-Wan Kenobi (McGregor) are sent to confront the leaders. But not everything goes to plan. The two Jedis escape, and along with their new Gungan friend, Jar Jar Binks (Best) head to Naboo to warn Queen Amidala (Portman), but droids have already started to capture Naboo and the Queen is not safe there. Eventually they land on Tatooine, where they become friends with a young boy known as Anakin Skywalker (Lloyd). Qui-Gon is curious about the boy, and sees a bright future for him. The group must now find a way of getting to Coruscant and to finally solve this trade dispute, but there is someone else hiding in the shadows. Are the sith really extinct? Is the Queen who she really says she is? and what's so special about this young boy? All these questions and more in the first chapter of the epic Star Wars saga. )
    }

    function testMovie2()
    {
        // Harold & Kumar Escape from Guantanamo Bay
        // http://www.imdb.com/title/tt0481536/

        $id = '0481536';
        $data = engineGetData($id, 'imdb');
        $this->assertTrue(sizeof($data) > 0);

#       dump($data);

        $this->assertEqual($data['istv'], '');
        $this->assertPattern('/Harold/', $data[plot]);
    }

    function testMovie3()
    {
    	// Omicron (1963)
    	// http://www.imdb.com/title/tt0191326/

    	$id = '0191326';
    	$data = engineGetData($id, 'imdb');

    	// There is no cover image in imdb
    	$this->assertEqual( $data['coverurl'], '' );
    }

    function testMovie4()
    {
    	// Astérix aux jeux olympiques (2008)
    	// http://www.imdb.com/title/tt0463872/

    	$id = '0463872';
    	$data = engineGetData($id, 'imdb');

    	// multiple directors
    	$this->assertEqual($data['director'], 'Frédéric Forestier, Thomas Langmann');
    }

    function testMovie5() {
    	// Role Models
    	// http://www.imdb.com/title/tt0430922/
    	// added for bug #3114003 - imdb.php does not fetch runtime in certain cases

    	$id = '0430922';
    	$data = engineGetData($id, 'imdb');

    	$this->assertTrue($data['runtime'] >= 99 && $data['runtime'] <= 101);
    }

    function testMovie6() {
    	// She's Out of My League
    	// http://www.imdb.com/title/tt0815236/
    	// added for bug #3114003 - imdb.php does not fetch runtime in certain cases

    	$id = '0815236';
    	$data = engineGetData($id, 'imdb');

    	$this->assertEqual($data['runtime'], 104);
    }

    function testMovie7() {
    	// Romasanta
    	// http://www.imdb.com/title/tt0374180/
    	// added for bug #2914077 - charset of plot

    	$id = '0374180';
    	$data = engineGetData($id, 'imdb');

    	$this->assertPattern('/Romasanta was tried in Allaríz in 1852 and avoided capital/', $data['plot']);
    }

    function testMovie8() {
        // Cars (2006)
        // http://www.imdb.com/title/tt0317219/
        // added for bug #3399788 - title & year

        $id = '0317219';
        $data = engineGetData($id, 'imdb');

        $this->assertEqual($data['title'],'Cars');
        $this->assertEqual($data['year'],2006);
    }

    /**
     * Case added for bug 1675281
     *
     * https://sourceforge.net/tracker/?func=detail&atid=586362&aid=1675281&group_id=88349
     */
    function testSeries()
    {
        // Scrubs
        // http://imdb.com/title/tt0285403/

        $id = '0285403';
        $data = engineGetData($id, 'imdb');
        $this->assertTrue(sizeof($data) > 0);

        #echo '<pre>';dump($data);echo '</pre>';

        $this->assertPattern("/Zach Braff::Dr. John 'J.D.' Dorian \(.+?\)::imdb:nm0103785.+Mona Weiss::Nurse \(.+?\)::imdb:nm2032293/is", $data[cast]);
        $this->assertPattern('/Sacred Heart/i', $data[plot]);
    }

    /**
     * Case added for "24" - php seems to have issues with matching large patterns...
     */
    function testSeries2()
    {
        // 24
        // http://imdb.com/title/tt0285331/

        $id = '0285331';
        $data = engineGetData($id, 'imdb');
        $this->assertTrue(sizeof($data) > 0);

        #echo '<pre>';dump($data);echo '</pre>';

        $this->assertTrue(sizeof(preg_split('/\n/', $data[cast])) > 400);
    }

    /**
     * Bis in die Spitzen
     */
    function testSeries3()
    {
        // Bis in die Spitzen
        // http://imdb.com/title/tt0461620/
        $id = '0461620';
        $data = engineGetData($id, 'imdb');
        $this->assertTrue(sizeof($data) > 0);

        #echo '<pre>';dump($data);echo '</pre>';

        $this->assertEqual($data['istv'], 1);
        $this->assertEqual($data[title], 'Bis in die Spitzen');
    }

    function testSeriesEpisode()
    {
        // Star Trek TNG Episode "Q Who?"
        // http://www.imdb.com/title/tt0708758/

        $id = '0708758';
        $data = engineGetData($id, 'imdb');
        $this->assertTrue(sizeof($data) > 0);

        #echo '<pre>';dump($data);echo '</pre>';

        $this->assertEqual($data['istv'], 1);
        $this->assertEqual($data['tvseries_id'], '0092455');
        $this->assertPattern('/Star Trek: The Next Generation|Raumschiff Enterprise - Das nächste Jahrhundert/', $data[title]);
        $this->assertEqual($data[subtitle], 'Q Who?');
        $this->assertPattern('/19\d\d/', $data[year]);
        $this->assertPattern('#http://ia.*imdb.com/.*.jpg#', $data[coverurl]);
        $this->assertEqual($data[director], 'Rob Bowman');
        $this->assertTrue($data[rating] >= 7);
        $this->assertTrue($data[rating] <= 9);
        $this->assertEqual($data[country], 'USA');
        $this->assertEqual($data[language], 'english');
        $this->assertEqual(join(',', $data[genres]), 'Action,Adventure,Sci-Fi');

        $cast = explode("\n", $data['cast']);

        $this->assertTrue( in_array('Patrick Stewart::Captain Jean-Luc Picard::imdb:nm0001772', $cast) );
        $this->assertTrue( in_array('Jonathan Frakes::Commander William T. Riker::imdb:nm0000408', $cast) );
        $this->assertTrue( in_array('Marina Sirtis::Counselor Deanna Troi::imdb:nm0000642', $cast) );
        $this->assertTrue( in_array('John de Lancie::Q (as John deLancie)::imdb:nm0209496', $cast) );
        $this->assertTrue( in_array('Rob Bowman::Borg (voice)::imdb:nm0101385', $cast) );
        $this->assertTrue( sizeof($cast) > 15 );
        $this->assertTrue( sizeof($cast) < 30 );

        $this->assertTrue($data['runtime'] >= 40);
        $this->assertTrue($data['runtime'] <= 50);

        $this->assertPattern('/Q pays the Enterprise another visit/', $data[plot]);
    }

    function testSeriesEpisode2()
    {
        // The Inspector Lynley Mysteries - Episode: Playing for the Ashes
        // http://www.imdb.com/title/tt0359476
        
        $id = '0359476';
        $data = engineGetData($id, 'imdb');
        $this->assertTrue(sizeof($data) > 0);

        #echo '<pre>';dump($data);echo '</pre>';

        $this->assertEqual($data['istv'], 1);
        $this->assertEqual($data['tvseries_id'], '0988820');
        $this->assertPattern('/Inspector Lynley/', $data[title]);
        $this->assertEqual($data[subtitle], 'Playing for the Ashes');
        $this->assertPattern('/200\d/', $data[year]);
        $this->assertPattern('#http://ia.*imdb.com/.*.jpg#', $data[coverurl]);
        $this->assertEqual($data[director], 'Richard Spence');
        $this->assertTrue($data[rating] >= 5);
        $this->assertTrue($data[rating] <= 8);
        $this->assertEqual($data[country], 'UK');
        $this->assertEqual($data[language], 'english');
        $this->assertEqual(join(',', $data[genres]), 'Crime,Drama,Mystery');

        $cast = explode("\n", $data['cast']);

        $this->assertTrue( in_array('Clare Swinburne::Gabriella Patten::imdb:nm0842673', $cast) );
        $this->assertTrue( in_array('Mark Brighton::Kenneth Waring::imdb:nm1347940', $cast) );
        $this->assertTrue( in_array('Nathaniel Parker::Thomas Lynley::imdb:nm0662511', $cast) );
        $this->assertTrue( in_array('Andrew Clover::Hugh Patten::imdb:nm0167249', $cast) );
        $this->assertTrue( in_array('Anjalee Patel::Hadiyyah::imdb:nm1347125', $cast) );
        $this->assertTrue( sizeof($cast) > 12 );
        $this->assertTrue( sizeof($cast) < 30 );

        $this->assertPattern('/When England cricketer Kenneth Waring dies/', $data[plot]);
    }

    function testSeriesEpisode3() {
        //Pushing Daisies - Episode 3
        // http://www.imdb.com/title/tt1039379/

        $id = '1039379';
        $data = engineGetData($id, 'imdb');

        // was not detected as tv episode
        $this->assertEqual($data['istv'], 1);

        $this->assertTrue($data['runtime'] >= 40);
        $this->assertTrue($data['runtime'] <= 50);

    }

    function testActorImage() {
        //William Shatner
        // http://www.imdb.com/name/nm0000638/
        $data = imdbActor('William Shatner', 'nm0000638');

        $this->assertPattern('#http://ia.*imdb.com/.*.jpg#', $data[0][1]);
    }

    function testActorWithoutImage() {
        // Lena Banks
        // http://www.imdb.com/name/nm3086341/

        $data = imdbActor('Lena Banks', 'nm3086341');

        $this->assertEqual('', $data[0][1]);
    }

    /**
     * https://sourceforge.net/tracker/?func=detail&atid=586362&aid=1675281&group_id=88349
     */
    function testSearch()
    {
        // Clerks 2
        // http://imdb.com/find?s=all&q=clerks
        
        $data = engineSearch('Clerks 2', 'imdb');
        $this->assertTrue(sizeof($data) > 0);

        $data = $data[0];

        $this->assertEqual($data[id], 'imdb:0424345');
        $this->assertEqual($data[title], 'Clerks II');
    }

    /**
     * Check fur UTF-8 encoded search and aka search
     */
    function testSearch2()
    {
        // Das Streben nach Glück
        // http://www.imdb.com/find?s=all&q=Das+Streben+nach+Gl%FCck
        
        $data = engineSearch('Das Streben nach Glück', 'imdb', true);
        $this->assertTrue(sizeof($data) > 0);

        $data = $data[0];
#       dump($data);

        $this->assertEqual($data[id], 'imdb:0454921');
        $this->assertPattern('/The Pursuit of Happyness|Das Streben nach Glück/', $data[title]);
    }

    /**
     * Make sure matching is correct and no HTML tags are included
     */
    function testPartialSearch()
    {
        // Serpico
        // http://imdb.com/find?s=all&q=serpico
        
        $data = engineSearch('Serpico', 'imdb');
		#echo("<pre>");dump($data);echo("</pre>");

        foreach ($data as $item)
        {
            $t = strip_tags($item['title']);
            $this->assertTrue($item['title'] == $t);
        }
    }
}

?>
