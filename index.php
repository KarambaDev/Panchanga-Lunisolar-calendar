<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html> 
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="description">
	<meta http-equiv="content-language" content="ru"/>
	<title>Паньчанга - 5anga.info</title>
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
	<link rel="icon" href="/favicon.ico" type="image/x-icon">
	<link rel="apple-touch-icon" sizes="192x192" href="images/fav/favicon-192x192.png">
	<link rel="icon" type="image/png" sizes="192x192" href="images/fav/favicon-192x192.png">
	<meta name="msapplication-config" content="browserconfig.xml">
	<link rel="stylesheet" href="/css/calendar.css">
	<link rel="stylesheet" href="/css/custom-theme/jquery-ui-1.10.2.custom.css">
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
	<script src="/js/jquery-ui-1.10.2.custom.min.js"></script>
	<script src="/js/jcookies.min.js"></script>
	<script src="/js/jquery.history.js"></script>
	<script src="/js/moment-with-locales.min.js"></script>

<script type="text/javascript">
if (typeof jQuery =='undefined') document.write(unescape("%3Cscript src='/jquery-1.11.1.min.js'" +"type='text/javascript'%3E%3C/script%3E"));

$(function() {
<?php
require_once(dirname(__FILE__) . '/noindex/configuration.php');
if (isset($_COOKIE["param"])) {
	$cookie = json_decode(base64_decode($_COOKIE["param"]));
	$set_language = $cookie->lang;
	}
else {
	$result = array ('en', '0.1');
	if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $lang) {
			$pattern = '/^(?P<primarytag>[a-zA-Z]{2,8})'.'(?:-(?P<subtag>[a-zA-Z]{2,8}))?(?:(?:;q=)'.'(?P<quantifier>\d\.\d))?$/';

			$splits = array();
			if (preg_match($pattern, $lang, $splits)) {
				if (isset($splits[3])) {
					if ($result[1]<$splits[3] AND ($splits[1]=='ru' OR $splits[1]=='en')) {	$result = array($splits[1], $splits[3]);	}
					}
				}
			}
		}
	$set_language = $result[0];
	}
if ($set_language == 'ru') {
	setlocale(LC_ALL, 'ru_RU.CP1251', 'rus_RUS.CP1251', 'Russian_Russia.1251');
} elseif ($set_language == 'en') {
	setlocale(LC_ALL, 'english');
}
echo "var language = '$set_language';\n";
require_once(dirname(__FILE__) . '/inc/language.php');

