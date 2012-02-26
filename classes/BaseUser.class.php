<?php

/**
 * Base class for implementation of users.
 *
 * Requires the following fields: 
 *	email, password_hash, password_salt, roles, 
 *
 * Temporary password are stored in a non-db field called 'password';
 * 
 */
class BaseUser extends DbObject
{

	/**
	 * Default table name;
	 */
	public static $table = 'users';


	/**
	 * Current user.
	 */
	protected static $_current;

	/**
	 * Callback function called before the user
	 * is saved to the database.
	 */
	public function beforeSave() 
	{
		if ($this->password != null && $this->password != '')
		{
			$this->password_salt = spl_object_hash($this);
			$this->password_hash = self::getHash($this->password_salt, $this->password);
		}
	}

	/**
	 * Tries to authenticate the user with the given e-mail and password.
	 * Returns null if impossible.
	 */
	public static function authenticate($email, $password) 
	{
		$u = self::findOne("where `email`=?", array($email));
		if ($u != null) 
		{
			if ($u->password_hash == self::getHash($u->password_salt, $password))
			{
				$u->storeInSession();
				return $u;
		 	}
		}
		return null;
	}

	/**
	 * @return Current authenticated user.
	 */
	public static function getCurrent()
	{
		if (self::$_current == null && isset($_SESSION['user_id']))
		{
			self::$_current = self::findById($_SESSION['user_id']);
		}
		return self::$_current;
	}

	/**
	 * @return True if the user has the given role.
	 */
	public function hasRole($role)
	{
		return in_array($role, explode(',', $this->roles));
	}

	/**
	 * @return HASH
	 */
	public static function getHash($salt, $password) 
	{
		return hash_hmac('md5', $password . $salt, SITE_KEY);
	}

	/**
	 * Stores the current user's ID in the session.
	 */
	protected function storeInSession() 
	{
		$_SESSION['user_id'] = $this->id;
	}

	public static function dropSession()
	{
		unset($_SESSION['user_id']);
	}
}


?>