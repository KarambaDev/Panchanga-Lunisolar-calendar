<?php
require_once(dirname(__FILE__) . '/../noindex/configuration.php');
$mysql_link = new mysqli($GLOBALS['mysql_server'], $GLOBALS['mysql_user'], $GLOBALS['mysql_password'], $GLOBALS['dbname']);
if (mysqli_connect_errno()) {
    die("Connect failed: %s\n". mysqli_connect_error().'</br>');
	}
else { 
	echo 'Connected successfully</br>';
	
	$mysql_link->query("DROP TABLE holyday");
	$mysql_link->query("DROP TABLE holyname");
	$mysql_link->set_charset("utf8");
	if (!$mysql_link->query('CREATE TABLE holyday (id INT, enable TINYINT, owner_id INT, masa_type TINYINT, masa TINYINT, b_tithi TINYINT, b_paksha TINYINT, special TINYINT, sp_range TINYINT, kshaya TINYINT, adhika TINYINT, e_tithi TINYINT, e_paksha TINYINT, e_special TINYINT, e_pure TINYINT, week TEXT,  PRIMARY KEY (id))'))
		{
		die("Could not create table <b>holyday</b>: " . mysqli_connect_error(). '</br>');
		}
	else {
		echo "Table <b>holyday</b> was successfully created</br>";

// Ganesha chaturthi vrat
$id[] = "INSERT INTO holyday VALUES (3, 1, 0, 0, NULL, 4, 0, 1, 1, 2, NULL, NULL, NULL, NULL, NULL, NULL);";
$id[] = "INSERT INTO holyday VALUES (4, 1, 0, 0, NULL, 4, 1, 1, 1, 2, NULL, NULL, NULL, NULL, NULL, NULL);";

// Shiva pradosha vrat
$id[] = "INSERT INTO holyday VALUES (5, 1, 0, 0, NULL, 13, NULL, 2, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL);";
//$id[] = "INSERT INTO holyday VALUES (4, 1, 0, 0, NULL, 1, 1, NULL, NULL, NULL, NULL, 9, 1, NULL, NULL, NULL);";
// Nava Ratri
$id[] = "INSERT INTO holyday VALUES (6, 1, 0, 0, 1, 1, 1, NULL, NULL, NULL, NULL, 9, 1, NULL, NULL, NULL);";
$id[] = "INSERT INTO holyday VALUES (7, 1, 0, 0, 4, 1, 1, NULL, NULL, NULL, NULL, 9, 1, NULL, NULL, NULL);";
$id[] = "INSERT INTO holyday VALUES (8, 1, 0, 0, 7, 1, 1, NULL, NULL, NULL, NULL, 9, 1, NULL, NULL, NULL);";
$id[] = "INSERT INTO holyday VALUES (9, 1, 0, 0, 10, 1, 1, NULL, NULL, NULL, NULL, 9, 1, NULL, NULL, NULL);";
$id[] = "INSERT INTO holyday VALUES (10, 1, 0, 0, 11, 8, 1, NULL, NULL, NULL, NULL, 15, 1, NULL, NULL, NULL);";
// Maha Shiva ratri
$id[] = "INSERT INTO holyday VALUES (11, 1, 0, 1, 12, 14, 0, 3, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL);";
// Durga vrat
$id[] = "INSERT INTO holyday VALUES (12, 1, 0, 0, NULL, 8, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);";
$id[] = "INSERT INTO holyday VALUES (13, 1, 0, 0, NULL, 9, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);";
$id[] = "INSERT INTO holyday VALUES (14, 1, 0, 0, NULL, 14, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);";
$id[] = "INSERT INTO holyday VALUES (15, 1, 0, 0, 1, 15, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);";
//$id[] = "INSERT INTO holyday VALUES (16, 1, 0, 0, 1, 15, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);";
/*
CREATE TABLE holyday (id INT, enable TINYINT, owner_id INT, masa_type TINYINT, masa TINYINT, b_tithi TINYINT, b_paksha TINYINT, special TINYINT, sp_range TINYINT, kshaya TINYINT, adhika TINYINT, e_tithi TINYINT, e_paksha TINYINT, e_special TINYINT, e_pure TINYINT, week TINYINT,  PRIMARY KEY (id))

id 
enable		- NULL - disable, 1 - enable
owner_id	- владелец события (0 -default, общая группа)
masa_type 	- тип календаря 0-амавасьянт, 1-пурнимант, 2-солнечный (default=0)
masa 		- лунный месяц (NULL если любой)
b_tithi 	- титхи в которые случается начало event (NULL если титхи не важны [обязательно должен быть задан masa и week]) (1-15)
b_paksha 	- пакша в которую случается event (NULL если пакша не важна, 0 - Кришна пакша, 1 - Шукла пакша)
special 	- на какое событие дня должны попасть титхи (default=0 - восход солнца, 1 - восход луны, 2 - прадоша, 3 - ratrimana, 4 - madhyahana)
sp_range	- NULL - весь день, 1 - интервал от восхода до special, 2 - особый интервал (зависит от special)
kshaya		- что делать если kshaya, 1 - сдвиг в прошлое, 2(NULL) - в будущее, 3 - по времени начала титх, 4 - по времени окончания титх
adhika		- что делать если adhika, NULL - первый попавший день, 1 - сдвиг в прошлое, 2 - второй попавший день, 3 - в будущее
e_tithi TINYINT, e_paksha TINYINT, e_special TINYINT, e_pure TINYINT, week TINYINT

*/

		$count_id = count($id);
		for ($i=0; $i<$count_id; $i++) {	$mysql_link->query($id[$i]);	}
		}
	if (!$mysql_link->query('CREATE TABLE holyname (id INT, lang TINYINT, d0 TEXT, d1 TEXT, d2 TEXT, d3 TEXT, d4 TEXT, d5 TEXT, d6 TEXT, d7 TEXT, name TEXT, color TEXT, picture TEXT, descr TEXT, comment TEXT, url1 TEXT, urln1 TEXT, url2 TEXT, urln2 TEXT, url3 TEXT, urln3 TEXT)'))
		{
		die("Could not create table <b>holyname</b>: " . mysqli_connect_error(). '</br>');
		}
	else {
		echo "Table <b>holyname</b> was successfully created</br>";

$name[] = "INSERT INTO holyname VALUES (1, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Солнечное затмение', '000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Sun Eclipse', '000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (2, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Лунное затмение', '000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (2, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Moon Eclipse', '000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (3, 2, 'Санкашта', NULL, 'Ангарак', NULL, NULL, NULL, NULL, NULL, 'Ганеша чатуртхи врата', 'DD8800', NULL, 'Пост, посвящённый богу Ганеше(Ганапати). Ганапати является повелителем ганов, он охраняет высшие сферы, где обитают все божества. Почитание Ганеши устраняет препятствия в любых начинаниях и на пути к Высшему. Врата(пост) начинается в чатуртхи и заканчивается с восходом луны.', NULL, NULL, NULL, NULL, NULL, NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (3, 1, 'Sankashta', NULL, 'Angarak', NULL, NULL, NULL, NULL, NULL, 'Ganesha chaturthi vrat', 'DD8800', NULL, 'It is believed that Lord Ganesh bestows his presence on earth for all his devotees during this festival. It is the day Shiva declared his son Ganesha as superior to all the gods, barring Vishnu, Lakshmi, Shiva and Parvati. Ganesha is widely worshipped as the god of wisdom, prosperity and good fortune and traditionally invoked at the beginning of any new venture or at the start of travel.', NULL, 'http://en.wikipedia.org/wiki/Ganesha_Chaturthi', 'Ganesha Chaturthi', NULL, NULL, NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (4, 2, 'Винаяка', NULL, 'Ангарак', NULL, NULL, NULL, NULL, NULL, 'Ганеша чатуртхи врата', 'DD8800', NULL, 'Пост, посвящённый богу Ганеше(Ганапати). Ганапати является повелителем ганов, он охраняет высшие сферы, где обитают все божества. Почитание Ганеши устраняет препятствия в любых начинаниях и на пути к Высшему. Врата(пост) начинается в чатуртхи и заканчивается с восходом луны.', NULL, NULL, NULL, NULL, NULL, NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (4, 1, 'Vinayaka', NULL, 'Angarak', NULL, NULL, NULL, NULL, NULL, 'Ganesha chaturthi vrat', 'DD8800', NULL, 'It is believed that Lord Ganesh bestows his presence on earth for all his devotees during this festival. It is the day Shiva declared his son Ganesha as superior to all the gods, barring Vishnu, Lakshmi, Shiva and Parvati. Ganesha is widely worshipped as the god of wisdom, prosperity and good fortune and traditionally invoked at the beginning of any new venture or at the start of travel.', NULL, 'http://en.wikipedia.org/wiki/Ganesha_Chaturthi', 'GaneshaChaturthi', NULL, NULL, NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (5, 2, NULL, 'Сом', 'Бхаума', NULL, NULL, NULL, 'Шани', NULL, 'Шива прадоша врата', 'DD8800', NULL, 'Прадоша-врата происходит дважды в месяц - на 13-й день (трайодаши)  шукла-пакши (растущей) и кришна-пакши (убывающей) фаз Луны. Более важной считается та Прадоша, которая попадает на кришна-пакшу. Говорят, что среди всех пудж Господу Шиве, Прадоша пуджа наиболее благоприятна, освобождает от грехов и дарует Мокшу.', '', 'http://nathas.org/dictionary/pradosha-vrata/?sphrase_id=635', 'Прадоша-врата', NULL, NULL, NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (5, 1, NULL, 'Som', 'Bhauma', NULL, NULL, NULL, 'Shani', NULL, 'Shiva pradosha vrat', 'DD8800', NULL, 'is a bimonthly occasion on the thirteenth day of every fortnight in Hindu calendar. It is closely connected with the worship of Hindu god Shiva. The auspicious 3 hour period, 1.5 hours before and after the sunset is one of the optimum time for worship of Lord Shiva. The fast or vow performed during the period is called Pradosha vrata. A devotee should wear rudraksha, Vibhuti and worship Lord Shiva by Abisheka, Sandal paste, Bilva leaves, Fragrance, Deepa & Neivaedyaas (Food offerings).', '', 'http://en.wikipedia.org/Pradosha', NULL, NULL, NULL, NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (6, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Васант Нава Ратри', 'FF0000', NULL, 'Васант или Басанта Наваратри. Празднуется в первый день светлой части (шукла пакша) месяца Чаитра. Этот праздник отмечается весной и знаменует начало нового года по лунному календарю.', 'В переводе с санскрита слово «наваратри» означает «девять ночей». В ходе этого праздника, продолжающегося десять дней и девять ночей, индуисты поклоняются девяти ипостасям Шакти/Деви — женской форме Бога. Кульминация фестиваля приходится на десятый его день, называемый Виджая-дашами.', 'http://ru.wikipedia.org/wiki/Наваратри', 'Наваратри', 'http://nathi.ru/read/articles/Navaratri', 'Наваратри', NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (6, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vasanta Nava Ratri', 'FF0000', NULL, 'Basanta Navaratri, also known as Vasant Navaratri, is the festival of nine days dedicated to the nine forms of Shakti (Mother Goddess) in the spring season (March–April). It is also known as Chaitra Navaratri. The nine days of festival are also known as Raama Navratri.', 'Navratri is a festival dedicated to the worship of the Hindu deity Durga. The word Navaratri literally means nine nights in Sanskrit, nava meaning nine and ratri meaning nights. During these nine nights and ten days, nine forms of Shakti/Devi are worshiped.', 'http://en.wikipedia.org/wiki/Navaratri', 'Navaratri', NULL, NULL, NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (7, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ашадха Нава Ратри', 'FF0000', NULL, 'Гупта Наваратри также называемое Ашадха или Гаятри или Шакамбари Наваратри. Празднуется в первый день светлой части (шукла пакша) месяца Ашадха.', 'В переводе с санскрита слово «наваратри» означает «девять ночей». В ходе этого праздника, продолжающегося десять дней и девять ночей, индуисты поклоняются девяти ипостасям Шакти/Деви — женской форме Бога. Кульминация фестиваля приходится на десятый его день, называемый Виджая-дашами.', 'http://ru.wikipedia.org/wiki/Наваратри', 'Наваратри', 'http://nathi.ru/read/articles/Navaratri', 'Наваратри', NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (7, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ashadha Nava Ratri', 'FF0000', NULL, 'Gupta Navaratri, also referred as Ashadha or Gayatri or Shakambhari Navaratri, is nine days dedicated to the nine forms of Shakti (Mother Goddess) in the month of Ashadha (June–July). Gupta Navaratri is observed during the Ashadha Shukla Paksha (waxing phase of moon).', 'Navratri is a festival dedicated to the worship of the Hindu deity Durga. The word Navaratri literally means nine nights in Sanskrit, nava meaning nine and ratri meaning nights. During these nine nights and ten days, nine forms of Shakti/Devi are worshiped.', 'http://en.wikipedia.org/wiki/Navaratri', 'Navaratri', NULL, NULL, NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (8, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Шарада Нава Ратри', 'FF0000', NULL, 'Это самое важно из Наваратри в году, так же называется Маха Наваратри (Великое Наваратри). Празднуется в первый день светлой части (шукла пакша) месяца Ашваюджа. Так же этот праздник известен под названием Шарад Наваратри, знаменующий начало зимы и нового года.', 'В переводе с санскрита слово «наваратри» означает «девять ночей». В ходе этого праздника, продолжающегося десять дней и девять ночей, индуисты поклоняются девяти ипостасям Шакти/Деви — женской форме Бога. Кульминация фестиваля приходится на десятый его день, называемый Виджая-дашами.', 'http://ru.wikipedia.org/wiki/Наваратри', 'Наваратри', 'http://nathi.ru/read/articles/Navaratri', 'Наваратри', NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (8, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Sharada Nava Ratri', 'FF0000', NULL, 'This is the most important of the Navaratris. It is simply called Maha Navaratri (the Great Navratri) and is celebrated in the pratipada (first day) of the bright fortnight of the lunar month of Ashvina. Also known as Sharad Navaratri, as it is celebrated during Sharad (beginning of winter, September–October).', 'Navratri is a festival dedicated to the worship of the Hindu deity Durga. The word Navaratri literally means nine nights in Sanskrit, nava meaning nine and ratri meaning nights. During these nine nights and ten days, nine forms of Shakti/Devi are worshiped.', 'http://en.wikipedia.org/wiki/Navaratri', 'Navaratri', NULL, NULL, NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (9, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Пауш Нава Ратри', 'FF0000', NULL, 'Так же называется Шакамбари Наваратри. Это один из скрытых (Гупта) Наваратри. Празднуется с восьмого дня, до Амавасьи светлой части (шукла пакша) месяца Пауш.', 'Мать Шакамбари является инкарнацией Бхагавати Деви. Считается, что Бхагавати Деви воплотилась как Шакамбари для устранения голода на Земле, она так же известна как богиня фруктов, овощей и съедобных листьев', 'http://ru.wikipedia.org/wiki/Наваратри', 'Наваратри', 'http://nathi.ru/read/articles/Navaratri', 'Наваратри', NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (9, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Paush Nava Ratri', 'FF0000', NULL, 'Paush Navaratri also known as Shakambari Navaratri. Celebrated from the ashtami (eighth day) to the Amavasia of the bright fortnight of the Paush lunar month.', 'Shakambari Mata is incarnation of Devi Bhagwati. It is believed that Devi Bhagwati incarnated as Shakambari to mitigate famine and severe food crisis on the Earth. She is also known as Goddess of vegetables, fruits and green leaves and depicted with green surroundings of fruits and vegetables.', NULL, NULL, NULL, NULL, NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (10, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Магха Нава Ратри', 'FF0000', NULL, 'Магха Наваратри - также известна как Гупта Наваратри. Празднуется в первый день светлой части (шукла пакша) месяца Пауш.', 'В переводе с санскрита слово «наваратри» означает «девять ночей». В ходе этого праздника, продолжающегося десять дней и девять ночей, индуисты поклоняются девяти ипостасям Шакти/Деви — женской форме Бога. Кульминация фестиваля приходится на десятый его день, называемый Виджая-дашами.', 'http://ru.wikipedia.org/wiki/Наваратри', 'Наваратри', 'http://nathi.ru/read/articles/Navaratri', 'Наваратри', NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (10, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Magha Nava Ratri', 'FF0000', NULL, 'Magha Navaratri, also referred as Gupta Navaratri, is nine days dedicated to the nine forms of Shakti (Mother Goddess) in the month of Magha (January–February). Magha Navaratri is observed during the Magha Shukla Paksha (waxing phase of moon).', 'Navratri is a festival dedicated to the worship of the Hindu deity Durga. The word Navaratri literally means nine nights in Sanskrit, nava meaning nine and ratri meaning nights. During these nine nights and ten days, nine forms of Shakti/Devi are worshiped.', 'http://en.wikipedia.org/wiki/Navaratri', 'Navaratri', NULL, NULL, NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (11, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Маха Шива ратри', 'FF0000', NULL, 'Праздник начинается с восходом солнца в день Шиваратри и продолжается всю ночь напролет в храмах и у домашних алтарей, этот день проходит в молитвах, чтении мантр, пении гимнов и поклонении Шиве. Шиваиты в этот день постятся, не едят и не пьют и даже прасад Махашиваратри. Его можно есть только на следующий день. Многие индусы участвуют в джагран (jaagran), всенощном бдении, в различных храмах Шивы по всей стране. Шиваиты считают, что искреннее соблюдение ритуалов и исполнение Шиваратри пуджи всю ночь освобождает их от всех своих грехов и дает милость Шивы в виде освобождения от цикла перерождений.', '', 'http://ru.wikipedia.org/wiki/Маха-Шиваратри', NULL, NULL, NULL, NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (11, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Maha Shiva ratri', 'FF0000', NULL, 'It is also known as padmarajarathri. Alternate common names/spellings include Maha Sivaratri, Shivaratri, Sivarathri, and Shivaratri. Shivaratri literally means the great night of Shiva or the night of Shiva. It is celebrated every year on the 13th night/14th day of the Maagha or Phalguna month of the Hindu calendar. Since many different calendars are followed by various ethno-linguistic groups of India, the month and the Tithi name are not uniform all over India. Celebrated in the dark fortnight or Krishna Paksha(waning moon) of the month of Maagha according to the Shalivahana or Gujarati Vikrama or Phalguna according to the Vikrama era.', '', 'http://en.wikipedia.org/wiki/Maha_Shivaratri', NULL, NULL, NULL, NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (12, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Дурга аштами врата', 'DD8800', NULL, 'Этот день благоприятен для соблюдение обетов (враты) и почитания Богини Матери Дурги.', 'В Деви-Махатмья сказано:<br>Те, кто с преданностью и безраздельной сосредоточенностью будут внимать этому высшему прославлению на восьмой, четырнадцатый и девятый дни (светлой половины месяца).<br>Не испытают зла, бед от дурных деяний, и нищеты, и разлуки с милым сердцу.<br>Ни страха от врагов, грабителей, царей, оружия, огня, наводнения.', NULL, NULL, NULL, NULL, NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (12, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Durga ashtami vrat', 'DD8800', NULL, 'Shukla ashtami is a day dedicated to Hindu deity Durga Devi. Devotees observe a fast during this day and night.', 'Devi-Mahatmyam:<br>All those who sing and praise the story of the death of Madhu, Kaidabha, Mahishasura, Shumbha and Nishumbha and all those with close attention hear these great stories of my greatness on Ashtami (eighth day after new and full moon), Chathurthi (fourth day after new and full moon) and Navami (ninth day after new and full moon) will never have sins, no danger by doing bad actions, suffer no poverty and have no separation from people who love them.', NULL, NULL, NULL, NULL, NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (13, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Дурга навами врата', 'DD8800', NULL, 'Этот день благоприятен для соблюдение обетов (враты) и почитания Богини Матери Дурги.', 'В Деви-Махатмья сказано:<br>Те, кто с преданностью и безраздельной сосредоточенностью будут внимать этому высшему прославлению на восьмой, четырнадцатый и девятый дни (светлой половины месяца).<br>Не испытают зла, бед от дурных деяний, и нищеты, и разлуки с милым сердцу.<br>Ни страха от врагов, грабителей, царей, оружия, огня, наводнения.', NULL, NULL, NULL, NULL, NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (13, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Durga navami vrat', 'DD8800', NULL, 'Shukla ashtami is a day dedicated to Hindu deity Durga Devi. Devotees observe a fast during this day and night.', 'Devi-Mahatmyam:<br>All those who sing and praise the story of the death of Madhu, Kaidabha, Mahishasura, Shumbha and Nishumbha and all those with close attention hear these great stories of my greatness on Ashtami (eighth day after new and full moon), Chathurthi (fourth day after new and full moon) and Navami (ninth day after new and full moon) will never have sins, no danger by doing bad actions, suffer no poverty and have no separation from people who love them.', NULL, NULL, NULL, NULL, NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (14, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Дурга чатурдаши врата', 'DD8800', NULL, 'Этот день благоприятен для соблюдение обетов (враты) и почитания Богини Матери Дурги.', 'В Деви-Махатмья сказано:<br>Те, кто с преданностью и безраздельной сосредоточенностью будут внимать этому высшему прославлению на восьмой, четырнадцатый и девятый дни (светлой половины месяца).<br>Не испытают зла, бед от дурных деяний, и нищеты, и разлуки с милым сердцу.<br>Ни страха от врагов, грабителей, царей, оружия, огня, наводнения.', NULL, NULL, NULL, NULL, NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (14, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Durga chaturdashi vrat', 'DD8800', NULL, 'Shukla ashtami is a day dedicated to Hindu deity Durga Devi. Devotees observe a fast during this day and night.', 'Devi-Mahatmyam:<br>All those who sing and praise the story of the death of Madhu, Kaidabha, Mahishasura, Shumbha and Nishumbha and all those with close attention hear these great stories of my greatness on Ashtami (eighth day after new and full moon), Chathurthi (fourth day after new and full moon) and Navami (ninth day after new and full moon) will never have sins, no danger by doing bad actions, suffer no poverty and have no separation from people who love them', NULL, NULL, NULL, NULL, NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (15, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Хануман Джаянти', 'FF0000', NULL, 'Хануман – это одновременно идеальный йоги, бхакта, карми, тьяги и джняни.', 'Согласно преданиям, Хануман обладал необыкновенными мистическими способностями: он мог пересечь океан одним прыжком, без труда перенести гору на своей спине и летать с ней по воздуху на длинные расстояния, становиться огромным или крошечным, когда пожелает и т.п. Но обладая всеми этими способностями, он не имел ни эго, ни единой мысли «я» и «мое», он полностью посвятил свою индивидуальность Высшему Духу, воплощенному в облике Рамы.', 'http://nathi.ru/read/legends/hanuman/worship_of_hanuman.php', 'Особенности почитания Ханумана', NULL, NULL, NULL, NULL);";
$name[] = "INSERT INTO holyname VALUES (15, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Hanuman Jayanti', 'FF0000', NULL, 'Hanuman is an ardent devotee of Lord Rama, and is worshipped for his unflinching devotion to the god. From early morning, devotees flock Hanuman temples to worship him.', 'Hanuman Jayanti is an important festival of Hindus. Hanuman is the symbol of strength and energy. Hanuman is said to be able to assume any form at will, wield rocks, move mountains, dart through the air, seize the clouds and rival Garuda in swiftness of flight. He is worshipped in folk tradition as a deity with magical powers and the ability to conquer evil spirits.', 'http://en.wikipedia.org/wiki/Hanuman_Jayanti', 'Hanuman_Jayanti', NULL, NULL, NULL, NULL);";
/*
CREATE TABLE holyname (id INT, lang TINYINT, d0 TEXT, d1 TEXT, d2 TEXT, d3 TEXT, d4 TEXT, d5 TEXT, d6 TEXT, d7 TEXT, name TEXT, color TEXT, picture TEXT, descr TEXT, comment TEXT, url1 TEXT, urln1 TEXT, url2 TEXT, urln2 TEXT, url3 TEXT, urln3 TEXT)

id - 
lang 		- ru, eng ... (en=1, ru=2,...)
d0			- Строка добавляется перед названием события, заменяется элементами d1-d7
d1-d7		- Строка добавляется перед названием события
name		- Название события
color		- Цвет названия события
picture		- Изображение
descr		- Описание события
comment		- Комментарии к событию
url1		- Ссылка на куда-то...
urln1		- Название ссылки url
url2		- Ссылка на куда-то...
urln2		- Название ссылки url
url3		- Ссылка на куда-то...
urln3		- Название ссылки url
*/
		$count_name = count($name);
		for ($i=0; $i<$count_name; $i++) {	$mysql_link->query($name[$i]);	print_r($mysql_link->error);}
		}
	$query = $mysql_link->query("SELECT * FROM holyday;");
	while($result = $query->fetch_array(MYSQLI_ASSOC)){ print_r($result); }
	$query = $mysql_link->query("SELECT * FROM holyname;");
	while($result = $query->fetch_array(MYSQLI_ASSOC)){ print_r($result); }
	}

$mysql_link->close();
?>