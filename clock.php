<?php

$tz = -8;
	$ref = $_SERVER['REQUEST_URI'];
	if (strstr($ref,"?timezone="))
	{
		$arr = explode('?timezone=',$ref);
		$tz = ceil($arr[0]);
	}  

$zone = 'Etc/GMT';
	if (abs($tz) > 14) $tz = 0;
	if ($tz>0) $zone = 'Etc/GMT'.-$tz;
	else $zone = 'Etc/GMT+'.-$tz;
	date_default_timezone_set($zone);

$expt	= 0;
	if (strstr($ref,"find=")) {
		$arr		= explode('find=',$ref); //find reference
		$timevals	= ceil($arr[1]);
		$expt		= array_pad(str_split($timevals, 2), 0, 0);
		$refy		= $expt[0].$expt[1]; //reference year
		//print_r($expt);
		//$curtime = strtotime($timevals);
	} else {
		$curtime	= time(); //current time
		$refy		= date('Y', $curtime);
	}
	$yr		= ($refy) % 4;			// Year 0-3 rel to leapyr
	//$yrn	= ($refy + 2) % 4;		// Year 0-3 rel to nemo
	//$yrh	= ($refy + 3) % 4;		// Year 0-3 rel to hrs
	$is100	= $refy % 100 == 0;
	$is400	= $refy % 400 == 0;
	$is130	= ($refy - 24) % 130 == 0;
	
	if ($expt) {
		if (!$is100)
			$curtime = mktime($expt[4],$expt[5],0,$expt[2],$expt[3],2004 + $yr);
		else if ($is400)
			$curtime = mktime($expt[4],$expt[5],0,$expt[2],$expt[3],2000);
		else  /*($is100)*/
			$curtime = mktime($expt[4],$expt[5],0,$expt[2],$expt[3],2001);
	}
	
	$refd	= date('z', $curtime);	// range 0-365
	$refh	= date('G', $curtime);	// range 0-23
	$refm	= date('i', $curtime);	// range 0-59
	//$refy	= 2011;					// range 24-9999
	//$refd	= 70;					// range 0-365
	//$refh	= 12;					// range 0-23
	//$refm	= 15;					// range 0-59
	
	$yr13	= ($refy - 11) % 13;	// 13 Year Cycle 0-12
									// abs($refy - 1922)
	
	$dmod	= 69 - floor(($refy - 24) / 130) - floor($refy / 400) + floor($refy / 100);
	if ($yr == 2 || $yr == 3) $dmod++;
	//if ($yr == 0 || $yr == 1) $dmod--;
	//if ($yr == 1) $dmod+=2;
	//print_r($refd);
	//print_r($dmod);
	
	$yd = 0;					// Day in Year 0-365
	if ($refd > $dmod || ($yr != 1 && $yr != 0 && $refd == $dmod)) {
		if ($yr == 0 || $yr == 1) {
			$dmod++;
		}
		$yd		= $refd - $dmod;
	} else {
		if ($yr == 1 || $yr == 2 || $yr == 3) {
			$dmod--;
		}
		$yd		= $refd + 364 - $dmod;
		$yr		=  $yr   == 0  ?   3  :  $yr - 1;
		$yr13	=  $yr13 == 0  ?  12  :  $yr13 - 1;
	}
	
	$yrhr= 6 * (($yr + 3) % 4);		// Starting Hour of Day {0,6,12,18}
	if ($refh < $yrhr) {
		if ($yd == 0) {
			$yd		=  $yr   == 1  ?  365  :  364;
			$yr		=  $yr   == 0  ?    3  :  $yr - 1;
			$yr13	=  $yr13 == 0  ?   12  :  $yr13 - 1;
			$yrhr	=  $yrhr == 0  ?   18  :  $yrhr - 6;
		} else {
			$yd--;
		}
	}
	
	$refh -= $yrhr;
	if  ($refh < 0) {
		$refh += 24;
	}
	
	$mnth	= floor($yd / 20);					// Month 0-18 (18==isnemo)
	$d		= $yd % 20;							// Day 0-19
	$d9		= $yd % 9;							// 9 Day Cycle 0-8
	$d13s	= (9 * $yr13) % 13;
	$d13	= ($d13s + $yd) % 13;				// 13 Day Cycle 0-12
	$th		= $d - $d13;						// Trecena 0-19
	if ($th < 0) $th += 20;
	$qhr	= floor(($refm + $refh * 60) / 18);	// 1/4 Hour 0-79
	$hr		= floor($qhr / 4);					// Hour 0-19
	$qhrs	= $qhr % 20 + 1;					// 1/4 Hour Symbol
	
	//				12 11 10 09 08 07
	// 5  9 period	 1  5  9  4  4  8
	// 5 13 period	 4 12  7  2 10  5
	$isnemo = FALSE;
	if ($mnth == 18)
		$isnemo = TRUE;
	else if ($is130 && ($mnth == 18 || ($mnth == 17 && $d == 19))) {
		$isnemo = TRUE;
		$d++;
	}
	
	if ($isnemo) {
		$d9   = (5 * ($refy - 5)) % 9;		// 1922
		$d13  = (5 * ($refy - 12)) % 13;	// 1923
		//$d13 = 5 * $yr13 % 13;
		$d = $yd - 360;
		if ($d == 5) $d--;
		/*if ($yr == 0) {
			$d = $yd - 361;
		} else {
			
		}*/
		$d9 += $d;
		if ($d9 >= 9) {
			$d9 -= 9;
		}
		$d13 += $d;
		if ($d13 >= 13) {
			$d13 -= 13;
		}
		//$ns =  $yrn == 0  ?  15  :  5 * ($yrn-1);		// Nemontemi Start Day of Year
		$d += 5 * (($yr + 2) % 4);
		
		//$d9  = 1;
		//$d13 = 8;
	}
	
	$yr = (($yr + 3) % 4) + 1;
	$yr13++;
	$mnth++;
	$d++;
	$d9++;
	$d13++;
	$th++;
		
	/*for ($i = 11 ; $i <= 3000 ; $i+=4)
		if ( (((5 * ($i - 12)) % 13) + 5) == 13 )
			print_r($i.", ");*/
	
	
	$year = Array(" ", "Kalli", "Tochtli", "Akatl", "Tekpatl");
	$month = Array("Veintena", "Atlakaualo", "Tlakaxipeualiztli", "Tozoztontli", "Ueitozoztli", "Toxkatl", "Etzakualiztli", "Tekuiluitl", "Ueitekuiluitl", "Tlaxochimako", "Xokotl Uetzin", "Ochpaniztli", "Teotleko", "Tepeiluitl", "Kecholli", "Panketzaliztli", "Atemoztli", "Tititl", "Izkalli", "Nemontemi days");
	$trecena = Array("Trecena", "Tonakatekutli", "Chantiko", "Itzpapalotl", "Tezkatlipoka", "Tlauizkalpantekutli and Xiutekutli", "Tonatiu Tonalteotl and Teziztekatl", "Tlazoteotl and Tepeyolotli", "Xiutekutli and Xipe Totek", "Chalchiutotolin", "Xipe Totek", "Tlazoteotl and Tepeyolotli", "Patekatl Tezkatlipoka and Mayaual", "Chalchiuitlikue", "Ketzalkoatl", "Xochiketzalli and Tezkatlipoka", "Xoltl Tekutli", "Tlazoteotl", "Tonatiu Tonalteotl and Miktlantekutli", "Tlalokantekutli", "Ixnextli and Ueuekoyotl");
	$day = Array("Tonalli", "Zipaktli", "E'ekatl", "Kalli", "Kuetzpalli", "Koatl", "Mikiztli", "Mazatl", "Tochtli", "Atl", "Itzkuintli", "Ozomatli", "Malinalli", "Akatl", "Ozelotl", "Kuautli", "Kozkakuautli", "Ollin", "Tekpatl", "Kiauitl", "Xochitl");
	$daycomp = Array("Iluikapotzintli", "Xochipilli as Tonakatekutli and Tonakaziuatl", "E'ekatl Ketzalkoatl", "Tepeyolotli", "Ueuekoyotl", "Chalchiuitlikue", "Meztli Teziztekatl", "Tlalokantekutli", "Mayaual", "Xiutekutli", "Miktlantekutli", "Xochipilli as Zenteotl", "Patekatl", "Yayauki Tezkatlipoka Ixkimilli", "Tlazoteotl", "Xipe Totek", "Itzpapalotl", "Xolotekutli Ketzalkoatl", "Chalchiuitotolin", "Tonatiu Tonalteotl", "Xochiketzalli");
	$hour = Array("Zenteotl", "Zipaktonal", "Miktlantekutli", "Ketzalkoatl", "Xochipilli", "Xochiketzalli", "Tlalokantekutli", "Miktlantekutli", "Tlauizkalpantekutli", "Mixcoatl", "Xochipilli ", "Tonatiu Tonalteotl", "Tezkatlipoka", "Xipe Totek", "Makuilxochitl", "Mayaual", "Tlazoteotl", "Miktlanziuatl", "Chalchiuitlikue", "Xochiketzalli ");
	$hourenergy = Array("Creativity", "Activity", "Reflection", "Activity", "Creativity", "Creativity", "Activity", "Reflection", "Activity", "Creativity", "Creativity", "Activity", "Reflection", "Activity", "Creativity", "Creativity", "Activity", "Reflection", "Activity", "Creativity");
	$poualli = Array("Poualli", "Ze", "Ome", "Yei", "Naui", "Makuilli", "Chikoaze", "Chikome", "Chikoei", "Chiknaui", "Matlaktli", "Matlaktli-Ze", "Matlaktli-Ome", "Matlaktli-Yei", "Matlaktli-Naui", "Caxtolli", "Caxtolli-Ze", "Caxtolli-Ome", "Caxtolli-Yei", "Caxtolli-Naui", "Zempoualli");
	$wingcomp = Array("In Totopotzintli", "Xiu-Uitzitzilli", "Ketzaluitzitzilli", "Totli", "Zolin", "Kakalotl", "Chikuautli", "Itzpapalotl", "Itzkuautli", "Chalchiutotolin", "Tekolotl", "Alotl", "Ketzaltototl", "Kochotl");
	$compwingcomp = Array(" ", "Tlauizkalpantekutli", "Ixtliton", "Xochipilli", "Xipe Totek", "Yaotl", "Uauantli", "Xiutekutli", "Tlalokantekutli", "Tlalokantekutli", "Tezkatlipoka", "Tonatiu Tonalteotl", "Zenteotl", "Xochiketzalli");
	$nightcomp = Array("Youalpotzintli", "Xiutekutli", "Tezkatlipoka Ixkimilli", "Piltzintekutli", "Chalchiuitlikue", "Miktalantekutli", "Zenteotl", "Tlazoteotl", "Tepeyolotli", "Tlalok");
	
	
	/*switch ($lang) {
		case 'english':*/
			$desc_day = Array("Day",
			"The Crocodile: Beginning of evolution of all beings. The Initiator, number One even with no number Two.",
			"The Wind: Our Breath of life and the medium of all living beings; it carries the sounds and symbolizes creativity and harmony.",
			"The House: Our home, refuge and house of thoughts; a safe place for reflection and regrouping for the comprehension of all living beings.",
			"The Lizard: Our maternal womb. Representing the Mother Earth associated with fertility, nurturing and the capability of regeneration.",
			"The Serpent: Our knowledge and wisdom. Represents energy and fertility, and of the senses it represents the touch, and is associated with the twins and the earth.",
			"The Skull: Our silence and transformation. Represents the reevaluation of life and death and of things done and things left to be done. Don't Hesitate!",
			"The Deer: Symbolizes our agility, instinct, intuition, perception and sensibility, as well as all the Fauna. The deer is activated by the energy of the Sun and is a messenger of love and peace from the grandfathers.",
			"The Rabbit: Our multiplicity and taste perception. Represents the fertility of the earth and all living beings by the lunar influence. They are very independent, yet are always giving to and providing for others.",
			"The Water: Our growth. It is a vital element and it possibilizes life and purifies it. It adopts the shape of the container that holds it. It is the duality of the fire and has constant creating activities and is penetrating and perseverant.",
			"The Dog: Our loyalty and fidelity. He is a best friend and a guide with the capability to transform himself and all that surrounds him. He loves to travel, but will not forget where he comes from and always returns to his place of origin.",
			"The Monkey: Our grasping, comprehension and agility. The monkey exhibits mobility to all directions without regard to neither heights nor distances. He also symbolizes recreation and happiness, and it is also the symbol of the dance.",
			"The Herb: Our umbilical cord and the constant regeneration of nature. It also represents all Flora, these are all medicinal, if we use them wisely.",
			"The Reed, bamboo: Our internal self. Conduit of heat and energy. Represents intelligence, observation, analysis, memory and the sub-conscious. Through the subconscious he can be all places and see all things, even with his eyes bandaged. Akatl can unite the collective intentions to make even nature fulfill its mission.",
			"The Jaguar: Our listening. An audacious and tenacious guide and a champion of the just cause is a guardian of the house of creating energy, Teokalli",
			"<strong>The Eagle:</strong> Our vision; a solar symbol. This is the physical and spiritual renovation, purification and cleansing of ourselves and our environment. Here is the presence of freedom and liberty and a guardian of the house of creating energy, Teokalli.",
			"The Condor: Our youth and a moment of reflection and remedy. Reflect to find the teachings and remedy the faults. Find life where there is, apparently, no life.",
			"Movement: Our lips and the movement of our heart. The essence of life and of existence, directly related to activity and creativity and the constant movements of the universe.",
			"The Flint: Our tongue; the word, profound, pointed and sharp. This is a profound method of study and analysis to truly comprehend things and then produce enduring concepts.",
			"The Rain: Our teardrops, peaceful and furious at its time. Sensitivity, the concept of Tlalok, Tlal = earth, Ok = a drink = What the earth drinks, to give us life.",
			"The Flower: Our completion, maturity, artistic and spiritual creativity, ready to produce fruit and seed. It represents artistic and scientific creativity.");