?>
	var today = new Date();
	var month = today.getMonth();
	var year = today.getFullYear();
	var holy_id;
	var fix_button = false;
	var fix_button2 = false;
	var fix_height = 800;
	var ready = false;	//not used yet
	var version = '1.2';
	
	$("#help").button({icons: {primary: "ui-icon-help"}, text: false});
	$("#panel #calculate, #forum").button();
	$("#latns, #longwe" ).buttonset();
	$("#dialog-message, div.history, #loading, #forum, #help-city, #help-coo, #help-tip, #help-date, #help-calendar, #warning, .ui-autocomplete-loading").hide();
	$('#gopast').button({icons: {primary: "ui-icon-carat-1-w"}, text: false});
	$('#gofuture').button({icons: {primary: "ui-icon-carat-1-e"}, text: false});
	$("#lang" ).buttonset();
	$('#lang #ru').button({icons: {primary: "lang-icon-ru"}, text: false});
	$('#lang #en').button({icons: {primary: "lang-icon-en"}, text: false});
	$('.slide input#longminut, .slide input#latminut').spinner({min:0, max:59});
	$('.slide input#lat').spinner({min:0, max:89});
	$('.slide input#long').spinner({min:0, max:179});
	$('.slide input#inputheight').spinner({min:0, max:9999});
	$('.slide input#tzt').spinner({min:-12, max:14});
	$('input#inputyear').attr("value", year);
	$('select#pmonth [value='+month+']').attr("selected", "selected");
	$("table#calendar").html("<tr id='row0'></tr>");
	for (var i = 1; i<=7; i++) {
		$("#calendar tr#row0").append("<td class='first_tr'><div id='panch_week"+i+"'></div></td>");
	}

	var param = $.jCookies({ get : 'param' });
	if (!param || param.version!=version) {
		$.jCookies({ name : 'param', value : {"masa_type":1, "show_panch":0, "version":version, "lang":language}});
		$.jCookies({erase: 'citylog'});
		param = $.jCookies({ get : 'param' });
		}		
	function set_param() {
		$.jCookies({ name : 'param', value : {"masa_type":param.masa_type, "show_panch":param.show_panch, "version":param.version, "lang":param.lang}});
		}
	var locSearch = History.getState().hash;
	if(locSearch != "") {
		var urlp = {};
		var string = (locSearch.substr(2)).split("&");
		for(var x = 0; x < string.length; x++) {
			var temp = string[x].split("=");
			urlp[temp[0]] = temp[1];
		};
		console.log(urlp);
		if (urlp.lng) {	param.lang=urlp.lng;	set_param();	}
		if (urlp.y && urlp.m) {	year=urlp.y;	month=urlp.m-1;	}
	}
	function get_lang(lng_tmp) {
		$.ajax({
			url: "../inc/language.php",
			dataType: "jsonp",
			error: function(jqXHR, textStatus, errorThrown) {console.log(textStatus);},
			data: {
				lang: lng_tmp
				},
			success: function( data ) {
				lng = {'head':data.head,'txt':data.txt,'p':data.p,'txtp':data.txtp,'holy':data.holy,'error':data.error};
				change_lang();
				}
			});
		}
	get_lang(param.lang);
	if (language=='ru') {	$("#lang #ru").click();	}
	else if (language=='en') {	$("#lang #en").click();	}
	
	function change_lang () {
		moment.locale(param.lang);
		document.title = lng.head.title;
		$("head meta[name='description']").attr('content', lng.head.descr);
		$("head meta[http-equiv='content-language']").attr('content', lng.head.lng);
		$("#headname h1").html(lng.head.headname);
		$("label[for='choosecity']").html(lng.txt.city);
		$("#help-city .tooltip-inner").html(lng.txt.helpcity);
		$(".slide #help-coo .tooltip-inner").html(lng.txt.helpcoo);
		$("#help-date .tooltip-inner").html(lng.txt.helpdate);
		$(".slide #help-tip .tooltip-inner").html(lng.txt.note);
		$("#help-calendar .tooltip-inner").load(lng.txt.help);
		$("#loading .loadingtext").html(lng.txt.calcul);
		$(".slide #txt-lat").html(lng.txt.lat);
		$(".slide #txt-long").html(lng.txt.long);
		$(".slide #latns #north").next().children().html(lng.txt.latns[1]);
		$(".slide #latns #south").next().children().html(lng.txt.latns[0]);
		$(".slide #longwe #east").next().children().html(lng.txt.longwe[1]);
		$(".slide #longwe #west").next().children().html(lng.txt.longwe[0]);
		$("#txt-latitude").html(lng.txt.height);
		$("#latitude b").html(lng.txt.height2);
		$("#txt-tz").html(lng.txt.tz);
		$("#calculate span").html(lng.txt.calc);
		$("#warning").append(lng.error.arc);
		$('#txt-year').html(lng.txt.year);
		$('#txt-month').html(lng.txt.month);
		$('#dateyear').html(year);
		$('#datemonth').html(lng.txt.monarr[month]);
		dialog_txt_chmo = lng.txt.chmo;
		for (i=0; i<12; i++) {	$("#pmonth option[value="+i+"]").html(lng.txt.monarr[i]);	}
		error_input_lat = lng.error.input_lat;
		error_input_long = lng.error.input_long;
		error_tz = lng.error.tz;
		error_dial = lng.error.dial;
		for (var i = 1; i<=7; i++) {
			$("#calendar tr#row0 #panch_week"+i).html(lng.txt.week[i]);
		}
		if (lng.head.lng=='ru') {	$("#forum").show();	}
		else if (lng.head.lng=='en') {	$("#forum").hide();	}
	}
	
	function citylog_set () {	
		var vname = $('#choosecity').val();
		var vlongwe = parseInt($('.slide #longwe :checked').val());
		var vlongitude = $('input#long').spinner('value');
		var vlongminut = parseInt($('input#longminut').spinner('value'));
		var vlatns = parseInt($('.slide #latns :checked').val());
		var vlatitude = $('input#lat').spinner('value');
		var vlatminut = parseInt($('input#latminut').spinner('value'));
		var vheight = ($('input#inputheight').spinner('value')===""?0:parseInt($('input#inputheight').spinner('value')));
		var vtz = $('.slide #tz').html();
		var vtzt = $('.slide input#tzt').spinner('value');
		$.jCookies({
			name : 'citylog',
			value : { name:vname,longwe:vlongwe,longitude:vlongitude,longminut:vlongminut,latns:vlatns,latitude:vlatitude,latminut:vlatminut,height:vheight,tz:vtz,tzt:vtzt}
		});
		}
	
	if (urlp && urlp.lgwe && urlp.lon && urlp.lonm && urlp.lns && urlp.lat && urlp.latm && urlp.hgt && urlp.tz && urlp.tzt) {
	//?y=2014&m=3&lgwe=1&lon=37&lonm=37&lns=1&lat=55&latm=45&hgt=150&tz=Europe%2FMoscow&tzt=4&name=Moscow&lng=en
		var citylog = new Object();
		citylog.tz=decodeURIComponent(urlp.tz);
		citylog.name=decodeURIComponent(urlp.name);
		citylog.longwe=parseInt(urlp.lgwe);
		citylog.longitude=urlp.lon;
		citylog.longminut=urlp.lonm;
		citylog.latns=urlp.lns;
		citylog.latitude=urlp.lat;
		citylog.latminut=urlp.latm;
		citylog.height=urlp.hgt;
		citylog.tzt=urlp.tzt;
//console.log(urlp.tzt);
		$.jCookies({name : 'citylog', value : citylog});
	}
	else {
		var citylog = $.jCookies({ get : 'citylog' });
		}
	if (citylog) {
		if (parseInt(citylog.latitude) && parseInt(citylog.longitude)) {
			var history = citylog.name+' '+parseInt(citylog.latitude)+'&deg'+parseInt(citylog.latminut)+'\' '+(parseInt(citylog.latns)==0?lng.txt.south_s:lng.txt.north_s)+' '+parseInt(citylog.longitude)+'&deg'+parseInt(citylog.longminut)+'\''+(parseInt(citylog.longwe)==0?lng.txt.west_s:lng.txt.east_s);
		}
		$("div.history").show();
		$("div.history").html(history);
		$('#choosecity').val(citylog.name);
		if (citylog.longwe==1) {
			$('.slide #east').click();
		}
		else {
			$('.slide #west').click();
		}
		$('input#long').spinner("value", citylog.longitude);
		$('input#longminut').spinner("value", citylog.longminut);
		if (citylog.latns==1) {
			$('.slide #north').click();
		}
		else {
			$('.slide #south').click();
		}
		$('input#lat').spinner("value", citylog.latitude);
		$('input#latminut').spinner("value", citylog.latminut);
		$('input#inputheight').spinner("value", citylog.height);
		$('.slide #tz').html(citylog.tz);
		$('.slide input#tzt').spinner("value", citylog.tzt);
		slide("up");
		calculate();
		hhelptip(false);
	}
	else {
		//$('#help').click();
		hhelptip(true);
		}

	function get_holy(holy_arr) {
		$.ajax({
			url: "../noindex/holytext.php",
			dataType: "jsonp",
			data: {
				lng: lng.head.lng,
				num: holy_arr
				},
			error: function(jqXHR, textStatus, errorThrown) {console.log(textStatus);},
			success: function( data ) {
				$.each(data, function(indx, elem) {
				var prename ='';
					if (isset(elem['d0'])) { prename=elem['d0']+' ';}
					if (isset(elem['m0'])) { prename=elem['m0']+' ';}
					$('.hid'+elem['id']+' .h-butt').append(prename+"<span>"+elem['name']).css("color", "#"+elem['color']+"</span>");
					$('.hid'+elem['id']+' .holytab').prepend("<div class='h-name' style='color:#"+elem['color']+"'>"+prename+elem['name']+"</div>");
					$('.hid'+elem['id']+' .holytab').append((isset(elem['descr']) ? ("<div class='h-descr'>"+elem['descr']+"</div>") : "")+(isset(elem['comment']) ? ("<div class='h-comment'>"+elem['comment']+"</div>") : ""));
					var urls = '';
					for (var i=1; i<=3 ; i++) {
						if (isset(elem['url'+i])) {
							var icon ="images/no-36x36.png";
							if (elem['url'+i].indexOf('wikipedia', 7)>0) {	icon ="images/wiki2-36x36.png";	}
							else if (elem['url'+i].indexOf('nathas.org', 7)>0) {	icon ="images/nathas2-36x36.png";	}
							else if (elem['url'+i].indexOf('nathi.ru', 7)>0) {	icon ="images/nathiru-36x36.png";	}
							urls += "<div class='h-url' style='background-image: url("+icon+")'><a href='"+elem['url'+i]+"' target='_blank'>"+(isset(elem['urln'+i])?elem['urln'+i]:elem['name'])+"</a></div>";
							}
						}
					$('.hid'+elem['id']+' .holytab').append(urls);
					if (isset(elem['varname'])) {
						if (elem['varname']==1) {
							for (var i=1; i<=7; i++) {
								if (isset(elem['d'+i]) && $('.hid'+elem['id']).is('.d'+i)) {
									$('.hid'+elem['id']+'.d'+i+' .h-name').prepend(elem['d'+i]+' ');
									$('.hid'+elem['id']+'.d'+i+' .h-butt').prepend(elem['d'+i]+' ');
									if (isset(elem['ecolor'])) {
										$('.hid'+elem['id']+'.d'+i+' .h-butt').css("color", "#"+elem['ecolor']);
										}
									}
								}
							}
						else if (elem['varname']==2 || elem['varname']==3) {
							for (var i=1; i<=13; i++) {
								if (isset(elem['m'+i]) && $('.hid'+elem['id']).is('.m'+i)) {
									$('.hid'+elem['id']+'.m'+i+' .h-name').prepend(elem['m'+i]+' ');
									$('.hid'+elem['id']+'.m'+i+' .h-butt').prepend(elem['m'+i]+' ');
									if (isset(elem['ecolor']) && elem['varname']==2) {
										$('.hid'+elem['id']+'.m'+i+' .h-butt').css("color", "#"+elem['ecolor']);
										}
									}
								if ($('.hid'+elem['id']).is(' .ek'+i) && i>=1 && i<=8) {
									$('.hid'+elem['id']+'.ek'+i+' .h-name').append("<br/>"+lng.holy.ek[i]);
									$('.hid'+elem['id']+'.ek'+i+' .h-butt').append("<br/>"+lng.holy.ek[i]);
									$('.hid'+elem['id']+'.ek'+i+' .h-butt').css("color", "#"+elem['ecolor']);
									}
								}
							}
						}
					});
				$('.holyday').each(function( index ) { //правка размера шрифта и отступов
					bg = $(this).find('.h-butt-bg');
					butt = $(this).find('.h-butt');
					while (butt.height()>bg.height() || butt.children().width()>bg.children().width()) {	butt.css("font-size", (parseInt(butt.css("font-size"))-1)+"px");	}
					if (bg.height()>=butt.height()) {	butt.css("margin-top", (bg.height()-butt.height())/2+"px");	}
					});
				}
			});
		}
		
	function timeZone(timeZone) {
		$('.slide #tz').html(timeZone);
		$.ajax({
			url: "../noindex/timezones.php",
			dataType: "jsonp",
			data: {
				tz: timeZone,
				year: year,
				month: month
				},
			beforeSend: function() {
				$(".ui-autocomplete-loading").show();
				fix_button2 = true;
				},
			complete: function() {
				$(".ui-autocomplete-loading").hide();
				fix_button2 = false;
				},
			success: function( data ) {
				$(".slide input#tzt").spinner("value", data.tzt);
				}
			});
		}
	function elevation(longitude, latitude) {
			$.ajax({
				url: "http://api.geonames.org/astergdemJSON",
				dataType: "jsonp",
				data: {
					lat: parseFloat(latitude),
					lng: parseFloat(longitude),
					username: "5anga"
				},
			beforeSend: function() {
					$(".ui-autocomplete-loading").show();
				},
			complete: function() {
					$(".ui-autocomplete-loading").hide();
				},
			success: function(data) {
					$("input#inputheight").spinner('value', data.astergdem!=-9999 ? data.astergdem : 0);
				}
			});
		}
	
