<!DOCTYPE html>
<html lang="en">
<head>
<?php
    session_start();
    $page= "Settings";
    $section = "setting";
    include 'check-login.php';
    include '../details.php';
    include 'header.php';
    
    
     require '2FA/vendor/autoload.php';

        $g = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();
        
        if($user_fetch['2fa_secret']== ""){
            $secret = $g->generateSecret();
            mysqli_query($con, "update members set 2fa_secret='$secret' where username='$username' ");
        }else{
            $secret= $user_fetch['2fa_secret'];
        }
        
        $link= \Sonata\GoogleAuthenticator\GoogleQrUrl::generate($username, $secret, $details_site_name);


?>

 
    <div class="row">
   <div class="col-xl-12">
       
       
      <div class="site-card">
   <div class="site-card-header">
      <h3 class="title">Profile Settings</h3>
   </div>
   <div class="site-card-body">
      <form action="" method="post" enctype="multipart/form-data">
         <div class="row">
            <div class="col-xl-3">
               <div class="mb-3">
                  <div class="body-title">Avatar:</div>
                  <div class="wrap-custom-file">
                     <input type="file" name="avatar" id="avatar" accept=".gif, .jpg, .png">
                     <?php
                            $avatar_name= $user_fetch['avatar_name'];
                            if($avatar_name== ""){
                                $avatar_label= '<label for="avatar" class="file-ok">
                                                     <img class="upload-icon" src="../assets/global/materials/upload.svg" alt="">
                                                     <span>Update Avatar</span>
                                                </label>';
                            }else{
                                $avatar_label= '<label for="avatar" class="file-ok" style="background-image: url(uploads/avatar/'.$avatar_name.')">
                                                <img class="upload-icon" src="../assets/global/materials/upload.svg" alt="">
                                                <span>Update Avatar</span>
                                            </label>';
                            }
                            
                            echo $avatar_label;
                    ?>
                     
                     
                  </div>
               </div>
            </div>
         </div>
         <div class="progress-steps-form">
            <div class="row">
               <div class="col-xl-6 col-md-12">
                  <label for="exampleFormControlInput1" class="form-label">First Name</label>
                  <div class="input-group">
                     <input type="text" class="form-control" id="fname" value="<?=$user_fetch['fname'] ?>" placeholder="First Name">
                  </div>
               </div>
               <div class="col-xl-6 col-md-12">
                  <label for="exampleFormControlInput1" class="form-label">Last Name</label>
                  <div class="input-group">
                     <input type="text" class="form-control" id="lname" value="<?=$user_fetch['lname'] ?>" placeholder="Last Name">
                  </div>
               </div>
               <div class="col-xl-6 col-md-12">
                  <label for="exampleFormControlInput1" class="form-label">Username</label>
                  <div class="input-group">
                     <input type="text" class="form-control" value="<?=$user_fetch['username'] ?>" placeholder="Username" readonly>
                  </div>
               </div>
               <div class="col-xl-6 col-md-12">
                  <label for="exampleFormControlInput1" class="form-label">Gender</label>
                  <div class="input-group">
                     <select name="gender" id="kycTypeSelect" class="nice-select site-nice-select" required="" style="display: none;">
                        <option value="male">male</option>
                        <option value="female">female</option>
                        <option value="other">other</option>
                     </select>
                     <div class="nice-select site-nice-select" tabindex="0">
                        <span class="current">male</span>
                        <ul class="list">
                           <li data-value="male" class="option selected">male</li>
                           <li data-value="female" class="option">female</li>
                           <li data-value="other" class="option">other</li>
                        </ul>
                     </div>
                  </div>
               </div>
               <div class="col-xl-6 col-md-12">
                  <label for="exampleFormControlInput1" class="form-label">Occupation</label>
                  <div class="input-group">
                     <input type="text" id="occupation" class="form-control" value="" placeholder="Source of income">
                  </div>
               </div>
               <div class="col-xl-6 col-md-12">
                  <label for="exampleFormControlInput1" class="form-label">Email Address</label>
                  <div class="input-group">
                     <input type="email" readonly class="form-control" value="<?=$user_fetch['email'] ?>" placeholder="Email Address">
                  </div>
               </div>
               <div class="col-xl-6 col-md-12">
                  <label for="exampleFormControlInput1" class="form-label">Phone</label>
                  <div class="input-group">
                     <input type="text" class="form-control" id="phone" value="<?=$user_fetch['phone'] ?>" placeholder="Phone">
                  </div>
               </div>
               <div class="col-xl-6 col-md-12">
                  <label for="exampleFormControlInput1" class="form-label">Country</label>
                  <div class="input-group">
                     <input type="text" class="form-control" value="<?=$user_fetch['country'] ?>" readonly placeholder="Country">
                  </div>
               </div>
               <div class="col-xl-6 col-md-12">
                  <label for="exampleFormControlInput1" class="form-label">City</label>
                  <div class="input-group">
                     <input type="text" class="form-control" id="city" value="<?=$user_fetch['state'] ?>" placeholder="City">
                  </div>
               </div>
               <div class="col-xl-6 col-md-12">
                  <label for="exampleFormControlInput1" class="form-label">Zip</label>
                  <div class="input-group">
                     <input type="text" class="form-control" id="zip" value="<?=$user_fetch['zip'] ?>" placeholder="Zip">
                  </div>
               </div>
               <div class="col-xl-6 col-md-12">
                  <label for="exampleFormControlInput1" class="form-label">Address</label>
                  <div class="input-group">
                     <input type="text" class="form-control" id="address" value="<?=$user_fetch['address'] ?>" placeholder="Address">
                  </div>
               </div>
               <div class="col-xl-6 col-md-12">
                  <label for="exampleFormControlInput1" class="form-label">Joining Date</label>
                  <div class="input-group">
                     <input type="text" class="form-control disabled" value="<?= date('jS M, Y h:i A', $user_fetch['reg_date']); ?>" placeholder="Joining Date" disabled="">
                  </div>
               </div>
               <div class="col-xl-6 col-md-12">
                  <button type="submit" class="site-btn blue-btn" id="sbmt">Save Changes</button>
               </div>
            </div>
         </div>
      </form>
   </div>
