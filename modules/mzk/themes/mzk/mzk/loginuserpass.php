<?php

$this->data['header'] = 'OpenID Login';
$this->data['autofocus'] = 'openid-identifier';
$this->includeAtTemplateBase('includes/header.php');
// FIXME: move to configuration
$maintenance_file = '/opt/shibboleth/simplesamlphp/config/maintenance.txt';


?>

<?php
	$employee = ($_SERVER['REMOTE_ADDR'] == "195.113.155.2");
	$login_str = $this->t('{login:username}') . "&nbsp;<a href='http://www.mzk.cz/sluzby/navody/jak-se-prihlasit-do-katalogu'" 
		."target='_blank' style='text-decoration: none;'>(" . $this->t('{mzk:login:login_help}') . ")</a>";
	if ($employee) {
		$login_str = ($current_lang == 'en')?"Username":"Uživatelské jménof";
	}
        $reg_link = 'https://www.mzk.cz/registration_mzk';
	$error = false;
	$content = file_exists($maintenance_file) ? trim(file_get_contents($maintenance_file)) : "";
	if ($content != "") {
		$error = true;
		$error_str = "{login:maintenance_in_progress}";
	}
?>

<?php if ($error): ?>
	<p style="color:#EF406B;">
		<h3 style="color:#EF406B;"><?php echo $this->t($error_str);?> </br> <?php echo $error_str_add; ?> </h3>
	<p>
<?php else: ?>
	<form name="loginform" id="loginform" action="?" method="post">
		<div class="loginform-wrapper">
			<p>
				<label><?php echo $login_str; ?><br />
					<input type="text" name="username" id="username" class="input" <?php if (isset($this->data['username'])) { echo 'value="' . htmlspecialchars($this->data['username']) . '"'; } ?> size="20" tabindex="10" />
				</label>
			</p>
			<p>
				<label><?php echo $this->t('{login:password}'); ?><br />
					<input type="password" name="password" id="user_pass" class="input" value="" size="20" tabindex="20" />
				</label>
			</p>
			<p>
				<label>
					<input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="90" /><?php echo $this->t('{mzk:login:remember_me}'); ?>
				</label>
			</p>
			<div class="login-btn">
				<input class="btn" type="submit" name="wp-submit" id="wp-submit" value="<?php echo $this->t('{login:login_button}'); ?> &raquo;" tabindex="100" />
					<a class="btn" href="<?php echo $reg_link; ?>" title="Nejste v MZK zaregistrovaní? Přejděte na online předregistraci"><?php echo $this->t('{mzk:login:registration}'); ?>&nbsp;&raquo;</a>
			</div>

			<?php if ($this->data['errorcode'] !== NULL) { ?>
				<div id="error">
					<img src="/<?php echo $this->data['baseurlpath']; ?>resources/icons/experience/gtk-dialog-error.48x48.png" style="float: right; margin: 15px " />
					<h2><?php echo $this->t('{login:error_header}'); ?></h2>
					<p style="clear: both"><b><?php echo $this->t('{errors:title_' . $this->data['errorcode'] . '}'); ?></b></p>
					<p><?php echo $this->t('{errors:descr_' . $this->data['errorcode'] . '}'); ?></p>
				</div>
			<?php } ?>

			<div class="service-btn">
				<a class="btn" href="https://aleph.mzk.cz/cgi-bin/login_recovery.pl"><?php echo $this->t('{mzk:login:forgotten_login}'); ?></a>
				<a class="btn" href="https://aleph.mzk.cz/cgi-bin/password_recovery.pl"><?php echo $this->t('{mzk:login:password_recovery}'); ?></a>
			</div>
		</div>

		<br />
		<div>
			<?php
				foreach ($this->data['stateparams'] as $name => $value) {
					$params[$name] = $value;
				}
				$params['source'] = 'mojeid';
				$href = htmlspecialchars(\SimpleSAML\Utilities::addURLparameter(SimpleSAML\Utilities::selfURL(), $params));
			?>
			<a href="<?php echo $href?>"><img src="<?=\SimpleSAML\Module::getModuleURL('mzk/mojeid.png');?>"></img></a>
		</div>

		<div class="login-footer">
			<p><?php echo $this->t('{mzk:login:login_comment}'); ?></p>
		</div>

		<?php
			foreach ($this->data['stateparams'] as $name => $value) {
				echo('<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />');
			}
		?>
	</form>
<?php endif; ?>

<?php
$this->includeAtTemplateBase('includes/footer.php');
?>
