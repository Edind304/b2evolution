<?php
/**
 * This file implements the Request class.
 *
 * This file is part of the b2evolution/evocms project - {@link http://b2evolution.net/}.
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2005 by Francois PLANQUE - {@link http://fplanque.net/}.
 *
 * @license http://b2evolution.net/about/license.html GNU General Public License (GPL)
 * {@internal
 * b2evolution is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * b2evolution is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with b2evolution; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * }}
 *
 * @package evocore
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author fplanque: Francois PLANQUE.
 *
 * @version $Id$
 */
if( !defined('EVO_CONFIG_LOADED') ) die( 'Please, do not access this page directly.' );


/**
 * Request class: handles the current HTTP request
 *
 * @todo (fplanque)
 */
class Request
{
	/**
	 * @var Array of values, indexed by param name
	 */
	var $params = array();

	/**
	 * @var Array of strings, indexed by param name
	 */
	var $err_messages = array();

	/**
	 * @var Log Reference to a Log object that gets used to add (error) messages to.
	 */
	var $Messages;

	/**
	 * If true, the internal function {@link _add_message_to_Log()} will create links to IDs
	 * of the fields where the error occurred.
	 * @var boolean
	 */
	var $link_log_messages_to_field_IDs = false;


	/**
	 * Constructor.
	 */
	function Request( & $Messages )
	{
		$this->Messages = & $Messages;
	}


	/**
	 * Sets a parameter with values from the request or to provided default,
	 * except if param is already set!
	 *
	 * Also removes magic quotes if they are set automatically by PHP.
	 * Also forces type.
	 * Priority order: POST, GET, COOKIE, DEFAULT.
	 *
	 * {@internal param(-) }}
	 *
	 * @author fplanque
	 * @param string Variable to set
	 * @param string Force value type to one of:
	 * - boolean
	 * - integer
	 * - float
	 * - string
	 * - array
	 * - object
	 * - null
	 * - html (does nothing)
	 * - '' (does nothing)
	 * Value type will be forced only if resulting value (probably from default then) is !== NULL
	 * @param mixed Default value or TRUE if user input required
	 * @param boolean Do we need to memorize this to regenerate the URL for this page?
	 * @param boolean Override if variable already set
	 * @param boolean Force setting of variable to default?
	 * @return mixed Final value of Variable, or false if we don't force setting and did not set
	 */
	function param( $var, $type = '', $default = '', $memorize = false, $override = false, $forceset = true )
	{
    return $this->params[$var] = param( $var, $type, $default, $memorize, $override, $forceset );
	}

	/**
	 * @param array
	 */
	function params( $vars, $type = '', $default = '', $memorize = false, $override = false, $forceset = true )
	{
		foreach( $vars as $var )
		{
			$this->param( $var, $type = '', $default = '', $memorize = false, $override = false, $forceset = true );
		}
	}


	/**
	 * Get the value of a param.
	 *
	 * @return NULL|mixed The value of the param, if set. NULL otherwise.
	 */
	function get( $var )
	{
		return isset($this->params[$var]) ? $this->params[$var] : NULL;
	}


	/**
	 * @param string param name
	 * @param string error message
	 * @return boolean true if OK
	 */
	function param_string_not_empty( $var, $err_msg )
	{
		$this->param( $var, 'string', true );
		return $this->param_check_not_empty( $var, $err_msg );
	}


	/**
	 * @param string param name
	 * @param string error message
	 * @return boolean true if OK
	 */
	function param_check_not_empty( $var, $err_msg )
	{
		if( empty( $this->params[$var] ) )
		{
			$this->param_error( $var, $err_msg );
			return false;
		}
		return true;
	}


	/**
	 * @param string param name
	 * @param string error message
	 * @return boolean true if OK
	 */
	function param_check_number( $var, $err_msg, $required = false )
	{
		if( empty( $this->params[$var] ) && ! $required )
		{ // empty is OK:
			return true;
		}

		if( ! preg_match( '#^[0-9]+$#', $this->params[$var] ) )
		{
			$this->param_error( $var, $err_msg );
			return false;
		}
		return true;
	}


