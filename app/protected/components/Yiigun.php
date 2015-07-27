<?php
// invoke Mailgun sdk
use Mailgun\Mailgun;

class Yiigun extends CComponent
{
  private $mg;
  private $mgValidate;
  
   function __construct() {     
     // initialize mailgun connection
     $this->mg = new Mailgun(Yii::app()->params['mailgun']['api_key']);
  }
    
  public function send_simple_message($to='',$subject='',$body='',$from='') {
    if ($from == '') 
      $from = Yii::app()->params['supportEmail'];
    $domain = Yii::app()->params['mail_domain'];
    // use only if supportEmail and from email are in mailgun account
  //  $domain = substr(strrchr($from, "@"), 1);      
    $result = $this->mg->sendMessage($domain,array('from' => $from,
                                               'to' => $to,
                                               'subject' => $subject,
                                               'text' => $body,
                                               'html' => $body,
                                               ));
    return $result->http_response_body;    
  }	

  public function fetchLists() {
    $result = $this->mg->get("lists");
    return $result->http_response_body;    
  }

  public function fetchListMembers($address, $skip=0, $limit=100) {
  	// Altered to help control memory on large list iterations.
    $result = $this->mg->get("lists/".$address.'/members?skip=' . $skip . "&limit=" . $limit );
//    print_r($result->http_response_body);
//    echo "Total Count for this list is ". $result->http_response_body->total_count . "   ";
//    if( $result->http_response_body->total_count > 100 )
//    {
//    	$totalMembers=$result->http_response_body->total_count;
//    	$offset=100;
//    	while( $offset < $totalMembers )
//    	{
//      		echo "Collected " . $offset . " of " . $totalMembers . " records    ";
//      		$offset += 100;
//    		$nextResult = $this->mg->get("lists/".$address.'/members?skip='.$offset);
//    		echo "Pulled another " . count( $nextResult->http_response_body->items ) . " addresses    ";
//    		$offset += count( $nextResult->http_response_body->items );
//    		$mergedResult = array_merge( $result->http_response_body->items, $nextResult->http_response_body->items );
//    		echo "Merged Size: " . count( $mergedResult );
//    		$result->http_response_body->items = $mergedResult;
//    	}
//    } 
    return $result->http_response_body;    
  }

  public function listCreate($newlist) {
    $result = $this->mg->post("lists",array('address'=>$newlist->address,'name'=>$newlist->name,'description' => $newlist->description,'access_level' => $newlist->access_level));
    return $result->http_response_body;    
  }
  
  public function listDelete($address='') {
    $result = $this->mg->delete("lists/".$address);
    return $result->http_response_body;    
  }
  
  public function listUpdate($existing_address,$model) {
    $result = $this->mg->put("lists/".$existing_address,array(
      'address'=>$model->address,
      'name' => $model->name,
      'description' => $model->description,
      'access_level' => $model->access_level
      ));
    return $result->http_response_body;    
   }  

   public function memberBulkAdd($list='',$json_str='') {
     $result = $this->mg->post("lists/".$list.'/members.json',array(
    'members' => $json_str,
     'subscribed' => true,
     'upsert' => 'yes'
     ));
     return $result->http_response_body;    
   }
  
  public function memberAdd($list='',$email='',$name='') {
    $result = $this->mg->post("lists/".$list.'/members',array('address'=>$email,'name'=>$name,'subscribed' => true,'upsert' => 'yes'));
    return $result->http_response_body;    
  }
  
  public function memberUpdate($list='',$email='',$propList) {
    $result = $this->mg->put("lists/".$list.'/members',$propList);
    return $result->http_response_body;    
   }
   
public function memberDelete( $list='', $email='' )
{
    $result = $this->mg->delete("lists/" . $list . "/members/" . $email );
	return( $result );
}   
   
   public function memberUnsubscribe($list='',$email='') {
     $propList = array('subscribed'=>false);
     $result=$this->memberUpdate($list,$email,$propList);
   }

   public function generateVerifyHash($model,$mglist) {
     // generate secure hash for verifying subscription requests
     $verify_secret = Yii::app()->params['verify_secret'];
     $optInHandler = $this->mg->OptInHandler();
     $generatedHash = $optInHandler->generateHash($mglist->address, $verify_secret, $model->address);
     // remove encodings - fixes yii routing issue
     $generatedHash = str_ireplace('%','',$generatedHash);
     return $generatedHash;
   }

   public function sendVerificationRequest($model,$mglist) {
     // send an email with the verification link 
		  $body="Please verify your subscription by clicking on the link below:\r\n".Yii::app()->getBaseUrl(true)."/request/verify/".$model->id."/".$model->hash;
		  $this->send_simple_message($model->address,'Please verify your subscription to '.$mglist->name,$body,Yii::app()->params['support_email']);
   }
   
   function validate($email='') {
     $this->mgValidate = new Mailgun(Yii::app()->params['mailgun']['public_key']);
     $result = $this->mgValidate->get('address/validate', array('address' => $email));
    return $result->http_response_body;
   }   
   
}

?>