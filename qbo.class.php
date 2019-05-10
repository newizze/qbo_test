<?php
/**
 * Created by PhpStorm.
 * User: vladislavumnov
 * Date: 10/05/2019
 * Time: 17:27
 */

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Invoice;

define('ClientId', 'AB8Gad3SScVwSKkgnxY886k8UbI1yaEeCXOHE0VPmLkr8POjiv');
define('ClientSecret', 'qbZ7BUIJNCV8snM54TqW4OyXShnf9BdTl9i6bFUZ');
define('RedirectURI', 'http://qbo.loc');

class qbo {

    private $authUrl;
    private $accessToken;
    private $accessTokenObj;
    private $realmId;
    private $OAuth2LoginHelper;
    private $dataService;

    function __construct() {

        // Prep Data Services
        $this->dataService = DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => ClientId,
            'ClientSecret' => ClientSecret,
            'RedirectURI' => RedirectURI,
            'scope' => "com.intuit.quickbooks.accounting",
            'baseUrl' => "https://sandbox-quickbooks.api.intuit.com"
        ));

        $this->OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();

        $this->authUrl = $this->OAuth2LoginHelper->getAuthorizationCodeURL();

    }

    /**
     * @return \QuickBooksOnline\API\Data\IPPCompanyInfo
     * @throws \QuickBooksOnline\API\Exception\SdkException
     */
    function getCompanyInfo() {
        return $this->dataService->getCompanyInfo();
    }

    /**
     * @return array
     * @throws Exception
     */
    function getAllEmployee() {
        return $this->dataService->Query("select * from Employee");
    }

    /**
     * @return mixed
     */
    function getTokenObject() {
        return $this->accessTokenObj;
    }

    /**
     * @return String
     */
    function getAuthUrl () {
        return $this->authUrl;
    }

    /**
     * @param $realmId
     */
    function setRealmId($realmId) {
        $this->realmId = $realmId;
    }

    /**
     * @param $accessToken
     */
    function setAccessToken($accessToken) {
        $this->accessToken = $accessToken;
    }

    /**
     * @throws \QuickBooksOnline\API\Exception\SdkException
     * @throws \QuickBooksOnline\API\Exception\ServiceException
     * Setting Access Data Token to QBO's SDK
     */
    function setAccessDataToQBOSDK() {
        $this->accessTokenObj = $this->OAuth2LoginHelper->exchangeAuthorizationCodeForToken($this->accessToken, $this->realmId);

    }

    /**
     * @param $accessToken
     */
    function setTokenObject ($accessToken) {
        $this->accessTokenObj = $accessToken;
    }

    /**
     *
     */
    function updateOAuth2Token () {
        $this->dataService->updateOAuth2Token($this->accessTokenObj);
    }

    /**
     * @return int
     * @throws \QuickBooksOnline\API\Exception\IdsException
     * New Invoice creation
     */
    function createInvoice() {
        $amount = rand(10,150);
        $invoiceToCreate = Invoice::create([
            "DocNumber" => rand(1,300),
            "Line" => [
                [
                    "Description" => "Sewing Service for Alex",
                    "Amount" => $amount,
                    "DetailType" => "SalesItemLineDetail",
                    "SalesItemLineDetail" => [
                        "ItemRef" => [
                            "value" => 1,
                            "name" => "Services"
                        ]
                    ]
                ]
            ],
            "CustomerRef" => [
                "value" => "1",
                "name" => "Alex" . rand(1,5)
            ]
        ]);

        $resultObj = $this->dataService->Add($invoiceToCreate);
        $error = $this->dataService->getLastError();
        return $amount;

    }
}