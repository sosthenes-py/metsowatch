<!DOCTYPE html>
<html lang="en">
<head>
<?php
    session_start();
    $page= "Withdraw Success";
    $section = "withdraw";
    include 'check-login.php';
    include '../details.php';
    include 'header.php';
    
    if(!isset($_GET['id']) ){
        header('location: withdraw');
        exit;
    }else{
        $code= $_GET['id'];
    }
    
    $query = mysqli_query($con, "select * from program_history where code='$code' ");
    if(mysqli_num_rows($query) < 1){
        exit('An error occured');
    }
    $fetch = mysqli_fetch_assoc($query);
    
 
?>

 
    <div class="row">
   <div class="col-xl-12">
      <div class="site-card">
         <div class="site-card-header">
            
            <h3 class="title">Withdrawal Success</h3>
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
               <div class="single-step current">
                  <div class="number">02</div>
                  <div class="content">
                     <h4>Success</h4>
                     <p>Withdrawal Success</p>
                  </div>
               </div>
            </div>
           <div class="progress-steps-form">
              <div class="transaction-status centered">
                 <div class="icon success">
                    <i class="anticon anticon-check"></i>
                 </div>
                 <h2>Withdrawal of  $<?= number_format($fetch['amt']); ?> via  <?= strtoupper($fetch['method']); ?> <span class="site-badge warnning">Submitted</span></h2>
                 <div class="referral-link">
                     <div class="referral-link-form">
                        <input type="text" value="<?=$fetch['wallet'] ?>" readonly/>
                        <button type="submit" disabled>
                        <span id="copy">Wallet</span>
                        </button>
                    </div>
                </div>
                <br>
                <br>
                 <p>Once this request is completed, you will receive a confirmation email from us.</p>
                 <p>Transaction ID: <?=$code ?></p>
                 <a href="withdraw" class="site-btn">
                    <i class="anticon anticon-plus"></i>Request Again
                 </a>
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
            toastr[status](msg);
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
       
       
       
       
        $(window).on('beforeunload', function(){
            loadOn();
        });
        
    })
</script>

</body>
</html>