// AUTOCOMPLETE
	$( "#choosecity" ).autocomplete({
		source: function( request, response ) {
			$.ajax({
				url: "http://api.geonames.org/searchJSON",
				dataType: "jsonp",
				data: {
					featureClass: "P",
					style: "full",
					maxRows: 12,
					name_startsWith: request.term,
					username: "5anga"
				},
				success: function( data ) {
					response( $.map( data.geonames, function( item ) {
						return {
							label: item.name + (item.adminName1 ? ", " + item.adminName1 : "") + ", " + item.countryName,
							value: item.name,
							latitude: item.lat,
							longitude: item.lng,
							timeZone: item.timezone.timeZoneId,
							elevation: item.elevation
						}
					}));
				}
			});
		},
		minLength: 2,
		select: function( event, ui ) {
			if (ui.item) {
				//Name
				//longitude
				fix_button = true;
				var intmess = parseInt(ui.item.longitude);
				$("input#long").spinner("value", Math.abs(intmess));
				$("input#longminut").spinner("value", Math.round((Math.abs(ui.item.longitude)-Math.abs(intmess))*60));
				if (ui.item.longitude>=0) {
					$('.slide #east').click();
				}
				else {
					$('.slide #west').click();
				}
				//latitude
				var intmess = parseInt(ui.item.latitude);
				$("input#lat").spinner("value", Math.abs(intmess));
				$("input#latminut").spinner("value", Math.round((Math.abs(ui.item.latitude)-Math.abs(intmess))*60));
				if (ui.item.latitude>=0) {
					$('.slide #north').click();
				}
				else {
					$('.slide #south').click();
				}
				//timeZone
				timeZone(ui.item.timeZone);
				//Elevation
				if (typeof ui.item.elevation!=="undefined") {	$("input#inputheight").spinner('value', ui.item.elevation);	} 
				else {	
					$("input#inputheight").spinner('value', elevation(ui.item.longitude,ui.item.latitude));
					}
				fix_button = false;
			}

		},
		open: function() {
			$( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
		},
		close: function() {
			$( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
		}
	});

//	DOM Interface actions		
	$('#lang #ru').click(function() {
		param.lang = 'ru';
		set_param();
		get_lang('ru');
		if ($("div.history").css('display')!='none') {	form_exception();	}
	});
	$('#lang #en').click(function() { 
		param.lang = 'en';
		set_param();
		get_lang('en');
		if ($("div.history").css('display')!='none') {	form_exception();	}
	});
//TEST удалить
	$("#test").click(function() {
		document.write(VK.Share.button({
			url: 'http://5anga.info',
			title: 'Хороший сайт',
			description: 'Это мой собственный сайт, я его очень долго делал',
			image: 'http://5anga.info/Lord-Shiva-1.jpg',
			noparse: true
		}));
	});

//	DOM Panchanga actions	
	$("#panel").click(function() {
		if ($(".slide").css("display")!="block") {	slide("down");	}
	});
	$("#help").click(function() {
		if($("#help-div input:checked").length==1) {	helptip(true);	}
		else {	helptip(false);	}
		$("#help").blur();
	});
	$("#choosecity").keypress(function(e) {
	  if(e.keyCode == 13) {
		e.preventDefault();
		$('.ui-autocomplete li').first().click();
		$(this).autocomplete('close');
		}
	});
	$('.slide input#tzt').spinner({
		change: function (){
		if (!fix_button2) {
			var tzt = parseFloat($('.slide input#tzt').val());
			$("#choosecity").val("");
			if (tzt>=0) $('.slide #tz').html("Etc/GMT-"+tzt);
			else $('.slide #tz').html("Etc/GMT+"+Math.abs(tzt));
		}
	}});

	$('.slide input#lat, .slide input#latminut, .slide input#long, .slide input#longminut').spinner({
		change: function() {
		if (!fix_button) {
			$('#choosecity').val("");
			elevation(longitude(),latitude());
		}}
	});
	$('.slide #longwe, .slide #latns').change(function() {
		if (!fix_button) {
			$('#choosecity').val("");
			elevation(longitude(),latitude());
		}
	});	
	$('.slide #inputheight').spinner({
	change: function() {
		if ($(this).val()==="") {	elevation(longitude(),latitude());	}
	}});
	$('#gopast div, #gofuture div').hover(
		function() { $(this).addClass('ui-state-hover'); }, 
		function() { $(this).removeClass('ui-state-hover'); }
	);
	
	$('#calculate').click(function() {
		form_exception();
		return false;
	});
	
	$('#gopast').click(function() { 
		if (month>0) {
			month=parseInt(month)-1;
			$('#dateyear').html(year);
			$('#datemonth').html(lng.txt.monarr[month]);
			}
		else {
			month=11;
			year=parseInt(year)-1;
			$('#dateyear').html(year);
			$('#datemonth').html(lng.txt.monarr[month]);
			}
		form_exception();
		});
	$('#gofuture').click(function() { 
		if (month<11) {
			month=parseInt(month)+1;
			$('#dateyear').html(year);
			$('#datemonth').html(lng.txt.monarr[month]);
			}
		else {
			month=0;
			year=parseInt(year)+1;
			$('#dateyear').html(year);
			$('#datemonth').html(lng.txt.monarr[month]);
			}
		form_exception();
		});

	$("#datemonth, #dateyear").click(function() {
		$( "#dialog-message" ).dialog({
			modal: true,
			buttons: {
				Ok: function() {
					year = parseInt($('input#inputyear').val());
					month = parseInt($('#pmonth :selected').val());
					$('#dateyear').html(year);
					$('#datemonth').html(lng.txt.monarr[month]);
					form_exception(); 
					$( this ).dialog( "close" );
				}
			}
		});
		$('#dialog-message').dialog( "option", "title", dialog_txt_chmo );
	});	
	function calculate() {
		$.ajax({
			url: "../noindex/panch.php",
			dataType: "jsonp",
			data: {
				y: parseInt(year),
				m: parseInt(month)+1,
				lt: latitude(),
				lg: longitude(),
				tz: $('.slide #tz').html(),
				h: parseInt($('input#inputheight').spinner('value')),
				mt: param.masa_type
			},
			beforeSend: function() {
				$("#calendar tr:not(#calendar tr:first)").each(function() {$(this).remove();});
				$("#panel #calculate").button("option", "disabled", true);
				$('#gopast').button("option", "disabled", true);
				$('#gofuture').button("option", "disabled", true);
				$('#warning').hide();
				$('#loading').show();
				},
			error: function(jqXHR, textStatus, errorThrown) {console.log(textStatus);},
			success: function( data ) {
//console.log(data);
				$( '#loading' ).hide();
				var today = new Date();
				var dlength = 0;
				if (param.masa_type == 0) { masa_type = 'am'; masa_type_y = 'ay';}
				else if (param.masa_type == 1) { masa_type = 'pm'; masa_type_y = 'py';}
				$.each(data, function() { dlength++; });
/*
внутри дня
'H:mm:ss(+/-)' title='D.M.YYYY H:mm:ss' - 'H:mm:ss(+/-)' title='D.M.YYYY H:mm:ss'
['from'] = '[с:] H:mm:ss'
['till'] = '[до:] H:mm:ss'
<hr><div class='p-h'>
	<span class='p-hd'>14.05.2014</span></br><span class='p-ht'>с: 20:12:32(<b>-</b>) до: 4:10:45(<b>+</b>)</span>
</div><hr>
[d1]
	[from] - xxxxxxxxx
	[till] - xxxxxxxxx

внутри нескольких разных дней
[d1]
	[from] - xxxxxxxxx
	[till] - xxxxxxxxx
[d2]
	[from] - xxxxxxxxx
	[till] - xxxxxxxxx
[d3]
	[from] - xxxxxxxxx
	[till] - xxxxxxxxx
(1)'D.M.YYYY'
(1)	[from] H:mm:ss(-) [till] H:mm:ss(+)'
(2)	[from] 'D.M.YYYY' H:mm:ss(-) 
	[till] 'D.M.YYYY' H:mm:ss(+)
<hr><div class='p-h'>
	<span class='p-hd'>14.05.2014</span></br><span class='p-ht'>с: 20:12:32(<b>-</b>) до: 4:10:45(<b>+</b>)</span>
</div><hr>
<div class='p-h'>
	<span class='p-hd'>14.05.2014</span></br><span class='p-ht'>с: 20:12:32(<b>-</b>) до: 4:10:45(<b>+</b>)</span>
</div><hr>
<div class='p-h'>
	<span class='p-hd'>14.05.2014</span></br><span class='p-ht'>с: 20:12:32(<b>-</b>) до: 4:10:45(<b>+</b>)</span>
</div><hr>

несколько  дней
'D.M.YYYY'
'D.M.YYYY'
'D.M.YYYY'
[d1]
	[date] - xxxxxxxxx
[d2]
	[date] - xxxxxxxxx
[d3]
	[date] - xxxxxxxxx
<hr><div class='p-h'><span class='p-hd'>14.05.2014</span></div>
<hr><div class='p-h'><span class='p-hd'>15.05.2014</span></div>
<hr><div class='p-h'><span class='p-hd'>25.05.2014</span></div>
<hr><div class='p-h'><span class='p-hd'>05.06.2014</span></div><hr>
	
несколько дней подряд
'D1' - 'D2.M.YYYY'
elem[0]-elem[1].month.year
[0]
	[day] - DD
	[month] - MM
	[year] - YYYY
[1]
	[day] - DD
	[month] - MM
	[year] - YYYY

<hr><div class='p-h'><span class='p-hd'>с : 14.05.2014</span> -	<span class='p-hd'>до: 15.05.2014</span></div><hr>
*/
				$.each(data['holy'], function(uniq, elem) {
					var interval_str = "";
					if (isset(elem.days)) {
						//console.log(uniq);
						//console.log(elem);
						interval_str = "";
						interval_str_m = "";
						interval_str_p = "";
						var int_begin = false;
						
						$.each(elem.days, function(hday, inter) {
							if (isset(elem.days[0])) { //несколько дней подряд
								if (isset(inter.day) && isset(inter.month) && isset(inter.year)) {
									if (int_begin) {
										interval_str += "<hr><div class='p-h'><span class='p-hd'>"+int_begin+"</span> - <span class='p-hd'>"+moment(inter.year+"-"+inter.month+"-"+inter.day, "YYYY-M-D").format(lng.txtp.date)+"</span></div>";
										}
									else {
										int_begin = moment(inter.year+"-"+inter.month+"-"+inter.day, "YYYY-M-D").format(lng.txtp.date);
										}
									}
								}
							else if (isset(inter.from)) {	//внутри дня или внутри нескольких дней
								var e_date = mom('span', 'p-hd', hday, inter.from, 'date');
								var e_from = mom('span', 'p-ht', hday, inter.from, 'from');
								interval_str+="<hr><div class='p-h'>"+e_date;
								if (isset(inter.till)) {
									interval_str+="</br>"+e_from+"</br>"+mom('span', 'p-ht', hday, inter.till, 'till');
									}
								interval_str+="</div>";
								}
							else if (isset(inter.b)) {	//для экадаши
								if (interval_str_m=="") interval_str_m="<hr>";
								interval_str += "<div class='p-h'><span class='p-hd'>"+moment(year+"-"+(parseInt(month)+1)+"-"+hday, "YYYY-M-D").format(lng.txtp.date)+"</span></div>";
								if (!isset(inter.b[1])) {
									interval_str+="<div class='p-h'><b>"+lng.txtp.beg+"</b>"+mom('span', 'p-ht', hday, inter.b[0], 'time')+"</div>";
									}
								else {
									interval_str+="<div class='p-h'><b>"+lng.txtp.beg+"</b>"+mom('div', 'p-ht', hday, inter.b[0], 'from')+mom('div', 'p-ht', hday, inter.b[1], 'till')+"</div>";
									}
								if (!isset(inter.e[1])) {
									interval_str+="<div class='p-h'><b>"+lng.txtp.end+"</b>"+mom('div', 'p-ht', hday, inter.e[0], 'after')+"</div>";
									}
								else {
									interval_str+="<div class='p-h'><b>"+lng.txtp.end+"</b>"+mom('div', 'p-ht', hday, inter.e[0], 'from')+mom('div', 'p-ht', hday, inter.e[1], 'till')+"</div>";
									}
								}
							else if (isset(inter.month) && isset(inter.year))	{
								if (interval_str_m=="") interval_str_m="<hr>";
								int_str = "<div class='p-h'><span class='p-hd'>"+moment(inter.year+"-"+inter.month+"-"+hday, "YYYY-M-D").format(lng.txtp.date)+"</span></div>";
								if ((inter.year==data.init.year && inter.month<data.init.month) || inter.year<data.init.year) {	interval_str_m += int_str;	}
								else if (inter.year==data.init.year && inter.month==data.init.month) {	interval_str += int_str;	}
								else if ((inter.year==data.init.year && inter.month>data.init.month) || inter.year>data.init.year) {	interval_str_p += int_str;	}
								}
							else {
								interval_str += "<hr><div class='p-h'><span class='p-hd'>"+moment(data.init.year+"-"+data.init.month+"-"+hday, "YYYY-M-D").format(lng.txtp.date)+"</span></div>";
								}
							});
						if (interval_str!="") data['holy'][uniq]['string'] = interval_str_m+interval_str+interval_str_p+"<hr>";
						}
					});
//console.log(data);
				
				firstday = data[1].w;
				var rows = Math.ceil((dlength + Number(firstday) - 1)/7);
				for (var row = 1; row <= rows; row++) {
					holydays_on_week = 0;
					if (row==1) {$("#calendar tr#row"+(row-1)).after("<tr id='row"+row+"'></tr>");}
					for (var coll = 1; coll <= 7; coll++) {
						day = ((row-1)*7)+coll-firstday+1;
						if (isset(data[day]) && day!=0) {
							if (coll==1 && row!=1) {$("#calendar tr#row"+(row-1)).after("<tr id='row"+row+"'></tr>");}
							toggler='toggler-l';
							if (coll>5) {	toggler='toggler-r';	}
							$("#calendar tr#row"+row).append("<td class='date'><div id='day"+(day)+"'></div></td>");
							$("#day"+(day)).append("<div id='daydate'>"+(day)+"</div>");
							$("#day"+(day)).append("<div class='hdspace'></div>");
							holydays_on_day = 0;
							if (isset(data[day].holy)) {
//console.log(data[day].holy);
								$.each(data[day].holy, function(indx, uniq) {
									var string = "<div class='holyday hid"+data['holy'][uniq]["id"]+" d"+coll+" m"+((data[day].ti[0]['l']=='1') ? "13" : data[day].ti[0][masa_type])+" uniq"+uniq+(isset(data['holy'][uniq]["ek"]) ? " ek"+data['holy'][uniq]["ek"] : "")+"'><div class='h-butt-bg ui-widget-content ui-corner-all'><div class='h-butt'></div></div><div class='"+toggler+" "+((row>3) ? ("toggler-b") : "")+"'><div class='holytab ui-widget ui-widget-content ui-corner-all'>";
									if (isset(data['holy'][uniq]["string"])) {
										string +=data['holy'][uniq]["string"];
										}
									string +="</div></div></div>";
									$("#day"+(day)+" .hdspace").append(string);
									holydays_on_day++;
									});
								
								if (holydays_on_day>holydays_on_week) holydays_on_week=holydays_on_day;
								}
							if (isset(data[day].se)) {
								$("#day"+(day)+" .hdspace").append("<div class='holyday hid1'><div class='h-butt-bg ui-widget-content ui-corner-all'><div class='h-butt'></div></div><div class='"+toggler+"'><div class='holytab ui-widget ui-widget-content ui-corner-all'><div class='h-comment'><b>"+lng.p.ecl[data[day].se['type']]+"</b></br>"+lng.p.ecl_sar+data[day].se['saros']+"</br>"+lng.p.ecl_par+"</div>"+((isset(data[day].se['p1'])&&isset(data[day].se['p2'])) ? ("<div class='h-space'>"+mom('div', 'p-dt', day, data[day].se['p1'], 'from')+mom('div', 'p-dt', day, data[day].se['p2'], 'till')+"</div>") : "")+((isset(data[day].se['t1'])&&isset(data[day].se['t2'])) ? ("<div class='h-comment'>"+lng.p.ecl_tot+"</div><div class='h-space'>"+mom('div', 'p-dt', day, data[day].se['t1'], 'from')+mom('div', 'p-dt', day, data[day].se['t2'], 'till')+"</div>") : "")+"<div class='h-comment'>"+lng.p.ecl_max+"</div><div class='h-space'>"+mom('div', 'p-dt', day, data[day].se['max'], 'time')+"</div>"+"<hr><div class='h-comment'>"+lng.p.ecl_coo+"</br>"+lng.txt.lat+deg_text(data[day].se['lat'])+"</br>"+lng.txt.long+deg_text(data[day].se['long'])+"</div>"+(isset(data[day].se['typel']) ? ("<hr><div class='h-comment'>"+lng.p.ecl_obs+"</br><b>"+lng.p.ecl[data[day].se['typel']]+"</b></div>"+((isset(data[day].se['p1l'])&&isset(data[day].se['p2l'])) ? ("<div class='h-comment'>"+lng.p.ecl_par+"</div>"+"<div class='h-space'>"+mom('div', 'p-dt', day, data[day].se['p1l'], 'from')+mom('div', 'p-dt', day, data[day].se['p2l'], 'till')+"</div>") : "")+((isset(data[day].se['t1l'])&&isset(data[day].se['t2l'])) ? ("<div class='h-comment'>"+lng.p.ecl_tot+"</div>"+"<div class='h-space'>"+mom('div', 'p-dt', day, data[day].se['t1l'], 'from')+mom('div', 'p-dt', day, data[day].se['t2l'], 'till')+"</div>") : "")) : "<hr><div class='h-comment'>"+lng.p.ecl_noobs+"</div>")+"</div></div></div>");
								holydays_on_day++;
								if (holydays_on_day>holydays_on_week) holydays_on_week=holydays_on_day;
								data.holy.se={'id': '1'};
								}
							if (isset(data[day].me)) {
								$("#day"+(day)+" .hdspace").append("<div class='holyday hid2'><div class='h-butt-bg ui-widget-content ui-corner-all'><div class='h-butt'></div></div><div class='"+toggler+"'><div class='holytab ui-widget ui-widget-content ui-corner-all'><div class='h-comment'><b>"+lng.p.ecl[data[day].me['type']]+"</b></br>"+lng.p.ecl_sar+data[day].me['saros']+"</br>"+lng.p.ecl_penu+"</div>"+((isset(data[day].me['pu1'])&&isset(data[day].me['pu2'])) ? ("<div class='h-space'>"+mom('div', 'p-dt', day, data[day].me['pu1'], 'from')+mom('div', 'p-dt', day, data[day].me['pu2'], 'till')+"</div>") : "")+((isset(data[day].me['p1'])&&isset(data[day].me['p2'])) ? ("<div class='h-comment'>"+lng.p.ecl_par+"</div><div class='h-space'>"+mom('div', 'p-dt', day, data[day].me['p1'], 'from')+mom('div', 'p-dt', day, data[day].me['p2'], 'till')+"</div>") : "")+((isset(data[day].me['t1'])&&isset(data[day].me['t2'])) ? ("<div class='h-comment'>"+lng.p.ecl_tot+"</div><div class='h-space'>"+mom('div', 'p-dt', day, data[day].me['t1'], 'from')+mom('div', 'p-dt', day, data[day].me['t2'], 'till')+"</div>") : "")+(isset(data[day].me['typel'])?("<hr><div class='h-comment'>"+lng.p.ecl_obs+"</br><b>"+lng.p.ecl[data[day].me['typel']]+"</b></br>"+lng.p.ecl_penu+"</div>"+((isset(data[day].me['pul'])) ? ("<div class='h-space'>"+mom('div', 'p-dt', day, data[day].me['pul'][0], 'from')+mom('div', 'p-dt', day, data[day].me['pul'][1], 'till')+"</div>") : "")+(isset(data[day].me['pl'])?("<div class='h-comment'>"+lng.p.ecl_par+"</div><div class='h-space'>"+mom('div', 'p-dt', day, data[day].me['pl'][0], 'from')+mom('div', 'p-dt', day, data[day].me['pl'][1], 'till')+"</div>"):"")+(isset(data[day].me['tl'])?("<div class='h-comment'>"+lng.p.ecl_tot+"</div><div class='h-space'>"+mom('div', 'p-dt', day, data[day].me['tl'][0], 'from')+mom('div', 'p-dt', day, data[day].me['tl'][1], 'till')+"</div>"): "")) : "<hr><div class='h-comment'>"+lng.p.ecl_noobs+"</div>")+"</div></div></div>");
								holydays_on_day++;
								if (holydays_on_day>holydays_on_week) holydays_on_week=holydays_on_day;
								data.holy.me={'id': '2'};
								}
							
							$("#day"+(day)).append("<div><button></button></div><div class='panch'></div>");
							daytxt = "<div class='daytext'>";
							if (isset(data[day].sm["m"])) {
								daytxt += "<div class='p-txt'>"+lng.txtp.sm+"</div><div class='p-arg'>"+lng.p.sm[data[day].sm["m"]]+"</div>";
								if (isset(data[day].sm["t"])) {
									daytxt += mom('div', 'p-dt', day, data[day].sm["t"], 'till');
									}
								daytxt += "<br>";
								}
							if (isset(data[day].ti)) {
								if (isset(data[day].ti[0][masa_type])) {
									daytxt += "<div class='p-arg'>"+data[day].ti[0][masa_type_y]+"</div>";
									daytxt += "<div class='p-txt'>"+lng.txtp.mm+"</div>";
									if (data[day].ti[0]['l']==1) daytxt += lng.txtp.mml+" ";
									daytxt += "<div class='p-arg'>"+lng.p.chm[data[day].ti[0][masa_type]]+"</div>";
									}
								daytxt += "<div class='p-txt'>"+lng.txtp.t+"</div>";
								daytxt += "<div class='p-arg'>";
								daytxt += lng.p.paksha[data[day].ti[0]['p']]+lng.p.tithi[data[day].ti[0]['n']]+"</div>"
								if ( data[day].ti[0]['n']==15 && isset(data[day-1]) && isset(data[day-1].ti[0]['d'])) {	$("#day"+(day)+" #daydate").after("<div class='moon'><img src='./images/moon1.png'></div>");	}
								else if (data[day].ti[0]['n']==0 && isset(data[day-1]) && isset(data[day-1].ti[0]['d'])) {	$("#day"+(day)+" #daydate").after("<div class='moon'><img src='./images/moon0.png'></div>");	}
								if (isset(data[day].ti[0]['d'])) {	
									daytxt += mom('div', 'p-dt', day, data[day].ti[0]['d0'], 'from')+mom('div', 'p-dt', day, data[day].ti[0]['d'], 'till');
									if (isset(data[day].ti[1]) && isset(data[day].ti[1]['n'])) {
										if (data[day].ti[1][masa_type]!=data[day].ti[0][masa_type]) {
											daytxt += "<div class='p-arg'>"+data[day].ti[1][masa_type_y]+"</div>";
											daytxt += "<div class='p-txt'>"+lng.txtp.mm+"</div>";
											if (data[day].ti[1]['l']==1) daytxt += lng.txtp.mml+" ";
											daytxt += "<div class='p-arg'>"+lng.p.chm[data[day].ti[1][masa_type]]+"</div>";
											daytxt += "<div class='p-txt'>"+lng.txtp.t+"</div>";
											}
										daytxt += "<div class='p-arg'>";
										daytxt += lng.p.paksha[data[day].ti[1]['p']]+lng.p.tithi[data[day].ti[1]['n']]+"</div>"+mom('div', 'p-dt', day, data[day].ti[1]['d0'], 'from')+mom('div', 'p-dt', day, data[day].ti[1]['d'], 'till');
										if ( data[day].ti[1]['n']==15 && isset(data[day-1]) && isset(data[day-1].ti[0]['d'])) {	$("#day"+(day)+" #daydate").after("<div class='moon'><img src='./images/moon1.png'></div>");	}
										else if (data[day].ti[1]['n']==0 && isset(data[day-1]) && isset(data[day-1].ti[0]['d'])) {	$("#day"+(day)+" #daydate").after("<div class='moon'><img src='./images/moon0.png'></div>");	}
										}
									}
								else {	daytxt += "<div class='p-txt'>"+lng.txtp.full+"</div><br>";	}
								daytxt += "<br>";
								}
							
							if (isset(data[day].na) && isset(data[day].na[0])) {
								daytxt += "<div class='p-txt'>"+lng.txtp.na+"</div>";
								daytxt += "<div class='p-arg'>"+lng.p.naksh[data[day].na[0]['n']]+"</div>";
								if (isset(data[day].na[0]['d'])) {
									daytxt += mom('div', 'p-dt', day, data[day].na[0]['d'], 'till');
									if (isset(data[day].na[1]) && isset(data[day].na[1]['n'])) {
										daytxt += "<div class='p-arg'>"+lng.p.naksh[data[day].na[1]['n']]+"</div>"+mom('div', 'p-dt', day, data[day].na[1]['d'], 'till');
										}
									}
								else {	daytxt += "<div class='p-txt'>"+lng.txtp.full+"</div><br>";	}
								daytxt += "<br>";
								}
							
							if (isset(data[day].sr)) {
								daytxt += "<div class='p-txt'>"+lng.txtp.sr+"</div>";
								daytxt += mom('div', 'p-dt', day, data[day].sr[0], 'time');
								}
							if (isset(data[day].ss)) {
								daytxt += "<div class='p-txt'>"+lng.txtp.ss+"</div>";
								daytxt += mom('div', 'p-dt', day, data[day].ss[0], 'time');
								}
							if (isset(data[day].mr)) {
								daytxt += "<div class='p-txt'>"+lng.txtp.mr+"</div>";
								daytxt += mom('div', 'p-dt', day, data[day].mr[0], 'time');
								if (isset(data[day].mr[1])) {
									daytxt += mom('div', 'p-dt', day, data[day].mr[1], 'time');
									}
								}
							if (isset(data[day].ms)) {
								daytxt += "<div class='p-txt'>"+lng.txtp.ms+"</div>";
								daytxt += mom('div', 'p-dt', day, data[day].ms[0], 'time');
								if (isset(data[day].ms[1])) {
									daytxt += mom('div', 'p-dt', day, data[day].ms[0], 'time');
									}
								}
								
							daytxt += "</div>";
							$("#day"+(day)+" .panch").append(daytxt);
							
							if (day==today.getDate() && month == today.getMonth() && year == today.getFullYear()) {	
								$("#day"+(day)).parent().attr("id","currentdate");
								}

							}
						else {
							$("#calendar tr#row"+row).append("<td class='outofdate'> </td>");
							}
						}
						if (holydays_on_week>=2) {
							$("#row"+row+" .hdspace").css("height", 50*holydays_on_week+5+"px");
							}
					}


				$('.toggler-r, .toggler-l').hide();
				$('.holyday .h-butt-bg').each(function(){ // В данной строке limp - название Вашего класса для создания прозрачности.
					  $(this).animate({opacity:'0.7'},1); // В данной строке задаётся начальная прозрачность элемента.
				});

				$('.holyday').mouseover(function(){
					$(this).children('.h-butt-bg').stop().css({opacity:'1.0'},0); // В данной строке задаётся прозрачность элемента при наведении курсора.
					$(this).children('.h-butt-bg').removeClass("ui-widget-content");
					$(this).children('.h-butt-bg').addClass("ui-state-hover");
					$(this).children('.toggler-r, .toggler-l').fadeIn(0);
					
					if ($(this).find('.toggler-b').is('.toggler-b')) {
						theight = Math.round($(this).find('.holytab').height());
						if (theight>200) {
							$(this).find('.holytab').css('margin-top', '-'+(theight-200)+'px');
							}
						}
				});
				$('.holyday').mouseout(function(){
					$(this).children('.h-butt-bg').stop().animate({opacity:'0.7'},0); // В данной строке задаётся прозрачность элемента при уходе курсора с элемента.
					$(this).children('.h-butt-bg').removeClass("ui-state-hover");
					$(this).children('.h-butt-bg').addClass("ui-widget-content");
					$(this).children('.toggler-r, .toggler-l').fadeOut(0);
				});


				$('.holyday .h-butt').each(function(){
					  $(this).mouseover(function(){
						  $(this).siblings('.h-butt-bg').mouseover();
					  });
					  $(this).mouseout(function(){
						  $(this).siblings('.h-butt-bg').mouseout();
					  });
				});
				$('table#calendar button').each(function(){
					if (param.show_panch==1) {$(this).button({icons: {primary: "ui-icon-carat-1-n"},text: false});}
					else {$(this).button({icons: {primary: "ui-icon-carat-1-s"},text: false});}
				});
				$('table#calendar button').css("width","100%");
				$('table#calendar button').animate({opacity:'0.3'},1);
				if (param.show_panch!=1) {
					$('.panch').each(function(){
						$(this).hide();
					});
					}
				$('table#calendar button').click(
					function () {
						if ($('table#calendar button span').hasClass("ui-icon-carat-1-s")) {
							$('.panch').show("slow");
							param.show_panch=1;
							set_param ();
							$('table#calendar button span').removeClass("ui-icon-carat-1-s");
							$('table#calendar button span').addClass("ui-icon-carat-1-n");
						}
						else {
							$('.panch').hide("slow");
							param.show_panch=0;
							set_param ();
							$('table#calendar button span').removeClass("ui-icon-carat-1-n");
							$('table#calendar button span').addClass("ui-icon-carat-1-s");
						}
					}
				);
				holy_id = {}, inc=0, last_id=0;
				$.each(data['holy'], function(indx, elem) {
					if (elem['id']!=last_id) {
						holy_id[inc] = elem['id'];
						inc++;
						last_id = elem['id'];
						}
					});
				if (data['warn_arctic']==true) $('#warning').show();
			},
			complete: function() {
				get_holy(holy_id);
				$("#panel #calculate").button("option", "disabled", false);
				$('#gopast').button("option", "disabled", false);
				$('#gofuture').button("option", "disabled", false);
				}
		});

	}
	
	function mom(mom_type, mom_class, hday, time, format) {
		var event = moment.unix(time).utc();
		var day = parseInt(hday);
		event.locale('ru');
		if (format=='date') {	return "<"+mom_type+" class='"+mom_class+"'>"+event.format(lng.txtp['date'])+"</"+mom_type+">";	}
		if (event.format('D')==day || day==0) {
			return "<"+mom_type+" class='"+mom_class+"'>"+event.format(lng.txtp[format])+"</"+mom_type+">";
			}
		else if (event.format('D')==day+1) {
			return "<"+mom_type+" class='"+mom_class+" help' title='"+event.format(lng.txtp['data']+"['>]"+lng.txtp[format])+"(<b>+</b>)</"+mom_type+">";
			}
		else if (event.format('D')==day-1 || event.format('D')>day+26) {
			return "<"+mom_type+" class='"+mom_class+" help' title='"+event.format(lng.txtp['data']+"['>]"+lng.txtp[format])+"(<b>-</b>)</"+mom_type+">";
			}
		else {
			return "<"+mom_type+" class='"+mom_class+" help' title='"+event.format(lng.txtp['data']+"['>]"+lng.txtp[format])+"(<b>+</b>)</"+mom_type+">";
			}
		}
	function form_exception() {
		var error_message='';
		timeZone($('.slide #tz').html());
		if ((-90<$('input#lat').spinner('value') && $('input#lat').spinner('value')>90) || $('input#lat').spinner('value') === null) { error_message = error_input_lat+'<br>';		}
		if ((-179<$('input#long').spinner('value') && $('input#long').spinner('value')>180) || $('input#long').spinner('value') === null) { error_message = error_message+error_input_long+'<br>';	}
		if ((-14<$('.slide #tzt').spinner('value') && $('.slide #tzt').spinner('value')>14) || $('.slide #tzt').spinner('value') === null) { error_message = error_message+error_tz;	}
		if (error_message && typeof error_message !== "undefined") {
			$('#error_message').html(error_message);
			$( "#error_message" ).dialog({
					modal: true,
					width: 380,
					buttons: {
						Ok: function() {
							$( this ).dialog( "close" );
						}
					}
				});
			$('#error_message').dialog( "option", "title", error_dial );
			slide("down");
			$("#calendar tr").each(function(){
				if ($(this).attr("id")!="row0")	$(this).remove();
				});
		}
		else {
			hhelptip(false);
			citylog_set();
			citylog = $.jCookies({ get : 'citylog' });
			var history = ((typeof citylog.name!=="undefined")?citylog.name:'')+' '+parseInt(citylog.latitude)+'&deg'+parseInt(citylog.latminut)+'\' '+(parseInt(citylog.latns)==0?lng.txt.south_s:lng.txt.north_s)+' '+parseInt(citylog.longitude)+'&deg'+parseInt(citylog.longminut)+'\''+(parseInt(citylog.longwe)==0?lng.txt.west_s:lng.txt.east_s);
			$("div.history").show();
			$("div.history").html(history);
			slide("up");
			History.replaceState('null',"state","?y="+year+"&m="+(parseInt(month)+1)+"&lgwe="+citylog.longwe+"&lon="+citylog.longitude+"&lonm="+citylog.longminut+"&lns="+citylog.latns+"&lat="+citylog.latitude+"&latm="+citylog.latminut+"&hgt="+citylog.height+"&tz="+encodeURIComponent(citylog.tz)+"&tzt="+citylog.tzt+"&name="+encodeURIComponent(citylog.name)+"&lng="+param.lang);
			calculate();
		}
	}
	function slide(direction) {
		if (direction=="up") {
			$(".slide").slideUp("slow");	
			$("#panel").css("cursor", "pointer");
			}
		else if (direction=="down") {
			$(".slide").slideDown("slow");
			$("#panel").css("cursor", "default");
			}
	}
	function latitude() {
		return parseInt($('.slide #latns :checked').val())>0 ? $('input#lat').spinner('value') + parseInt($('input#latminut').spinner('value'))/60 : (-1*($('input#lat').spinner('value') + parseInt($('input#latminut').spinner('value'))/60));
	}
	function longitude() {	
		return parseInt($('.slide #longwe :checked').val())>0 ? $('input#long').spinner('value') + parseInt($('input#longminut').spinner('value'))/60 : (-1*($('input#long').spinner('value') + parseInt($('input#longminut').spinner('value'))/60));
	}
	function helptip(show) {	
		if (show) {
			$(".city-panel").css({border: "4px dotted #CCCCCC", padding: "0px"});
			$("#coo").css({border: "4px dotted #CCCCCC", padding: "0px"});
			helpcity = ($(".city-panel").outerHeight(true)-$("#help-city").outerHeight(true))/2;
			$("#help-city").css('top', helpcity>0?Math.abs(helpcity):0);
			helpcoo = ($("#coo").outerHeight(true)-$("#help-coo").outerHeight(true))/2;
			$("#help-coo").css('top', helpcoo>0?Math.abs(helpcoo):0);
			$("#help-city").show("slide", 200);
			$("#help-coo").show("slide", 200);
			$("#help-date").show("slide", 200);
			$("#help-tip").show("slide", {direction: "right"}, 200);
			$("#help-calendar").show("slide", {direction: "up"}, 200);
		}
		else {
			$("#help-city").hide("slide", 200);
			$("#help-coo").hide("slide", 200);
			$("#help-date").hide("slide", 200);
			$("#help-tip").hide("slide", {direction: "right"}, 200);
			$("#help-calendar").hide("slide", {direction: "up"}, 200);
			$(".city-panel").css({border: "", padding: "4px"});
			$("#coo").css({border: "", padding: "4px"});
		}
	}
	function hhelptip(show) {
		if (show) {
			if ($("#help-div input:checked").length==0) {	$("#help").click();	}
			helptip(true);
			}
		else {
			if ($("#help-div input:checked").length==1) {	$( "#help" ).click();	}
			helptip(false);
			}
	}
	function deg_text(deg) {
		if (deg>=0) {
			deg_abs=Math.abs(deg);
			return " "+Math.floor(deg_abs)+"° "+Math.round((deg_abs-Math.floor(deg_abs))*60)+"'";
		}
		else {
			deg_abs=Math.abs(deg);
			return " -"+Math.floor(deg_abs)+"° "+Math.round((deg_abs-Math.floor(deg_abs))*60)+"'";
		}
	}
	function isset(isset_obj) {
	//if (typeof isset_obj !== "undefined" && !$.isEmptyObject(isset_obj)) {	return true;	}
	if (typeof isset_obj !== "undefined" && isset_obj!==null) {	return true;	}
	else {	return false;	}
	}
});
	
	
	
