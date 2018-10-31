<?php

$recipient = "pora@gatografias.cl";
$name = $_POST['name'];
$email = $_POST['email'];
$subject = $_POST['subject'];
$message = $_POST['message'];

if (isset($_POST['email'])) {	
	if (preg_match('(\w[-._\w]*\w@\w[-._\w]*\w\.\w{2,})', $_POST['email'])) {
		$msg = 'E-mail es v&aacute;lido';
	} else {
		$msg = 'E-mail es inv&aacute;lido';
	}

  $ip = getenv('REMOTE_ADDR');
  $host = gethostbyaddr($ip);	
  $mess = "Nombre: ".$name."\n";
  $mess .= "Email: ".$email."\n";
  $mess .= "Asunto: ".$subject."\n";
  $mess .= "Mensaje: ".$message."\n\n";
  $mess .= "IP:".$ip." HOST: ".$host."\n";
  
  $headers = "From: no-reply <no-reply@gatografias.cl>\r\n"; 
  
   if(isset($_POST['url']) && $_POST['url'] == ''){

       $sent = mail($recipient, $subject, $mess, $headers); 
  } 
} else {
	die('Entrada inv&aacute;lida!');
}

?>