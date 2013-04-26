<?php
/**
 * test_dvdfr.php
 *
 * DVDfr engine test case
 *
 * @package Test
 * @author Sébastien Koechlin <seb.videodb@koocotte.org>
 * @version $Id: test_dvdfr.php,v 1.9 2013/02/02 11:38:59 andig2 Exp $
 */

require_once './core/functions.php';
require_once './engines/engines.php';

class TestDVDFR extends UnitTestCase
{
	function TestDVDFR()
	{
		parent::__construct();
	}
	
	function testMovie()
	{
		// Star Wars: Episode I
		// http://www.dvdfr.com/dvd/f2869_star_wars_-_episode_i_-_la_menace_fantome.html
		// http://www.dvdfr.com/dvd/dvd.php?id=2869
		$id = 'dvdfr:2869';
		
		$data = engineGetData($id, 'dvdfr');
		#$this->assertNoErrors();
		$this->assertTrue(sizeof($data) > 0);
		
		#dump($data);

/*
Array ( 
	[title] => Star Wars - Episode I - La Menace Fantôme 
	[subtitle] => Star Wars: Episode I - The Phantom Menace 
	[country] => USA 
	[year] => 1999 
	[coverurl] => http://www.dvdfr.com/images/dvd/cover_200x280/2/2869.jpg 
	[runtime] => 130 
	[director] => George Lucas 
	[plot] => Premier volet de la nouvelle trilogie dont l'action se déroule avant les épisodes que le monde entier 
		connaît par coeur. Faites connaissance avec Anakin Skywalker, 9 ans, en qui le jeune Obi Wan Kenobi et son 
		maître voient "l'élu" qui apportera l'équilibre dans la force. 
	[cast] => Liam Neeson::::dvdfr:10977 Ewan McGregor::::dvdfr:9192 Natalie Portman::::dvdfr:6177 Jake Lloyd::::dvdfr:11160 Ian McDiarmid::::dvdfr:24388 
) 
*/		
		$this->assertEqual($data[title], 'Star Wars - Episode I - La Menace Fantôme');
		$this->assertEqual($data[subtitle], 'Star Wars: Episode I - The Phantom Menace');
		$this->assertEqual($data[year], 1999);
		$this->assertEqual($data[coverurl], 'http://images.dvdfr.com/images/dvd/cover_200x280/2/2869.jpg');
		$this->assertEqual($data[runtime], 130);
		$this->assertEqual($data[director], 'George Lucas');
		$this->assertEqual($data[country], 'USA');
		
		$this->assertPattern('/Liam Neeson.*Ewan McGregor.*Natalie Portman/s', $data[cast]);
		$this->assertPattern('/Anakin Skywalker.*le jeune Obi Wan Kenobi/', $data[plot]);
		
/*
Array ( [title] => Star Wars: Episode I [subtitle] => The Phantom Menace [year] => 1999 [coverurl] => http://ia.imdb.com/media/imdb/01/I/47/66/60m.jpg [mpaa] => Rated PG for sci-fi action/violence. [bbfc] => U [runtime] => 133 [director] => George Lucas [rating] => 6.3 [country] => USA [language] => english [genres] => Array ( [0] => Action [1] => Adventure [2] => Fantasy [3] => Sci-Fi ) [cast] => Liam Neeson::Qui-Gon Jinn::imdb:nm0000553 Ewan McGregor::Obi-Wan Kenobi::imdb:nm0000191 Natalie Portman::Queen Padmé Amidala::imdb:nm0000204 Jake Lloyd::Anakin Skywalker::imdb:nm0005157 Pernilla August::Shmi Skywalker::imdb:nm0000278 Frank Oz::Yoda::imdb:nm0000568 Ian McDiarmid::Senator Palpatine::imdb:nm0001519 Oliver Ford Davies::Gov. Sio Bibble::imdb:nm0203882 Ray Park::Darth Maul::imdb:nm0661917 Hugh Quarshie::Capt. Panaka::imdb:nm0702934 Ahmed Best::Jar Jar Binks::imdb:nm0078886 Anthony Daniels::C-3PO::imdb:nm0000355 Kenny Baker::R2-D2::imdb:nm0048652 Terence Stamp::Supreme Chancellor Valorum::imdb:nm0000654 Brian Blessed::Boss Nass::imdb:nm0000306 Andrew Secombe::Watto::imdb:nm0781181 Lewis Macleod::Sebulba::imdb:nm0533914 Steve Speirs::Capt. Tarpals::imdb:nm0818648 Silas Carson::Viceroy Nute Gunray/Ki-Adi-Mundi/Lott Dodd/Radiant VII Pilot::imdb:nm0141324 Ralph Brown::Ric Olié::imdb:nm0114460 Celia Imrie::Fighter Pilot Bravo 5::imdb:nm0408309 Benedict Taylor::Fighter Pilot Bravo 2::imdb:nm0852028 Karol Cristina da Silva::Rabé::imdb:nm0196263 Clarence Smith::Fighter Pilot Bravo 3::imdb:nm0807734 Samuel L. Jackson::Mace Windu::imdb:nm0000168 Dominic West::Palace guard::imdb:nm0922035 Liz Wilson::Eirtaé (as Friday 'Liz' Wilson)::imdb:nm0933770 Candice Orwell::Yané::imdb:nm0651425 Sofia Coppola::Saché::imdb:nm0001068 Keira Knightley::Sabé::imdb:nm0461136 Bronagh Gallagher::Radiant VII captain::imdb:nm0302345 John Fensom::TC-14::imdb:nm0271993 Greg Proops::Beed (voice)::imdb:nm0698681 Scott Capurro::Fode::imdb:nm0135750 Margaret Towner::Jira::imdb:nm0870026 Dhruv Chanchani::Kitster::imdb:nm0151268 Oliver Walpole::Seek::imdb:nm0909488 Katie Lucas::Amee (as Jenna Green)::imdb:nm0337964 Megan Udall::Melee::imdb:nm0879818 Hassani Shapi::Eeth Koth::imdb:nm0788434 Gin Clarke::Adi Gallia (as Gin)::imdb:nm0164778 Khan Bonfils::Saesee Tiin::imdb:nm0094416 Michelle Taylor::Yarael Poof::imdb:nm1180158 Michaela Cottrell::Even Piell::imdb:nm0183169 Dipika O'Neill Joti::Depa Billaba::imdb:nm0642086 Phil Eason::Yaddle::imdb:nm0247500 Mark Coulier::Aks Moe::imdb:nm0183418 Lindsay Duncan::TC-14 (voice)::imdb:nm0242026 Peter Serafinowicz::Darth Maul (voice)::imdb:nm0784818 James Taylor::Rune Haako::imdb:nm0852511 Chris Sanders::Daultay Dofine::imdb:nm0761497 Toby Longworth::Sen. Lott Dodd/Gragra (voice)::imdb:nm0519528 Marc Silk::Aks Moe (voice)::imdb:nm0798025 Amanda Lucas::Tey How/Diva Funquita (voice) (as Tyger)::imdb:nm0878741 Amy Allen::Twi'Lek Senatorial Aide (DVD deleted scenes) (uncredited)::imdb:nm1116989 Don Bies::Pod race mechanic (uncredited)::imdb:nm0081636 Trisha Biggar::Orn Free Taa's aide (uncredited)::imdb:nm0081773 Jerome Blake::Rune Haako/Mas Amedda/Oppo Rancisis/Orn Free Taa (uncredited)::imdb:nm0086592 Ben Burtt::Naboo Courier (uncredited)::imdb:nm0123785 Doug Chiang::Flag bearer (uncredited)::imdb:nm0156956 Rob Coleman::Pod race spectator in Jabba's Private Box (uncredited)::imdb:nm0171197 Roman Coppola::Senate guard (uncredited)::imdb:nm0178910 Russell Darling::Naboo Royal Security Guard (uncredited)::imdb:nm0201402 Warwick Davis::Wald/Pod race spectator/Mos Espa Citizen (uncredited)::imdb:nm0001116 C. Michael Easton::Pod race spectator (uncredited)::imdb:nm0247694 Joss Gower::Naboo fighter pilot (uncredited)::imdb:nm0332980 Raymond Griffiths::GONK Droid (uncredited)::imdb:nm0341739 Nathan Hamill::Pod race spectator/Naboo palace guard (uncredited)::imdb:nm0357686 Tim Harrington::Extra (Naboo Security Gaurd) (uncredited)::imdb:nm0364360 Jack Haye::Pod race spectator in Jabba's Private Box (uncredited)::imdb:nm0370849 Nifa Hindes::Ann Gella (uncredited)::imdb:nm0385539 Nishan Hindes::Tann Gella (uncredited)::imdb:nm0385540 John Knoll::Lt. Rya Kirsch (Bravo 4)/Flag bearer (uncredited)::imdb:nm0461306 Kamay Lau::Sei Taria - Senators Aide (uncredited)::imdb:nm2151895 Dan Madsen::Kaadu handler (uncredited)::imdb:nm0535186 Iain McCaig::Orn Free Taa's aide (uncredited)::imdb:nm0564572 Rick McCallum::Naboo courier (uncredited)::imdb:nm0564768 Jeff Olson::Pod race spectator in Jabba's Private Box (uncredited)::imdb:nm0647926 Lorne Peterson::Mos Espa citizen (uncredited)::imdb:nm0677285 Alan Ruscoe::Plo Koon/Bib Foruna/Daultay Dofine (uncredited)::imdb:nm0750603 Steve Sansweet::Naboo courier (uncredited)::imdb:nm0763109 Mike Savva::Naboo Royal Guard (uncredited)::imdb:nm1979766 Christian Simpson::Bravo 6 (uncredited)::imdb:nm0800939 Paul Martin Smith::Naboo courier (uncredited)::imdb:nm0809551 Scott Squires::Naboo speeder driver (uncredited)::imdb:nm0820140 Tom Sylla::Battle Droid (voice) (uncredited)::imdb:nm0843143 Bill Tlusty::Chokk, Jabba's Bodyguard (uncredited)::imdb:nm0864754 Danny Wagner::Mawhonic (uncredited)::imdb:nm0905855 Matthew Wood::Bib Fortuna/Voice of Ody Mandrell (uncredited)::imdb:nm0003214 [plot] => The evil Trade Federation, led by Nute Gunray (Carson) is planning to take over the peaceful world of Naboo. Jedi's Qui-Gon Jinn (Neeson) and Obi-Wan Kenobi (McGregor) are sent to confront the leaders. But not everything goes to plan. The two Jedis escape, and along with their new Gungan friend, Jar Jar Binks (Best) head to Naboo to warn Queen Amidala (Portman), but droids have already started to capture Naboo and the Queen is not safe there. Eventually they land on Tatooine, where they become friends with a young boy known as Anakin Skywalker (Lloyd). Qui-Gon is curious about the boy, and sees a bright future for him. The group must now find a way of getting to Coruscant and to finally solve this trade dispute, but there is someone else hiding in the shadows. Are the sith really extinct? Is the Queen who she really says she is? and what's so special about this young boy? All these questions and more in the first chapter of the epic Star Wars saga. ) 
*/
	}

