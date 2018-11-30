<?php
$array['head']['lng'] = 'en';
$array['head']['title'] = 'Panchaga - 5anga.info';
$array['head']['headname'] = 'Panchaga';
$array['head']['descr'] = 'Panchanga everywhere. Hindu Holydays.';

$array['txt']['note'] = "Panchangam is always location specific. Please select location or coordinates";
$array['txt']['calcul'] = "Computing...";
$array['txt']['help'] = "./inc/enindex.html";
$array['txt']['helpcity'] = "Type name of nearest city";
$array['txt']['helpcoo'] = "Set coordination and time zone";
$array['txt']['city'] = "City:";
$array['txt']['long'] = "Longitude:";
$array['txt']['lat'] = "Latitude:";
$array['txt']['longwe'] = array("West", "East");
$array['txt']['latns'] = array("South", "North");
$array['txt']['height'] = "Altitude:";
$array['txt']['height2'] = " meters";
$array['txt']['tz'] = "Time Zone:";
$array['txt']['calc'] = "Calculate";
$array['txt']['north_s'] = "N. ";
$array['txt']['south_s'] = "S. ";
$array['txt']['east_s'] = "E. ";
$array['txt']['west_s'] = "W. ";
$array['txt']['year'] = "Year:";
$array['txt']['month'] = "Month:";
$array['txt']['chmo'] = "Choose the month";
$array['txt']['monarr'] = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
$array['txt']['week'] = array("","Mon","Tue","Wed","Thr","Fri","Sat","Sun");

$array['p']['sm'] = array("Mesha", "Vrishabha", "Mithuna", "Karkataka", "Simha", "Kanya", "Thula", "Vrischika", "Dhanus", "Makara", "Kumbha", "Meena");
$array['p']['chm'] = array("", "Chaitra", "Vaishakha", "Jyeshta", "Aashada", "Shravana", "Bhadrapada", "Aashwayuja", "Karthika", "Marghashira", "Pushya", "Magha", "Phalguna");
$array['p']['paksha'] = array("Krishna paksha ", "Shukla paksha ");
$array['p']['tithi'] = array("Amavasya","Prathipath","Dwitheeya","Thrutheeya","Chathurthy","Panchami","Shashti","Sapthami","Ashtami","Navami","Dashami","Ekadashi","Dwadashi","Thrayodashi","Chathurdashi","Pournami");
$array['p']['naksh'] = array("","Ashwini","Bharani","Krittika","Rohini","Mrigasira","Aridra","Punarvasu","Pushya","Aslesha","Makha","Poorva Phalguni","Uttara Phalguni","Hasta","Chitra","Swati","Vishakha","Anuradha","Jyeshta","Moola","Purvashadha","Uttarashadha","Sravana","Dhanishta","Shatabhisa","Purvabhadra","Uttarabhadra","Revati");
$array['p']['ecl'] = array("","Annular eclipse","Non-central eclipse","Total eclipse"," Annular-total (hybrid) eclipse","Partial eclipse","Penumbral eclipse");
$array['p']['ecl_par'] = "Partial eclipse:";
$array['p']['ecl_penu'] = "Penumbral eclipse:";
$array['p']['ecl_tot'] = "Total eclipse:";
$array['p']['ecl_max'] = "Eclipse`s maximum:";
$array['p']['ecl_sar'] = "saros: ";
$array['p']['ecl_coo'] = "Maximum`s position:";
$array['p']['ecl_obs'] = "The eclipse can be observed in these coordinates.";
$array['p']['ecl_noobs'] = "In these coordinates the eclipse is not observed.";

$array['txtp']['sm'] = 'Solar month:';
$array['txtp']['mm'] = 'Moon month:';
$array['txtp']['mml'] = 'adhika';
$array['txtp']['full'] = 'Full day';
$array['txtp']['t'] = 'Tithi:';
$array['txtp']['na'] = 'Nakshatra:';
$array['txtp']['sr'] = 'Sun rise:';
$array['txtp']['ss'] = 'Sun set:';
$array['txtp']['mr'] = 'Moon rise:';
$array['txtp']['ms'] = 'Moon set:';
$array['txtp']['time'] = 'H:mm:ss';
$array['txtp']['from'] = '[from: ]H:mm:ss';
$array['txtp']['beg'] = 'begin: ';
$array['txtp']['till'] = '[till: ]H:mm:ss';
$array['txtp']['after'] = '[after:] H:mm:ss';
$array['txtp']['end'] = 'end: ';
$array['txtp']['date'] = 'D MMMM YYYY';
$array['txtp']['data'] = 'D.M.YYYY H:mm:ss';

$array['holy']['ek'] = array(1=>"Unmilani Mahadvadashi", 2=>"Vyanjuli Mahadvadashi", 3=>"Trisprsa Mahadvadashi", 4=>"Pakshavardhini Mahadvadashi", 5=>"Jaya Mahadvadashi", 6=>"Jayanti Mahadvadashi", 7=>"Papa nasini Mahadvadashi", 8=>"Vijaya Mahadvadashi", 9=>"", 10=>"", 11=>"", 12=>"", 13=>"");

//$array['txt']['error_input_year'] = 'Укажите год в диапазоне с 1800 до 2399';
//$array['txt']['error_input_month'] = 'Укажите месяц в диапазоне от 1 до 12';
$array['error']['input_lat'] = 'Set location`s latitude -90 to 90';
$array['error']['input_long'] = 'Set location`s longitude -179 to 180';
//$array['txt']['error_lat_m'] = 'Укажите минуты широты от 0 до 59';
//$array['txt']['error_long_m'] = 'Укажите минуты долготы от 0 до 59';
//$array['txt']['error_height1'] = 'Высота над уровнем моря не должна быть меньше 0м';
//$array['txt']['error_height2'] = 'Высота над уровнем моря должна быть меньше 9000м';
//$array['txt']['error_polar'] = 'За полярным кругом может не быть восходов/заходов солнца, без них расчеты могут быть неточными';
$array['error']['tz'] = 'Set location`s time zone';
$array['error']['dial'] = 'Not enoght settings';
$array['error']['arc'] = 'Panchanga`s events calculation are impossible during the polar days or nights';
$array['error']['net'] = 'No connection with the server, please turn off AdBlock on your web browser';

//print_r($array);
//echo json_encode($array, JSON_FORCE_OBJECT);
?>