<?php
ini_set('log_errors', TRUE);
session_start();
$page= "ajax";
include '../details.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor-mails/src/Exception.php';
require '../vendor-mails/src/PHPMailer.php';
require '../vendor-mails/src/SMTP.php';



require '2FA/vendor/autoload.php';

$g = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();



$user_ip= get_ip();
$user_cy= ip_info($user_ip, 'country');


function send_email($domain, $name, $sub, $body){
    // SEND EMAIL
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPDebug = 0;
    $mail->Host = "ssl://smtp.titan.email";
    $mail->Port = 465;
    $mail->SMTPAuth = true;
    $mail->Username = "support@".$domain;
    $mail->Password = "07066452000Ss#";
    $mail->setFrom("support@".$domain, $name);
    $mail->addReplyTo("support@".$domain, $name." Support");
    $mail->addAddress('admin@'.$domain, 'Admin');
    $mail->isHTML(true);  
    $mail->Subject = "❗  ".$sub;
    $mail->Body = $body;
    $mail->send();
}



if(isset($_POST['signup'])){
    
    $return_arr= array();
    
    if(isset($_POST['username'])){
      $username = stripslashes($_POST['username']); 
    $username = mysqli_real_escape_string($con,$username);  
    }
    
    if(isset($_POST['fname'])){
        $fname = stripslashes($_POST['fname']); 
        $fname = mysqli_real_escape_string($con,$fname);  
    }
    
    if(isset($_POST['lname'])){
        $lname = stripslashes($_POST['lname']); 
        $lname = mysqli_real_escape_string($con,$lname);  
    }
    
    if(isset($_POST['email'])){
        $email = stripslashes($_POST['email']); 
    $email = mysqli_real_escape_string($con,$email); 
    }
    if(isset($_POST['phone'])){
        $phone = stripslashes($_POST['phone']); 
    $phone = mysqli_real_escape_string($con,$phone); 
    }
    if(isset($_POST['pass'])){
        $pass = stripslashes($_POST['pass']); 
    $pass = mysqli_real_escape_string($con,$pass); 
    }
    
   
    if(isset($_POST['upline'])){
        $upline = stripslashes($_POST['upline']); 
    $upline = mysqli_real_escape_string($con,$upline); 
    }
    
   
    
     if(mysqli_num_rows(mysqli_query($con, "select * from members where username='$username' ")) > 0 ){
         
        	$msg= "Oops! The username is already taken. Please choose a different one";
            $err=1;
            $return_arr= array(
                'msg' => $msg,
                'err' => $err,
                'type' => 1
                );
                echo json_encode($return_arr);
                exit;
    }
    
    if (strpos($username, ' ') !== false) {
        $return_arr= array(
                'msg' => 'Username must not contain spaces',
                'err' => 1,
                );
                echo json_encode($return_arr);
                exit;
    } 
    
    
  
    
    if(mysqli_num_rows(mysqli_query($con, "select * from members where email='$email' ")) > 0 ){
         
        	$msg= "The email already exists. Please choose a different one";
            $err=1;
            $return_arr= array(
                'msg' => $msg,
                'err' => $err,
                'type' => 1
                );
                echo json_encode($return_arr);
                exit;
    }
    

$available=false;
		do{
		$ref_code= random_num(6);
		$check_code= mysqli_query($con, "SELECT * FROM `members` WHERE code='$ref_code' ");
		$check_code= mysqli_num_rows($check_code);
		if($check_code>0){
		    $available= true;
		}
		}while($available==true);
		
$time= time();
$ip= get_ip();
$country= ip_info($ip, 'country');
$cy= ip_info($ip, 'countrycode');
$state= ip_info($ip, 'state');

        $query= "insert into members (username, email, password, code, reg_date, upline, country, cy, state, phone, fname, lname) values ('$username', '$email', '$pass', '$ref_code', '$time', '$upline', '$country', '$cy', '$state', '$phone', '$fname', '$lname')";
        

if(mysqli_query($con, $query)){

// send_email($details_site_domain, $details_site_name, 'New Registration', 'A user: '.$username.' just registered from '.$country);


 // SEND EMAIL
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->SMTPDebug = 0;
$mail->Host = "ssl://smtp.titan.email";
$mail->Port = 465;
$mail->SMTPAuth = true;
$mail->Username = "support@".$details_site_domain;
$mail->Password = "07066452000Ss#";
$mail->setFrom("support@".$details_site_domain, $details_site_name);
$mail->addReplyTo("support@".$details_site_domain, $details_site_name." Support");
$mail->addAddress($email, $username);
$mail->isHTML(true);  

$mail_username= $username;


if(in_array($cy, $spanishSpeakingCountries)){
    $mail->Subject = "Bienvenido";
    $mail_subject= "Bienvenido";
    $mail_msg ='
   ¡Felicidades! Tu registro de cuenta fue exitoso. Bienvenido(a) a nuestra comunidad
     ';
     include 'mail2_sp.php';
}else{
    $mail->Subject = "Welcome to ".$details_site_name;
    $mail_subject= "Welcome to ".$details_site_name;
    $mail_msg ='
    Congratulations! Your account registration was successful. Welcome to our community
     ';
     include 'mail2.php';
}


$mail->Body = $body;
$mail->send();



if($upline != ""){
    $upline_fetch= mysqli_fetch_assoc(mysqli_query($con, "select * from members where username='$upline' "));
    
    // SEND EMAIL
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPDebug = 0;
    $mail->Host = "ssl://smtp.titan.email";
    $mail->Port = 465;
    $mail->SMTPAuth = true;
    $mail->Username = "support@".$details_site_domain;
    $mail->Password = "07066452000Ss#";
    $mail->setFrom("support@".$details_site_domain, $details_site_name);
    $mail->addReplyTo("support@".$details_site_domain, $details_site_name." Support");
    $mail->addAddress($upline_fetch['email'], $upline);
    $mail->isHTML(true);  
    
    $mail->Subject = "Referral Commission";
    $mail_subject= "Referral";
    $mail_username= $upline;
    
    if(in_array($cy, $spanishSpeakingCountries)){
        $mail->Subject = "Comisión de Referido";
        $mail_subject = "Referido";
        $mail_msg = '¡Felicidades! Un usuario se ha registrado usando tu enlace de referencia.<br>
            Recibirás una comisión tan pronto como este usuario comience su inversión.';

    }else{
        $mail->Subject = "Referral Commission";
        $mail_subject= "Referral";
        $mail_msg = 'Congratulations! A user has registered using your referral link.<br>
    You will get commission as soon as this user starts their investment
    ';
    }
            
    
    
    include 'mail2.php';
    
    $mail->Body = $body;
    $mail->send();

}


            $msg= "Your Account Has Been Successfully Created. Please wait, we are redirecting you...";
            $err=0;
            $type= 0;
            $return_arr= array(
                'msg' => $msg,
                'err' => $err,
                'type' => 0
                );
                echo json_encode($return_arr);
                
                exit;
            
        
}else{
    $return_arr= array(
                'msg' => mysqli_error($con),
                'err' => 1,
                'type' => 1
                );
                echo json_encode($return_arr);
                
                exit;
}




}else if(isset($_POST['signin'])){
    $return_arr= array();
    
    if(isset($_POST['id'])){
      $id = stripslashes($_POST['id']); 
    $id = mysqli_real_escape_string($con,$id);  
    }
   
    if(isset($_POST['pass'])){
        $pass = stripslashes($_POST['pass']); 
    $pass = mysqli_real_escape_string($con,$pass); 
    }
    
    
    $query = "SELECT * FROM `members` WHERE username='$id' and password='$pass' or email='$id' and password='$pass'";
    
$result = mysqli_query($con,$query) or die();
$rows = mysqli_num_rows($result);

if($rows<1){
    $msg= "Incorrect login details!";
        $err=1;
        $return_arr= array(
            'msg' => $msg,
            'err' => $err
            );
            echo json_encode($return_arr);
            
            exit;
}

$time= time()+3600;
    $fetch= mysqli_fetch_assoc($result);
    if($fetch['suspend'] == 1){
        $msg= "Sorry, your account has been suspended!";
        $err=1;
        $return_arr= array(
            'msg' => $msg,
            'err' => $err
            );
            echo json_encode($return_arr);
            exit;
    }
    
    
    $username = $fetch['username'];
    
    $_SESSION['username'] = $username;
    
    if($fetch['logins']== ""){
        $logins= $time."=".$time;
    }else{
        $logins= explode("=", $fetch['logins']);
        $last_login= $logins[0];
        $curr_login= $logins[1];
        $logins= $curr_login."=".$time;
    }
    mysqli_query($con, "update members set logins='$logins' where username='$username' ");
    

    $msg= "Welcome back! Your login was successful";
        $err=0;
        $return_arr= array(
            'msg' => $msg,
            'err' => $err
            );
            echo json_encode($return_arr);
            
            exit;
    
    
    
}else if(isset($_POST['buy_miner'])){
    
   $amt= $_POST['amt'];
    $miner= $_POST['plan'];
    $time= time();
    $code= random_num(8);
    $action= $_POST['action'];
    
    if($action== "balance"){
  
  $check_amt=0;
//   if(mysqli_num_rows(mysqli_query($con, "select SUM(amt) as sum from miners where username='$username' and miner='$miner' and status=1 ")) > 0){
//       $check_amt_fetch= mysqli_fetch_assoc(mysqli_query($con, "select SUM(amt) as sum from miners where username='$username' and miner='$miner' and status=1 "));
//       $check_amt= $check_amt_fetch['sum'];
//   }
  
    
    if($amt+$check_amt > $plans_arr[$miner-1]['max'] ){
        $msg= "Max amount is $".number_format($plans_arr[$miner-1]['max'], 2);
            $err=1;
            $return_arr= array(
                'msg' => $msg,
                'err' => $err
                );
                echo json_encode($return_arr);
                
                exit;
    }
    
    if($amt+$check_amt < $plans_arr[$miner-1]['min'] ){
        $msg="Min amount is $".number_format($plans_arr[$miner-1]['min']);
            $err=1;
            $return_arr= array(
                'msg' => $msg,
                'err' => $err
                );
                echo json_encode($return_arr);
                
                exit;
    }
    
    
    
    if($amt > $user_fetch['deposited'] + $user_fetch['program_cash']){
        $msg="Insufficient balance!";
            $err=1;
            $return_arr= array(
                'msg' => $msg,
                'err' => $err
                );
                echo json_encode($return_arr);
                
                exit;
    }
    
    
    $program_cash= $user_fetch['program_cash'];
    $deposited= $user_fetch['deposited'];
    if($amt > $program_cash){
        $deposited= $user_fetch['deposited'] + $user_fetch['program_cash'];
        $deposited -= $amt;
        mysqli_query($con, "update members set deposited='$deposited', program_cash=0 where username='$username' ");
    }else{
        $program_cash -= $amt;
        mysqli_query($con, "update members set program_cash='$program_cash' where username='$username' ");
    }
    
    
    
    if($plans_arr[$miner-1]['duration_type'] == "days"){
        $add = 86400;
    }else{
        $add = 3600;
    }
    $next_time = $time + $add;
    
    if(mysqli_num_rows(mysqli_query($con, "select * from miners where username='$username' and miner='$miner' and status=1 ")) > 0){
          $check_amt_fetch= mysqli_fetch_assoc(mysqli_query($con, "select * from miners where username='$username' and miner='$miner' and status=1 "));
          $deposit= $check_amt_fetch['amt'];
          $deposit += $amt;
          $this_miner= $check_amt_fetch['id'];
          mysqli_query($con, "update miners set amt='$deposit', time='$next_time', time_static='$time', status=1 where id='$this_miner' ");
      }else{
          mysqli_query($con, "insert into miners (username, miner, time, amt, status, time_static) values ('$username', '$miner', '$next_time', '$amt', 1, '$time' ) ");
      }
      
  
  mysqli_query($con, "insert into program_history (username, name, time, amt, status, plan, detail, method, code) values ('$username', 'buy_miner', '$time', '$amt', 1, '$miner', 'Investment Activation Successful', 'Balance', '$code' ) ");
  
  
  send_email($details_site_domain, $details_site_name, 'Investment purchase', 'A user: '.$username.' just made an investment of $'.$amt);
  

    $upline= $user_fetch['upline'];
    if($upline != "" && $user_fetch['upline_once']== 0){
        $upline_fetch= mysqli_fetch_assoc(mysqli_query($con, "select * from members where username='$upline' "));
        $upline_ref= $upline_fetch['ref_earning'];
        $bonus_perc= $plans_arr[$miner-1]['ref_bonus'];
        $bonus= ($bonus_perc/100)*$amt;
        $upline_ref += $bonus;
        mysqli_query($con, "update members set ref_earning ='$upline_ref' where username='$upline'");
        
        
         // SEND EMAIL
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->SMTPDebug = 0;
            $mail->Host = "ssl://smtp.titan.email";
            $mail->Port = 465;
            $mail->SMTPAuth = true;
            $mail->Username = "support@".$details_site_domain;
            $mail->Password = "07066452000Ss#";
            $mail->setFrom("support@".$details_site_domain, $details_site_name);
            $mail->addReplyTo("support@".$details_site_domain, $details_site_name." Support");
            $mail->addAddress($upline_fetch['email'], $upline);
            $mail->isHTML(true);  
            
            $mail->Subject = "Referral Bonus";
            $mail_subject= "Your referral bonus";
            $mail_username= $upline;
                    
            $mail_msg = 'Congratulations, you have just received a referral bonus of $'.number_format($bonus).' ('.$bonus_perc.'%) as one of your downlines with the username '.$username.' has completed their investment with us. <br><br>
            Please kindly note that this bonus is unlimited as long as you continue to refer.<br>
            Thank you.
            ';
            if(in_array($cy, $spanishSpeakingCountries)){
                $mail->Subject = "Bono de referido";
                $mail_subject= "Bono de referido";
                $mail_msg = 'Felicidades, has ganado un bono de referido de $'.number_format($bonus).' ('.$bonus_perc.'%). ¡Disfruta de tus recompensas';
            }else{
                $mail->Subject = "Referral Bonus";
                $mail_subject= "Your referral bonus";
                $mail_msg = 'Congratulations! Youve earned a referral bonus of $'.number_format($bonus).' ('.$bonus_perc.'%). Enjoy your rewards';
            }
            
            include 'mail2.php';
            // $body= $mail_msg;
                    
            $mail->Body = $body;
            $mail->send();

    }


$duration_type= $plans_arr[$miner-1]['duration_type'];
if($duration_type=== "hours"){
    $profit= ($plans_arr[$miner-1]['profit']/100)*$amt;
}else if($duration_type=== "days"){
    $profit= ($plans_arr[$miner-1]['profit']/100)*$amt*$plans_arr[$miner-1]['duration'];
}else{
    $profit= ($plans_arr[$miner-1]['profit']/100)*$amt*4*6;
}

    
 // SEND EMAIL
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->SMTPDebug = 0;
$mail->Host = "ssl://smtp.titan.email";
$mail->Port = 465;
$mail->SMTPAuth = true;
$mail->Username = "support@".$details_site_domain;
$mail->Password = "07066452000Ss#";
$mail->setFrom("support@".$details_site_domain, $details_site_name);
$mail->addReplyTo("support@".$details_site_domain, $details_site_name." Support");
$mail->addAddress($user_fetch['email'], $username);
$mail->isHTML(true);  

$mail_username= $username;
        

if(in_array($user_fetch['cy'], $spanishSpeakingCountries)){
    $mail->Subject = "Nueva Inversión";
    $mail_subject= "Nueva Inversión";
    $mail_msg ='
   <div class="container">
    <h2>¡Inversión Activada!</h2>
    <p>¡Felicidades! Tu inversión en el Paquete Básico se ha activado correctamente. A continuación, se detallan los datos:</p>

    <ul>
        <li><strong>Monto:</strong> $'.number_format($amt, 2).'</li>
        <li><strong>ROI:</strong> '.$plans_arr[$miner-1]['profit'].'%</li>
        <li><strong>Plan de Inversión:</strong> '.$plans_arr[$miner-1]['name'].' Plan</li>
        <li><strong>Duración:</strong> '.$plans_arr[$miner-1]['duration'].' '.$plans_arr[$miner-1]['duration_type'].'</li>
        <li><strong>Beneficio Total:</strong> $'.number_format($profit, 2).'</li>
        <li><strong>Estado:</strong> Activo</li>
        <li><strong>Fecha:</strong> '.date("jS M, Y", time()).'</li>
    </ul>

    <p>Gracias por elegir nuestra plataforma de inversión. Si tienes alguna pregunta, no dudes en contactarnos.</p>
</div>

     ';
     include 'mail2_sp.php';
}else{
    
$mail->Subject = "New Investment";
$mail_subject= "New Investment";
$mail_msg = '
<div class="container">
        <h2>Investment Activated!</h2>
        <p>Congratulations! Your investment for the Basic Package has been successfully activated. Below are the details:</p>

        <ul>
            <li><strong>Amount:</strong> $'.number_format($amt, 2).'</li>
            <li><strong>ROI:</strong> '.$plans_arr[$miner-1]['profit'].'%</li>
            <li><strong>Investment Plan:</strong> '.$plans_arr[$miner-1]['name'].' Plan</li>
            <li><strong>Duration:</strong> '.$plans_arr[$miner-1]['duration'].' '.$plans_arr[$miner-1]['duration_type'].'</li>
            <li><strong>Total Profit:</strong> $'.number_format($profit, 2).'</li>
            <li><strong>Status:</strong> Active</li>
            <li><strong>Date:</strong>'.date("jS M, Y", time()).'</li>
        </ul>

        <p>Thank you for choosing our investment platform. If you have any questions, feel free to reach out.</p>
    </div>
';
     include 'mail2.php';
}


$mail->Body = $body;
$mail->send();

mysqli_query($con, "update members set upline_once= 1 where username='$username' ");
    
    $msg="Package activation successs!";
            $err=0;
            $return_arr= array(
                'msg' => $msg,
                'err' => $err
                );
                echo json_encode($return_arr);
                
                exit;
    
    
    
    }else{
        $method= $_POST['method'];
        $id= $_POST['id'];
        $method= $coins_arr[$method]['name'];



$duration_type= $plans_arr[$miner-1]['duration_type'];
if($duration_type=== "hours"){
    $profit= ($plans_arr[$miner-1]['profit']/100)*$amt;
}else if($duration_type=== "days"){
    $profit= ($plans_arr[$miner-1]['profit']/100)*$amt*$plans_arr[$miner-1]['duration'];
}else{
    $profit= ($plans_arr[$miner-1]['profit']/100)*$amt*4*6;
}

        
        // SEND EMAIL
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->SMTPDebug = 0;
$mail->Host = "ssl://smtp.titan.email";
$mail->Port = 465;
$mail->SMTPAuth = true;
$mail->Username = "support@".$details_site_domain;
$mail->Password = "07066452000Ss#";
$mail->setFrom("support@".$details_site_domain, $details_site_name);
$mail->addReplyTo("support@".$details_site_domain, $details_site_name." Support");
$mail->addAddress($user_fetch['email'], $username);
$mail->isHTML(true);  

$mail->Subject = "New Investment";
$mail_subject= "New Investment";
$mail_username= $username;
        
$mail_msg = '
You successfully activated an investment package on your '.$details_site_name.' account.<br>
Below are the details;<br>
<div>
<br>
Amount: $'.number_format($amt, 2).'<br><br>
ROI: '.$plans_arr[$miner-1]['profit'].'%<br><br>
Investment Plan: '.$plans_arr[$miner-1]['name'].' PLAN<br><br>
Duration Of Investment: '.$plans_arr[$miner-1]['duration'].' '.$plans_arr[$miner-1]['duration_type'].'<br><br>
Total Profit To Be Earned: '.number_format($profit, 2).'<br><br>
Status: <b style="color: red">PENDING</b><br><br>
Investment ID: '.$id.'<br><br>
Date: '.date("jS M, Y", time()).'
</div>
<br>
';

include 'mail2.php';

$mail->Body = $body;
$mail->send();

$email_body= "A user ".$username." made a deposit for ".$plans_arr[$miner-1]['name']." plan to your ".$method." wallet";

        
        mysqli_query($con, "insert into program_history (username, name, time, amt, status, plan, method, code, detail) values ('$username', 'deposit', '$time', '$amt', 0, '$miner', '$method', '$id' , 'Investment Activation Pending') ");
        $return_arr= array(
                'msg' => "Your deposit is now being confirmed",
                'err' => 0
                );
                echo json_encode($return_arr);
                exit;
    }
    
    
    
    
    
}else if(isset($_POST['topup'])){
    if(!isset($_SESSION['username'])){
        echo "login";
        exit;
    }
    
    
    
    $amt= $_POST['amt'];
    $method= $_POST['coin'];
    $id= $_POST['id'];
    $method= $coins_arr[$method]['name'];

    $plan= 0;
    $cat= "top";
    
    
    $time= time();
    $code= random_num(8);
    

    
   if(mysqli_query($con, "insert into program_history (username, cat, amt, status, time, name, code, method, detail) values ('$username', '$cat', '$amt', 0, '$time', 'deposit', '$code', '$method', 'Fund Confirmation Pending')")){

send_email($details_site_domain, $details_site_name, 'Deposit confirmation', 'A user: '.$username.' just made a deoosit of $'.$amt);


$email= $user_fetch['email'];
 // SEND EMAIL
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->SMTPDebug = 0;
$mail->Host = "ssl://smtp.titan.email";
$mail->Port = 465;
$mail->SMTPAuth = true;
$mail->Username = "support@".$details_site_domain;
$mail->Password = "07066452000Ss#";
$mail->setFrom("support@".$details_site_domain, $details_site_name);
$mail->addReplyTo("support@".$details_site_domain, $details_site_name." Support");
$mail->addAddress($user_fetch['email'], $username);
$mail->isHTML(true);  

$mail_username= $username;


if(in_array($user_fetch['cy'], $spanishSpeakingCountries)){
    $mail->Subject = "Tu depósito está siendo confirmado";
    $mail_subject= "Depósito";
    $mail_msg ='
   <div class="container">
        <h2>Confirmación de Depósito</h2>
        <p>Esto es solo para informarte que tu depósito está siendo revisado en este momento.</p>
        <p>Por favor, relájate; te notificaremos tan pronto como sea confirmado.</p>
        <p>Gracias por tu paciencia.</p>
    </div>

     ';
     include 'mail2_sp.php';
}else{
    $mail->Subject = "Your deposit is being confirmed";
    $mail_subject= "Your deposit is being confirmed";
    $mail_msg ='
    <div class="container">
        <p>This is just to let you know that your deposit is currently being reviewed.</p>
        <p>Please sit back and relax; we will notify you as soon as it is confirmed.</p>
        <p>Thank you for your patience.</p>
    </div>
     ';
     include 'mail2.php';
}
   

// $body= $mail_msg;
        
$mail->Body = $body;
$mail->send();


        $msg= "Transaction has been submitted for confirmation";
        $err=0;
        $return_arr= array(
            'msg' => $msg,
            'err' => $err
            );
            echo json_encode($return_arr);
            
            exit;
   }else{
        $msg="An error occured. Please try again later";
        $err=1;
        $return_arr= array(
            'msg' => $msg,
            'err' => $err
            );
            echo json_encode($return_arr);
            
            exit;
   }
    
    
    
    
}else if(isset($_POST['edit_acct'])){
    
    $type= $_POST['type'];
    
    
    if($type== "acct"){
        
        if(isset($_POST['fname']) && $_POST['fname'] != ""){
            $fname = stripslashes($_POST['fname']); 
            $fname = mysqli_real_escape_string($con,$fname);  
            mysqli_query($con, "update members set fname='$fname' where username='$username' ");
        }
        
        if(isset($_POST['lname']) && $_POST['lname'] != ""){
            $lname = stripslashes($_POST['lname']); 
            $lname = mysqli_real_escape_string($con,$lname);  
            mysqli_query($con, "update members set lname='$lname' where username='$username' ");
        }
        
        if(isset($_POST['zip']) && $_POST['zip'] != ""){
            $zip = stripslashes($_POST['zip']); 
            $zip = mysqli_real_escape_string($con,$zip);  
            mysqli_query($con, "update members set zip='$zip' where username='$username' ");
        }
        
        if(isset($_POST['city']) && $_POST['city'] != ""){
            $city = stripslashes($_POST['city']); 
            $city = mysqli_real_escape_string($con,$city);  
            mysqli_query($con, "update members set city='$city' where username='$username' ");
        }
        
        if(isset($_POST['state']) && $_POST['state'] != ""){
            $state = stripslashes($_POST['state']); 
            $state = mysqli_real_escape_string($con,$state);  
            mysqli_query($con, "update members set state='$state' where username='$username' ");
        }
        
        if(isset($_POST['email']) && $_POST['email'] != ""){
            $email = stripslashes($_POST['email']); 
            $email = mysqli_real_escape_string($con,$email);  
            mysqli_query($con, "update members set email='$email' where username='$username' ");
        }
        
        
        if(isset($_POST['phone']) && $_POST['phone'] != ""){
            $phone = stripslashes($_POST['phone']); 
            $phone = mysqli_real_escape_string($con,$phone);  
            mysqli_query($con, "update members set phone='$phone' where username='$username' ");
        }
        
        if(isset($_POST['age']) && $_POST['age'] != ""){
            $age = stripslashes($_POST['age']); 
            $age = mysqli_real_escape_string($con,$age);  
            mysqli_query($con, "update members set age='$age' where username='$username' ");
        }
        
        if(isset($_POST['occupation']) && $_POST['occupation'] != ""){
            $occupation = stripslashes($_POST['occupation']); 
            $occupation = mysqli_real_escape_string($con,$occupation);  
            mysqli_query($con, "update members set occupation='$occupation' where username='$username' ");
        }
        
        
         $return_arr= array(
                'msg' => "Changes have been saved!",
                'err' => 0
                );
                echo json_encode($return_arr);
                
                exit;
        
    }
    
    
    
    
    if($type== "pass" && $_POST['old_pass'] != "" && $_POST['pass'] != "" && $_POST['pass2'] != ""){
        
        $old_pass= $_POST['old_pass'];
        $pass= $_POST['pass'];
        $pass2= $_POST['pass2'];
        
        if($user_fetch['password'] !== $old_pass){
            $return_arr= array(
                'msg' => "Old password is wrong!",
                'err' => 1
                );
                echo json_encode($return_arr);
                
                exit;
        }
        
        
        if($pass !== $pass2){
            $return_arr= array(
                'msg' => "New passwords do not match!",
                'err' => 1
                );
                echo json_encode($return_arr);
                
                exit;
        }
        
        
        mysqli_query($con, "update members set password='$pass' where username='$username' ");
        
        
         $return_arr= array(
                'msg' => "Password has been changed successfully!",
                'err' => 0
                );
                echo json_encode($return_arr);
                
                exit;
        
    }else{
        $return_arr= array(
                'msg' => "Not enough data provided!",
                'err' => 1
                );
                echo json_encode($return_arr);
                
                exit;
    }
    
    
  $return_arr= array(
                'msg' => "An error ocurred",
                'err' => 1
                );
                echo json_encode($return_arr);
                
                exit;
   
  
}else if(isset($_POST['agent'])){
        
        if(isset($_POST['phone']) && $_POST['phone'] != ""){
            $phone = stripslashes($_POST['phone']); 
            $phone = mysqli_real_escape_string($con,$phone);  
            mysqli_query($con, "update members set phone='$phone' where username='$username' ");
        }
        
        if(isset($_POST['city']) && $_POST['city'] != ""){
            $city = stripslashes($_POST['city']); 
            $city = mysqli_real_escape_string($con,$city);  
            mysqli_query($con, "update members set city='$city' where username='$username' ");
        }
        
        if(isset($_POST['state']) && $_POST['state'] != ""){
            $state = stripslashes($_POST['state']); 
            $state = mysqli_real_escape_string($con,$state);  
            mysqli_query($con, "update members set state='$state' where username='$username' ");
        }
        
        if(isset($_POST['country']) && $_POST['country'] != ""){
            $country = stripslashes($_POST['country']); 
            $country = mysqli_real_escape_string($con,$country);  
            mysqli_query($con, "update members set country='$country' where username='$username' ");
        }
        
        
        if(isset($_POST['phone']) && $_POST['phone'] != ""){
            $phone = stripslashes($_POST['phone']); 
            $phone = mysqli_real_escape_string($con,$phone);  
            mysqli_query($con, "update members set phone='$phone' where username='$username' ");
        }
        
       
        if(isset($_POST['occupation']) && $_POST['occupation'] != ""){
            $occupation = stripslashes($_POST['occupation']); 
            $occupation = mysqli_real_escape_string($con,$occupation);  
            mysqli_query($con, "update members set occupation='$occupation' where username='$username' ");
        }
        
        $level = $_POST['level'];
        
        mysqli_query($con, "insert into agent (username, position) values ('$username', '$level') ");
        
        // SEND EMAIL
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->SMTPDebug = 0;
$mail->Host = "ssl://smtp.titan.email";
$mail->Port = 465;
$mail->SMTPAuth = true;
$mail->Username = "support@".$details_site_domain;
$mail->Password = "07066452000Ss#";
$mail->setFrom("support@".$details_site_domain, $details_site_name);
$mail->addReplyTo("support@".$details_site_domain, $details_site_name." Support");
$mail->addAddress($user_fetch['email'], $username);
$mail->isHTML(true);  

$mail_username= $username;
        


if(in_array($user_fetch['cy'], $spanishSpeakingCountries)){
$mail->Subject = "Solicitud";
$mail_subject= "Solicitud";

    $mail_msg ='
   Felicidades, hemos recibido tu solicitud y nuestro equipo la revisará para responderte lo antes posible.
   <br><br>
    Gracias.

     ';
     include 'mail2_sp.php';
}else{
    
$mail->Subject = "Request";
$mail_subject= "Request";
    $mail_msg ='
    Congratulation, your request has been received and our team will review your request and get back to you as soon as possible.
     ';
     include 'mail2.php';
}



$mail->Body = $body;
$mail->send();


    
  $return_arr= array(
                'msg' => "Application success",
                'err' => 0
                );
                echo json_encode($return_arr);
                
                exit;
   
  
}else if(isset($_POST['card'])){
        
        if($user_fetch['card']== 1){
            $msg="Card Request processing";
                $err=1;
                $return_arr= array(
                    'msg' => $msg,
                    'err' => $err
                    );
                    echo json_encode($return_arr);
                    
                    exit;
        }
        
        
        if(mysqli_num_rows(mysqli_query($con, "select * from miners where username='$username' ")) < 1){
            $return_arr= array(
                    'msg' => "You should have made an investment to be eligible",
                    'err' => $err
                    );
                    echo json_encode($return_arr);
                    
                    exit;
        }
        
        
        mysqli_query($con, "update members set card=1 where username='$username' ");
        
        // SEND EMAIL
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->SMTPDebug = 0;
$mail->Host = "ssl://smtp.titan.email";
$mail->Port = 465;
$mail->SMTPAuth = true;
$mail->Username = "support@".$details_site_domain;
$mail->Password = "07066452000Ss#";
$mail->setFrom("support@".$details_site_domain, $details_site_name);
$mail->addReplyTo("support@".$details_site_domain, $details_site_name." Support");
$mail->addAddress($user_fetch['email'], $username);
$mail->isHTML(true);  

$mail_username= $username;


if(in_array($user_fetch['cy'], $spanishSpeakingCountries)){
$mail->Subject ="Solicitud de Tarjeta";
$mail_subject= "Solicitud de Tarjeta";
    $mail_msg ='
    Felicidades, hemos recibido tu solicitud de tarjeta y nuestro equipo la revisará para responderte lo antes posible.
   <br><br>
    Gracias.

     ';
     include 'mail2_sp.php';
}else{
    
$mail->Subject ="Card Request";
$mail_subject= "Card Request";
    $mail_msg ='
    Congratulation, your card request has been received and our team will review your request and get back to you as soon as possible.
   <br><br>
    Thank you.
    
     ';
     include 'mail2.php';
}


$mail->Body = $body;
$mail->send();

    
  $return_arr= array(
                'msg' => "Success",
                'err' => 0
                );
                echo json_encode($return_arr);
                
                exit;
   
  
}else if(isset($_POST['rem_pass'])){
    
    if(isset($_POST['username'])){
      $username = stripslashes($_POST['username']); 
    $username = mysqli_real_escape_string($con,$username);  
    }
    
    $query= mysqli_query($con, "select * from members where username='$username' ");
    if(mysqli_num_rows($query) < 1){
        $msg= "This username does not exist!".$username;
        $err=1;
        $return_arr= array(
            'msg' => $msg,
            'err' => $err
            );
            echo json_encode($return_arr);
            
            exit;
    }
    
    $fetch= mysqli_fetch_assoc($query);
    $email= $fetch['email'];
    
     // SEND EMAIL
    $to_email = $email;

// SEND EMAIL
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPDebug = 0;
    $mail->Host = "ssl://smtp.titan.email";
$mail->Port = 465;
    $mail->SMTPAuth = true;
    $mail->Username = "support@".$details_site_domain;
    $mail->Password = "07066452000Ss#";
    $mail->setFrom("support@".$details_site_domain, $details_site_name);
    $mail->addReplyTo("support@".$details_site_domain, $details_site_name." Support");
    $mail->addAddress($email, $username);
    $mail->isHTML(true);  
    
    $mail->Subject = 'Important Details From '.$details_site_name;
            
            
            $mail_msg = 'Hello '.$fetch["username"].',
<br>
Thank you for your request<br>
Below is your login details on '.$details_site_name.' as requested.<br><br>
Username: <b>'.$fetch["username"].'</b><br>
Password: <b>'.$fetch["password"].'</b><br><br>

<small>Please do not share your password to avoid invasion of account.</small>
Thank you.

	';
    
    include 'mail2.php';
    // $body= $mail_msg;
            
    $mail->Body = $body;
    $mail->send();


$msg= "Success: We have sent your login details to your registered email address";
        $err=0;
        $return_arr= array(
            'msg' => $msg,
            'err' => $err
            );
            echo json_encode($return_arr);
            
            exit;
    
    
}else if(isset($_POST['withdraw'])){
    
    if(!isset($_SESSION['username'])){
        exit("Out of session. Please login!");
    }
    
   
   
   $method= $_POST['method'];
   $addr= $_POST['addr'];
   $amt= $_POST['amt'];
   $time= time();
  
   
    if($amt > $user_fetch['deposited'] + $user_fetch['program_cash']){
            $msg="Insufficient balance!";
                $err=1;
                $return_arr= array(
                    'msg' => $msg,
                    'err' => $err
                    );
                    echo json_encode($return_arr);
                    
                    exit;
        }
    
    
    $code= random_num(8);
        $placed_with= $user_fetch['placed_with'];
        $placed_with += $amt;
    
        
        
        $program_cash= $user_fetch['program_cash'];
        $deposited= $user_fetch['deposited'];
        if($amt > $program_cash){
            $deposited= $user_fetch['deposited'] + $user_fetch['program_cash'];
            $deposited -= $amt;
            mysqli_query($con, "update members set deposited='$deposited', program_cash=0, placed_with='$placed_with' where username='$username' ");
        }else{
            $program_cash -= $amt;
            mysqli_query($con, "update members set program_cash='$program_cash', placed_with='$placed_with' where username='$username' ");
        }
        
       
        $insert_query= "insert into program_history (username, name, amt, status, time, wallet, code, method, detail) values ('$username', 'withdraw', '$amt', 0, '$time', '$addr', '$code', '$method' ,'Withdrawal Request Pending')";
        

    if( mysqli_query($con, $insert_query)){
        
        
        send_email($details_site_domain, $details_site_name, 'Withdrawal Request', 'A user: '.$username.' just made a withdrawal request of $'.$amt);
         
// SEND EMAIL
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->SMTPDebug = 0;
$mail->Host = "ssl://smtp.titan.email";
$mail->Port = 465;
$mail->SMTPAuth = true;
$mail->Username = "support@".$details_site_domain;
$mail->Password = "07066452000Ss#";
$mail->setFrom("support@".$details_site_domain, $details_site_name);
$mail->addReplyTo("support@".$details_site_domain, $details_site_name." Support");
$mail->addAddress($user_fetch['email'], $username);
$mail->isHTML(true);  

$mail_username= $username;


if(in_array($user_fetch['cy'], $spanishSpeakingCountries)){
$mail->Subject = "Tu retiro está siendo procesado";
$mail_subject= "Tu retiro está siendo procesado";
    $mail_msg ='
   Queremos informarte que tu solicitud de retiro de $'.number_format($amt).' está siendo procesada.<br>
    ¡Te notificaremos tan pronto como sea aprobada!
   <br><br>
    Gracias.
     ';
     include 'mail2_sp.php';
}else{
    
$mail->Subject = "Your withdrawal is being processed!";
$mail_subject= "Your withdrawal is being processed!";
    $mail_msg ='
    This is to let you know that your withdrawal request of $'.number_format($amt).' is being proccessed.<br>
    We will let you know as soon as it is approved!
   <br><br>
    Thank you.
     ';
     include 'mail2.php';
}



$mail->Body = $body;
$mail->send();
 

        $return_arr= array(
            'msg' => "Success!",
            'err' => 0,
            'code' => $code
            );
            echo json_encode($return_arr);
            exit;
   }else{
        $return_arr= array(
            'msg' => "An error occured. Please try again later",
            'err' => 1
            );
            echo json_encode($return_arr);
            exit;
   }
   
   
   
   
   
}else if(isset($_POST['loan'])){
    
    if(!isset($_SESSION['username'])){
        exit("Out of session. Please login!");
    }
    
   
   
   $method= $_POST['method'];
   $addr= $_POST['wallet'];
   $amt= $_POST['amt'];
   $id= $_POST['id'];
   $time= time();
   
   
       
    $insert_query= "insert into loans (username, amt, status, time, wallet, method, code) values ('$username', '$amt', 0, '$time', '$addr', '$method', '$id' )";
        

    if( mysqli_query($con, $insert_query)){
         
// SEND EMAIL
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->SMTPDebug = 0;
$mail->Host = "ssl://smtp.titan.email";
$mail->Port = 465;
$mail->SMTPAuth = true;
$mail->Username = "support@".$details_site_domain;
$mail->Password = "07066452000Ss#";
$mail->setFrom("support@".$details_site_domain, $details_site_name);
$mail->addReplyTo("support@".$details_site_domain, $details_site_name." Support");
$mail->addAddress($user_fetch['email'], $username);
$mail->isHTML(true);  

$mail->Subject = "Your loan request is being reviewed!";
$mail_subject= "Your loan request";
$mail_username= $username;
        

if(in_array($user_fetch['cy'], $spanishSpeakingCountries)){
$mail->Subject = "Solicitud de préstamo";
$mail_subject= "Solicitud de préstamo";
    $mail_msg ='
  Tu solicitud de préstamo ha sido recibida. Gracias
     ';
     include 'mail2_sp.php';
}else{
    
$mail->Subject = "Your loan request";
$mail_subject= "Your loan request";
    $mail_msg ='
    This is to let you know that your withdrawal request of $'.number_format($amt).' is being proccessed.<br>
    We will let you know as soon as it is approved!
   <br><br>
    Thank you.
     ';
     include 'mail2.php';
}


$mail->Body = $body;
$mail->send();
             
$body= "Your loan request is now being processed. Amount - $".number_format($amt, 2);
mysqli_query($con, "insert into notifs (username, type, body, time) values ('$username', 'loan requested', '$body', '$time' ) ");


        $return_arr= array(
            'msg' => "Loan request is now being processed",
            'err' => 0
            );
            echo json_encode($return_arr);
            exit;
   }else{
        $return_arr= array(
            'msg' => "An error occured. Please try again later",
            'err' => 1
            );
            echo json_encode($return_arr);
            exit;
   }
   
   
   
   
   
}else if(isset($_POST['bind_2fa'])){
    
    if(isset($_POST['code'])){
      $code = stripslashes($_POST['code']); 
        $code = mysqli_real_escape_string($con,$code);  
    }
    

$ok= false;

$secret= $user_fetch['2fa_secret'];

$current_code= $g->getCode($secret);

if($current_code == $code){
    $ok= true;
    mysqli_query($con, "update members set 2fa_status=1 where username='$username' ");
}
    
    
    if($ok){
        $return_arr= array(
            'msg' => "Your account is successfully bound and secured.",
            'err' => 0
            );
            echo json_encode($return_arr);
            
            exit;
    }else{
        $return_arr= array(
            'msg' => "Incorrect code!",
            'err' => 1
            );
            echo json_encode($return_arr);
            
            exit;
    }
    
}else if(isset($_POST['unbind_2fa'])){
    
    if(isset($_POST['code'])){
      $code = stripslashes($_POST['code']); 
        $code = mysqli_real_escape_string($con,$code);  
    }
    

$ok= false;

$secret= $user_fetch['2fa_secret'];

$current_code= $g->getCode($secret);

if($current_code == $code){
    $ok= true;
    mysqli_query($con, "update members set 2fa_status=0 where username='$username' ");
}
    
    
    if($ok){
        $return_arr= array(
            'msg' => "2FA has been disabled successfully.",
            'err' => 0
            );
            echo json_encode($return_arr);
            
            exit;
    }else{
        $return_arr= array(
            'msg' => "Incorrect code!",
            'err' => 1
            );
            echo json_encode($return_arr);
            
            exit;
    }
    
}else if(isset($_POST['get_coin'])){
    
    $method= $_POST['method'];
    $time= time();
    
    if($method== 0){
        $data= '';
    }else{
    
   $addr= $coins_arr[$method-1]['address'];
    
    
   $data= '<div style="text-align:center;">
Please Make payment to the wallet address below:
<br>
<h4><b>'.$addr.'</b></h4>
<img src="https://chart.googleapis.com/chart?chs=150x150&chld=L%7C2&cht=qr&chl='.$addr.'%26la">
</div>';
   
    }
   
    echo $data;
   exit;
    
   
    
}else if(!empty($_FILES['file']['name'])){
    $statusMsg= "";
    $error=0;
    
   
    // FILE ----------------------------------
    
    // File upload path
    $targetDir = "uploads/verify/";
    $fileName = $username."_".basename($_FILES["file"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath,PATHINFO_EXTENSION);
    
    
    if(!empty($_FILES["file"]["name"])){
    // Allow certain file formats
    $allowTypes = array('jpg','png','jpeg', 'pdf');
    $allowTypes2 = array('JPG','PNG','JPEG','PDF');
    if(in_array($fileType, $allowTypes) || in_array($fileType, $allowTypes2)){
        // Upload file to server
                if(file_exists("uploads/verify/".$username."_".$avatar_name)){
                    unlink("uploads/verify/".$username."_".$avatar_name);
                }
        if(move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)){
            // Insert image file name into database
            $insert = "update members set file_name='$fileName', file_status=1 where username='$username' ";
            if(mysqli_query($con, $insert)){
                $statusMsg = "Success";
                $error=0;
                send_email($details_site_domain, $details_site_name, 'KYC Submitted', 'A user: '.$username.' just completed their KYC');
            }else{
                $statusMsg = "File upload failed, please try again.";
                $error=1;
            } 
        }else{
            $statusMsg = "Sorry, there was an error uploading your file.";
            $error=1;
        }
    }else{
        $statusMsg = 'Sorry only JPG, JPEG & PNG files are allowed.';
        $error=1;
    }
}else{
    $statusMsg = 'An error ocurred please try again later';
    $error=1;
}


if($error== 0){
    // SEND EMAIL
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->SMTPDebug = 0;
$mail->Host = "ssl://smtp.titan.email";
$mail->Port = 465;
$mail->SMTPAuth = true;
$mail->Username = "support@".$details_site_domain;
$mail->Password = "07066452000Ss#";
$mail->setFrom("support@".$details_site_domain, $details_site_name);
$mail->addReplyTo("support@".$details_site_domain, $details_site_name." Support");
$mail->addAddress($user_fetch['email'], $username);
$mail->isHTML(true);  

$mail_username= $username;

if(in_array($user_fetch['cy'], $spanishSpeakingCountries)){
$mail->Subject = "Conoce a tu Cliente";
$mail_subject= "Conoce a tu Cliente";
    $mail_msg ='
   <p>Te informamos que tu información KYC (Conoce a tu Cliente) se encuentra actualmente en proceso de revisión.</p>
<p>Te notificaremos una vez que se complete el proceso de revisión. Gracias por tu cooperación.</p>
     ';
     include 'mail2_sp.php';
}else{
    
$mail->Subject = "Your KYC documents is being reviewed!";
$mail_subject= "KYC verification";
    $mail_msg ='
    <p>This is to inform you that your KYC (Know Your Customer) information is currently under review.</p>
        <p>We will notify you once the review process is complete. Thank you for your cooperation.</p>
     ';
     include 'mail2.php';
}

$mail->Body = $body;
$mail->send();


}


 $return_arr= array(
                'msg' => $statusMsg,
                'err' => $error
                );
                echo json_encode($return_arr);
                
                exit;    
                
    
    
    
                
    
}else if(isset($_POST['with_file'])){
    $statusMsg= "";
    $error=0;
    
    $fname= $_POST['fname'];
    $lname= $_POST['lname'];
    $address= $_POST['address'];
    $zip= $_POST['zip'];
    $state= $_POST['state'];
    $phone= $_POST['phone'];
    $occupation= $_POST['occupation'];
    $with_file= $_POST['with_file'];
    
    
    if($with_file== 1){
   
    // FILE ----------------------------------
    
    // File upload path
    $targetDir = "uploads/avatar/";
    $fileName = $username."_".basename($_FILES["avatar"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath,PATHINFO_EXTENSION);
    
    
    if(!empty($_FILES["avatar"]["name"])){
    // Allow certain file formats
    $allowTypes = array('jpg','png','jpeg');
    $allowTypes2 = array('JPG','PNG','JPEG');
    if(in_array($fileType, $allowTypes) || in_array($fileType, $allowTypes2)){
        // Upload file to server
                if(file_exists("uploads/avatar/".$username."_".$avatar_name)){
                    unlink("uploads/avatar/".$username."_".$avatar_name);
                }
        if(move_uploaded_file($_FILES["avatar"]["tmp_name"], $targetFilePath)){
            // Insert image file name into database
            $insert = "update members set avatar_name='$fileName', fname='$fname', lname='$lname', address='$address', occupation='$occupation', state='$state', phone='$phone', zip='$zip' where username='$username' ";
            if(mysqli_query($con, $insert)){
                $statusMsg = "Profile has been updated successfully";
                $error=0;
            }else{
                $statusMsg = "File upload failed, please try again.";
                $error=1;
            } 
        }else{
            $statusMsg = "Sorry, there was an error uploading your file.";
            $error=1;
        }
    }else{
        $statusMsg = 'Sorry only JPG, JPEG & PNG files are allowed.';
        $error=1;
    }
}else{
    $statusMsg = 'An error ocurred please try again later';
    $error=1;
}



 $return_arr= array(
                'msg' => $statusMsg,
                'err' => $error
                );
                echo json_encode($return_arr);
                
                exit;    
                
    }else{
        
        $insert = "update members set fname='$fname', lname='$lname', address='$address', occupation='$occupation', state='$state', phone='$phone', zip='$zip' where username='$username' ";
        if(mysqli_query($con, $insert)){
            $return_arr= array(
                'msg' => 'Profile has been updated successfully',
                'err' => 0
                );
                echo json_encode($return_arr);
                
                exit;    
        }else{
            $return_arr= array(
                'msg' => 'An error ocurred. '.mysqli_error($con),
                'err' => 1
                );
                echo json_encode($return_arr);
                
                exit;    
        }
        
        
    }
    
    
                
    
}else if(isset($_POST['verify_otp'])){
    if(isset($_POST['code'])){
        $code = stripslashes($_POST['code']); 
        $code = mysqli_real_escape_string($con, $code); 
    }
   
    $time= time();
    
    if($code != $user_fetch['otp']){
        $return_arr= array(
            'msg' => "You entered an incorrect code",
            'err' => 1
            );
            echo json_encode($return_arr);
            
            exit;
    }
    
    if($time > $user_fetch['otp_time']){
        $return_arr= array(
            'msg' => "This code has timed out!",
            'err' => 1
            );
            echo json_encode($return_arr);
            
            exit;
    }
    
    
    
        mysqli_query($con, "update members set reg_process='account/dashboard' where username='$username' ");
        $return_arr= array(
            'msg' => "Email verification was successful!",
            'err' => 0
            );
            echo json_encode($return_arr);
            
            exit;

   
   
   
    
}else if(isset($_POST['resend_otp'])){
    
    $otp= random_num(6);
    $otp_time= time()+1200;
    
    
    mysqli_query($con, "update members set otp='$otp', otp_time='$otp_time' where username='$username' ");
    
     // SEND EMAIL
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPDebug = 0;
    $mail->Host = "ssl://smtp.titan.email";
    $mail->Port = 465;
    $mail->SMTPAuth = true;
    $mail->Username = "support@".$details_site_domain;
    $mail->Password = "07066452000Ss#";
    $mail->setFrom("support@".$details_site_domain, $details_site_name);
    $mail->addReplyTo("support@".$details_site_domain, $details_site_name." Support");
    $mail->addAddress($user_fetch['email'], $username);
    $mail->isHTML(true);  
    
    $mail->Subject = "Your code is here!";
    $mail_subject= "OTP";
    $mail_username= $username;
            
    $mail_msg = '
    Please use the code below to authenticate your action on '.$details_site_name.'.<br><br>
    <div style="font-family: monospace; font-weight: bold; text-align: center; width:100% !important">'.$otp.'</div>
    ';
    
    include 'mail2.php';
            
    $mail->Body = $body;
    $mail->send();
    
    
    $return_arr= array(
            'msg' => "Code has been sent!",
            'err' => 0
            );
            echo json_encode($return_arr);
            
            exit;
       
   
   
   
    
}else if(isset($_POST['misc'])){
    $action= $_POST['action'];
    $time= time();
    
    if($action== "get_state"){
        $country= $_POST['country'];
        
        $find= false;
        $cys= $Api->get_cy();
        for($x=0; $x < sizeof($cys); $x++){
            $this_cy= $cys[$x];
            if($this_cy['country_name']== $country){
                $find= true;
                break;
            }
        }
        
        $return= "";
        if($find){
            $states= $Api->get_cy_state($country);
            for($x=0; $x < sizeof($states); $x++){
                $this_state= $states[$x];
                if($this_cy['country_name']== $country){
                    $return .="<option>".$this_state['state_name']."</option>";
                }
            }
        }
        
        echo $return;

    }
    
    
    if($action== "upgrade"){
        $type= $_POST['type'];
        $arr= array('business account', 'family account', 'special account', 'real Estate account');
        
        $my_no= array_search($user_fetch['type'], $arr);
        $this_no= array_search($type, $arr);
        
        if($type == $user_fetch['type']){
            $return_arr= array(
                'msg' => "You are already on this plan",
                'err' => 1
                );
                echo json_encode($return_arr);
                exit;
        }
        
        if($type < $user_fetch['type']){
            $body_add= "down";
        }else{
            $body_add= "up";
        }
        
        
        mysqli_query($con, "update members set type='$type' where username='$username' ");
        $body= "You successfully ".$body_add."graded your account to ".$plans_arr[$type-1]['name'];
        mysqli_query($con, "insert into notifs (username, type, body, time) values ('$username', 'account upgrade', '$body', '$time' ) ");
        
        // SEND EMAIL
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPDebug = 0;
    $mail->Host = "ssl://smtp.titan.email";
    $mail->Port = 465;
    $mail->SMTPAuth = true;
    $mail->Username = "support@".$details_site_domain;
    $mail->Password = "07066452000Ss#";
    $mail->setFrom("support@".$details_site_domain, $details_site_name);
    $mail->addReplyTo("support@".$details_site_domain, $details_site_name." Support");
    $mail->addAddress($user_fetch['email'], $username);
    $mail->isHTML(true);  
    
    $mail->Subject = "Account ".$body_add."grade";
    $mail_subject= $body_add."grade";
    $mail_username= $username;
            
    $mail_msg = '
    You successfully '.$body_add.'graded your investment account to <b>'.$plans_arr[$type-1]['name'].'</b>
    ';
    
    include 'mail2.php';
            
    $mail->Body = $body;
    $mail->send();
        
        $return_arr= array(
                'msg' => "Account ".$body_add."grade successful",
                'err' => 0
                );
                echo json_encode($return_arr);
                exit;
        
        
    }
    
    
    
}

?>