</script>
</head>
<body>
<div id="mainpart">
<div class="float-left">
<div id="left1" class="ui-tabs ui-widget-content ui-corner-all">
<header id="headname"><h1></h1></header>
<div id="lang" class="ui-buttonset">
<input type='radio' id='ru' value="ru" name='radio' /><label for='ru'>ru</label>
<input type='radio' id='en' value="en" name='radio' /><label for='en'>en</label>
</div>
<a id='forum' href='http://forum.dharmanathi.ru/' target='_blank'>Форум</a>
</div>
</div>

<div id="centerpart" class="float-left">
<div id="panel" class="ui-tabs ui-accordion ui-accordion-content ui-widget-content ui-corner-bottom" style="cursor: pointer;">
<div class="city-panel float-left" style="padding: 4px;">
<label for="choosecity" ></label>
<input id="choosecity" class="ui-corner-all"/></div><div id="help-city" class="float-left tooltip-right tooltip" ><div class="tooltip-inner"></div></div>
<div class="history float-right ui-state-default ui-corner-all"></div>
<div class="clear-left"></div>
<div class="slide clear-left">
<div id="coo" class="float-left">
<form><div id="txt-lat" class="float-left" ></div><div class="float-left">
<div id="latns" class="float-left"><input type="radio" id="north" name="radio" value='1'/><label for="north"></label><input type="radio" id="south" name="radio" value='0'/><label for="south"></label></div> : 
<input id="lat" size="3"/>°
<input id="latminut" size="2"/>'
</div>
<div class="clear-left"></div>
</form><form>
<div id="txt-long" class="float-left"></div><div class="float-left">
<div id="longwe" class="float-left"><input type="radio" id="east" name="radio" value='1'/><label for="east"></label><input type="radio" id="west" name="radio" value='0'/><label for="west"></label></div> : 
<input id="long" max="180" min="0" size="3" />°
<input id="longminut" max="59" min="0" size="2" />'
</div>
<div class="clear-left"></div>
</form>
<div class="float-left">
<div id="txt-latitude" class="float-left"></div><div id="latitude" class="float-left"><input class="ui-corner-all" id="inputheight" type="int" max="9999" min="0" size="4" /><b></b></div><div class="float-left ui-autocomplete-loading"></div>
<div class="clear-left"></div>
<div id="txt-tz" class="tz float-left"></div><div class="float-left"><div class="float-left ui-state-default ui-corner-all" id="tz"></div> GMT <input type="int" id="tzt" class="ui-corner-all" max="14" min="-12" size="2" /></div>
</div>
</div>
<div id="help-space" class="float-left">
<div id="help-div"><input type="checkbox" id="help" /><label for="help"></label></div>
<div id="help-tip" class="tooltip-left tooltip" ><div class="tooltip-inner"></div></div>
<div id="help-coo" class="float-left tooltip-right tooltip" ><div class="tooltip-inner"></div></div>
<div id="calculate" class="clear-both float-right"></div></div>
<div class="clear-left"></div>
</div>
<div id="warning" class="ui-state-error ui-corner-all"><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span></div>
</div>