</div>
      
      
   </div>
</div>
                    <!--Page Content-->
                    
                    
                    <div class="row">
                       <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                          
                          <div class="site-card">
                               <div class="site-card-header">
                                  <h3 class="title">2FA Security - <?php if($user_fetch['2fa_status'] == 1){ echo '<span style="font-weight: bold" class="green-color">ON</span>';}else{ echo '<span style="font-weight: bold" class="red-color">OFF</span>'; } ?></h3>
                               </div>
                               <div class="site-card-body">
                                  <div class="progress-steps-form">
                                     <p>Two Factor Authentication (2FA) Strengthens Access Security By Requiring Two Methods (also Referred To As Factors) To Verify Your Identity. Two Factor Authentication Protects Against Phishing, Social Engineering And Password Brute Force Attacks And Secures Your Logins From Attackers Exploiting Weak Or Stolen Credentials.</p>
                                     
                                     <br>
                                     
                                     <?php
                                        if($user_fetch['2fa_status'] == 0){ 
                                     ?>
                                     <p style="font-weight: bold">Scan the QR code with your Google Authenticator App or Use the secret code below</p>
                                     <img src="<?=$link ?>">
                                     <br><br>
                                     <div class="referral-link">
                                         <div class="referral-link-form">
                                            <input type="text" value="<?=$secret ?>" id="key" readonly/>
                                            <button type="submit" id="copy_key">
                                            <i class="anticon anticon-copy"></i>
                                            <span id="copy">Copy Secret</span>
                                            </button>
                                        </div>
                                    </div>
                                    <br>
                                    <p class="pt-2">
                                        Enter the PIN from Google Authenticator App
                                     </p>
                                    
                                        <div class="input-group">
                                           <input type="number" id="enable_2fa_code" class="form-control">
                                        </div>
                                        <div class="buttons mt-4">
                                           <button type="submit" class="site-btn blue-btn" value="enable" id="enable_2fa_sbmt">Enable 2FA<i class="anticon anticon-double-right"></i>
                                           </button>
                                        </div>
                                        
                                    <?php }else{ ?>
                                    
                                    <p class="pt-2">
                                        Enter the PIN from Google Authenticator App to disable 2FA
                                     </p>
                                        <div class="input-group">
                                           <input type="number" id="disable_2fa_code" class="form-control">
                                        </div>
                                        <div class="buttons mt-4">
                                           <button type="submit" class="site-btn blue-btn" value="enable" id="disable_2fa_sbmt">Disable 2FA<i class="anticon anticon-double-right"></i>
                                           </button>
                                        </div>
                                    <?php } ?>
                                  </div>
                               </div>
                            </div>
                          
                       </div>
                       
                       
                       <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                          <div class="site-card">
                             <div class="site-card-header">
                                <h3 class="title">KYC</h3>
                             </div>
                             <div class="site-card-body">
                                <a href="kyc" class="site-btn blue-btn">Upload KYC</a>
                                <p class="mt-3"></p>
                             </div>
                          </div>
                          <div class="site-card">
                             <div class="site-card-header">
                                <h3 class="title">Change Password</h3>
                             </div>
                             <div class="site-card-body">
                                <a href="change-password" class="site-btn blue-btn">Change Password</a>
                             </div>
                          </div>
                       </div>
                    </div>
                                        
                    
                    
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
        var fd = new FormData()
        fd.append('fname', $("#fname").val())
        fd.append('lname', $("#lname").val())
        fd.append('address', $("#address").val())
        fd.append('phone', $("#phone").val())
        fd.append('state', $("#state").val())
        fd.append('zip', $("#zip").val())
        fd.append('occupation', $("#occupation").val())
        fd.append('avatar', 'set')
        var files = $('#avatar')[0].files;
        
        if(files.length < 1){
            var with_file = 0;
            fd.append('with_file', 0)
        }else{
            var with_file = 1;
            fd.append('avatar',files[0]);
            fd.append('with_file', 1)
        }
        
        loadOn();
        
                $.ajax({
                  url: 'ajax',
                  type: 'post',
                  data: fd,
                  contentType: false,
                  processData: false,
                  dataType: 'JSON', 
                  success: function(data){
                      loadOff();
                     if(data.err== 0){
                        notify('success', data.msg)
                     }else{
                        notify('error', data.msg)
                     }
                  }
               });
    }) 
        
        
                            $("#enable_2fa_sbmt").click(function(){
                               event.preventDefault();
                               if($("#enable_2fa_code").val() !== ""){
                                   loadOn();
                                   $.post('ajax', {
                                       bind_2fa: "set",
                                       code: $("#enable_2fa_code").val()
                                   }, function(data, status){
                                       if(data.err== 0){
                                           notify('success', data.msg)
                                           setTimeout(function(){
                                               window.location.reload();
                                           }, 3000);
                                       }else{
                                            loadOff();
                                            notify('error', data.msg)
                                       }
                                   }, "JSON")
                               }else{
                                   notify('warning', 'Please enter code from your Authenticator')
                               }
                           })
                           
                           
                           $("#disable_2fa_sbmt").click(function(){
                               event.preventDefault();
                               if($("#disable_2fa_code").val() !== ""){
                                   loadOn();
                                   $.post('ajax', {
                                       unbind_2fa: "set",
                                       code: $("#disable_2fa_code").val()
                                   }, function(data, status){
                                       if(data.err== 0){
                                           notify('success', data.msg)
                                           setTimeout(function(){
                                               window.location.reload();
                                           }, 3000);
                                       }else{
                                            loadOff();
                                            notify('error', data.msg)
                                       }
                                   }, "JSON")
                               }else{
                                   notify('warning', 'Please enter code from your Authenticator')
                               }
                           })
       
       
       
       $("body").on('click', '#copy_key', function(){
            event.preventDefault();
            var item= $("#key").val();
            
            navigator.clipboard.writeText(item).then(function () {
                notify('info', 'Secret copied!')
            }, function () {
                notify('info', 'An error occured. Please try to copy manually')
            });
        })
        
       
        $(window).on('beforeunload', function(){
            loadOn();
        });
        
    })
</script>

</body>
</html>

