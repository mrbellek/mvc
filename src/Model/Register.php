<?php
namespace MVC\Model;
use MVC\Lib\Model;
use \Exception;

class Register extends Model {

	public function Validate($aData) {

		if ($this->UserExists($aData['username']) && !$_SESSION['user']['username'] == $aData['username']) {
			throw new Exception('This email address is already taken.');

		} elseif (!validEmailSyntax($aData['username'])) {
			throw new Exception('Email adress is invalid.');

		} elseif (empty($aData['password']) || empty($aData['password_confirm'])) {
			throw new Exception('Password is empty.');

		} elseif ($aData['password'] !== $aData['password_confirm']) {
			throw new Exception('Passwords aren\'t the same.');
		}

		return TRUE;
	}

	private function UserExists($sUsername) {

		return $this->SQL->fetch_single('
			SELECT *
			FROM user
			WHERE username = :username
			OR email = :username',
			array(
				':username' => $sUsername,
			)
		);
	}

	public function RegisterAndLogin($aData) {

		$iUserId = $this->Register($aData, !empty($aData['admin']));

		return ($iUserId ? $this->Login($aData) : FALSE);
	}

	private function Register($aData, $bAdmin = FALSE) {

		$sUsername = $aData['username'];
		$sPassword = $aData['password'];

		$sHash = password_hash($sPassword, PASSWORD_BCRYPT, array('cost' => 12));

		$aRoles = array('ROLE_USER');
		if ($bAdmin) {
			$aRoles[] = 'ROLE_SUPER_ADMIN';
		}

		$iUserId = $this->SQL->fquery('
			INSERT INTO user
				(username, email, enabled, salt, password, locked, expired, roles, credentials_expired)
			VALUES
				(:username, :email, 1, "", :password, 0, 0, :roles, 0)',
			array(
				':username' => $sUsername,
				':email' => $sUsername,
				':password' => $sHash,
				':roles' => serialize($aRoles),
			)
		);

		return $iUserId;
	}

	private function Login($aData) {

		return (new \MVC\Model\Login)->login($aData);
	}
}