<div class="calendar ui-tabs ui-widget-content ui-corner-all">

<div class="datetab ui-widget-header ui-corner-all">
<div id="wrapper">
<div id="dateyear"></div>
</div>
<div id="gopast"></div>
<div id="gofuture"></div>
<div id="dialog-message" title="">
<div id='txt-year'></div><input type="int" class="ui-corner-all" id="inputyear" maxlength="4" size="4"/><br>
<div id='txt-month'></div><select id="pmonth" class="ui-corner-all ui-menu-item">
<option value="0" ></option><option value="1" ></option><option value="2" ></option><option value="3" ></option><option value="4" ></option><option value="5" ></option><option value="6" ></option><option value="7" ></option><option value="8" ></option><option value="9" ></option><option value="10" ></option><option value="11" ></option>
</select>
</div>
<div id="error_message" title=""></div>
<div id="datemonth"></div>
</div>
<div>
<table id='calendar'></table>
<div id="loading"><span class="loadingtext">Computing...</span>
	<div id="circularG_1" class="circularG"></div>
	<div id="circularG_2" class="circularG"></div>
	<div id="circularG_3" class="circularG"></div>
	<div id="circularG_4" class="circularG"></div>
	<div id="circularG_5" class="circularG"></div>
	<div id="circularG_6" class="circularG"></div>
	<div id="circularG_7" class="circularG"></div>
	<div id="circularG_8" class="circularG"></div>
</div>

<main><div id="help-calendar" class="tooltip-bottom tooltip" style="display: none;"><div class='tooltip-inner'>
<?php 
require_once(dirname(__FILE__) . '/inc/'.$set_language.'index.html');
?>
</div></div></main>

</div>
</div>
<div style="height:100px;"></div>
</div>
<div class="clear-left"></div>
</div>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-27028174-1', 'auto');
  ga('send', 'pageview');

</script>

<?php
/*
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-27028174-1', 'auto');
  ga('send', 'pageview');

</script>
//require_once(dirname(__FILE__) . '/bottom.php');

//print_r($panch);
//$exec_time = microtime(true) - $start_time;
//printf ("Время выполнения: %f сек.", $exec_time);
//printf ("size=", file_put_contents("1.txt", serialize($panch)));
*/
?>
</body></html>