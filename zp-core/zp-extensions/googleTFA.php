<?php

/**
 * This plugin is a handler for authentication via the Google Authenticator App.
 * It is based on the {@link https://github.com/Dolondro/google-authenticator google-authenticator library}
 * by Doug Dolondro.
 *
 * When the plugin is enabled there will be a check box for each user that allows him to use
 * <i>Two Factor Authentication</i> when logging onto your site. If <i>Two Factor Authentication</i>
 * is enabled there will also be a QR code image displayed
 * so that the user can add your site to his Google Authenticator providers.
 *
 * If a user has enabled <i>Two Factor Authentication</i> there will be an extra step added
 * to his logon process. After his <code>user name</code> and <code>password</code> have been verified he will be
 * asked to supply the <code>token</code> generated by the Google Authenticator App. If the <code>token</code>
 * he enters is valid the logon will complete.
 *
 * <strong>NOTE</strong>: Google Authenticator relies on time to create the <code>token</code>.
 * If your Server's clock is not in-sync with devices running Google Authenticator,
 * token validation may fail. This can be alleviated by setting the web servers to
 * synchronize with an accurate time source such as an NTP server.
 *
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins/googleTFA
 * @pluginCategory admin
 *
 * Copyright 2018 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 */
$plugin_is_filter = 5 | CLASS_PLUGIN;
$plugin_description = gettext('Two Factor Authentication.');

$option_interface = 'googleTFA';

require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/common/fieldExtender.php');
require_once (SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/googleTFA/Secret.php');
require_once (SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/googleTFA/SecretFactory.php');

zp_register_filter('admin_login_attempt', 'googleTFA::check');
zp_register_filter('save_admin_custom_data', 'googleTFA::save');
zp_register_filter('edit_admin_custom_data', 'googleTFA::edit', 999);

class googleTFA extends fieldExtender {

	function __construct() {
		if (OFFSET_PATH == 2) {
			setOptionDefault('googleTFA_issuer', $_SERVER['HTTP_HOST'] . WEBPATH);

			parent::constructor('googleTFA', self::fields());
		}
	}

	function getOptionsSupported() {
		return array(
				gettext('Issuer name') => array('key' => 'googleTFA_issuer', 'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext('This is the name the Google Authenticator app associate with the one time pin code.'))
		);
	}

	static function fields() {
		return array(
				array('table' => 'administrators', 'name' => 'OTAsecret', 'desc' => gettext('secret for googleAuthenticator'), 'type' => 'tinytext'),
				array('table' => 'administrators', 'name' => 'QRuri', 'desc' => gettext('googleAuthenticator QR code data'), 'type' => 'tinytext')
		);
	}

	static function check($loggedin, $post_user, $post_pass, $userobj) {
		if ($userobj->getOTAsecret()) {
			$_SESSION['OTA'] = array('user' => $post_user, 'redirect' => $_POST['redirect']);
			header('Location: ' . WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/googleTFA/auth_code.php');
			exitZP();
		}
		// redirect to form to have the user provide the googleAuth key
		return $loggedin;
	}

	static function save($updated, $userobj, $i, $alter) {
		if (isset($_POST['user'][$i]['otp']) && $alter) {
			if (!$userobj->getOTAsecret()) {
				$secretFactory = new \Dolondro\GoogleAuthenticator\SecretFactory();
				$secret = $secretFactory->create(WEBPATH, $userobj->getUser());
				$userobj->setOTAsecret($secret->getSecretKey());
				$userobj->setQRuri($secret->getUri());
				$updated = true;
			}
		} else {
			if ($userobj->getOTAsecret()) {
				$userobj->setOTAsecret(NULL);
				$updated = true;
			}
		}
		return $updated;
	}

	static function edit($html, $userobj, $id, $background, $current) {
		if ($userobj->getOTAsecret()) {
			$checked = ' checked="checked"';
		} else {
			$checked = '';
		}
		$result = '<div class="user_left">' . "\n"
						. '<input type="checkbox" name="user[' . $id . '][otp]" value="1" ' . $checked . ' />&nbsp;'
						. gettext("Two Factor Authentication") . "\n";

		if ($checked) {
			$result .= "<br />\n"
							. "<fieldset>\n"
							. '<legend>' . gettext('Provide to GoogleAuthenticator') . "</legend>\n"
							. '<div style="display: flex; justify-content: center;">'
							. '<img src="' . WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/qrcode/image.php?content=' . html_encode($userobj->getQRuri()) . '" title="' . html_encode($userobj->getOTAsecret()) . '" />'
							. '</div>'
							. "</fieldset>\n"
			;
		}
		$result .= '</div>' . "\n"
						. '<br class="clearall">' . "\n";
		return $html . $result;
	}

}

function googleTFA_enable($enabled) {
	if ($enabled) {
		$report = gettext('<em>OTAsecret</em> field will be added to the Administrator object.');
	} else {
		$report = gettext('<em>OTAsecret</em> field will be <span style="color:red;font-weight:bold;">dropped</span> from the Administrator object.');
	}
	requestSetup('googleTFA', $report);
}