	/**
	 * @param string param name
	 * @param integer min value
	 * @param integer max value
	 * @param string error message
	 * @return boolean true if OK
	 */
	function param_integer_range( $var, $min, $max, $err_msg, $required = true )
	{
		$this->param( $var, 'integer', $required ? true : '' );
		return $this->param_check_range( $var, $min, $max, $err_msg, $required );
	}


	/**
	 * @param string param name
	 * @param integer min value
	 * @param integer max value
	 * @param string error message
	 * @return boolean true if OK
	 */
	function param_check_range( $var, $min, $max, $err_msg, $required = true )
	{
		if( empty( $this->params[$var] ) && ! $required )
		{ // empty is OK:
			return true;
		}

		if( $this->params[$var] < $min || $this->params[$var] > $max )
		{
			$this->param_error( $var, sprintf( $err_msg, $min, $max ) );
			return false;
		}
		return true;
	}


	/**
	 * @param string param name
	 * @return boolean true if OK
	 */
	function param_check_email( $var, $required = false )
	{
		if( empty( $this->params[$var] ) && ! $required )
		{ // empty is OK:
			return true;
		}

		if( !is_email( $this->params[$var] ) )
		{
			$this->param_error( $var, T_('The email address is invalid.') );
			return false;
		}
		return true;
	}


	/**
	 * @param string param name
	 * @return boolean true if OK
	 */
	function param_check_phone( $var, $required = false )
	{
		if( empty( $this->params[$var] ) && ! $required )
		{ // empty is OK:
			return true;
		}

		if( ! preg_match( '|^\+?[\-*#/(). 0-9]+$|', $this->params[$var] ) )
		{
			$this->param_error( $var, T_('The phone number is invalid.') );
			return false;
		}
		return true;
	}


	/**
	 * @param string param name
	 * @param string error message
	 * @return boolean true if OK
	 */
	function param_check_url( $var, & $uri_scheme )
	{
		if( $error_detail = validate_url( $this->params[$var], $uri_scheme ) )
		{
			$this->param_error( $var, sprintf( T_('Supplied URL is invalid. (%s)'), $error_detail ) );
			return false;
		}
		return true;
	}



	/**
	 * Check if param is an ISO date
	 *
	 * @param string param name
	 * @param string error message
	 * @return boolean true if OK
	 */
	function param_check_date( $var, $err_msg, $required = false )
	{
		if( empty( $this->params[$var] ) && ! $required )
		{ // empty is OK:
			return true;
		}

		if( preg_match( '#^(\d\d\d\d)-(\d\d)-(\d\d)$#', $this->params[$var], $matches ) )
		{
			$year = intval($matches[1]);
			$month = intval($matches[2]);
			$day = intval($matches[3]);
			if( checkdate( $month, $day, $year ) )
			{ // all clean! :)
				return true;
			}
		}

		$this->param_error( $var, $err_msg );
		return false;
	}


	/**
	 * Check if the value of a param is a regular expression (syntax).
	 *
	 * @param string param name
	 * @param string error message
	 * @return boolean true if OK
	 */
	function param_check_regexp( $var, $err_msg )
	{
		if( ! isRegexp( $this->params[$var] ) )
		{
			$this->param_error( $var, $err_msg );
			return false;
		}
		return true;
	}


