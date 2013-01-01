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
 * postcaptcha.php handles the CAPTCHA portion of the
 * script. When called, it will spit out a question to
 * penguinpost.js or verify if a question was answered
 * correctly, depending on the "type" variable that
 * penguinpost.js shoots out.
 * 
 * The functions getData and verifyData are what's used
 * to grab the CAPTCHA Question and verify the question.
 * Below is the Question class declaration. It contains
 * a string called question along with either a string 
 * or an array of srings called answer. The configuration
 * variables are listed below, make note of the
 * questionbank, which is an array of Question(s). You
 * may also have to modify the language settings.
 * 
 * ******************************************************
 */
	/* Start: Declaration of Classes */
	
	class Question {
		// The Question, represented as a string.
		public $question;
		
		// The Answer(s), represented either as a string or an array of strings.
		// Each answer MUST be in lowercase. The user's answer is converted as such.
		public $answers;
		
		// The constructor function
		function __construct($question, $answers) {
			$this->question = $question;
			$this->answers = $answers;
		}
	}
	
	/* End: Declaration of Classes */
	
	// The Question Bank. These are an array of Question (the class).
	/* Ex.
	$questionbank = array(	new Question('Are you a human? Type Yes or No.', array('yes','y','yeah')),
				new Question('Who is the original lead vocalist of BOSTON?', 'brad delp'),
				new Question('In the global Pokédex, which Pokémon is at #25', 'pikachu'),
				new Question('Type 1, 2, or 3.', array('1', '2', '3')),
				new Question('What colour is the sky?', array('white', 'blue', 'dark'))
				);*/
	$questionbank = array(	new Question('Are you a human? Type Yes or No.', array('yes','y','yeah')),
				new Question('Who is the original lead vocalist of BOSTON?', 'brad delp'),
				new Question('In the global Pokédex, which Pokémon is at #25', 'pikachu'),
				new Question('Type 1, 2, or 3.', array('1', '2', '3')),
				new Question('What colour is the sky?', array('white', 'blue', 'dark'))
				);
	
	// The language you are you using.
	// ex. $lang = 'en-us';
	$lang = 'en-us';
	
	// Delcaration of all the language variables. We will
	// use a switch statement for this.	
	switch($lang) {
		case 'en-us':
			$post_request_fail = "We're sorry, this file is not intended to be initialized.";
			$incorrect_answer = '{"success":false}';
			$correct_answer = '{"success":true}';
		break;
	}
	
	if(!isset($_POST['type']) || !isset($_POST['questionno'])) {
		header("HTTP/1.0 403 Forbidden");
		die($post_request_fail);
	}
	
	/* Start: Functions */
	
	function getData() {
		global $questionbank;
		do {
			$questionno = rand() % count($questionbank); // random question
		} while($questionno == $_POST['questionno']);
		echo '{"question":"' . $questionbank[$questionno]->question . '","number":' . $questionno . '}';
		exit;
	}
	
	function verifyData() {
		global $questionbank, $incorrect_answer, $correct_answer;
		if(!isset($_POST['questionno']) || !isset($_POST['answer'])) { // if this is not defined, reject immediately
			echo $incorrect_answer; // almost like a return false
			exit;
		}
		$answerbank = $questionbank[$_POST['questionno']]->answers; // The correct answer(s)
		$answer = strtolower($_POST['answer']); // The user's answer, converted to lowercase
		
		// We check if answerbank is an array, if so, check if
		// answer is in answerbank. Otherwise, check if answer
		// is equal to answerbank.
		if((is_array($answerbank) && in_array($answer, $answerbank)) || ($answerbank == $answer))
			echo $correct_answer;
		else
			echo $incorrect_answer;
			
		exit; // get out afterwards.
	}
	
	/* End: Functions */
	
	/* Start: Main code */
	
	switch($_POST['type']) {
		case 'get':
			getData();
		break;
		case 'submit':
			verifyData();
		break;
	}
	
	/* End: Main code */
?>
