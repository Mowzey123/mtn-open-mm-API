<?php
//@mowzey mosesl@cognativeinsights.com,molutalo@gmail.com
//you get these after signinup with momodevelopera program
//and subscribing to a particular product like collections
define("primarykey", "f20dbd2bb73442f5aed24ccbec254f2c");
define("secondarykey", "c679c4e530174617813217509dc7d181");

class momoPayApi 
{
  private $OcpApimSubscriptionKey = '';  
  private $apiKey = '';
  private $XTargetEnvironment = '';
  function __construct($apikey='',$apiUserxrefid,$XTargetEnvironment='')
  {
    $this->OcpApimSubscriptionKey = primarykey?primarykey:secondarykey;
    $this->apikey = $apikey?$apikey:$this->generateApiKey($apiUserxrefid);
    $this->XTargetEnvironment = $XTargetEnvironment?$XTargetEnvironment:$this->getApiUser($apiUserxrefid);
  }

  //if getting started do this first
  public function createSandboxUser($xrefid){
      //$xrefid should be Format - UUID like c72025f5-5cd1-4630-99e4-8ba4722fad80, this will be your user identifier
      $headers=array('Ocp-Apim-Subscription-Key:'.$this->OcpApimSubscriptionKey.',X-Reference-Id:'.$xrefid);
      $data="{providerCallbackHost:clinic.com}";
      $url='https://ericssonbasicapi2.azure-api.net/v1_0/apiuser';
      return $this->makeCurlRequest($url,$data,$headers,"POST");
  }

  //check to see  if user exists
  public function getApiUser($xrefid){
      $headers=array('Ocp-Apim-Subscription-Key:'.$this->OcpApimSubscriptionKey);
      $data="";
      $url='https://ericssonbasicapi2.azure-api.net/v1_0/apiuser/'.$xrefid;
      return $this->makeCurlRequest($url,$data,$headers,"GET")->targetEnvironment;
  }

  //generated once, one key per $xrefid(apiuserid)
  public function generateApiKey($xrefid){
      $headers=array('Ocp-Apim-Subscription-Key:'.$this->OcpApimSubscriptionKey);
      $data="";
      $url='https://ericssonbasicapi2.azure-api.net/v1_0/apiuser/'.$xrefid.'/apikey';
      $res = $this->makeCurlRequest($url,$data,$headers,"POST");
      return $res->apiKey;
  }

  //initiate payment from user account
  public function InititatePayment($xrefid,$data){
      $transcationref=$this->guidv4(openssl_random_pseudo_bytes(16));
      $headers=array(
            'Authorization: Basic '.base64_encode((string)$xrefid.':'.(string)$this->apiKey),
            'Ocp-Apim-Subscription-Key: '.(string)$this->OcpApimSubscriptionKey,
            // 'X-Callback-Url:http:localhost/momopay/verifypayment/'.$transcationref,
            // 'X-Reference-Id:'.$xrefid,
            // 'X-Target-Environment:'.$this->XTargetEnvironment,
            // 'Content-Type:application/json'
      );
      echo "<pre>";
      echo json_encode($headers);
      echo "</pre>";
      $url='https://ericssonbasicapi2.azure-api.net/collection/v1_0/requesttopay';
      echo "<pre>";
      echo json_encode($this->makeCurlRequest($url,$data,$headers,"POST"));
      echo "</pre>";
      // return $this->makeCurlRequest($url,$data,$headers,"POST");
  }

  private  function guidv4($data)
  {
      assert(strlen($data) == 16);

      $data[6] = chr(ord($data[6]) & 0x0f | 0x40); 
      $data[8] = chr(ord($data[8]) & 0x3f | 0x80); 

      return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
  }


  public function makeCurlRequest($url,$data,$headers,$method){
      $curl = curl_init();
      curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 40,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_HEADER=>true,
          CURLOPT_CUSTOMREQUEST=> $method,
          CURLOPT_POST => 1,
          CURLOPT_POSTFIELDS => json_encode($data),
          CURLOPT_HTTPHEADER=> $headers,
      ));

      $response = curl_exec($curl);
      $err = curl_error($curl);
      curl_close($curl);
      if ($err){
                  echo $err;
      } else {
          var_dump($response);
          echo "<br><br>";
          if(!is_array(json_decode($response))<1){
              echo $response;
              return null;
          }
          return json_decode($response);
      }
  }

}

$obj = new momoPayApi('23f93ca969574eedab6631265a718595','c72025f5-5cd1-4630-99e4-8ba4722fad80','sandbox');
$data=array("amount"=>"5000","currency"=>"EUR","externalId"=>"greengo123","payer"=>array("partyIdType"=>"MSISDN","partyId"=>"46733123453"),"payerMessage"=>"Hello","payeeNote"=>"test");
$obj->InititatePayment('c72025f5-5cd1-4630-99e4-8b67822fad80',$data);