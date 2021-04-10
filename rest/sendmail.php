<?php

require 'vendor/autoload.php';


// Create the Transport
$transport = (new Swift_SmtpTransport('smtp.sendgrid.net', 587))
  ->setUsername('apikey')
  ->setPassword('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');

// Create the Mailer using your created Transport
$mailer = new Swift_Mailer($transport);

// Create a message
$message = (new Swift_Message('Wonderful Subject'))
  ->setFrom(['aldin.berisa@stu.ibu.edu.ba' => 'Aldin B'])
  ->setTo(['aldinberisa1514@gmail.com', 'other@domain.org' => 'A name'])
  ->setBody('Here is the message itself')
  ;

// Send the message
$result = $mailer->send($message);