//$daycomp = Array("Iluikapotzintli", "Xochipilli as Tonakatekutli and Tonakaziuatl", "E'ekatl Ketzalkoatl", "Tepeyolotli", "Ueuekoyotl", "Chalchiuitlikue", "Meztli Teziztekatl", "Tlalokantekutli", "Mayaual", "Xiutekutli", "Miktlantekutli", "Xochipilli as Zenteotl", "Patekatl", "Yayauki Tezkatlipoka Ixkimilli", "Tlazoteotl", "Xipe Totek", "Itzpapalotl", "Xolotekutli Ketzalkoatl", "Chalchiuitotolin", "Tonatiu Tonalteotl", "Xochiketzalli");
			$desc_daycomp = Array("Day Companion",
			"The Flower Child that guides photosynthesis through the Male Energies and Female Energies of the Sun Rays to give life.",
			"Precious Serpent, precious knowledge and wisdom, a precious twin, and our breath of life. ",
			"The Heart of the Mountain, a Jaguar. This is a representation fo Tezkatlipoca, giving us internal knowledge of events and of people and leads us to concentration and dedication.",
			"The old, old Coyote. Representing the game plan and experience. It is sensuality and very, very motherly.",
			"The Jade Waters that skirt us. Mother Earth is the lady with the jade skirt and she represents all the terrestrial waters, as well as tranquility.",
			"The Moon and the Carrier of the Caracol, the conch shell. This is the lunar influence on the tides and the psyche of humans and represents evolution and growing cosmic consciousness.",
			"The Guide of the Region of the Rain. What the earth drinks, the water from on high in all of its manifestations. This is the principal action to fertilize and produce sustenance. Tlalok belongs to the context of the science of life, Ketzalkoatl, uniting two life generating actions, heat and water.",
			"She who lives in the Maguey, the Agave. She is a protector and proportions incalculable ways of producing, obtaining and and maintaining home, dress and sustenance. She has a heart of honey and represents abundance in all things.",
			"The Guide of the Fire. It is the ultraviolet rays of the sun and the cosmos. He is the first guide of the day and of the night and he attributes Atlachinolli, the energy of one who has learned to be an internal warrior.",
			"The Guide of the place of Transformation and Rest. Your guide to that regenerating rest to achieve new consciousness and existence before returning to activity.",
			"Photosynthesis, the light and energy of the Sun through the plants to give us oxygen. Zenteotl is the principal generator of our sustenance and our health by planting.",
			"The One that cures with Plants. Represents the Xiuipatiliztli, the study and practice of herbal medicine.",
			"The Black Smoking of the Mirror. This is an ability to see without eyes and represents the conscience and memory. Assists in consciousness, dreams, visions and subconscious activity.",
			"Creating energy generated by love. Regeneration of creating energy by the mother earth of those things that spent their lives.",
			"The Red Smoking of the Mirror. Red smoking of the mirror, and our guide that changes us. The conscious organizing of time and space that leads to our transmutation.",
			"Obsidian Butterfly. The essence of earth's female energy with the strength and decision of the internal warrior, that is cold and cuts when appropriate.",
			"Companion, Complement and Precious Twin. It is venus as the evening star, twin of the morning star, which helps measure the movements in the cosmos and brings balance.",
			"Jade Turkey. It is the force of action, and at the same time the vanity of man. You must sacrifice the eyes to overcome the vanity and the ego so you may be as a torch that gives light and not smoke. Be a mirror for others, by learning to see with your heart.",
			"The Sun. The principal generating element of life on earth. He who has the heat and energy and gives us light and warmth, like a good father.",
			"The Precious Flower. This is the practical and scientific knowledge and all that is accomplished by love, beauty and positive endurance. In the minds of men it produces the poetic word, the flowery songs and the actions of flourishing thoughts.");
			$desc_wingcomp = Array("Winged Companion", "The Blue Hummingbird", "The Green Hummingbird", "The Hawk", "The Partridge", "The Crow", "The Hoot Owl", "The Obsidian Butterfly", "The Obsidian Eagle", "The Jade Turkey", "The Barn Owl", "The Macaw", "The Precious Bird", "The Parrot");
			$desc_nightcomp = Array("Night Companion", "The Guide of the Fire", "The Smokey Mirror; the guide that directs you to seeing the obscured reflection of your self opening the door to your inner conscience", "The Guide that Gives Color to Life", "The Jade Skirted Rivers of Mother Earth that brings life to her children", "The Guide from the Land of Transition", "The Guide of the Corn", "The one that generates and transforms everything which has completed its normal cycle", "The Heart of the Mountain", "The Liquid Essence of the Earth");
			$desc_trecena = Array("Thirteen Day Cycle", "Tonakatekutli", "Chantiko", "Itzpapalotl", "Tezkatlipoka", "Tlauizkalpantekutli and Xiutekutli", "Tonatiu Tonalteotl and Teziztekatl", "Tlazoteotl and Tepeyolotli", "Xiutekutli and Xipe Totek", "Chalchiutotolin", "Xipe Totek", "Tlazoteotl and Tepeyolotli", "Patekatl Tezkatlipoka and Mayaual", "Chalchiuitlikue", "Ketzalkoatl", "Xochiketzalli and Tezkatlipoka", "Xoltl Tekutli", "Tlazoteotl", "Tonatiu Tonalteotl and Miktlantekutli", "Tlalokantekutli", "Ixnextli and Ueuekoyotl");
			$desc_month = Array("Twenty Day Cycle", "What is left behind after the waters have left", "A shedding of the old that is realized in the people and in nature", "Our small vigil for the fertilizing rains", "Our great vigil for the generator of our sustenance", "Dry times, commemoration of Tezkatlipoka, our consciousness", "Time of eating the tender beans and corn", "Small day of celebration fo our guides", "Great day of celebration of our guides and the tender corn", "A time for the giving of flowers, flourishing", "Respectable descent of the ripening fruit", "the sweeping of our lives by our brother E'ekatl, the wind", "Rising energies, the coming of the regenerators of nature", "Days that the mountains rejoice and commemorate Tlalok, the rain", "Arrival of the birds with beautiful feathers, time of Mixkoatl, the hunter", "Raising of the precious standards, Uitzilopochtli, the will", "Descent of the waters, Uitzilopochtli, the internal war", "Shrinking of things in the winter, our mother earth has given us her fruits", "Resurgence of Totlazotlalnatzin, our beloved and respected mother earth", "A time for reflection, balance and adjustment of what has been lived");
			$desc_year = Array(" ", "The House: Our home, refuge and house of thoughts; a safe place for reflection and regrouping for the comprehension of all living beings", "The Rabbit: Our multiplicity and taste perception; the fertility of the earth and all living beings by the lunar influence; very independent, yet always giving to and providing of others", "The Reed, bamboo: Our internal self; conduit of heat and energy; intelligence, observation, analysis, memory and the sub-conscious", "The Flint: Our tongue; the word, profound, pointed and sharp; profound method of study and analysis to truly comprehend things and then produce enduring concepts");
			/*break;

		case 'spanish':
			$desc_day = Array(" ", "El Cocodrilo", "El Viento", "La Casa", "La Lagartija", "La Serpiente", "La Calavera", "El Venado", "El Conejo", "La Agua", "El Perro", "El Mono", "La Hierba", "El Carrizo", "El Jaguar", "El &Aacute;guila", "C&Oacute;ndor", "El Movimiento", "El Pedernal", "La Lluvia", "La Flor");
			$desc_daycomp = Array("Dia de", "el Cocodrilo", "el Viento", "la Casa", "la Lagartija", "la Serpiente", "la Calavera", "el Venado", "el Conejo", "la Agua", "el Perro", "el Mono", "la Hierba", "el Carrizo", "el Jaguar", "el &Aacute;guila", "el C&Oacute;ndor", "el Movimiento", "el Pedernal", "la Lluvia", "la Flor");
			break;
	};*/
	
