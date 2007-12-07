<?php


if(!empty($_GET['language']) && $_GET['language'] == 'da') {
    $text[0] = 'Intraface Betaling';
    $text[1] = 'Du er nu ved at betale for ordre nummer';
    $text[2] = 'I alt hæves %s på fra dit kort';
    $text[3] = 'Betalingen foretages over Quickpay\'s sikker betalingsserver.';
    
} 
else {
    $text[0] = 'Intraface Payment';
    $text[1] = 'You are now about to pay for order number';
    $text[2] = '%s is withdrawed from your card';
    $text[3] = 'The payment is carried out via Quickpay\'s secure server.';
}



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="da" xml:lang="da">
<head>
    <title><?php echo $text[0]; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <style type="text/css">
        body { 
            font-family: Verdana, sans-serif; 
            font-size: 0.8em; 
            background:  white; 
            color: #000; 
            margin: 0; 
            padding: 0;
            text-align: center;
        }
                
        div#container { 
            padding: 2em;
            margin: 2em auto; 
            background: white; 
            width: 40em; 
            text-align: left; 
            color: black;
        }
        
        form#payment_details {
            
            border: 10px solid #f6ed70;
            padding: 2em; 
        }
        
        div#formrow {
            padding: 0.5em; 
            background-color: #f7f4ab;
            
        }
        
        div#formrow label {
            width: 13em;
            display: block;
            float: left;
        }
        
        div#formrow:hover {
            background-color: #EEEEEE;
        }
        
        div#cards_container {
            padding: 0.5em;
        }
        
        div#cards_container img {
            padding: 0em 0.2em;
        }
        
        p.cards {
            display: inline;
            margin-left: 1em;
        }
       
    </style>
</head>

<body>
    <div id="container">
        <h1>Intraface I/S</h1>
        <form action="https://secure.quickpay.dk/quickpay_pay.php" method="post" autocomplete="off" id="payment_details">

            <p><?php echo $text[1]; ?> <strong>###ORDERNUM###</strong></p> 
            <p><?php printf($text[2], '<strong>###CURRENCY### ###AMOUNT_FORMATTED###</strong>'); ?></p>
        
            <div id="cards_container">
                ###CARDS###
            </div>
            
            <div id="formrow">
                <label for="cardnum">###TXT_CARDNUM###</label>
                <input type="text" maxlength="16" size="19" name="cardnum" id="cardnum" />
            </div>
            
            <div id="formrow">
                <label for="month">###TXT_EXPIR###</label>
                <select name="month" id="month">###MONTH_OPTIONS###</select> / 
                <select name="year" id="year">###YEAR_OPTIONS###</select>
            </div>
            
            <div id="formrow">
                <label for="cvd">###TXT_CVD###</label>
                <input type="text" maxlength="3" size="3" name="cvd" id="cvd" />
            </div>

            <input name="submit" type="submit" value="   ###TXT_PAYBUTTON###   ">
        </form>
        
        <p><?php echo $text[3]; ?></p>
    </div>
</body>
</html> 
