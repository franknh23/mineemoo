<?php

class Xonu_Sepaone_Model_Api extends Mage_Core_Model_Abstract {

    const API_URL_TEST = 'https://www.sepaone.com/test/api/';
    const API_URL_LIVE = 'https://www.sepaone.com/api/';

    protected $httHeader = array();
    protected $apiBaseUrl = '';

    // mixed microtime ([ bool $get_as_float = false ] ), get_as_float introduced in PHP5
    public static function mtime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    public function timestampLocal($format = 'Y-m-d H:i:s') {
        return date($format, Mage::getModel('core/date')->timestamp(time()));
    }

    public function timestampGlobal($format = 'Y-m-d H:i:s') {
        return date($format, time());
    }

    protected function _getHttpHeader() {
       return array(
           "Content-Type: application/json",
           "Authorization: Token f6e43814b879d6c45a7d3775d7739895",
           "Accept: application/vnd.sepaone.v1+json;"
       );
    }

    protected function _apiCall($getPath, $postData = null, $customRequest = null) {
        $log = Mage::getModel('xonu_sepaone/log');
        list($requestType,) = explode('/', $getPath, 2);
        $log->setRequestType($requestType);
        $log->setRequestAt($this->timestampLocal());

        $apiTestMode = Mage::getStoreConfigFlag('xonu_directdebit/sepaone/testmode_active');

        $apiUrl = $apiTestMode ? self::API_URL_TEST : self::API_URL_LIVE;

        $apiToken = Mage::getStoreConfig($apiTestMode ?
            'xonu_directdebit/sepaone/token_test' :
            'xonu_directdebit/sepaone/token_live');

        $url = $apiUrl . $getPath;
        $log->setRequestUri($url);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        if(!is_null($customRequest)) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $customRequest);
            $log->setRequestCustom($customRequest);
        }

        if(!is_null($postData)) {
            $log->setData('order_id', $postData['log']['order_id']);
            $log->setData('order_increment_id', $postData['log']['order_increment_id']);
            $log->setData('mandate_id', $postData['log']['mandate_id']);
            unset($postData['log']); // do not transfer log data

            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body = json_encode($postData));

            $log->setRequestBody($body);
            $log->setRequestBodyLength(strlen($body));
        }

        $headers = array(
            "Content-Type: application/json",
            "Authorization: Token $apiToken",
            "Accept: application/vnd.sepaone.v1+json;"
        );
        // $log->setRequestHeaders(json_encode($headers));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $log->save();

        $tStart = self::mtime();
        $response = array('json' => curl_exec($ch)); $response['assoc'] = json_decode($response['json'], true);

        $tFinish = self::mtime();

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $log->setResponseCode($httpCode);

        $log->setResponseAt($this->timestampLocal());

        $log->setResponseTime($tFinish - $tStart);
        $log->setResponseBody($response['json']);
        $log->setResponseBodyLength(strlen($response['json']));

        if(isset($response['assoc'][0])) {
            @$log->setData('remote_livemode', $response['assoc'][0]['livemode']);
        } else {
            @$log->setData('remote_transaction_id', $response['assoc']['id']);
            @$log->setData('remote_livemode', $response['assoc']['livemode']);
        }

        $log->save();

        curl_close($ch);

        return $response;
    }

// Mandates / Collection / Create a mandate
// http://docs.sepaone.apiary.io/#reference/mandates/collection/create-a-mandate
/**
 * reference: Unique identifier of a single mandate
 * signature_date: Date when the customer accepted / signed the mandate
 * ip: Customer IP (as submitted while creation)
 * recurring: Indicator if this is a one-off or a mandate for recurring transactions
 * bank_account: A bank account entity:
 *      name: Name of account holder
 *      iban: IBAN
 *      bic: BIC
 * created_at: Date and time at which the mandate has been created
 * updated_at: Date and time at which the most recent change has been made
 */
    public function mandateCreate($mandateData) {
        return $this->_apiCall('mandates', $mandateData);


        /*
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://www.sepaone.com/test/api/mandates");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_POST, TRUE);

        curl_setopt($ch, CURLOPT_POSTFIELDS, "{
            \"reference\": \"810b096cbe1e4d05b5b59880ffa7afcd\",
            \"signature_date\": \"2014-06-12\",
            \"ip\": \"127.0.0.1\",
            \"recurring\": true,
            \"bank_account\": {
                \"iban\": \"DE89370400440532013000\",
                \"bic\": \"COBADEFF370\",
                \"name\": \"Fritz Mate\"
            }
        }");

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Token f6e43814b879d6c45a7d3775d7739895",
            "Accept: application/vnd.sepaone.v1+json;"
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        var_dump($response);
        */
    }

