<?php
/* ******************** Penguin Post ********************
 *
 * Website: http://tc-pub.com
 *
 * Copyright (C)2012 Alexander Poon
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * ******************************************************
 * 
 * penguinpost.php is used to send email. Below are the
 * language and email variables needed to execute the
 * send email. It returns a JSON code that includes the
 * status (success or fail) and then the success or error
 * message. penguinpost.js will take the data and modify
 * the HTML accordingly.
 *
 * The functions valid_email and construct_message are
 * included in this file. The email is sent outside of a
 * function and the script exits immediately after
 * some text has been outputted.
 * 
 * ******************************************************
 */
	/* Start: Declarations */
	
	// Email address this stuff sends to. So for long as 
	// this string complies with the RFC 2822 format, you 
	// can send to multiple users. If unsure, separating
	// email addresses with comments would most likely
	// comply.
	// Ex. $to = 'Some Guy <graymond@gmail.com>';
	$to = 'Dharma Bum <tcpub.dummy@gmail.com>';
	
	// Email address where the message comes from. A
	// no-reply email works well. This will be overridden
	// if you pass email in the POST request.
	// Ex. $from = 'The Corner Pub <no-reply@tc-pub.com>';
	$from = 'The Corner Pub <no-reply@tc-pub.com>';
	
	// Subject of the email. Must comply with RFC 2047
	// http://www.faqs.org/rfcs/rfc2047
	// Ex. $subject = 'This is the subject of every message.';
	$subject = 'You (may have) received a donation!';
	
	// The actual message of the email. Each line should
	// not be longer than 70 characters (separate with \n).
	$message = "This is a test email.\nThanks,\nAlexander";
	
	// The language you are you using.
	// ex. $lang = 'en-us'
	$lang = 'en-us';
	
	// Delcaration of all the language variables. We will
	// use a switch statement for this. Just declare 
	
	switch($lang) {
		case 'en-us':
			$post_request_fail = "We're sorry, this file is not intended to be initialized.";
			$success_email_message = 'Email returned successfully! If you have not already, please hit the donate button, and thank you for donating!';
			$fail_email_message = "We're sorry, but there are errors in our part. Please let a staff member know if the error persists.";
			$invalid_email = "We're sorry, but this email address is invalid. Please try again.";
		break;
	}
	
	// Allowed Domains. Put this in an array of strings.
	// If you have issues with other domains using this
	// script, add your domain in here as an array of
	// strings.
	// ex. $allowed = array('pub.crystalcoconut.com', 'tc-pub.com');
	
	$allowed;
	
	/* End: Declarations */
	
	// Means you cannot access the page directly. 
	// You also have to pass a value (any value)
	// for submit via. POST.
	if(!isset($_POST['submit']) || (!is_null($allowed) && !in_array($_SERVER["HTTP_HOST"], $allowed))) {
		header("HTTP/1.0 403 Forbidden");
		die($post_request_fail);
	}
	
	/* Start: Declaration of functions */
	
	function construct_message() {
		$finalstring = '';
		if(isset($_POST['name']) && $_POST['name'] != '') // if the name is not empty
			$finalstring .= $_POST['name'] . " submitted a form, so see if he donated.\n" . 'Name: ' . $_POST['name'] . "\n"; // Let the users' name known.
		
		if(isset($_POST['email'])) // if the email is defined
			$finalstring .= 'Email: ' . $_POST['email'] . "\n"; // define the email
		
		if(isset($_POST['steamURL'])) // if the steam URL is defined
			$finalstring .= 'Steam URL: ' . $_POST['steamURL'] . "\n";
		
		if(isset($_POST['steamUsnm'])) // if the Steam Username/ID is defined
			$finalstring .= 'Steam Username/ID: ' . $_POST['steamUsnm'] . "\n";
		
		if(isset($_POST['selection']))
			$finalstring .= 'Amount Donated: ' . $_POST['selection'] . "\n";
		
		if(isset($_POST['message'])) {
			$finalstring .= "\n" . wordwrap($_POST['message'], 70, "\n") . "\n";
		}
		
		return $finalstring;
	}
	
	// checks from a regex if the email address is valid
	function valid_email($email_address) {
		$regex = '/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/';
		return (preg_match($regex, $email_address) == 1); // returns true if preg_match returns the integer 1 (hence the check, cause it returns an integer)
	}
	
	/* End: Declaration of functions */
	
	/* Start: The real stuff */
	
	if(!valid_email($_POST['email'])) { // Essentially 
		echo '{"status": "fail", "message": "' . $invalid_email . '"}';
		exit(1);
	}	
	
	if(isset($_POST['name']) && isset($_POST['email'])) { // set the from to the format, 'NAME <email@email.com>'
		$from = $_POST['name'] . '<' . $_POST['email'] . '>';
	}
	else if(isset($_POST['email'])) // set the from to the email address if a name is not defined
		$from = $_POST['email'];
	}
	
	$message = construct_message(); // construct the message.
	
	if(mail($to, $subject, $message, 'From: ' . $from)) {
		echo '{"status": "success", "message": "' . $success_email_message . '"}';
	}
	else {
		echo '{"status": "fail", "message": "' . $fail_email_message . '"}';
	}
	
	/* End: The real stuff */
?>