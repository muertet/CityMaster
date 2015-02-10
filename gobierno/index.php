<?
include_once('bootstrap.php');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gobierno de CityMaster</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
    <base href="http://<?=Config::get('domain');?><?=Config::get('basedir');?>">
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <link rel="shortcut icon" href="/favicon.ico" />
    <style>
      html, body, #map {
        height: 100%;
        padding: 0;
        margin: 0;
        font-family: Verdana;
      }
      p {
      	margin: 5px;
      }
    </style>
</head>
<body>
<div>
	<h1>Crear objeto</h1>
	<div>
		<form method="post">
			
			<button>Crear</button>
		</form>
	</div>
</div>	
</body>
</html>