// Mandates / Entity / Retrieve a mandate
// http://docs.sepaone.apiary.io/#reference/mandates/entity/retrieve-a-mandate
// id : reference of the Mandate to perform action with. Example: man-b8609c02-a456-4b4c-a5cc-ea1e4c4
    public function mandateGet($id) {
        return $this->_apiCall("mandates/$id");


        /*
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://www.sepaone.com/test/api/mandates/$id");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Token f6e43814b879d6c45a7d3775d7739895",
            "Accept: application/vnd.sepaone.v1+json;",
            "Content-Type: application/json"
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        var_dump($response);
        */
    }

// Mandates / Entity / Update a mandate
// http://docs.sepaone.apiary.io/#reference/mandates/entity/update-a-mandate
// id : reference of the Mandate to perform action with. Example: man-b8609c02-a456-4b4c-a5cc-ea1e4c4
    public function mandateUpdate($id, $mandateData) {
        return $this->_apiCall("mandates/$id", $mandateData, 'PATCH');


        /*
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://www.sepaone.com/test/api/mandates/$id");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");

        curl_setopt($ch, CURLOPT_POSTFIELDS, "{
           \"signature_date\": \"2014-06-12\"
        }");

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Token f6e43814b879d6c45a7d3775d7739895",
            "Accept: application/vnd.sepaone.v1+json;",
            "Content-Type: application/json"
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        var_dump($response);
        */
    }

// Mandates / Entity / Remove a mandate
// http://docs.sepaone.apiary.io/#reference/mandates/entity/remove-a-mandate
// id : reference of the Mandate to perform action with. Example: man-b8609c02-a456-4b4c-a5cc-ea1e4c4
    public function mandateRemove($id) {
        return $this->_apiCall("mandates/$id", null, 'DELETE');


        /*
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://www.sepaone.com/test/api/mandates/$id");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Token f6e43814b879d6c45a7d3775d7739895",
            "Accept: application/vnd.sepaone.v1+json;",
            "Content-Type: application/json"
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        var_dump($response);
        */
    }

// Mandates / New Reference / Generate a new unique mandate reference
// http://docs.sepaone.apiary.io/#reference/mandates/new-reference/generate-a-new-unique-mandate-reference
// no input parameters
    public function mandateCreateUniqueReference() {
        return $this->_apiCall("mandates/new");


        /*
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://www.sepaone.com/test/api/mandates/new");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Token f6e43814b879d6c45a7d3775d7739895",
            "Accept: application/vnd.sepaone.v1+json;",
            "Content-Type: application/json"
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        var_dump($response);
        */
    }

// Mandates / Lookup by IBAN / lookup a mandate by bank account
// http://docs.sepaone.apiary.io/#reference/mandates/lookup-by-iban/lookup-a-mandate-by-bank-account
// The IBAN used to loopup a mandate. Example: DE89370400440532013000.
    public function mandateGetByIban($iban) {
        return $this->_apiCall("mandates/lookup?iban=$iban");


        /*
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://www.sepaone.com/test/api/mandates/lookup?iban=$iban");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Token f6e43814b879d6c45a7d3775d7739895",
            "Accept: application/vnd.sepaone.v1+json;",
            "Content-Type: application/json"
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        var_dump($response);
        */
    }

