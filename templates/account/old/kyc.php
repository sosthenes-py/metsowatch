<!DOCTYPE html>
<html lang="en">
<head>
<?php
    session_start();
    $page= "KYC";
    $section = "kyc";
    include 'check-login.php';
    include '../details.php';
    include 'header.php';
?>

 
    <div class="row">
   <div class="col-xl-12">
       
       
      <div class="site-card">
    
    <?php
                    if($user_fetch['file_status']== 0){
                        $show_text= "Verify your KYC details now to enhance the security of your account and enjoy seamless transactions";
                        $show_color= "primary";
                    }else if($user_fetch['file_status']== 1){
                        $show_text= "Your KYC submission is currently under review.";
                        $show_color= "yellow";
                    }else if($user_fetch['file_status']== 2){
                        $show_text= "Congratulations! Your KYC verification was successful.";
                        $show_color= "green";
                    }else if($user_fetch['file_status']== 3){
                        $show_text= "Your KYC document was rejected. Please contact us";
                        $show_color= "red";
                    }
                ?>
   
   <?php
    if($user_fetch['file_status'] == 0){
   ?>
   <div class="site-card-header">
      <h3 class="title">KYC</h3>
   </div>
   <div class="site-card-body">
      <form action="https://bulkfex.net/user/kyc-submit" method="post" enctype="multipart/form-data">
       <div class="col-xl-12 col-md-12">
          <div class="progress-steps-form">
             <label for="exampleFormControlInput1"
                class="form-label">Verification Type</label>
             <div class="input-group">
                <select id="kycTypeSelect" class="site-nice-select" required>
                   <option selected disabled>----</option>
                   <option value="1">National ID Verification</option>
                   <option value="2">Drivers license</option>
                </select>
             </div>
          </div>
       </div>
       
       <div class="col-xl-12 col-md-12">
           <div class="row kycData" id="kyc1" style="display: none">
              <div class="col-xl-12 col-md-12">
                 <div class="progress-steps-form">
                    <label for="exampleFormControlInput1" class="form-label">NID Number</label>
                    <div class="input-group">
                       <input type="text" class="form-control" aria-label="Amount" id="nid" aria-describedby="basic-addon1">
                    </div>
                 </div>
              </div>
              <div class="col-xl-12 col-md-12">
                 <div class="body-title">Image Of NID</div>
                 <div class="wrap-custom-file">
                    <input type="file" id="file1" accept=".gif, .jpg, .png" required="">
                    <label for="file1">
                    <img class="upload-icon" src="../assets/global/materials/upload.svg" alt="">
                    <span>Select Image Of NID</span>
                    </label>
                 </div>
              </div>
           </div>
           <input type="file" id="file" accept=".gif, .jpg, .png" style="display: none">
           <div class="row kycData" id="kyc2" style="display: none">
               <div class="col-xl-12 col-md-12">
                  <div class="body-title">Front</div>
                  <div class="wrap-custom-file">
                     <input type="file" id="file2" accept=".gif, .jpg, .png" required="">
                     <label for="file2">
                     <img class="upload-icon" src="../assets/global/materials/upload.svg" alt="">
                     <span>Select Front</span>
                     </label>
                  </div>
               </div>
            </div>
        </div>
       
       <button type="submit" class="site-btn blue-btn mt-3" id="sbmt">Submit Now</button>
    </form>
   </div>
   
   <?php }else{ ?>
   <div class="site-card-header">
      <h3 class="title <?=$show_color ?>-color"><?=$show_text ?></h3>
   </div>
   <?php } ?>
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
        
        
        $('#kycTypeSelect').change(function(){
            var type = $(this).val();
            $('.kycData').hide()
            if(type != null){
                $('#kyc'+type).show()
            }
        })
        
        
        $("#sbmt").click(function(){
            event.preventDefault();
            var type = $('#kycTypeSelect').val()
            var fd = new FormData();
            var files = $('#file'+type)[0].files;
             if(files.length < 1 && type != null){
                 notify("success", 'Please select an ID card to upload')
             }else{
                 fd.append('file',files[0]);
                loadOn();
                
                $.ajax({
                  url: 'ajax',
                  type: 'post',
                  data: fd,
                  contentType: false,
                  processData: false,
                  dataType: 'JSON', 
                  success: function(data){
                     if(data.err== 0){
                       notify("success", data.msg)
                       setTimeout(function(){
                            window.location.reload();
                        }, 2000);
                     }else{
                         loadOff();
                        notify("error", data.msg)
                     }
                  }
               });
            }
    
        })
       
       
        $(window).on('beforeunload', function(){
            loadOn();
        });
        
    })
</script>

</body>
</html>

