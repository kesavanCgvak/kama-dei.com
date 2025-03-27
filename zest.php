<?php
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://pythond.kama-dei.com/enterprise_list_all_objs_in_source/v1',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => array('org' => '1','source_type' => 'S3','container' => '{"vault":"kama.ai"}'),
  CURLOPT_HTTPHEADER => array(
    'apikey: txxxxxed711c9191c83e1136450563e61c5c5'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
