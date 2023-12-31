<!DOCTYPE html>
<html lang="en">
<head>
<?php
    session_start();
    $page= "Withdrawal";
    $section = "withdraw";
    include 'check-login.php';
    include '../details.php';
    include 'header.php';
?>

 
    <div class="row">
   <div class="col-xl-12">
      <div class="site-card">
         
    
         <div class="site-card-header">
            <h3 class="title">Request Withdrawal</h3>
            <div class="card-header-links">
               <a href="withdraw-history"
                  class="card-header-link">Withdrawal History</a>
            </div>
         </div>
         <div class="site-card-body">
            <div class="progress-steps">
               <div class="single-step current">
                  <div class="number">01</div>
                  <div class="content">
                     <h4>Withdrawal Amount</h4>
                     <p>Enter withdrawal details</p>
                  </div>
               </div>
               <div class="single-step ">
                  <div class="number">02</div>
                  <div class="content">
                     <h4>Success</h4>
                     <p>Withdrawal Success</p>
                  </div>
               </div>
            </div>
            <div class="progress-steps-form">
               <div class="row">
                  <div class="col-xl-6 col-md-12 mb-3">
                     <label for="exampleFormControlInput1" class="form-label">Payout Method:</label>
                     <div class="input-group">
                        <select name="gateway_code" id="method" class="site-nice-select">
                           <option value="" selected>--Select Gateway--</option>
                           <?php
                                for($c=0; $c < count($coins_arr); $c++){
                                    if(strtolower($coins_arr[$c]['method'])== strtolower($coins_arr[$c]['name'])){
                                        $name= $coins_arr[$c]['name'];
                                    }else{
                                        $name= $coins_arr[$c]['name']." (".$coins_arr[$c]['method'].")";
                                    }
                                                        
                                    echo '<option value="'.$name.'">'.$name.' Wallet</option>';
                                }
                            ?>  
                          
                        </select>
                     </div>
                     <div style="font-weight: bold" class="primary-color">Total Balance: $<?= number_format($user_fetch['deposited'] + $user_fetch['program_cash'], 2) ?></div>
                  </div>
                  <div class="col-xl-6 col-md-12">
                     <label for="exampleFormControlInput1" class="form-label">Enter Amount:</label>
                     <div class="input-group">
                        <input type="text" name="amount" class="form-control"
                            aria-label="Amount" id="amt"
                           aria-describedby="basic-addon1">
                        <span class="input-group-text" id="basic-addon1">USD</span>
                     </div>
                     <div class="input-info-text min-max"></div>
                  </div>
                  
                  <div class="col-xl-6 col-md-12">
                     <label for="exampleFormControlInput1" class="form-label">Destination Wallet:</label>
                     <div class="input-group">
                        <input type="text" class="form-control"
                            aria-label="Wallet" id="addr"
                           aria-describedby="basic-addon1">
                     </div>
                  </div>
                  
               </div>
               <div class="row manual-row">
               </div>
               <div class="transaction-list table-responsive">
                  <div class="user-panel-title">
                     <h3>Review Details:</h3>
                  </div>
                  <table class="table">
                     <tbody>
                        <tr>
                           <td><strong>Amount</strong></td>
                           <td><span class="amount"></span></td>
                        </tr>
                        <tr>
                           <td><strong>Charge</strong></td>
                           <td class="charge"></td>
                        </tr>
                        <tr>
                           <td><strong>Payment Method</strong></td>
                           <td class="method"></td>
                        </tr>
                        <tr>
                           <td><strong>Payout Wallet</strong></td>
                           <td class="addr"></td>
                        </tr>
                     </tbody>
                  </table>
               </div>
               <div class="buttons">
                  <button id="sbmt" type="submit" class="site-btn blue-btn">
                  Proceed for Payout<i class="anticon anticon-double-right"></i>
                  </button>
               </div>
            </div>
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
        
        
        $("#amt").keyup(function(){
            var amt = $(this).val()
            if(amt !== 0 && amt !== ""){
                $('.amount').text(amt+' USD');
                $('.currency').text('USD')
                $('.charge').text('0 USD')
            }else{
                $('.amount').text('');
                $('.currency').text('')
                $('.charge').text('')
            }
        })
        
        $("#addr").keyup(function(){
            var addr = $(this).val()
            $('.addr').text(addr)
        })
        
        
        
        $('#method').change(function(){
            $('.method').text($('#method option:selected').text())
        })
        

        $("#sbmt").click(function(){
           event.preventDefault();
           var amt= $("#amt").val();
           var method= $("#method").val();
           var addr= $("#addr").val();
           if(amt== "" || amt== 0 || method== "" || addr== ""){
               notify('warning', 'All fields are required')
           }else{
              loadOn();
                  $.post('ajax', {
                      withdraw: "set",
                      amt: amt,
                      addr: addr,
                      method: method
                  }, function(data, status){
                      if(data.err== 1){
                          loadOff();
                          notify('error', data.msg)
                      }else{
                          notify('success', data.msg)
                          setTimeout(function(){
                              window.location.href="https://"+site_domain+"/account/withdraw2?id="+data.code
                          }, 2000);
                      }
                  }, "JSON")
           }
              
       })
       
       
       
       
        $(window).on('beforeunload', function(){
            loadOn();
        });
        
    })
</script>

</body>
</html>

