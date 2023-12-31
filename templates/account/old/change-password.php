<!DOCTYPE html>
<html lang="en">
<head>
<?php
    session_start();
    $page= "Change Password";
    $section = "change-password";
    include 'check-login.php';
    include '../details.php';
    include 'header.php';
?>

 
    <div class="row">
   <div class="col-xl-12">
       
       
      <div class="site-card">
   <div class="site-card-header">
      <h3 class="title">Change Password</h3>
      <div class="card-header-links">
         <a href="https://bulkfex.net/user/settings" class="card-header-link">Back</a>
      </div>
   </div>
   <div class="site-card-body">
      <div class="progress-steps-form">
            <div class="row">
               <div class="col-xl-12 col-md-12">
                  <label for="exampleFormControlInput1" class="form-label">Current Password</label>
                  <div class="input-group">
                     <input type="password" id="old_pass" class="form-control">
                  </div>
               </div>
               <div class="col-xl-6 col-md-12">
                  <label for="exampleFormControlInput1" class="form-label">New Password</label>
                  <div class="input-group">
                     <input type="password" id="pass" class="form-control">
                  </div>
               </div>
               <div class="col-xl-6 col-md-12">
                  <label for="exampleFormControlInput1" class="form-label">Confirm Password</label>
                  <div class="input-group">
                     <input type="password" id="pass2" class="form-control">
                  </div>
               </div>
               <div class="col-xl-6 col-md-12">
                  <button type="submit" class="site-btn blue-btn" id="sbmt">Change Password</button>
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
        
        
                            $("#sbmt").click(function(){
                               event.preventDefault();
                                   loadOn();
                                   $.post('ajax', {
                                       edit_acct: "set",
                                       old_pass: $("#old_pass").val(),
                                       pass: $("#pass").val(),
                                       pass2: $("#pass2").val(),
                                       type: "pass"
                                   }, function(data, status){
                                      loadOff();
                                       if(data.err== 0){
                                           notify('success', data.msg)
                                       }else{
                                            notify('error', data.msg)
                                       }
                                   }, "JSON")
                           })
       
       
        $(window).on('beforeunload', function(){
            loadOn();
        });
        
    })
</script>

</body>
</html>

