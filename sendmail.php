<?php

function sendMail($emailto, $emailsubject, $emailmessage) {

	// Compose the email
	ini_set("sendmail_from", "system@website.com"); 
	
	mail($email_to, $email_subject, $email_message, $headers, "-fsystem@website.com"); 
	
	$to = $emailto;
	$subject = $emailsubject;
	$message = '<html><body>';

	$message .= $emailmessage;

	$message .= '</body></html>';
	
	$from = "system@website.com";
	
	$headers = "From: " . $from . "\r\n";
	
	$headers .= "Reply-To: " . $from . "\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1";
	
	// Send Mail
	$sent = mail($to,$subject,$message,$headers, "-f" . $from . "");

	// Check if mail is sent ok
	if (!$sent) {
		exit('Message not sent');	
	} 

}

?>
