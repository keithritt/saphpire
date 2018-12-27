<?
//Db::datetime(time());
print 'executing email test';
//mail('mail@keithritt.net', 'test cron job', 'test content');
$aParams = array(
  'vTo' => 'mail@keithritt.net',
  'sSubject' => 'test cron job2',
  'sContent' => 'test content');
Mail::save($aParams);