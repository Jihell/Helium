<!DOCTYPE html>
<html>
	<head>
		<title>{$pageTitle}</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="icon" type="image/png" href="img/favicon.ico" />
		<link rel="stylesheet" media="screen" type="text/css" title="Style" href="css/he.css" />
		<link rel="stylesheet" media="screen" type="text/css" title="Style" href="css/default.css" />
		<link rel="stylesheet" media="screen" type="text/css" title="Style" href="css/jquery-ui-1.8.14.css" />
		<link rel="stylesheet" media="screen" type="text/css" title="Style" href="css/reset.css" />
		<script type="text/javascript" src="js/jquery-1.6.1.js"></script>
		<script type="text/javascript" src="js/jquery-ui-1.8.14.js"></script>
		
		<script type="text/javascript" src="js/he/tools/ajaxform.js"></script>
		<script type="text/javascript" src="js/he/tools/infobulles.js"></script>
		<script type="text/javascript" src="js/he/tools/layer.js"></script>
		<script type="text/javascript" src="js/he/tools/overlay.js"></script>
		<script type="text/javascript" src="js/he/tools/md5.js"></script>
		<script type="text/javascript" src="js/he/tools/formTrigger.js"></script>
		<!--[if IE]>
			<script type="text/javascript" src="view/js/he/html5-ie.js"></script>
		<![endif]-->
	</head>
	<body>
		<header>
	<h1>He - Home</h1>
	<h2>Helium Engine</h2>
</header><div id="HeLogbar">
	<div class="HeSubBG">
		<div id="HeLogForm">
			<form action="ajax/login" class="ajaxForm" method="POST">
	<table style="margin-bottom: 10px;">
		<thead>
		</thead>
		<tbody>
			<tr>
				<th>Login</th>
				<td><input type="text" maxlength="50" value="" required /></td>
			</tr>
			<tr>
				<th>Mot de passe</th>
				<td><input type="password" maxlength="50" value="" required /></td>
			</tr>
			<tr>
				<th>Se souvenir de moi</th>
				<td><input type="checkbox" /></td>
			</tr>
			<tr>
				<td colspan="2" style="text-align: center;"><span class="form_submit">Connexion</span></td>
			</tr>
			<tr>
				<td style="text-align: right;">Inscription</td>
				<td>Mot de passe oublié ?</td>
			</tr>
		</tbody>
	</table>
</form>
		</div>
	</div>
	<div id="HelogButtons">
		<div id="HelogButton">
		Se connecter
		</div>
	</div>
	<script type="text/javascript" src="js/he/handler/login.js"></script>
</div><nav>
	<ul>
		<li>Menu</li>
	</ul>
</nav>
<div id="content"><h1>{%FORM_TEST}</h1>
{node::comment}
<p>
        Commentaire : {$comment}
</p>
{/node::comment}
<form action="" method="POST">
	<table>
		<thead>
		</thead>
		<tbody>
			<tr>
				<td>Test old pswd</td>
				<td><input type="password" name="testoldpswd" value="" required /></td>
			</tr>
			<tr>
				<td>Test pswd</td>
				<td><input type="password" name="testpswd" value="" required /></td>
			</tr>
			<tr>
				<td>Test re pswd</td>
				<td><input type="password" name="testpswd2" value="" required /></td>
			</tr>
			<tr>
				<td colspan="4">
					<input type="submit" value="Envoyer"/>
				</td>
			</tr>
		</tbody>
	</table>
</form>		</div> 
		<footer>
			Footer
		</footer>
	</body>
</html>