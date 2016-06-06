<?php
namespace MVC\Model;
use MVC\Lib\Model;

class Login extends Model {

	public function login($aData) {

		if (empty($aData['username']) || empty($aData['password'])) {
			return FALSE;
		}

		$aUser = $this->SQL->fetch_single('
			SELECT *
			FROM user
			WHERE username = :username
			AND enabled = 1
			AND locked = 0
			AND expired = 0
			AND credentials_expired = 0
			LIMIT 1',
			array(':username' => $aData['username'])
		);

		if ($aUser) {
			if (password_verify($aData['password'], $aUser['password'])) {

				$this->SQL->fquery('
					UPDATE user
					SET last_login = NOW()
					WHERE id = :id
					LIMIT 1',
					array(':id' => $aUser['id'])
				);

				$aRoles = @unserialize($aUser['roles']);
				$_SESSION['user'] = array(
					'id' => $aUser['id'],
					'username' => $aUser['username'],
					'email' => $aUser['email'],
					'roles' => array(
						'is_admin' => in_array('ROLE_SUPER_ADMIN', $aRoles),
					),

				);

				return TRUE;
			}

		}

		return FALSE;
	}
}
