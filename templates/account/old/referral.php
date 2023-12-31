<!DOCTYPE html>
<html lang="en">
<head>
<?php
    session_start();
    $page= "Referral";
    $section = "referral";
    include 'check-login.php';
    include '../details.php';
    include 'header.php';
?>

 
    <div class="row">
   <div class="col-xl-12">
       
       
      <div class="site-card">
       <div class="site-card-header">
          <h3 class="title">Referral URL</h3>
       </div>
       <div class="site-card-body">
          <div class="referral-link">
             <div class="referral-link-form">
                <input type="text" value="http://<?=$details_site_domain ?>/register?by=<?=$user_fetch['code'] ?>" id="refLink" readonly/>
                <button type="submit" id="copy_link">
                    <i class="anticon anticon-copy"></i>
                    <span id="copy">Copy Url</span>
                </button>
             </div>
             <p class="referral-joined">
                <?php
                    $ref_count = mysqli_num_rows(mysqli_query($con, "select * from members where upline='$username' "));
                ?>
                <?= number_format($ref_count) ?> people have joined using your link
             </p>
          </div>
       </div>
    </div>
      
      
   </div>
</div>
                    <!--Page Content-->
                    
                    
                    <div class="row">
   <div class="col-xl-12">
      <div class="site-card">
         <div class="site-card-header">
            <h3 class="title">All Referral Logs</h3>
            <div class="card-header-links">
               <span class="card-header-link rounded-pill"> Referral Profit: 0 USD</span>
            </div>
         </div>
         <div class="site-card-body table-responsive">
            <div class="site-tab-bars">
               <ul class="nav nav-pills" id="pills-tab" role="tablist">
                  <li class="nav-item" role="presentation">
                     <a href="" class="nav-link active" id="generalTarget-tab" data-bs-toggle="pill" data-bs-target="#generalTarget" type="button" role="tab" aria-controls="generalTarget" aria-selected="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="network" class="lucide lucide-network">
                           <rect x="9" y="2" width="6" height="6"></rect>
                           <rect x="16" y="16" width="6" height="6"></rect>
                           <rect x="2" y="16" width="6" height="6"></rect>
                           <path d="M5 16v-4h14v4"></path>
                           <path d="M12 12V8"></path>
                        </svg>
                        <?php if($user_fetch['upline'] == ""){ echo "General"; }else{ echo $upline; } ?>
                     </a>
                  </li>
               </ul>
            </div>
            <div class="tab-content" id="pills-tabContent">
               <div class="tab-pane fade show active" id="generalTarget" role="tabpanel" aria-labelledby="generalTarget-tab">
                  <div class="row">
                     <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                        <div class="site-datatable">
                           <div class="row table-responsive">
                              <div class="col-xl-12">
                                 <table class="display data-table">
                                    <thead>
                                       <tr>
                                          <th>Description</th>
                                          <th>Amount</th>
                                          <th>Status</th>
                                       </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                                $query= mysqli_query($con, "select * from members where upline='$username' ");
                                                if(mysqli_num_rows($query) < 1){
                                                    echo '<tr><td colspan=4>Your referrals will appear here...</td></tr>';
                                                }else{
                                                    while($row= mysqli_fetch_assoc($query)){
                                                        if(time() - $row['reg_date'] > 172800){
                                                            $date_show = date('d/m/y', $row['reg_date']);
                                                        }else{
                                                            $date_show = st(time() - $row['reg_date']). " ago";
                                                        }
                                                        
                                                        if($row['total_deposit'] > 0){
                                                            $status_text= "Active";
                                                            $status_class= "success";
                                                        }else{
                                                            $status_text= "Inactive";
                                                            $status_class= "warnning";
                                                        }
                                                        
                                                        $user = $row['username'];
                                                        
                                                        $dep_query = mysqli_query($con, "select * from program_history where username='$user' and name='deposit' and status = 1 limit 1");
                                                        $total = 0;
                                                        if(mysqli_num_rows($dep_query) > 0){
                                                            $fetch = mysqli_fetch_assoc($dep_query);
                                                            $total = (10/100)*$fetch['amt'];
                                                        }
                                                        
                                                        ?>
                                        
                                         <tr>
                                                 <td>
                                                    <div class="table-description">
                                                       <div class="icon">
                                                          <i icon-name="backpack"></i>
                                                       </div>
                                                       <div class="description">
                                                          <strong>You referred <?=$row['username'] ?></strong>
                                                          <div class="date"><?=$date_show ?></div>
                                                       </div>
                                                    </div>
                                                 </td>
                                                 <td><strong
                                                    class="green-color"><?="+".number_format($total, 2) ?> USD</strong>
                                                 </td>
                                                 <td>
                                                    <div style="text-transform: capitalize" class="site-badge <?=$status_class ?>"><?=$status_text ?></div>
                                                 </td>
                                              </tr>
                                          
                                          
                                          <?php
                                                }
                                                }
                                          ?>
                                        
                                        
                                    </tbody>
                                 </table>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
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
        
       
       
        $(window).on('beforeunload', function(){
            loadOn();
        });
        
    })
</script>

</body>
</html>