?>

<script>
function addLoadEvent(func) {
    var oldonload = window.onload;
    if (typeof window.onload !== "function") {
        window.onload = func;
    } else {
        window.onload = function () {
            if (oldonload) {
                oldonload();
            }
            func();
        };
    }
 }
 var noscript = addLoadEvent(noscript);
 
 function noscript()
 {
   if (document.removeChild)
     {
       var div = document.getElementById("noscript");
           div.parentNode.removeChild(div);
     }
   else if (document.getElementById)
     {
       document.getElementById("noscript").style.display = "none";
     }
 }
 </script>

<div class="container_16" id="main" role="main">
	<h1>Ancient Mexika Chronological System</h1>
	<br />
	<h3><?php echo date("F j, ", $curtime).$refy.date(", g:i a", $curtime)." UTC";
				if($tz>0){echo "+";}
				echo $tz;?></h3>
	
	
	<div class="grid_2"><br /></div>
	
	<div class="grid_7"><br />
		
	</div>
	<div class="clear"></div>
	
	
	<div class="grid_2"></div>
	<div class="grid_4">
		<p class="item_desc" id="hour">
			A special time for <strong><?php echo $hourenergy[$hr];?></strong><br />

<div class="grid_6">
		<img src="../wp-content/uploads/img/hr/<?php echo $hour[$hr];?>.png" alt="<?php echo $hour[$hr];?>" width="250" />
	</div>
