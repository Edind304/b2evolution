<?php
/*
 * b2evolution - http://b2evolution.net/
 *
 * Copyright (c) 2003-2004 by Francois PLANQUE - http://fplanque.net/
 * Released under GNU GPL License - http://b2evolution.net/about/license.html
 *
 * This file posts a comment!
 */

// Initialize everything:
require_once( dirname(__FILE__) . '/../b2evocore/_main.php' );

// statuses allowed for acting on:
$show_statuses = array( 'published', 'protected', 'private' );

// Getting GET or POST parameters:
param( 'comment_post_ID', 'integer', true ); // required

$commented_Item = Item_get_by_ID( $comment_post_ID );

if( ! $commented_Item->can_comment( '', '', '', '' ) )
{
	errors_add( T_('You cannot leave comments on this post!') );
}

param( 'author', 'string' );
param( 'email', 'string' );
param( 'url', 'string' );
param( 'comment' , 'html' );
param( 'comment_autobr', 'integer', ($comments_use_autobr == 'always') ? 1 : 0 );
param( 'comment_cookies', 'integer', 0 );

if( is_logged_in() )
{ // User is loggued in, we'll use his ID
	$author_ID = $current_User->ID;
	$author = NULL;
	$email = NULL;
	$url = NULL;
}
else
{	// User is not logged in, we need some id info from him:
	$author_ID = NULL;

	if ($require_name_email)
	{ // Blog wants Name and EMail with comments
		if( empty($author) ) errors_add( T_('Please fill in the name field') );
		if( empty($email) ) errors_add( T_('Please fill in the email field') );
	}
	
	if( (!empty($email)) && (!is_email($email)) )
	{
		errors_add( T_('Supplied email address is invalid') );
	}
	
	// add 'http://' if no protocol defined for URL
	$url = ((!stristr($url, '://')) && ($url != '')) ? 'http://' . $url : $url;
	if( strlen($url) < 7 ){
		$url = '';
	}
	if( $error = validate_url( $url, $comments_allowed_uri_scheme ) )
	{
		errors_add( T_('Supplied URL is invalid: ') . $error );	
	}
}

$user_ip = $_SERVER['REMOTE_ADDR'];
$now = date("Y-m-d H:i:s", $localtimenow );

// CHECK and FORMAT content
//echo 'allowed tags:',htmlspecialchars($comment_allowed_tags);	
$original_comment = strip_tags($comment, $comment_allowed_tags);
$comment = format_to_post($original_comment, $comment_autobr, 1);

if( empty($comment) )
{ // comment should not be empty!
	errors_add( T_('Please do not send empty comment') );
}

/* flood-protection */
$query = "SELECT max(comment_date)
					FROM $tablecomments
					WHERE comment_author_IP = '$user_ip'";
$ok = 1;
if( $then = $DB->get_var( $query ) )
{
	$time_lastcomment = mysql2date("U",$then);
	$time_newcomment = mysql2date("U",$now);
	if( ($time_newcomment - $time_lastcomment) < 30)
		$ok = 0;
}
if( !$ok )
{
	errors_add( T_('You can only post a new comment every 30 seconds.') );
}
/* end flood-protection */

if( errors_display( T_('Cannot post comment, please correct these errors:'),
	'[<a href="javascript:history.go(-1)">'. T_('Back to comment editing') . '</a>]' ) )
{
	exit();
}

$query = "INSERT INTO $tablecomments( comment_post_ID, comment_type, comment_author_ID, comment_author, 
																			comment_author_email, comment_author_url, comment_author_IP,
																			comment_date, comment_content)  
					VALUES( $comment_post_ID, 'comment', ".$DB->null($author_ID).",
									".$DB->quote($author).", ".$DB->quote($email).",
									".$DB->quote($url).",'".$DB->escape($user_ip)."','$now',
									'".$DB->escape($comment)."' )";
$DB->query( $query );

/*
 * New comment notification:
 */
