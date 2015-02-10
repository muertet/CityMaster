<?php
include_once('bootstrap.php');

if (!empty($_SESSION['user'])) {
	
	header('Location: '.Config::get('basedir'));
	exit;
}

if (!empty($_REQUEST['ref'])) {
	$_SESSION['referrer'] = $_REQUEST['ref'];
}

if (empty($_POST)) {
?>
<!DOCTYPE html>
<html>
<head>
    <title>City Master</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
    <base href="http://<?=Config::get('domain');?><?=Config::get('basedir');?>">
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <style>
    	body {
			font-family: Verdana;
			font-size: 1em;
		}
		#user-box {
			border: 1px solid;
			border-radius: 5px;
			margin-top: 40px;
			background-color: white;
			padding: 10px;
			z-index: 2;
			position: relative;
			width: 46%;
		}
		.clearfix:after {
			content: ".";
			display: block;
			clear: both;
			visibility: hidden;
			line-height: 0;
			height: 0;
		}
		 
		.clearfix {
			display: inline-block;
		}
		 
		html[xmlns] .clearfix {
			display: block;
		}
		 
		* html .clearfix {
			height: 1%;
		}
		#home-background {
			height: 417px;
			
		}
		#background-image{
			position: fixed;
			background: url(/assets/images/home/background.png);
			background-position: -110px -323px;
			width: 100%;
			height: 579px;
			z-index: 1;
			-webkit-filter: blur(5px);
			-moz-filter: blur(5px);
			-o-filter: blur(5px);
			-ms-filter: blur(5px);
			filter: blur(5px);
		}
		#user-box .column{
			float:left;
			width: 47%;
			text-align: left;
		}
		#user-box .column h2 {
			text-align: center;
		}
		#user-box input[type=text],#user-box input[type=password],#user-box input[type=email] {
			display: block;
			width: 94%;
			padding: 5px;
		}
		#user-box button {
			padding: 20px;
			font-size: 2em;
			margin-top: 10px;
		}
		#user-box label {
			display:block;
			margin-top: 10px;
		}
		#register-step3 img{
			width: 40%;
			margin-top: 10px;
		}
    </style>
    <link rel="stylesheet" href="assets/css/lib/apprise-v2.css" />
</head>
<body>
	<center>
	<h1>¡Bienvenido a CityMaster!</h1>
	</center>
	<div id="home-background">
		<div id="background-image"></div>
		<center>
		<div id="user-box" class="clearfix">
			<p>Para poder jugar deberás registrate / acceder a tu cuenta.</p>
			<div class="column">
				<h2>Acceder a mi cuenta</h2>
				<form method="post">
					<label>
						Email:
						<input name="email" type="email" required>
					</label>
					<label>
						Contraseña:
						<input name="password" type="password" required>
					</label>
					<center>
						<button>Acceder</button>
					</center>
				</form>
			</div>
			<div class="column">
				<h2>Registrarse</h2>
				<form id="register-form" method="post">
				<label>
					Nick:
					<input name="nick" type="text" required>
				</label>
				<label>
					Email:
					<input name="email" type="email" required>
				</label>
				<label>
					Contraseña:
					<input name="password" type="password" required>
				</label>
				<label>
					Repite la contraseña:
					<input name="password2" type="password" required>
				</label>
				<label>
					<input id="terms" type="checkbox" required>
					<input name="referrer" type="hidden" value="<? if (!empty($_SESSION['referrer'])) {echo $_SESSION['referrer'];}?>">
					Aceptas los términos y condiciones el servicio
				</label>
				<center>
					<button>Registrarse</button>
				</center>
			</form>
			</div>
			<div id="register-step2" style="display: none;">
				<h1>¿Dónde quieres crear tu imperio?</h1>
				<form action="/" id="geo_code_form" name="geo_code_form" method="get">
					<input type="text" name="form_postal_address" id="form_postal_address" autocomplete="off" placeholder="Calle, número, ciudad">
					<input name="lat" id="lat" value="" type="hidden" />
					<input name="lng" id="lng" value="" type="hidden" />
					<input name="formatted_address" value="" type="hidden" />
					<input name="postal_code" value="" type="hidden" />
					<input name="country_short" value="" type="hidden" />
					<button class="submit-button">Empezar</button>
				</form>
			</div>
			<div id="register-step3" style="display: none;">
				<center><h1>¡Sólo te queda verificar el email!</h1></center>
				<img src="assets/images/home/email-icon.jpg">
			</div>
		</div>
		</center>
	</div>
