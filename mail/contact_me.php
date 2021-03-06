<?php
// Require connection information and PHPMailer library
require_once('email_config.php');
require('phpmailer/PHPMailer/PHPMailerAutoload.php');

$message = [];
$output = [
    'success' => null,
    'messages' => []
];

//Sanitize name field
$message['name'] = filter_var($_POST['name'],FILTER_SANITIZE_STRING);
if(empty($message['name'])){
    $output['success'] = false;
    $output['messages'][] = 'missing name key';
}
//Validate email name field
$message['email'] = filter_var($_POST['email'],FILTER_VALIDATE_EMAIL);
// $message['email'] = filter_var($_POST['email'], FILTER_VALIDATE_REGEXP, ['options'=>['regexp'=>'/^(?=[A-Z0-9][A-Z0-9@._%+-]{5,253}$)[A-Z0-9._%+-]{1,64}@(?:(?=[A-Z0-9-]{1,63}\.)[A-Z0-9]+(?:-[A-Z0-9])+)*\.){1,8}[A-Z]{2,63}$/i']]);
if(empty($message['email'])){
    $output['success'] = false;
    $output['messages'][] = 'invalid email key';
}
//Sanitize phone field
$message['phone'] = filter_var($_POST['phone'],FILTER_SANITIZE_STRING);
if(empty($message['phone'])){
    $message['phone'] = 'was not provided';
}

//Sanitaze message field
$message['message'] = filter_var($_POST['message'],FILTER_SANITIZE_STRING);
if(empty($message['message'])){
    $output['success'] = false;
    $output['messages'][] = 'missing message key';
}

if($output['success'] !== null){
    // http_response_code(400);
    echo json_encode($output);
    exit();
}

//set up email object
$mail = new PHPMailer;
$mail->SMTPDebug = 0;           // Enable verbose debug output. Use 3 for debugging. Change to 0 to disable debugging output.

$mail->isSMTP();                // Set mailer to use SMTP.
$mail->Host = 'smtp.gmail.com'; // Specify main and backup SMTP servers.
$mail->SMTPAuth = true;         // Enable SMTP authentication


$mail->Username = EMAIL_USER;   // SMTP username
$mail->Password = EMAIL_PASS;   // SMTP password
$mail->SMTPSecure = 'tls';      // Enable TLS encryption, `ssl` also accepted, but TLS is a newer more-secure encryption
$mail->Port = 587;              // TCP port to connect to
$options = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);
$mail->smtpConnect($options);
$mail->From = $message['email'];  // sender's email address (shows in "From" field)
$mail->FromName = $message['name'];   // sender's name (shows in "From" field)
$mail->addAddress(EMAIL_USER);  // Add a recipient
//$mail->addAddress('ellen@example.com');                        // Name is optional
$mail->addReplyTo($message['email'], $message['name']);                          // Add a reply-to address
//$mail->addCC('cc@example.com');
//$mail->addBCC('bcc@example.com');

//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
$mail->isHTML(true);                                  // Set email format to HTML

//if you dont have a subject in your portfolio
$message['subject'] = $message['name']." has sent you a message on your portfolio";
// $message['subject'] = substr($message['message'],0,78);
$message['message'] = $message['message'] . "\n\n" . 'Phone: '.$message['phone'] ;
$message['message'] = nl2br($message['message']); //Convert newline characters to line break html tags
$mail->Subject = $message['subject'];
$mail->Body = $message['message'];//'This is the HTML message body <b>in bold!</b>';
$mail->AltBody = htmlentities($message['message'] .'Phone: '.$message['phone']);//'This is the body in plain text for non-HTML mail clients';
//Attemp email send, output result to client
if(!$mail->send()) {
    $output['success'] = false;
    $output['messages'][] = $mail->ErrorInfo;  
} else {
    $output['success'] = true;
}
echo json_encode($output);

?>