<br/>


			72 minute cycle: <strong><?php echo $hour[$hr];?></strong><br />
			18 minute cycle of <strong><?php echo $day[$qhrs];?></strong><span>&nbsp;<?php echo $desc_day[$qhrs];?>&nbsp;</span>
		</p>
	</div>
	<div class="grid_2"><br /></div>
	<div class="grid_7">

<img src="../wp-content/uploads/img/Tonalli/<?php echo $day[$d];?>.png" alt="<?php echo $day[$d];?>" width="350" />

		<h2 class="item_desc" id="day">
			Day/Dia/Tonalli:
				<strong><?php echo $d13." ".$day[$d];?></h2></strong><span>&nbsp;<?php echo $desc_day[$d];?>&nbsp;</span><br />
				
			<?php echo 'Iluikapotzintli: <span>&nbsp;'.$desc_daycomp[0].'&nbsp;</span>';?>
				<strong><?php echo $daycomp[$d];?></strong><span>&nbsp;<?php echo $desc_daycomp[$d];?>&nbsp;</span><br />
				<br/>  <hr>

<div class="grid_6"><br />
		<img src="../wp-content/uploads/img/Tonalpoualli/Ze <?php echo $day[$th];?>.png" alt="Ze <?php echo $day[$th];?>" width="250" />
	</div>
	<br/>
				<div class="grid_6" id="trecena">
		<p class="item_desc">
			<h2><?php echo 'Trecena: &nbsp;'.$desc_trecena[0].' - ';?>
				Ze <?php echo $day[$th];?></h2><span>&nbsp;<?php echo $desc_day[$th];?>&nbsp;</span><br />
			governed by <strong><?php echo $trecena[$th];?></strong>
		</p>
	</div>            