$item_author_User = & $commented_Item->Author;
if( $item_author_User->notify )
{	// Author wants to be notified:
	locale_temp_switch($item_author_User->locale);
	$recipient = $item_author_User->email;
	$subject = sprintf( T_('New comment on your post #%d "%s"'), $comment_post_ID, $commented_Item->get('title') );
	$Blog = Blog_get_by_ID( $commented_Item->blog_ID );
	
	$notify_message  = sprintf( T_('New comment on your post #%d "%s"'), $comment_post_ID, $commented_Item->get('title') )."\n";
	$notify_message .= $commented_Item->gen_permalink( 'pid' )."\n\n"; // We use pid to get a short URL and avoid it to wrap on a new line in the mail which may prevent people from clicking
	if( is_logged_in() )
	{
		$notify_message .= T_('Author').': '.$current_User->get('preferedname').
												' ('.$current_User->get('login').")\n";
	}
	else
	{
		$user_domain = gethostbyaddr($user_ip);
		$notify_message .= T_('Author').": $author (IP: $user_ip, $user_domain)\n";
		$notify_message .= T_('Email').": $email\n";
		$notify_message .= T_('Url').": $url\n";
	}
	$notify_message .= T_('Comment').": \n".$original_comment."\n\n";
	$notify_message .= T_('Edit/Delete').': '.$admin_url.'/b2browse.php?blog='.$blog.'&p='.$comment_post_ID."&c=1\n";
	
	
	// echo "Sending notification to $recipient :<pre>$notify_message</pre>";
	
	if( is_logged_in() )
		$mail_from = $current_User->get('email');
	elseif( empty( $email ) )
		$mail_from = $notify_from;
	else
		$mail_from = "\"$author\" <$email>";
	
	ini_set('sendmail_from', $mail_from); // set Return-Path for Win32
	@mail($recipient, $subject, $notify_message, "From: $mail_from\nX-Mailer: b2evolution $b2_version - PHP/" . phpversion(), "-f$mail_from");
	locale_restore_previous();
}


/*
 * Handle cookies
 */
if( $comment_cookies )
{	// Set cookies:
	if ($email == '')	
		$email = ' '; // this to make sure a cookie is set for 'no email'
	if ($url == '')	
		$url = ' '; // this to make sure a cookie is set for 'no url'
	
	// fplanque: made cookies available for whole site
	setcookie( $cookie_name, $author, $cookie_expires, $cookie_path, $cookie_domain);
	setcookie( $cookie_email, $email, $cookie_expires, $cookie_path, $cookie_domain);
	setcookie( $cookie_url, $url, $cookie_expires, $cookie_path, $cookie_domain);
}
else
{	// Erase cookies:
	if( !empty($_COOKIE[$cookie_name]) ) 
	{	
		// echo "del1<br />";
		setcookie('comment_author', '', $cookie_expired, '/');
		setcookie('comment_author', '', $cookie_expired, $cookie_path, $cookie_domain);
		setcookie( $cookie_name, '', $cookie_expired, $cookie_path, $cookie_domain);
	}
	if( !empty($_COOKIE['comment_author_email']) )
	{	
		// echo "del2<br />";
		setcookie('comment_author_email', '', $cookie_expired, '/');
		setcookie('comment_author_email', '', $cookie_expired, $cookie_path, $cookie_domain);
		setcookie( $cookie_email, '', $cookie_expired, $cookie_path, $cookie_domain);
	}
	if( !empty($_COOKIE['comment_author_url']) )
	{	
		// echo "del3<br />";
		setcookie('comment_author_url', '', $cookie_expired, '/');
		setcookie('comment_author_url', '', $cookie_expired, $cookie_path, $cookie_domain);
		setcookie( $cookie_url, '', $cookie_expired, $cookie_path, $cookie_domain);
	}
}

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

param( 'redirect_to', 'string' );
$location = (!empty($redirect_to)) ? $redirect_to : $_SERVER['HTTP_REFERER'];
header('Refresh:0;url=' . $location);

?>
