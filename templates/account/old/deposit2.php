<!DOCTYPE html>
<html lang="en">
<head>
<?php
    session_start();
    $page= "Confirm Deposit";
    $section = "deposit";
    include 'check-login.php';
    include '../details.php';
    include 'header.php';
    
    if(!isset($_GET['amount']) || !isset($_GET['method'])){
        header('location: deposit');
        exit;
    }else{
        $amt= $_GET['amount'];
        $method= $_GET['method'];
    }
    
    $c= $method;
    if(strtolower($coins_arr[$c]['method'])== strtolower($coins_arr[$c]['name'])){
        $method_name= $coins_arr[$c]['name'];
    }else{
        $method_name= $coins_arr[$c]['name']." (".$coins_arr[$c]['method'].")";
    }
    $method_wallet= $coins_arr[$c]['address'];
    $id= random_num(8);
?>

 
    <div class="row">
   <div class="col-xl-12">
      <div class="site-card">
         <div class="site-card-header">
            
            <h3 class="title">Add Money</h3>
            <div class="card-header-links">
               <a href="deposit-history"
                  class="card-header-link">Deposit History</a>
            </div>
         </div>
         <div class="site-card-body">
           <div class="progress-steps">
              <div class="single-step current">
                 <div class="number">01</div>
                 <div class="content">
                    <h4>Deposit Amount</h4>
                    <p>Enter your deposit details</p>
                 </div>
              </div>
              <div class="single-step current">
                 <div class="number">02</div>
                 <div class="content">
                    <h4>Success</h4>
                    <p>Success Your Deposit Process</p>
                 </div>
              </div>
           </div>
           <div class="progress-steps-form">
              <div class="transaction-status centered">
                 <div class="icon success">
                    <i class="anticon anticon-check"></i>
                 </div>
                 <h2>Deposit of  $<?= number_format($amt); ?> via  <?= strtoupper($method_name); ?>  <span class="site-badge warnning">Pending</span></h2>
                 <div class="referral-link">
                     <div class="referral-link-form">
                        <input type="text" value="<?=$method_wallet ?>" id="wallet" readonly/>
                        <button type="submit" id="copy_wallet">
                        <i class="anticon anticon-copy"></i>
                        <span id="copy">Copy Wallet</span>
                        </button>
                    </div>
                </div>
                <br><br>        
                 <div id="qrcode" class="" style="width: 100%; text-align: center">
                    <img src="https://chart.googleapis.com/chart?chs=200x200&chld=L%7C2&cht=qr&chl=<?= $method_wallet; ?>" alt="qr-code">
                </div>
                <br>
                 <p>Once this payment is completed, click on the button below</p>
                 <p>Transaction ID: <?=$id ?></p>
                 <a href="#0" class="site-btn" id="sbmt">
                    <i class="anticon anticon-plus"></i>Deposit completed
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
        
        
        $("body").on('click', '#copy_wallet', function(){
            event.preventDefault();
            var item= $("#wallet").val();
            
            navigator.clipboard.writeText(item).then(function () {
                notify('info', 'wallet copied!')
            }, function () {
                notify('info', 'An error occured. Please try to copy manually')
            });
        })
        

        $("#sbmt").click(function(){
           event.preventDefault();
           var amt= "<?= $amt; ?>"
           var method= "<?= $method; ?>"
          
              loadOn();
            $.ajax({
                url: "ajax",
                dataType: "json",
                type: "POST",
                data: {'topup': 'set', 'amt': amt, 'coin': method},
                success: function(data){
                    if(data.err== 1){
                        loadOff();
                        notify('error', data.msg)
                    }else{
                        notify('success', data.msg)
                        setTimeout(function(){
                            window.location.href="https://"+site_domain+"/account/dashboard"
                        }, 3000);
                    }
                },
                error: function(err1, err2){
                    notify('error', JSON.stringify(err1))
                }
            })
               

       })
       
       
       
       
        $(window).on('beforeunload', function(){
            loadOn();
        });
        
    })
</script>

</body>
</html>