	function testSearchStarWars()
	{
		// Star Wars
		//	http://www.dvdfr.com/api/search.php?title=star%20wars
		//	Result: XML with many results
		$search = 'star wars';
		
		$data = engineSearch($search, 'dvdfr');
		#dump($data);
		
		#$this->assertNoErrors();
		
		// Results
		// - More than 5
		// - Contain 
		//	7919 (Star Wars - Episode I - La menace fantome)
		//	11092 (Star Wars - Episode II - L'attaque des clones)
		//	6504 (Star Wars - Episode III - La revanche des Sith)
		//	663 (Star Wars - Episode IV - Un nouvel espoir)
		//	492 (Star Wars - Episode V - L'Empire contre-attaque)
		//	1250 (Star Wars - Episode VI - Le retour du Jedi)

		$obj = array( 'dvdfr:7919', 'dvdfr:11092', 'dvdfr:6504', 'dvdfr:663', 'dvdfr:492', 'dvdfr:1250' );
		
		$this->assertTrue(sizeof($data) > 0);
		
		// Search each movie in result
		foreach( $obj as $search ) {
			$f = 0;
			foreach( $data as $movie ) {
				if( $search == $movie['id'] ) $f = 1;
			}
			$this->assertTrue($f);
		}
	}
}

?>
