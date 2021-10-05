<?php
class EcontRestClient {
    public static function request($method, $params = array(),$timeout = 10) {
        //production endpoint
        $endpoint = 'https://ee.econt.com/services';

        //test endpoint
        //$endpoint = 'https://demo.econt.com/ee/services';

        //demo login - iasp-dev/iasp-dev
        $auth = array(
            'login' => '',
            'password' => '',
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint . '/' . rtrim($method,'/'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
        if(!empty($auth)) curl_setopt($ch, CURLOPT_USERPWD, $auth['login'].':'.$auth['password']);
        if(!empty($params)) curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($params));
        curl_setopt($ch, CURLOPT_TIMEOUT, !empty($timeout) && intval($timeout) ? $timeout : 4);
        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);

        $jsonResponse = json_decode($response,true);
        if(!$jsonResponse) {
            throw new \Exception("Invalid response.");
        }
        if(strpos($httpStatus,'2') !== 0) {
            throw new \Exception(self::flattenError($jsonResponse));
        } else {
            return $jsonResponse;
        }
    }

    public static function flattenError($err) {
        $msg = trim($err['message']);
        $innerMsgs = array();
        foreach ($err['innerErrors'] as $e) $innerMsgs[] = self::flattenError($e);
        if (!empty($msg) && !empty($innerMsgs)) {
            $msg .= ": ";
        }
        return $msg . implode("; ", array_filter($innerMsgs));
    }
}


    //data for create shipment
    $receiver_name = 'Ivan Georgiev';
    $receiver_phone = '+359879945627';
    $payment = '18.24';
    $order_id = '000024';
    $sender_name = 'Company ltd.';
    $sender_phone = '+359887684612';

    $econt_office_selected = '9518';//office code
    $explodOffice = explode("[",$econt_office_selected);
    $officeCode = explode("]",$explodOffice[1]);
    $SelectOffice = mysqli_query($mysqli,"SELECT * FROM `econt_offices` WHERE office_code = '".$officeCode[0]."'");
    $viewOffice = mysqli_Fetch_Array($SelectOffice);
    $senderOfficeCode = $viewOffice['office_id'];

    $emailOnDelivery = 'exapmle@domain.com';
    $smsOnDelivery = 1; //1-true;0-false
    $econt_office_selected = $_POST['receiverOfficeCode'];
    $explodOffice = explode("[",$econt_office_selected);
    $officeCode = explode("]",$explodOffice[1]);
    $SelectOffice = mysqli_query($mysqli,"SELECT * FROM `econt_offices` WHERE office_code = '".$officeCode[0]."'");
    $viewOffice = mysqli_Fetch_Array($SelectOffice);
    $econt_office = $viewOffice['office_code'];
    $receiverOfficeCode = $econt_office;
    $packCount = $_POST['packCount'];
    $weight = $_POST['weight'];
    $shipmentDescription = 'Shipment description';
    $payAfterTest = $_POST['payAfterTest'];

if($_POST['shipment_type'] == 'office'){
    $econt = (EcontRestClient::request("Shipments/LabelService.createLabel.json",array(
        'mode' => 'create',
        'label' => array(
            'senderClient' => array(
                'name' => 'Company ltd.',
                'phones' => array('0887878787'),
                'molName' => 'Ivan Ivanov Georgiev',
                'companyType' => 'ЕООД',
                'clientNumber' => ''
            ),
            "senderAgent" => array(
                'name' => 'Ivan Ivanov Georgiev',
                'phones' => array('0887878787'),
            ),
            'senderOfficeCode' => '9518',
            'emailOnDelivery' => 'office@domain.com',
            'smsOnDelivery' => ''.$receiver_phone.'',
            'receiverClient' => array(
                'name' => ''.$receiver_name.'',
                'phones' => array(''.$receiver_phone.'')
            ),
            'receiverOfficeCode' => ''.$receiverOfficeCode.'',//receiver office code
            'packCount' => $packCount,
            'shipmentType' => 'pack',
            'weight' => ''.$weight.'',
            'sizeUnder60cm' => true,
            'shipmentDescription' => ''.$shipmentDescription.'',
            'services' => array(
                'declaredValueAmount' => 0.00,
                'declaredValueCurrency' => 'BGN',
                'cdAmount' => $payment,
                'cdCurrency' => 'BGN',
            ),
            'payAfterTest' => true,
            'paymentSenderMethod' => 'credit'
        )
    )));
}

if($_POST['shipment_type'] == 'address'){
    $econt = (EcontRestClient::request("Shipments/LabelService.createLabel.json",array(
        'mode' => 'create',
        'label' => array(
            'senderClient' => array(
                'name' => 'Company ltd.',
                'phones' => array('0887878787'),
                'molName' => 'Ivan Ivanov Georgiev',
                'companyType' => 'ЕООД',
                'clientNumber' => ''
            ),
            "senderAgent" => array(
                'name' => 'Ivan Ivanov Georgiev',
                'phones' => array('0887878787'),
            ),
            'senderOfficeCode' => '9518',
            'emailOnDelivery' => 'office@domain.com',
            'smsOnDelivery' => ''.$receiver_phone.'',
            'receiverClient' => array(
                'name' => ''.$receiver_name.'',
                'phones' => array(''.$receiver_phone.'')
            ),   
            'receiverAddress' => array(
                'city' => array(
                    'postCode' => 'Client postcode',
                    'name' => 'Client city name'
                ),
                'street' => 'Client street',
                'num' => 'Client street number'
            ),
            'packCount' => $packCount,
            'shipmentType' => 'pack',
            'weight' => ''.$weight.'',
            'sizeUnder60cm' => true,
            'shipmentDescription' => ''.$shipmentDescription.'',
            'services' => array(
                'declaredValueAmount' => 0.00,
                'declaredValueCurrency' => 'BGN',
                'cdAmount' => $payment,
                'cdCurrency' => 'BGN',
            ),
            'payAfterTest' => true,
            'paymentSenderMethod' => 'credit'
        )
    )));
}

foreach ($econt as $courier){
    $shipmentNumber = $courier['shipmentNumber'];
    $createdTime = $courier['createdTime'];
    $packCount = $courier['packCount'];
    $shipmentDescription = $courier['shipmentDescription'];
    $totalPrice = $courier['totalPrice'];
    $description = $courier['description'];
    $pdfURL = $courier['pdfURL'];
}

?>