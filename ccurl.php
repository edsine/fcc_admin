<?php

function CallAPI($method, $url, $data = false)
{
    $curl = curl_init();


    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }


    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);

    curl_close($curl);

    return $result;
}


if(isset($_POST['phone']))
{
    $number = $_POST['phone'];
    $message = $_POST['msg'];

    if(!empty($number) && !empty($message))
    {
        $postData = array('cmd' => 'sendquickmsg34', 'owneremail' => 'saleahmadu@gmail.com', 'subacct' => 'FEDERAL-CC', 'subacctpwd' => 'Federal@1#', 'message' => urldecode($message), 'sender' => "PGLEddy", 'sendto' => $number, 'msgtype' => '0');

        try {
            $response = CallAPI('', 'http://www.smslive247.com/index.aspx', $postData);

            print_r("Response: $response <br> Sent to: $number <br> Message: $message" );


        } catch (Throwable $t) {
            echo $t->getMessage();
        }

    }else{
        echo"Error: Fill in all details";
}
echo "<hr>";
}

?>



<form method="POST">

    <div>
    <label>Phone number</label>
    <input type="text" name="phone" placeholder="Phone numbers">
    </div>

    <div>
    <label>Message</label>
    <textarea rows="5" name="msg"></textarea>
    </div>

    <input type="submit" value="Send">

</form>
