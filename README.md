# Amazon Instant Access SDK for PHP

Amazon Instant Access (AIA), is a fulfillment technology for virtual content that is purchased on the Amazon web site and needs to be
delivered to a third party server. The **Amazon Instant Access SDK for PHP** enables PHP developers to easily integrate their systems and
onboard with the AIA system.

## Example Usage

### az_link.php
```
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Amazon\InstantAccess\Signature\CredentialStore;
use Amazon\InstantAccess\Controllers\AccountLinkingController;
use Amazon\InstantAccess\Serialization\Enums\GetUserIdResponseValue;
use Amazon\InstantAccess\Serialization\GetUserIdResponse;


$monolog = new Monolog\Logger('AmazonInstantAccessTest');
$monolog->pushHandler(new Monolog\Handler\StreamHandler('php://output',Monolog\Logger::WARNING));
Amazon\InstantAccess\Log\Logger::setLogger($monolog);

$credentialStore = new CredentialStore();
$credentialStore->loadFromFile(__DIR__ . '/../az_instantaccess.keys');
$credentialStore->load('secret public');

$controller = new AccountLinkingController($credentialStore);

$controller->onGetUserId(function ($req) use($queryFactory) {
	$q = $queryFactory->newInstance();
	
	// stuff amazon sends us
	$email_req = $req->getInfoField1();
	

	$user_id = MyCompany::userIdForEmail($email_req);

	$res = new GetUserIdResponse();

	if ($user_id) {
		$res->setResponse(GetUserIdResponseValue::OK);
		$res->setUserId($user_id);
	} else {
		$res->setResponse(GetUserIdResponseValue::FAIL_ACCOUNT_INVALID);
	}

	return $res;

});

$response = $controller->process($_SERVER);
echo $response;

```

### az_fulfill.php
```
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Amazon\InstantAccess\Signature\CredentialStore;
use Amazon\InstantAccess\Controllers\PurchaseController;
use Amazon\InstantAccess\Serialization\SubscriptionActivateResponse;
use Amazon\InstantAccess\Serialization\SubscriptionDeactivateResponse;
use Amazon\InstantAccess\Serialization\Enums\SubscriptionActivateResponseValue;
use Amazon\InstantAccess\Serialization\Enums\SubscriptionDeactivateResponseValue;

$monolog = new Monolog\Logger('AmazonInstantAccessTest');
$monolog->pushHandler(new Monolog\Handler\StreamHandler('php://output',Monolog\Logger::WARNING));
Amazon\InstantAccess\Log\Logger::setLogger($monolog);

$credentialStore = new CredentialStore();
$credentialStore->loadFromFile(__DIR__ . '/../az_instantaccess.keys');
$credentialStore->load('secret public');

$controller = new PurchaseController($credentialStore);

$controller->onSubscriptionActivate(function ($req) use($queryFactory,$az_products) {
	$res = new SubscriptionActivateResponse();

	$req_user_id = $req->getUserId();
	$user_id = MyCompany::find_user_id($req_user_id);
	
	if(!$user_id) {
		$res->setResponse(SubscriptionActivateResponseValue::FAIL_USER_INVALID);
		return $res;
	}

	$az_product_code = $req->getProductId(); // should be an SKU/ASIN type code
	$az_subscription_id = $req->getSubscriptionId();  // a complicated amazon code
	$product_data = MyCompany::AmazonProductInfo($az_product_code);

	if(!$product_data) {
		$res->setResponse(SubscriptionActivateResponseValue::FAIL_OTHER);
		return $res;
	}

	// check for existing subscription 
	$subscription_id = MyCompany::findSubscriptionForUserAndAmazonSubscriptionId($user_id,$az_subscription_id);
	if($subscription_id) {
		$res->setResponse(SubscriptionActivateResponseValue::OK);
		return $res;
	}

	// create a subscription, and save the amazon
	$success = MyCompany::createSubscription($user_id,$product_data,$az_subscription_id);

	if($success) {
		$res->setResponse(SubscriptionActivateResponseValue::OK);
		return $res;
	}

	$res->setResponse(SubscriptionActivateResponseValue::FAIL_OTHER);
	return $res;

});

$controller->onSubscriptionDeactivate(function ($req) use($queryFactory) {
	$res = new SubscriptionDeactivateResponse();

	$az_subscription_id = $req->getSubscriptionId();
	$subscription_id = MyCompany::findSubscriptionFromAmazonSubscriptionId($az_subscription_id);
	if(!$subscription_id) {
		$res->setResponse(SubscriptionDeactivateResponseValue::FAIL_INVALID_SUBSCRIPTION);
		return $res;
	}

	MyCompany::deactivateSubscription($subscription_id);

	$res->setResponse(SubscriptionDeactivateResponseValue::OK);
	return $res;
});

$response = $controller->process($_SERVER);
echo $response;



```
### az_register.php
```
<?php

$amazon_redirect_url = $_GET['redirectUrl'];

if($_POST) {

	$result = MyCompany::registerNewUser($_POST);

	if($result['success']) {
		header(sprintf('Location: %s&infoField1=%s',$amazon_redirect_url,urlencode($result['user']['email'])))
	} else  {
		MyCompany::showRegistrationForm($result['errors']);
	}

} else {
	MyCompany::showRegistrationForm();
}

```
