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
?>

 
    <div class="row">
   <div class="col-xl-12">
       
       
      <div class="site-card">
   <div class="site-card-header">
      <h3 class="title">All The Schemas</h3>
   </div>
   <div class="site-card-body">
      <div class="row">
          
          <?php
                for($x=0; $x < sizeof($plans_arr); $x++){
                    if($plans_arr[$x]['max'] >= 100000000){
                        $max = "UNLIMITED";
                    }else{
                        $max = "$".number_format($plans_arr[$x]['max']);
                    }
                    $id = $x+1;
            ?>  
          
         <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
            <div class="single-investment-plan">
               <img class="investment-plan-icon" src="../assets/global/images/plan<?=$id ?>.jpg" alt="">
               <div class="feature-plan"><?=$plans_arr[$x]['name'] ?></div>
               <h3>Plan <?=$id ?></h3>
               <p>Daily <?=$plans_arr[$x]['profit'] ?>%</p>
               <ul>
                  <li>Investment <span class="special">
                     $<?=number_format($plans_arr[$x]['min']) ?> - <?=$max ?>
                     </span>
                  </li>
                  <li>Capital Back
                     <span>Yes</span>
                  </li>
                  <li>Return Type <span>Period</span>
                  </li>
                  <li>Number of Period
                     <span><?=$plans_arr[$x]['duration'] ?> Days</span>
                  </li>
                  <li>Profit Withdraw <span>Anytime</span></li>
               </ul>
               <a href="#0" class="site-btn grad-btn w-100 centered invest" data-plan="<?=$id ?>"><i class="anticon anticon-check"></i>Invest Now</a>
            </div>
         </div>
         
         <?php
                }
            ?>
         
        
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
        
        
        $(".invest").click(function(){
            var plan = $(this).data('plan');
            window.location.href="https://"+site_domain+"/account/invest2?plan="+plan
        })
       
       
       
        $(window).on('beforeunload', function(){
            loadOn();
        });
        
    })
</script>

</body>
</html>