// Mandates / Lookup by account number and bank code / lookup a mandate by account number and bank code
// http://docs.sepaone.apiary.io/#reference/mandates/lookup-by-account-number-and-bank-code/lookup-a-mandate-by-account-number-and-bank-code
// account_number: The account number to loopup a mandate. Example: 0648489890.
// bank_code: The bank code to loopup a mandate. Example: 50010517.
    public function mandateGetByAccount($account_number, $bank_code) {
        return $this->_apiCall("mandates/lookup?account_number$account_number=&bank_code=$bank_code");


        /*
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://www.sepaone.com/test/api/mandates/lookup?account_number$account_number=&bank_code=$bank_code");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Token f6e43814b879d6c45a7d3775d7739895",
            "Accept: application/vnd.sepaone.v1+json;",
            "Content-Type: application/json"
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        var_dump($response);
        */
    }

// Mandates / Lookup by reference / lookup a mandate by reference
// http://docs.sepaone.apiary.io/#reference/mandates/lookup-by-reference/lookup-a-mandate-by-reference
// The reference used to loopup a mandate. Example: 168ed17bce654a618f90f35a64db325e
    public function mandateGetByReference($reference) {
        return $this->_apiCall("mandates/lookup?reference=$reference");


        /*
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://www.sepaone.com/test/api/mandates/lookup?reference=$reference");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Token f6e43814b879d6c45a7d3775d7739895",
            "Accept: application/vnd.sepaone.v1+json;",
            "Content-Type: application/json"
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        var_dump($response);
        */
    }

// Mandates / Collection / List all mandates
// http://docs.sepaone.apiary.io/#reference/mandates/collection/list-all-mandates
// no input parameters
    public function mandateGetAll() {
        return $this->_apiCall("mandates");


        /*
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://www.sepaone.com/test/api/mandates");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Token f6e43814b879d6c45a7d3775d7739895",
            "Accept: application/vnd.sepaone.v1+json;"
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        var_dump($response);
        */
    }

// Transactions / Collection / Create new transaction for an existing mandate
// http://docs.sepaone.apiary.io/#reference/transactions/collection/create-new-transaction-for-an-existing-mandate
/**
 * mandate: Nested mandate entity
 *      reference: Unique identifier of mandate for which the transaction is being created
 * amount_in_cents: Transaction amount in EUR cents
 * remittance_information: will be shown on the customers statement (optional)
 */
    public function transactionCreate($transactionData) {
        return $this->_apiCall("transactions", $transactionData);


        /*
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://www.sepaone.com/test/api/transactions");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_POST, TRUE);

        curl_setopt($ch, CURLOPT_POSTFIELDS, "{
            \"mandate\": {
                \"reference\": \"168ed17bce654a618f90f35a64db325e\"
            },
            \"amount_in_cents\": 12033,
            \"remittance_information\": \"order id #xxxx\"
        }");

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Token f6e43814b879d6c45a7d3775d7739895",
            "Accept: application/vnd.sepaone.v1+json;"
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        var_dump($response);
        */
    }

// Transactions / Collection / Create new transaction and mandate
// http://docs.sepaone.apiary.io/#reference/transactions/collection/create-new-transaction-and-mandate
    public function transactionMandateCreate($mandateData, $transactionData) {
                                                 // array merge order is important!
        return $this->_apiCall("transactions", array_merge($transactionData, array('mandate' => $mandateData)));


        /*
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://www.sepaone.com/test/api/transactions");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_POST, TRUE);

        curl_setopt($ch, CURLOPT_POSTFIELDS, "{
            \"amount_in_cents\": 12033,
            \"remittance_information\": \"order id #xxxx\",
            \"mandate\": {
                \"bank_account\": {
                    \"name\": \"Fritz Mate\",
                    \"bic\": \"COBADEFF370\",
                    \"iban\": \"DE89370400440532013000\"
                },
                \"ip\": \"127.0.0.1\",
                \"recurring\": true,
                \"reference\": \"810b096cbe1e4d05b5b59880ffa7afcd\",
                \"signature_date\": \"2015-05-12\"
            }
        }");

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Token f6e43814b879d6c45a7d3775d7739895",
            "Accept: application/vnd.sepaone.v1+json;"
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        var_dump($response);
        */
    }

