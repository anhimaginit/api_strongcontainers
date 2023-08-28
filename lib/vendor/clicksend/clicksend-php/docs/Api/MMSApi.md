# ClickSend\MMSApi

All URIs are relative to *https://rest.clicksend.com/v3*

Method | HTTP request | Description
------------- | ------------- | -------------
[**mmsPricePost**](MMSApi.md#mmsPricePost) | **POST** /mms/price | Get Price for MMS sent
[**mmsReceiptsGet**](MMSApi.md#mmsReceiptsGet) | **GET** /mms/receipts | Get all delivery receipts
[**mmsReceiptsReadPut**](MMSApi.md#mmsReceiptsReadPut) | **PUT** /mms/receipts-read | Mark delivery receipts as read
[**mmsSendPost**](MMSApi.md#mmsSendPost) | **POST** /mms/send | Send MMS


# **mmsPricePost**
> string mmsPricePost($mms_messages)

Get Price for MMS sent

Get Price for MMS sent

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure HTTP basic authorization: BasicAuth
$config = ClickSend\Configuration::getDefaultConfiguration()
              ->setUsername('YOUR_USERNAME')
              ->setPassword('YOUR_PASSWORD');


$apiInstance = new ClickSend\Api\MMSApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$mms_messages = new \ClickSend\Model\MmsMessageCollection(); // \ClickSend\Model\MmsMessageCollection | MmsMessageCollection model

try {
    $result = $apiInstance->mmsPricePost($mms_messages);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MMSApi->mmsPricePost: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **mms_messages** | [**\ClickSend\Model\MmsMessageCollection**](../Model/MmsMessageCollection.md)| MmsMessageCollection model |

### Return type

**string**

### Authorization

[BasicAuth](../../README.md#BasicAuth)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **mmsReceiptsGet**
> string mmsReceiptsGet($page, $limit)

Get all delivery receipts

Get all delivery receipts

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure HTTP basic authorization: BasicAuth
$config = ClickSend\Configuration::getDefaultConfiguration()
              ->setUsername('YOUR_USERNAME')
              ->setPassword('YOUR_PASSWORD');


$apiInstance = new ClickSend\Api\MMSApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$page = 1; // int | Page number
$limit = 10; // int | Number of records per page

try {
    $result = $apiInstance->mmsReceiptsGet($page, $limit);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MMSApi->mmsReceiptsGet: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **page** | **int**| Page number | [optional] [default to 1]
 **limit** | **int**| Number of records per page | [optional] [default to 10]

### Return type

**string**

### Authorization

[BasicAuth](../../README.md#BasicAuth)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **mmsReceiptsReadPut**
> string mmsReceiptsReadPut($date_before)

Mark delivery receipts as read

Mark delivery receipts as read

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure HTTP basic authorization: BasicAuth
$config = ClickSend\Configuration::getDefaultConfiguration()
              ->setUsername('YOUR_USERNAME')
              ->setPassword('YOUR_PASSWORD');


$apiInstance = new ClickSend\Api\MMSApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$date_before = new \ClickSend\Model\DateBefore(); // \ClickSend\Model\DateBefore | DateBefore model

try {
    $result = $apiInstance->mmsReceiptsReadPut($date_before);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MMSApi->mmsReceiptsReadPut: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **date_before** | [**\ClickSend\Model\DateBefore**](../Model/DateBefore.md)| DateBefore model | [optional]

### Return type

**string**

### Authorization

[BasicAuth](../../README.md#BasicAuth)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **mmsSendPost**
> string mmsSendPost($mms_messages)

Send MMS

Send MMS

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure HTTP basic authorization: BasicAuth
$config = ClickSend\Configuration::getDefaultConfiguration()
              ->setUsername('YOUR_USERNAME')
              ->setPassword('YOUR_PASSWORD');


$apiInstance = new ClickSend\Api\MMSApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$mms_messages = new \ClickSend\Model\MmsMessageCollection(); // \ClickSend\Model\MmsMessageCollection | MmsMessageCollection model

try {
    $result = $apiInstance->mmsSendPost($mms_messages);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MMSApi->mmsSendPost: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **mms_messages** | [**\ClickSend\Model\MmsMessageCollection**](../Model/MmsMessageCollection.md)| MmsMessageCollection model |

### Return type

**string**

### Authorization

[BasicAuth](../../README.md#BasicAuth)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

