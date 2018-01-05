<?php
class Servers {
	public $connections = array();
	public $settings = array(
		'admin_menu_category' => 'Settings',
		'admin_menu_name' => 'Servers',
		'admin_menu_icon' => '<i class="icon-tasks"></i>',
		'description' => 'Allows modules to connect to servers through SSH and execute commands.',
	);
	function admin_area() {
		global $billic, $db;
		if (isset($_GET['DeleteServer'])) {
			$db->q('DELETE FROM `servers` WHERE `name` = ?', urldecode($_GET['DeleteServer']));
			$billic->status = 'deleted';
		}
		if (isset($_GET['DeleteKey'])) {
			$db->q('DELETE FROM `servers_keys` WHERE `name` = ?', urldecode($_GET['DeleteKey']));
			$billic->status = 'deleted';
		}
		if (isset($_GET['Server'])) {
			$server = $db->q('SELECT * FROM `servers` WHERE `name` = ?', urldecode($_GET['Server']));
			$server = $server[0];
			if (empty($server)) {
				err('Server does not exist');
			}
			$billic->set_title('Admin/Server ' . safe($server['name']));
			echo '<h1>Server ' . safe($plan['name']) . '</h1>';
			if (isset($_POST['update'])) {
				if (empty($_POST['name'])) {
					$billic->error('Name can not be empty', 'name');
				}
				if (empty($billic->errors)) {
					$check = $db->q('SELECT COUNT(*) FROM `servers` WHERE `name` = ? AND `id` != ?', $_POST['name'], $server['id']);
					$check = $check[0]['COUNT(*)'];
					if ($check > 0) {
						$billic->error('Name is already in use');
					}
				}
				if (empty($billic->errors)) {
					$db->q('UPDATE `servers` SET `name` = ?, `pool` = ?, `ipaddress` = ?, `ssh_port` = ?, `ssh_user` = ?, `ssh_key` = ?, `ssh_pass` = ?, `ssh_status` = ? WHERE `name` = ?', $_POST['name'], $_POST['pool'], $_POST['ipaddress'], $_POST['ssh_port'], $_POST['ssh_user'], $_POST['ssh_key'], $_POST['ssh_pass'], '', $server['name']);
					$server = $db->q('SELECT * FROM `servers` WHERE `name` = ?', urldecode($_GET['Server']));
					$server = $server[0];
					$billic->status = 'updated';
				}
			}
			$billic->show_errors();
			echo '<form method="POST"><table class="table table-striped"><tr><th colspan="2">Server Settings</th></td></tr>';
			echo '<tr><td width="125">Name</td><td><input type="text" class="form-control" name="name" value="' . safe($server['name']) . '" maxlength="50"></td></tr>';
			echo '<tr><td width="125">Pool</td><td><input type="text" class="form-control" name="pool" value="' . safe($server['pool']) . '" maxlength="50"></td></tr>';
			echo '<tr><td width="125">IP Address</td><td><input type="text" class="form-control" name="ipaddress" value="' . safe($server['ipaddress']) . '" maxlength="45"></td></tr>';
			echo '<tr><td width="125">SSH Port</td><td><input type="text" class="form-control" name="ssh_port" value="' . safe($server['ssh_port']) . '" maxlength="6"></td></tr>';
			echo '<tr><td width="125">SSH User</td><td><input type="text" class="form-control" name="ssh_user" value="' . safe($server['ssh_user']) . '" maxlength="20" readonly onfocus="$(this).removeAttr(\'readonly\');"></td></tr>';
			echo '<tr><td width="125">SSH Key</td><td><textarea type="text" name="ssh_key" style="width: 100%;height: 200px" class="form-control">' . safe($server['ssh_key']) . '</textarea></td></tr>';
			echo '<tr><td width="125">SSH Pass</td><td><input type="password" class="form-control" name="ssh_pass" value="' . safe($server['ssh_pass']) . '" maxlength="255" readonly onfocus="$(this).removeAttr(\'readonly\');"></td></tr>';
			echo '<tr><td colspan="4" align="center"><input type="submit" class="btn btn-success" name="update" value="Update &raquo;"></td></tr></table></form>';
			return;
		}
		if (isset($_GET['Key'])) {
			$key = $db->q('SELECT * FROM `servers_keys` WHERE `name` = ?', urldecode($_GET['Key']));
			$key = $key[0];
			if (empty($key)) {
				err('SSH Key does not exist');
			}
			$billic->set_title('Admin/SSH Key ' . safe($key['name']));
			echo '<h1>SSH Key ' . safe($plan['name']) . '</h1>';
			if (isset($_POST['update'])) {
				if (empty($_POST['name'])) {
					$billic->error('Name can not be empty', 'name');
				}
				if (empty($billic->errors)) {
					$check = $db->q('SELECT COUNT(*) FROM `servers_keys` WHERE `name` = ? AND `id` != ?', $_POST['name'], $key['id']);
					$check = $check[0]['COUNT(*)'];
					if ($check > 0) {
						$billic->error('Name is already in use');
					}
				}
				if (empty($billic->errors)) {
					$db->q('UPDATE `servers_keys` SET `name` = ?, `key` = ? WHERE `name` = ?', $_POST['name'], $_POST['key'], $key['name']);
					if (!empty($_POST['change_p'])) {
						$db->q('UPDATE `servers_keys` SET `password` = ? WHERE `name` = ?', $_POST['password'], $key['name']);
					}
					$key = $db->q('SELECT * FROM `servers` WHERE `name` = ?', urldecode($_GET['Key']));
					$key = $key[0];
					$billic->status = 'updated';
				}
			}
			$billic->show_errors();
			echo '<form method="POST"><table class="table table-striped"><tr><th colspan="2">Server Settings</th></td></tr>';
			echo '<tr><td width="125">Name</td><td><input type="text" class="form-control" name="name" value="' . safe($key['name']) . '" maxlength="50"></td></tr>';
			echo '<tr><td width="125">Key</td><td><textarea type="text" name="ssh_key" style="width: 100%;height: 200px" class="form-control">' . safe($key['key']) . '</textarea></td></tr>';
			echo '<tr><td width="125">Passphrase</td><td><input type="password" class="form-control" name="change_p" value="' . safe($key['password']) . '" placeholder="Click here to change password" maxlength="255" readonly onfocus="$(this).removeAttr(\'readonly\');"></td></tr>';
			echo '<tr><td colspan="4" align="center"><input type="submit" class="btn btn-success" name="update" value="Update &raquo;"></td></tr></table></form>';
			return;
		}
		if (isset($_GET['NewServer'])) {
			$title = 'New Server';
			$billic->set_title($title);
			echo '<h1>' . $title . '</h1>';
			$form = array(
				'name' => array(
					'label' => 'Name',
					'type' => 'text',
					'required' => true,
					'default' => '',
				) ,
			);
			$billic->module('FormBuilder');
			if (isset($_POST['Continue'])) {
				$billic->modules['FormBuilder']->check_everything(array(
					'form' => $form,
				));
				if (empty($billic->errors)) {
					$check = $db->q('SELECT COUNT(*) FROM `servers` WHERE `name` = ?', $_POST['name']);
					$check = $check[0]['COUNT(*)'];
					if ($check > 0) {
						$billic->error('Name is already in use');
					}
				}
				if (empty($billic->errors)) {
					$db->insert('servers', array(
						'name' => $_POST['name'],
						'ssh_port' => 22,
						'pool' => 'Default',
					));
					$billic->redirect('/Admin/Servers/Server/' . urlencode($_POST['name']) . '/');
				}
			}
			$billic->show_errors();
			$billic->modules['FormBuilder']->output(array(
				'form' => $form,
				'button' => 'Continue',
			));
			return;
		}
		if (isset($_GET['NewKey'])) {
			$title = 'New Key';
			$billic->set_title($title);
			echo '<h1>' . $title . '</h1>';
			$form = array(
				'name' => array(
					'label' => 'Name',
					'type' => 'text',
					'required' => true,
					'default' => '',
				) ,
			);
			$billic->module('FormBuilder');
			if (isset($_POST['Continue'])) {
				$billic->modules['FormBuilder']->check_everything(array(
					'form' => $form,
				));
				if (empty($billic->errors)) {
					$check = $db->q('SELECT COUNT(*) FROM `servers_keys` WHERE `name` = ?', $_POST['name']);
					$check = $check[0]['COUNT(*)'];
					if ($check > 0) {
						$billic->error('Name is already in use');
					}
				}
				if (empty($billic->errors)) {
					$db->insert('servers_keys', array(
						'name' => $_POST['name'],
					));
					$billic->redirect('/Admin/Servers/Key/' . urlencode($_POST['name']) . '/');
				}
			}
			$billic->show_errors();
			$billic->modules['FormBuilder']->output(array(
				'form' => $form,
				'button' => 'Continue',
			));
			return;
		}
		$total = $db->q('SELECT COUNT(*) FROM `servers`');
		$total = $total[0]['COUNT(*)'];
		$pagination = $billic->pagination(array(
			'total' => $total,
		));
		echo $pagination['menu'];
		$servers = $db->q('SELECT * FROM `servers` ORDER BY `name` ASC LIMIT ' . $pagination['start'] . ',' . $pagination['limit']);
		$billic->set_title('Admin/Servers');
		echo '<h1><i class="icon-tasks"></i> Servers</h1>';
		echo '<a href="NewServer/" class="btn btn-success"><i class="icon-plus"></i> New Server</a>';
		echo '<div>Showing ' . $pagination['start_text'] . ' to ' . $pagination['end_text'] . ' of ' . $total . ' Servers</div>';
		$billic->show_errors();
		echo '<table class="table table-striped"><tr><th>Name</th><th>Pool</th><th>IP</th><th>Status</th><th>Actions</th></tr>';
		if (empty($servers)) {
			echo '<tr><td colspan="4">No Servers matching filter.</td></tr>';
		}
		foreach ($servers as $server) {
			echo '<tr><td><b>' . safe($server['name']) . '</b></td><td>' . safe($server['pool']) . '</td><td>' . safe($server['ipaddress']) . '</td><td>' . safe($server['status']) . '</td><td><a href="/Admin/Servers/Server/' . urlencode($server['name']) . '/" class="btn btn-primary btn-xs"><i class="icon-edit-write"></i> Edit</a> <a href="/Admin/Servers/DeleteServer/' . urlencode($server['name']) . '/" class="btn btn-danger btn-xs" onclick="return confirm(\'Are you sure you want to delete the server?\')"><i class="icon-delete-circle"></i> Delete</a></td></tr>';
		}
		echo '</table>';
		echo '<h1><i class="icon-shield"></i> SSH Keys</h1>';
		echo '<a href="NewKey/" class="btn btn-success"><i class="icon-plus"></i> New Key</a>';
		$servers = $db->q('SELECT * FROM `servers_keys` ORDER BY `name` ASC');
		echo '<table class="table table-striped"><tr><th>Name</th><th>Actions</th></tr>';
		if (empty($servers)) {
			echo '<tr><td colspan="4">No SSH Keys.</td></tr>';
		}
		foreach ($servers as $server) {
			echo '<tr><td><b>' . safe($server['name']) . '</b></td><td><a href="/Admin/Servers/Key/' . urlencode($server['name']) . '/" class="btn btn-primary btn-xs"><i class="icon-edit-write"></i> Edit</a> <a href="/Admin/Servers/DeleteKey/' . urlencode($server['name']) . '/" class="btn btn-danger btn-xs" onclick="return confirm(\'Are you sure you want to delete the key?\')"><i class="icon-delete-circle"></i> Delete</a></td></tr>';
		}
		echo '</table>';
	}
	function exec($array) {
		global $billic, $db;
		if (isset($array['debug']) && $array['debug'] === true) {
			$debug = true;
		} else {
			$debug = false;
		}
		$server = $db->q('SELECT * FROM `servers` WHERE `name` = ?', $array['server']);
		$server = $server[0];
		if (empty($server)) {
			err('SSH: Server "' . safe($array['server']) . '" does not exist');
		}
		if (!isset($this->connections[$server['name']])) {
			$dir = getcwd();
			chdir('Modules/Servers/phpseclib/');
			require_once 'Exception/BadConfigurationException.php';
			require_once 'Exception/FileNotFoundException.php';
			require_once 'Exception/NoSupportedAlgorithmsException.php';
			require_once 'Exception/UnsupportedAlgorithmException.php';
			require_once 'Crypt/Base.php';
			require_once 'Crypt/Blowfish.php';
			require_once 'Crypt/DES.php';
			require_once 'Crypt/Rijndael.php';
			require_once 'Crypt/TripleDES.php';
			require_once 'Crypt/Twofish.php';
			require_once 'Crypt/AES.php';
			require_once 'Crypt/Hash.php';
			require_once 'Crypt/RC2.php';
			require_once 'Crypt/RC4.php';
			require_once 'Crypt/RSA.php';
			require_once 'Crypt/Random.php';
			require_once 'Math/BigInteger.php';
			require_once 'Net/SSH2.php';
			// Hide Errors to prevent password being shown
			$error_reporting = ini_get('error_reporting');
			ini_set('error_reporting', 0);
			if ($debug) {
				echo '[' . safe($server['name']) . '] Connecting... ';
			}
			$port = $server['ssh_port'];
			if ($port < 1) {
				$port = 22;
			}
			$this->connections[$server['name']] = new \phpseclib\Net\SSH2($server['ipaddress'], $port);
			if ($debug) {
				echo 'OK<br>';
				echo '[' . safe($server['name']) . '] Authenticating... ';
			}
			if (!empty($server['ssh_key'])) {
				$key = new Crypt_RSA();
				$key->loadKey($server['ssh_key']);
				try {
					if (!$this->connections[$server['name']]->login($server['ssh_user'], $key)) {
						err('SSH: Login to  "' . safe($array['server']) . '" failed');
					}
				}
				catch(RuntimeException $e) {
					err('SSH: Login to  "' . safe($array['server']) . '" failed');
				}
			} else if (!empty($server['ssh_pass'])) {
				try {
					if (!$this->connections[$server['name']]->login($server['ssh_user'], $server['ssh_pass'])) {
						err('SSH: Login to  "' . safe($array['server']) . '" failed');
					}
				}
				catch(RuntimeException $e) {
					err('SSH: Login to  "' . safe($array['server']) . '" failed');
				}
			} else {
				err('I don\t know how to login to "' . safe($array['server']) . '"');
			}
			chdir($dir);
			if ($debug) {
				echo 'OK<br>';
			}
			ini_set('error_reporting', $error_reporting);
		}
		if (isset($array['timeout'])) {
			$this->connections[$server['name']]->setTimeout($array['timeout']);
		}
		if ($debug) {
			echo '[' . safe($server['name']) . '] Executing Command... ';
		}
		if (isset($array['bash_base64']) && $array['bash_base64'] === true) {
			$array['command'] = 'echo ' . escapeshellarg(base64_encode($array['command'])) . ' | base64 -d | bash';
		}
		$start = microtime(true);
		$return = array();
		$return['output'] = $this->connections[$server['name']]->exec($array['command']);
		$return['time'] = round(microtime(true) - $start, 2);
		if ($debug) {
			echo 'OK (' . $return['time'] . 's)<br>';
			echo '<pre>' . safe($return['output']) . '</pre><br><br>';
		}
		return $return;
	}
}
