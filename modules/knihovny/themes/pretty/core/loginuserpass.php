<?php
	$theme = 'light';
	if (isset($_SERVER['SIMPLESAMLPHP_THEME'])) {
		$theme = $_SERVER['SIMPLESAMLPHP_THEME'];
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" data-tldr="true">
<head>
	<title><?php echo $this->t('{login:user_pass_header}'); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

	<link rel='stylesheet' href="<?php echo SimpleSAML_Module::getModuleURL('knihovny/'. $theme . '.css?v0'); ?>" type='text/css' />
	<!--[if IE]><style type="text/css">#login h1 a { margin-top: 35px; } #login #login_error { margin-bottom: 10px; }</style><![endif]--><!-- Curse you, IE! -->

	<script type="text/javascript">
		function focusit() {
			document.getElementById('username').focus();
		}
		window.onload = focusit;
	</script>
</head>
<body class="login">
<!-- Google Tag Manager -->
<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-TXJ544"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-TXJ544');</script>
<!-- End Google Tag Manager -->

<?php
	$loginRecoveryLink = "";
	$passwordRecoveryLink = "";
	$registeryLink = "";

	$current_lang = "en";
	$languages = $this->getLanguageList();
	foreach ($languages AS $lang => $current) {
		if ($current) {
			$current_lang = $lang;
		}
	}
	if ($current_lang == 'en') {
		$switch_lang = 'cs';
	} else {
		$switch_lang = 'en';
	}
	$params = array('language' => $switch_lang);
	foreach ($this->data['stateparams'] as $name => $value) {
		$params[$name] = $value;
	}
	$href = htmlspecialchars(SimpleSAML_Utilities::addURLparameter(SimpleSAML_Utilities::selfURL(), $params));
	$img = SimpleSAML_Module::getModuleURL('knihovny/'.$switch_lang.'.gif');
	$lang = "<a href='$href'> <img align='right' src='$img'/> </a>";
	$login_str = ($current_lang == 'en')?"Username":"Uživatelské jméno";

	$error = false;
	$content = trim(file_get_contents("/data/www/idps-hosted/maintenance.txt"));
	if ($content != "") {
		$error = true;
		$error_str = "{login:maintenance_in_progress}"; // "You can't login at this time. Maintenance in progress.";
	}
?>

<div class="container">
	<div class="login-header">
		<!-- <p class="lng-switch"><?php echo $lang ?></p> -->
	</div>
	<div id="login">
		
		<form name="loginform" id="loginform" action="?" method="post">			
				<p>
					<img src="/logo.png" height="40" align="bottom" alt="logo"/>
					<span align='right'><?php echo $lang; ?></span>
				</p>
				<p><h1><?php echo $this->t('{knihovny:login:institution_login}'); ?></h1></p>
				<?php if($error) { ?>
					<p style="color:#EF406B;">
						<h3 style="color:#EF406B;"><?php echo $this->t($error_str);?> </br> <?php echo $error_str_add; ?> </h3>
					<p>
				<?php } else { ?>
				<div class="loginform-wrapper">
				<p>
					<label><?php echo $login_str; ?><br />					
					<input type="text" name="username" id="username" class="input" <?php if (isset($this->data['username'])) { echo 'value="' . htmlspecialchars($this->data['username']) . '"'; } ?> size="20" tabindex="10" /></label>
				</p>
				
				<p>
					<label><?php echo $this->t('{login:password}'); ?><br />
					<input type="password" name="password" id="user_pass" class="input" value="" size="20" tabindex="20" /></label>
				</p>
				
				<p><label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="90" /><?php echo $this->t('{login:remember_me}'); ?> </label></p>
				
				<div class="login-btn">
					<input class="btn-large" type="submit" name="wp-submit" id="wp-submit" value="<?php echo $this->t('{login:login_button}'); ?> &raquo;" tabindex="100" />
					<?php if (!empty($registeryLink)): ?>
						<a class="btn" href="<?php echo $registeryLink ?>" title="Nejste u nás zaregistrovaní? Přejděte na online předregistraci"><?php echo $this->t('{login:registration}'); ?>&nbsp;&raquo;</a>
					<?php endif; ?>
				</div>


	<?php
	if ($this->data['errorcode'] !== NULL) {
	?>
		<div id="error">
			<img src="/<?php echo $this->data['baseurlpath']; ?>resources/icons/experience/gtk-dialog-error.48x48.png" style="float: right; margin: 15px " />
			<h2><?php echo $this->t('{login:error_header}'); ?></h2>
			<p style="clear: both"><b><?php echo $this->t('{errors:title_' . $this->data['errorcode'] . '}'); ?></b></p>
			<p><?php echo $this->t('{errors:descr_' . $this->data['errorcode'] . '}'); ?></p>
		</div>
	<?php
	}
        ?>

		<?php if (!empty($loginRecoveryLink) || !empty($passwordRecoveryLink)): ?>
			<div class="service-btn">
				<?php if (!empty($loginRecoveryLink)): ?>
					<a class="btn" href="<?php echo $loginRecoveryLink ?>"><?php echo $this->t('{login:forgotten_login}'); ?></a>
				<?php endif; ?>
				<?php if (!empty($passwordRecoveryLink)): ?>
					<a class="btn" href="<?php echo $passwordRecoveryLink ?>"><?php echo $this->t('{login:password_recovery}'); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>

			<div class="login-footer">
				<p><?php echo $this->t('{knihovny:login:login_comment}'); ?></p>
			</div>
                        <?php } ?>			

	<?php
	if(!empty($this->data['links'])) {
		echo '<ul class="links" style="margin-top: 2em">';
		foreach($this->data['links'] AS $l) {
			echo '<li><a href="' . htmlspecialchars($l['href']) . '">' . htmlspecialchars($this->t($l['text'])) . '</a></li>';
		}
		echo '</ul>';
	}

	?>
	<?php
	foreach ($this->data['stateparams'] as $name => $value) {
		echo('<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />');
	}
	?>
		</form>
	</div>
</div>
</body>
</html>
