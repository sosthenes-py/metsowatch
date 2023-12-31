<!DOCTYPE html>
<html lang="en">
<head>
<?php
    session_start();
    $page= "Investment";
    $section = "invest";
    include 'check-login.php';
    include '../details.php';
    include 'header.php';
    
    if(!isset($_GET['plan']) ){
        header('location: invest');
        exit;
    }else{
        $plan= $_GET['plan'];
    }
    
    $x = $plan-1;
    if($plans_arr[$x]['max'] >= 100000000){
        $max = "UNLIMITED";
    }else{
        $max = "$".number_format($plans_arr[$x]['max']);
    }
    $id = $x+1;
    
?>

 
    <div class="row">
   <div class="col-xl-12">
       
       
      <div class="site-card">
   <div class="site-card-header">
      <h3 class="title">Review and Confirm Investment</h3>
   </div>
   <div class="site-card-body">
      <form action="" method="post" enctype="multipart/form-data">
         <div class="progress-steps-form">
            <div class="transaction-list table-responsive">
               <table class="table preview-table">
                  <tbody>
                     <tr>
                        <td><strong>Selected Schema:</strong></td>
                        <td>
                           <div class="input-group mb-0">
                              <select class="site-nice-select" aria-label="Default select example" id="select-schema" name="schema_id" required="" style="display: none;">
                                 <option value="<?=$plan ?>" selected=""><?=$plans_arr[$x]['name'] ?> Plan</option>
                              </select>
                           </div>
                        </td>
                     </tr>
                     <tr>
                        <td><strong>Amount:</strong></td>
                        <td id="amount">
                           Minimum <?=$plans_arr[$x]['min'] ?> USD - Maximum <?=$max ?>
                        </td>
                     </tr>
                     <tr>
                        <td><strong>Enter Amount:</strong></td>
                        <td>
                           <div class="input-group mb-0">
                              <input type="text" class="form-control" placeholder="Enter Amount" aria-label="Amount" id="amt" aria-describedby="basic-addon1" required="">
                              <span class="input-group-text" id="basic-addon1">USD</span>
                           </div>
                        </td>
                     </tr>
                     <tr>
                        <td><strong>Select Wallet:</strong></td>
                        <td>
                           <div class="input-group mb-0">
                              <select class="site-nice-select" aria-label="Default select example" name="wallet" required="" id="method" style="display: none;">
                                  <option value="bal">Total Balance (<?= number_format($user_fetch['deposited'] + $user_fetch['program_cash']) ?> USD)</option>
                                  <?php
                                        for($c=0; $c < count($coins_arr); $c++){
                                            if(strtolower($coins_arr[$c]['method'])== strtolower($coins_arr[$c]['name'])){
                                                $name= $coins_arr[$c]['name'];
                                            }else{
                                                $name= $coins_arr[$c]['name']." (".$coins_arr[$c]['method'].")";
                                            }
                                                        
                                            echo '<option value="'.$c.'">Deposit From '.$name.' Wallet</option>';
                                        }
                                    ?>  
                                 
                              </select>
                              
                           </div>
                        </td>
                     </tr>
                     <tr class="gatewaySelect">
                     </tr>
                     <tr>
                        <td colspan="2">
                           <div class="row manual-row"></div>
                        </td>
                     </tr>
                     <tr>
                        <td><strong>Return of Interest:</strong></td>
                        <td id="return-interest"><?=$plans_arr[$x]['profit'] ?>% (Daily)</td>
                     </tr>
                     <tr>
                        <td><strong>Number of Period:</strong></td>
                        <td id="number-period"><?=$plans_arr[$x]['duration'] ?> Times </td>
                     </tr>
                     <tr>
                        <td><strong>Capital Back:</strong></td>
                        <td id="capital_back">Yes</td>
                     </tr>
                     
                  </tbody>
               </table>
            </div>
            <div class="button">
               <button type="submit" class="site-btn primary-btn me-3" id="sbmt">
               <i class="anticon anticon-check"></i>Invest Now
               </button>
               <a href="invest" class="site-btn black-btn">
               <i class="anticon anticon-stop"></i>Cancel & Restart
               </a>
            </div>
         </div>
      </form>
   </div>
</div>
      
      
   </div>
</div>
                    <!--Page Content-->
                </div>
            </div>
        </div>
    </div>


    <!-- Automatic Popup -->
    
    <!-- /Automatic Popup End -->
</div>
<!--/Full Layout-->

<?php
    include 'footer.php'
?>

<script>
    $(function(){

        var overlay= $("#overlay");
        var loader= $("#loader");
        function loadOn(){
            overlay.css('display', 'block');
            loader.css('display', 'block');
        }
        function loadOff(){
            overlay.css('display', 'none');
            loader.css('display', 'none');
        }
        
        function notify(status, msg){
            if(status === "success"){
                iziToast.success({
                    title: 'Success',
                    message: msg,
                    position: 'topRight',
                  });
            }else if(status === "error"){
                iziToast.error({
                    title: 'Error',
                    message: msg,
                    position: 'topRight',
                  });
            }else if(status === "info"){
                iziToast.info({
                    title: 'Info',
                    message: msg,
                    position: 'topRight',
                  });
            }else if(status === "warning"){
                iziToast.warning({
                    title: 'Warning',
                    message: msg,
                    position: 'topRight',
                  });
            }
        }
        
        var site_domain= "<?php echo $details_site_domain; ?>"
        
        
        $("#sbmt").click(function(){
            event.preventDefault();
            var plan= "<?=$id ?>";
           var method= $("#method").val();
           var max= "<?=$plans_arr[$x]['max'] ?>";
           var min= "<?=$plans_arr[$x]['min'] ?>"
           var amt= $("#amt").val();

           if(amt== "" || amt == 0){
               notify('warning', 'Please enter amount to proceed')
           }else{
               if(method== "bal"){
                   loadOn();
                   $.post('ajax', {
                       buy_miner: "set",
                       amt: amt,
                       plan: plan,
                       action: "balance"
                   }, function(data, status){
                       if(data.err== 1){
                           loadOff();
                           notify('error', data.msg)
                       }else{
                           notify('success', data.msg)
                           setTimeout(function(){
                               window.location.href="https://"+site_domain+"/account/dashboard"
                           }, 2000);
                       }
                   }, "JSON")
               }else{
                   if(amt < parseInt(min)){
                       notify('warning', 'Minimum amount for this plan is $'+min)
                   }else if(amt > parseInt(max)){
                       notify('warning', 'Maximum amount for this plan is $'+max)
                   }else{
                       loadOn()
                       notify('success', 'Please wait for payment details');
                       setTimeout(function(){
                           window.location.href="https://"+site_domain+"/account/invest3?amount="+amt+"&method="+method+"&plan="+plan
                       }, 2000);
                   }
               }
           }

          
       })
       
       
       
       
        $(window).on('beforeunload', function(){
            loadOn();
        });
        
    })
</script>

</body>
</html>

