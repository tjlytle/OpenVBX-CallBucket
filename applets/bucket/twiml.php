<?php
require_once 'bucket.php';

$bucket = new Bucket();

$response = new Response();

//store call in bucket
if(isset($_REQUEST['CallSid'])){ //check that someone's not just viewing the twiml
	$bucket->addCall(AppletInstance::getValue('bucket'), $_REQUEST['CallSid']);
}	

//direct call to next applet
$next = AppletInstance::getDropZoneUrl('next');                                                                                            
if(!empty($next)) {
  $response->addRedirect($next);                                                                                                         
} 

$response->Respond();