// Transactions / Collection / List all
// http://docs.sepaone.apiary.io/#reference/transactions/list-all
/**
 * q: Simple search term. Examples:
 *      q[transaction.mandate.reference]=17072014001e4d05b5b59880ffa7test
 *      q[transaction.mandate.id]=man-b8609c02-a456-4b4c-a5cc-ea1e4c4
 * page: Which page of the collection. Example: 1.
 * per_page (optional, integer, `10`) ... How many transactions `per_page`.
 */
    public function transactionGetAll() {
        return $this->_apiCall("transactions?q=&page=");


        /*
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://www.sepaone.com/test/api/transactions?q=&page=");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Token f6e43814b879d6c45a7d3775d7739895",
            "Accept: application/vnd.sepaone.v1+json;"
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        var_dump($response);
        */
    }

// Transactions / Entity / Get a single transaction
// http://docs.sepaone.apiary.io/#reference/transactions/entity/get-a-single-transaction
// id: String id of the transaction. Example: trx-baacf5f8-a2c3-4c7a-a6a3-af6cc15.
/**
 * id: Unique identifier of transaction
 * mandate: Unique identifier of mandate for which the transaction is being created
 * status: current status of transaction
 * date: Date when transaction was requested
 * amount: Transaction amount in EUR cents
 * fee: Transaction fee in EUR cents
 * chargeback: Information about the chargeback if a chargeback was issued.
 *             (Information includes id, reason, and fee). Otherwise null.
 * created_at: Date and time at which transaction has been created
 * updated_at: Date and time at which the most recent change has been made
 */
    public function transactionGetById($id) {
        return $this->_apiCall("transactions/$id");


        /*
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://www.sepaone.com/test/api/transactions/{id}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Token f6e43814b879d6c45a7d3775d7739895",
            "Accept: application/vnd.sepaone.v1+json;"
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        var_dump($response);
        */
    }

// Chargebacks / Entity / GET single chargeback
// http://docs.sepaone.apiary.io/#reference/chargebacks/entity/get-single-chargeback
// id: Integer id of the chargeback. Has example value. Example: 1.
    public function chargebackGetById($id) {
        return $this->_apiCall("chargebacks/$id");


        /*
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://www.sepaone.com/test/api/chargebacks/{id}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Token f6e43814b879d6c45a7d3775d7739895",
            "Accept: application/vnd.sepaone.v1+json;"
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        var_dump($response);
        */
    }

// Refunds / Collection / Create a refund
// http://docs.sepaone.apiary.io/#reference/refunds/collection/create-a-refund
/**
 * transaction_id: Unique identifier of the transaction to be refunded
 * remittance_information: Usage information that will be shown on the customer's bank statment
 * amount_in_cents: The amount that should be refunded. (maximum is the transaction amount)
 */
    public function refundCreate($id, $refundData) {
        return $this->_apiCall("transactions/$id/refunds", $refundData);


        /*
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://www.sepaone.com/test/api/transactions/transaction_id/refunds");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_POST, TRUE);

        curl_setopt($ch, CURLOPT_POSTFIELDS, "{
            \"amount_in_cents\": \"12033\",
            \"remittance_information\": \"refund for #xxxx\"
        }");

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Token f6e43814b879d6c45a7d3775d7739895",
            "Accept: application/vnd.sepaone.v1+json;"
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        var_dump($response);
        */
    }

// Events / Transactions / List all events
// http://docs.sepaone.apiary.io/#reference/events/transactions/list-all-events
/**
 * transaction_id: Which page of the collection. Example: trx-baacf5f8-a2c3-4c7a-a6a3-af6cc15.
 * page: Which page of the collection. Example: 1.
 * per_page (optional, integer, `10`) ... How many transactions `per_page`.
 */
    public function eventGetAll($id) {
        return $this->_apiCall("transactions/$id/events?page=");


        /*
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://www.sepaone.com/test/api/transactions/{transaction_id}/events?page=");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Token f6e43814b879d6c45a7d3775d7739895",
            "Accept: application/vnd.sepaone.v1+json;"
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        var_dump($response);
        */
    }


}