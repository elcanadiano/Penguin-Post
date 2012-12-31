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
 * Penguin Post's JavaScript file is used to grab values
 * from input fields and passes them through using POST
 * requests to PHP model files to process. A model is
 * used to handle the Q&A CAPTCHA, if enabled, while
 * another one is used to send mail and check if it was
 * sent successfully.
 *
 * This file provides the functions, validateEmail,
 * getQuestion, validateCAPTCHA, and submitEmail.
 * An additional function is for when the page is loaded
 * while another jQuery command is used to close a message.
 * 
 * ******************************************************
 */

/* Start: CAPTCHA Variables */

// Set using_captcha to false if you do not wish to
// use the built-in Q&A CAPTCHA. Set to true otherwise.
// questionno is a record of the 
var using_captcha = true;//false;
var questionno = -1;

/* End: CAPTCHA Variables */

// Function courtesy of http://jsbin.com/ozeyag/19
function validateEmail(email) { 
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

// getQuestion() makes an AJAX Call to the CAPTCHA
// file and retreives a given question that the CAPTCHA
// file spits out.
function getQuestion(){
	$.ajax({
		url: '../donate/postcaptcha.php',
		type: "POST",
		dataType: "json",
		data: {
			type: 'get',
			questionno: questionno
		},
		success: function(data){
			$('#captcha_question').html(data['question']); // displays question to user
			questionno = data['number']; // keeps a record of which question CAPTCHA spits out.
		},
		error: function(jqXHR, textStatus, errorThrown) {
			$('#postCAPTCHAMessage').html('An error has occured. Please refresh and contact the administrator if this error persists.');
		}
	});
}

// validateCAPTCHA() returns true if the Question and
// answer CAPTCHA system is not being used or if the
// answer provided by the user is correct. It submits
// a POST request to the CAPTCHA file which computes
// if the question is correct or not.
function validateCAPTCHA() {
	var dataflag;
	if(!using_captcha) // if we're not using the CAPTCHA, then we automatically say the test passes.
		return true;
	else {
		var answer = $('#postCAPTCHA').val().toLowerCase();
		
		if(answer == '')
			return false; // There is no point in doing the check.
		else {
			$.ajax({
				url: '../donate/postcaptcha.php',
				type: "POST",
				async: false,
				dataType: "json",
				data: {
					type: 'submit',
					answer: answer,
					questionno: questionno
				},
				success: function(data){
					dataflag = data['success'];
				},
				error: function(jqXHR, textStatus, errorThrown) {
					$('#postCAPTCHAMessage').html('An error has occured. Please refresh and contact the administrator if this error persists.');
					dataflag = 0;
				}
			});
		}
	}
	return dataflag;
}

// submitEmail() grabs the values of the user's input
// and then checks to see if the users' input is valid.
// If it is, it will create an AJAX call to the PHP
// file and send the email. The PHP file will return
// JSON code regarding whether the email was succesfully
// sent or not.
function submitEmail() {
	var newquestion; // used only if using_captcha is on and the question was incorrect.
	var cansend = true; // flag - if set to false, the AJAX call will not happen.
	var realname = $('#realName').val(); // Grab the dude's name
	var email = $('#emailAddress').val(); // grab the dude's email address
	var steamURL = $('#steamURL').val(); // grab the Steam URL
	var steamUsnm = $('#steamUsnm').val(); // grab the Steam Username or ID they provide
	var selection = $('#donationAmount option:selected').text(); // grab the value the user selected.
	var message = $('#message').val(); // grab the message
	var ajaxurl = '../donate/newemail.php';
	var validated = validateCAPTCHA();
	
	/* Start: Validation checks */
	
	if(email == '' || !validateEmail(email)) { // Check to see if the email address is in a valid format
		$('#emailAddressMessage').html('Please enter a valid email address.');
		cansend = false;
	}
	else
		$('#emailAddressMessage').html(''); // Don't show the error anymore if it shows.
	
	if(steamURL == '') { // we'll get a regexp checker soon like the email validation.
		$('#steamURLMessage').html('Please enter a valid Steam URL.');
		cansend = false;
	}
	else
		$('#steamURLMessage').html(''); // Don't show the error anymore if it shows.
	
	if(steamUsnm == '') { // check to see if Steam Username is empty
		$('#steamUsnmMessage').html('Please enter a Steam username or name.');
		cansend = false;
	}
	else
		$('#steamUsnmMessage').html(''); // Don't show the error anymore if it shows.
	
	if(selection == "Select") { // to make sure if the user actually made a selection
		$('#donationAmountMessage').html('Please make a selection.');
		cansend = false;
	}
	else
		$('#donationAmountMessage').html(''); // Don't show the error anymore if it shows.
	
	if(!validated) { // if we are not using the CAPTCHA, this will always be true.
		$('#postCAPTCHAMessage').html('Incorrect answer. A new question has been set forward.');
		cansend = false;
	}
	else
		$('#postCAPTCHAMessage').html(''); // Don't show the error anymore if it shows.

	/* End: Validation Checks */
	
	if( cansend ) { // if we have passed all of the validation checks, we will call the PHP function via. an AJAX call
		$.ajax({
			type: "POST",
			url: '../donate/penguinpost.php',
			dataType: "json",
			data: {
				submit: 1, // for security purposes
				name: realname,
				email: email,
				steamURL: steamURL,
				steamUsnm: steamUsnm,
				selection: selection,
				message: message
			},
			success: function(data) {
				//alert('SUCCESS: ' + data);
				//alert(data['status'] + ' ' + data['message']);
				
				if( $('#confirmationBox').hasClass('fail') ) { // for the event that one fails then suceeds
					$('#confirmationBox').removeClass('fail');
				}
				
				$('#confirmationBox').addClass(data['status'])
				$('#confirmationMessage').html(data['message']);
				
				if(data['status'] == "success") {
					$('#penguinForm').hide(400, function() {
						$('#confirmationBox').delay(400).fadeIn(500);
					});
				}
				else
					$('#confirmationBox').fadeIn(500);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				$('#confirmationBox').addClass('fail');
				$('#confirmationMessage').html('ERROR: The server could not complete the request. Technical Reason: ' + errorThrown);
				$('#confirmationBox').fadeIn(500);
				//alert(errorThrown); //'ERROR: ' + textStatus + ' ' + 
			}
		});
	}
	else {
		if(using_captcha && !validated) {
			getQuestion();
		}
	}
}

// When the close button is clicked, we will hide the box and
// strip the success or fail class associated with it.
$('.close').click(function() {
	$('#confirmationBox').hide(0, function() {
		$('#confirmationBox').removeAttr('class');
	});
});

// On load, we will grab a new CAPTCHA question if needed.
$(function() {
	if(using_captcha) {
		$('#penguinFields').append('<div class="formRow"> <span class="title" id="captcha_question"></span> <input type="text" id="postCAPTCHA" /> <span id="postCAPTCHAMessage" class="error"></span> </div>');
		getQuestion();
	}
});