	/**
	 * @param string param name
	 * @param string param name
	 * @param boolean
	 * @return boolean true if OK
	 */
	function param_check_passwords( $var1, $var2, $required = false )
	{
		global $Settings;

		$pass1 = $this->params[$var1];
		$pass2 = $this->params[$var2];

		if( empty($pass1) && empty($pass2) && ! $required )
		{ // empty is OK:
			return true;
		}

		if( empty($pass1) )
		{
			$this->param_error( $var1, T_('Please enter your password twice.') );
			return false;
		}
		if( empty($pass2) )
		{
			$this->param_error( $var2, T_('Please enter your password twice.') );
			return false;
		}

		// checking the password has been typed twice the same:
		if( $pass1 != $pass2 )
		{
			$this->param_error_multiple( array( $var1, $var2), T_('You typed two different passwords.') );
			return false;
		}

		if( strlen($pass1) < $Settings->get('user_minpwdlen') )
		{
			$this->param_error_multiple( array( $var1, $var2), sprintf( T_('The mimimum password length is %d characters.'), $Settings->get('user_minpwdlen') ) );
			return false;
		}

		return true;
	}


	/**
	 * @param array of param names
	 * @param string error message
	 * @return boolean true if OK
	 */
	function params_check_at_least_one( $vars, $err_msg )
	{
		foreach( $vars as $var )
		{
			if( !empty( $this->params[$var] ) )
			{ // Okay, we got at least one:
				return true;
			}
		}

		// Error!
		$this->param_error_multiple( $vars, $err_msg );
		return false;
	}


	/**
	 * Check if there have been validation errors
	 *
	 * We play it safe here and check for all kind of errors, not just those from this particlar class.
	 *
	 * @return integer
	 */
	function validation_errors()
	{
		return $this->Messages->count('error');
	}


	/**
	 * @access protected
	 *
	 * @param string param name
	 * @param string error message
	 * @param string|NULL error message for form field ($err_msg gets used if === NULL).
	 */
	function param_error( $var, $err_msg, $field_msg = NULL )
	{
		if( ! isset( $this->err_messages[$var] ) )
		{ // We haven't already recorded an error for this field:
			if( $field_msg === NULL )
			{
				$field_msg = $err_msg;
			}
			$this->err_messages[$var] = $field_msg;

			$this->_add_message_to_Log( $var, $err_msg, 'error' );
		}
	}


	/**
	 * @access protected
	 *
	 * @param array of param names
	 * @param string error message
	 * @param string|NULL error message for form fields ($err_msg gets used if === NULL).
	 */
	function param_error_multiple( $vars, $err_msg, $field_msg = NULL )
	{
		if( $field_msg === NULL )
		{
			$field_msg = $err_msg;
		}

		foreach( $vars as $var )
		{
			if( ! isset( $this->err_messages[$var] ) )
			{ // We haven't already recorded an error for this field:
				$this->err_messages[$var] = $err_msg;
			}
		}

		$this->_add_message_to_Log( $var, $err_msg, 'error' );
	}


	/**
	 * This function is used by {@link param_error()} and {@link param_error_multiple()}.
	 *
	 * @access protected
	 *
	 * @param string param name
	 * @param string error message
	 */
	function _add_message_to_Log( $var, $err_msg, $log_category = 'error' )
	{
		if( $this->link_log_messages_to_field_IDs )
		{
			$var_id = Form::get_valid_id($var);
			$this->Messages->add( '<a href="#'.$var_id.'" onclick="var form_elem = document.getElementById(\''.$var_id.'\'); if( form_elem ) { form_elem.select(); }">'.$err_msg.'</a>', $log_category );
		}
		else
		{
			$this->Messages->add( $err_msg, $log_category );
		}
	}
}

/*
 * $Log$
 * Revision 1.7  2005/08/10 13:18:03  blueyed
 * Added property $link_log_messages_to_field_IDs;
 * param(): only set/remember param if it has been set (which must not be the case for !$forceset);
 * get(): explicitly return NULL if param is not set;
 * added optional $field_msg param to param_error()/param_error_multiple();
 * doc
 *
 * Revision 1.6  2005/06/10 18:25:44  fplanque
 * refactoring
 *
 * Revision 1.5  2005/06/06 17:59:39  fplanque
 * user dialog enhancements
 *
 * Revision 1.4  2005/06/03 20:14:39  fplanque
 * started input validation framework
 *
 */
?>