<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Composer
require_once __DIR__ . '/vendor/autoload.php';

// https://github.com/vlucas/phpdotenv
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$lastTitle = file_get_contents('last_car.log');

$rss_url = 'https://www.usedvictoria.com/index.rss?category=cars&pricefrom='.getenv('PRICE_LOW').'&priceto='.getenv('PRICE_HIGH');

if (getenv('AUTO_TRANS_ONLY')) {
  $rss_url .= '&attr_13=Automatic';
}

$usedvic_rss = file_get_contents($rss_url);
$rss = simplexml_load_string($usedvic_rss);
$currentTitle = $rss->channel->item[0]->title;
$currentLink = $rss->channel->item[0]->link;

if ($currentTitle !== $lastTitle) {

  // Send a notification
  $transport = Swift_SmtpTransport::newInstance(
    getenv('MAIL_HOST'),
    getenv('MAIL_PORT'),
    getenv('MAIL_ENCRYPTION')
  )
    ->setUsername(getenv('MAIL_USER'))
    ->setPassword(getenv('MAIL_PASSWORD'));

  $mailer = Swift_Mailer::newInstance($transport);

  $message = Swift_Message::newInstance()
    // ->setSubject('New vehicle posted on UsedVictoria.com')
    ->setFrom(array(getenv('MAIL_FROM_ADDRESS') => getenv('MAIL_FROM_NAME')))
    ->setTo(array(getenv('MAIL_TO_ADDRESS') => getenv('MAIL_TO_NAME')))
    ->setBody('New car posted on '.getenv('MAIN_SITE').': '.$currentLink.' '.$currentTitle, 'text/html');

  $f = false;
  if ($mailer->send($message, $f)) {

    echo 'Notification sent successfully.'.PHP_EOL;
    file_put_contents('notifications.log', '[HURRAY] New vehicle notification sent. ['.$currentTitle.'] - '.date(DATE_RFC2822).PHP_EOL, FILE_APPEND);

    // Set a new "latest car"
    file_put_contents('last_car.log', $currentTitle);

  } else {

    echo 'Error: Unable to send email.'.PHP_EOL;
    file_put_contents('notifications.log', '[WARNING] Email not sent: ['.$currentTitle.'] - '.date(DATE_RFC2822).PHP_EOL, FILE_APPEND);
    foreach ($f as $failure) {
      file_put_contents('notifications.log', '[ERROR] Unable to send notification: ['.$failure.'] - '.date(DATE_RFC2822).PHP_EOL, FILE_APPEND);
    }

  }

} else {

  echo 'No new cars to report.'.PHP_EOL;

}
