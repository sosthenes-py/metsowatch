<!DOCTYPE html>
<html lang="en">
<head>
<?php
    session_start();
    $page= "Ranking";
    $section = "rank";
    include 'check-login.php';
    include '../details.php';
    include 'header.php';
?>

 
    <div class="row">
   <div class="col-xl-12">
       
       
      <div class="site-card">
   <div class="site-card-header">
      <h3 class="title">All The Badges</h3>
   </div>
   <div class="site-card-body">
      <div class="row justify-content-center">
         <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="single-badge ">
               <div class="badge">
                  <div class="img"><img src="../assets/global/images/rank1.svg" alt=""></div>
               </div>
               <div class="content">
                  <h3 class="title">Access Member</h3>
                  <p class="description">By signing up to the account and earn up to $500</p>
               </div>
            </div>
         </div>
         <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="single-badge <?php if($user_fetch['program_cash_static'] < 500000){ echo "locked";} ?>">
               <div class="badge">
                  <div class="img"><img src="../assets/global/images/rank2.svg" alt=""></div>
               </div>
               <div class="content">
                  <h3 class="title"><?=$details_site_name ?> 2</h3>
                  <p class="description">By earning $500000 from the site</p>
               </div>
            </div>
         </div>
         <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="single-badge <?php if($user_fetch['program_cash_static'] < 1200000){ echo "locked";} ?>">
               <div class="badge">
                  <div class="img"><img src="../assets/global/images/rank3.svg" alt=""></div>
               </div>
               <div class="content">
                  <h3 class="title">Access 3</h3>
                  <p class="description">By earning $1200000 from the site</p>
               </div>
            </div>
         </div>
         <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="single-badge <?php if($user_fetch['program_cash_static'] < 2000000){ echo "locked";} ?>">
               <div class="badge">
                  <div class="img"><img src="../assets/global/images/rank4.svg" alt=""></div>
               </div>
               <div class="content">
                  <h3 class="title"><?=$details_site_name ?> 4</h3>
                  <p class="description">By earning $2000000 from the site</p>
               </div>
            </div>
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
        
       
       
        $(window).on('beforeunload', function(){
            loadOn();
        });
        
    })
</script>

</body>
</html>