<img src="../wp-content/uploads/img/Iluikapozintli/<?php echo $nightcomp[$d9];?>.png" alt="<?php echo $nightcomp[$d9];?>" width="350" />

	<br/>


			<?php echo 'Youalpotzintli: <span>&nbsp;'.$desc_nightcomp[0].'&nbsp;</span>';?>
				<h2><?php echo $nightcomp[$d9];?></h2><span>&nbsp;<?php echo $desc_nightcomp[$d9];?>&nbsp;</span><br />
<br/>

<img src="../wp-content/uploads/img/Tototzintli/<?php echo $wingcomp[$d13];?>.png" alt="<?php echo $wingcomp[$d13];?>" width="350" />
<br/>


			<?php echo 'In Totopotzintli: <h2>&nbsp;'.$desc_wingcomp[0].'&nbsp;</h2>';?>
				<h2><?php echo $wingcomp[$d13];?></h2><span>&nbsp;<?php echo $desc_wingcomp[$d13];?>&nbsp;</span>
		</p>
	</div>
	<div class="clear"></div>
	

	
	<div class="grid_1"><br /></div>
	<div class="grid_6"><br />
		
	</div>
	<div class="grid_1"><br /></div>
	<div class="grid_1">

<hr>

		<img src="../wp-content/uploads/img/Poualli/<?php echo $poualli[$yr13];?>.png" alt="<?php echo $poualli[$yr13];?>" width="70" />
	</div>


	<div class="grid_4">
		<img src="../wp-content/uploads/img/Tonalli/<?php echo $year[$yr];?>.png" alt="<?php echo $year[$yr];?>" width="350" />
		<br />

<!--description of year-->
		<h2 class="item_desc" id="year">
			Year: </h2>
				

<strong><?php echo $yr13." ".$year[$yr];?></strong><span>&nbsp; <?php echo $desc_year[$yr];?>&nbsp;</span>
			<br />

	</div>
	<div class="clear"></div>
	
	
	<div class="grid_1"><br /></div>
	<div class="grid_6" id="trecena">
			</div>
	<div class="grid_2"><br /></div>

			<?php 
if($isnemo == 0) 


echo '<h2>Veintena:</h2> <span>&nbsp;'.$desc_month[0].'&nbsp;</span>';?>
				<strong><?php echo $month[$mnth];?></strong><span>&nbsp;<?php echo $desc_month[$mnth];?>&nbsp;</span>
		</p>
	</div>
	<div class="clear"></div><br />
	
	<h5 id="noscript">Javascript is disabled.</h5>
	
	<?