<script src="http://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=places"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
<script src="http://ubilabs.github.io/geocomplete/jquery.geocomplete.js"></script>
<script src="/assets/js/lib/apprise-v2.js"></script>

<script>
	$( document ).ready(function() {
		$("#form_postal_address").geocomplete({ details: "#geo_code_form", componentRestrictions: {country: 'es'} }).bind("geocode:result", function(event, result){
			$("#register-form").submit();
		});
		$('#geo_code_form').on('submit',function (){
			$("#register-form").submit();
			return false;
		});
		
		$('#register-form').on('submit', function(data) {
			var nick = $('[name=nick]').val(),
				email = $('#register-form [name=email]').val(),
				password = $('#register-form [name=password]').val(),
				password2 = $('[name=password2]').val(),
				referrer = $('[name=referrer]').val(),
				lat = $('[name=lat]').val(),
				lng = $('[name=lng]').val(),
				country = $('[name=country_short]').val();

			if (password != password2 || password.length < 6) {
				Apprise('Las contraseñas no coinciden o son demasiado cortas');
				return false;
			}
			
			if (!$('#terms').is(':checked')) {
				Apprise('Debes aceptar los términos y condiciones');
				return false;
			}
			
			if (lat == '' || lat == '') {
				$('#user-box .column').hide();
				$('#register-step2').show();
				return false;
			}
			
			if ($('[name=country_short]').val() != 'ES') {
				Apprise('<center>El juego sólo está disponible en España :( </center><br>Síguenos en Twitter/Facebook para saber cuándo estará disponible en tu país.');
				return false;
			}
			
			$.post('/login.php',{nick:nick, email:email, password:password, referrer:referrer, lat:lat, lng:lng, country:country}, function(response) {

				if (response == '0') {
					Apprise('Debes completar todos los campos');
				} else if(response.status == 0) {
					$('#user-box .column').show();
					$('#register-step2').hide();
						
					if (response.data.indexOf('email') != -1) {
						Apprise('Email en uso');
					}else if (response.data.indexOf('nick') != -1) {
						Apprise('nick en uso');
					}
				} else if (response.status == 1){
					$('#register-step2').hide();
					$('#user-box .column').hide();
					$('#register-step3').show();
				}
			});
			return false;
		});
	});
</script>
</body>
</html>
<?
exit;
}

/*
 register form
*/

if (!empty($_POST['nick'])) {
	header('Content-type: application/json');
	
	$post = array(
		'nick' => $_POST['nick'],
		'email' => $_POST['email'],
		'password' => $_POST['password'],
		'lat' => $_POST['lat'],
		'lng' => $_POST['lng'],
		'country' => $_POST['country'],
		'referrer' => $_POST['referrer'],
	);
	
	foreach ($post as $k => $v) {
		if (empty($v) && $k != 'referrer') {
			die('0');
		}
	}
	
	$response = ApiCaller::get('user/create', $post);
	die(json_encode($response));
}


// check if call origin
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	$fromAjax = true;
}else{
	$fromAjax = false;
}
$post = array(
	'email' => $_POST['email'],
	'password' => $_POST['password'],
);

if (empty($post['email']) || empty($post['password'])) {
	
	if ($fromAjax) {
		die('0');
	}else{
		header('Location: http://'.Config::get('domain'));
	}
}

$userToken = ApiCaller::get('usertoken/create',$post);

if( $userToken->status == 1)
{
	$_SESSION['app']->userToken = $userToken->data;	
	
	$userData = ApiCaller::get('user/get',array(),true);
	
	if($userData->status == 1)
	{
		$_SESSION['user']=$userData->data;
		
		setcookie('uid',$_SESSION['user']->id, time()+(60*60*24*7), '/'); // 1 week
		setcookie('__utmf', md5($_SESSION['user']->id), time()+(60*60*24*7), '/'); // 1 week	
	}
}


if ($fromAjax) {
	if(!empty($userData->status) && $userData->status == 1) {
		die('1');
	}else{
		die('0');
	}
}else{
	if (empty($_SERVER['HTTP_REFERER'])) {
		$url = 'http://'.Config::get('domain');
	}else{
		$url = $_SERVER['HTTP_REFERER'];
	}

	header('Location: '.$url);
}


?>
