<!DOCTYPE html>
<html lang="en">
   <head>
<?php
session_start();
$page= "Dashboard";
$section = "dashboard";
include 'check-login.php';
include '../details.php';
include 'header.php';
?>
                     
                     
                     
                     <?php
                        if($user_fetch['program_cash_static'] >=0 && $user_fetch['program_cash_static'] < 500000){
                            $rank = 1;
                            $rank_detail = 'By signing up to the account and earn up to $500';
                            $rank_title = "Access Member";
                        }else if($user_fetch['program_cash_static'] >=500000 && $user_fetch['program_cash_static'] < 1200000){
                            $rank = 2;
                            $rank_detail = 'By earning $500,000 from the site';
                            $rank_title = $details_site_name. "2";
                        }else if($user_fetch['program_cash_static'] >=1200000 && $user_fetch['program_cash_static'] < 2000000){
                            $rank = 3;
                            $rank_detail = 'By earning $1,200,000 from the site';
                            $rank_title = "Access 3";
                        }else if($user_fetch['program_cash_static'] >= 2000000){
                            $rank = 4;
                            $rank_detail = 'By earning $2,000,000 from the site';
                            $rank_title = $details_site_name. "4";
                        }
                     ?>
                     
                     <!--Page Content-->
                     <div class="row">
                        <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6 col-12">
                            
                           <?php
                                if($user_fetch['avatar_name'] == ""){
                                    $avatar_link = "";
                                }else{
                                    $avatar_link = 'style="background: url(uploads/avatar/'.$user_fetch['avatar_name'].');"';
                                }
                           ?>
                           <div class="user-ranking" <?=$avatar_link ?>>
                              <h4>Level <?=$rank ?></h4>
                              <p><?=$rank_title ?></p>
                              <div class="rank" data-bs-toggle="tooltip" data-bs-placement="top" title="<?=$rank_detail ?>">
                                 <img src="../assets/global/images/rank<?=$rank ?>.svg" alt="">
                              </div>
                           </div>
                        </div>
                        <div class="col-xl-9 col-lg-9 col-md-8 col-sm-12 col-12">
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
                                       <?=number_format($ref_count) ?> people have joined using your link
                                    </p>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                     <?php
                        $query = mysqli_query($con, "select * from program_history where username='$username' ");
                        $all_tx = 0;
                        $total_deposit = 0;
                        $total_with = 0;
                        while($row = mysqli_fetch_assoc($query)){
                            $all_tx ++;
                            if($row['name'] == "deposit" && $row['status'] == 1){
                                $total_deposit += $row['amt'];
                            }
                            if($row['name'] == "withdraw" && $row['status'] == 1){
                                $total_with += $row['amt'];
                            }
                        }
                        
                        $inv_query = mysqli_query($con, "select * from miners where username='$username' ");
                        $total_inv = 0;
                        $total_profit =0;
                        while($row = mysqli_fetch_assoc($inv_query)){
                            $total_inv += $row['amt'];
                            $total_profit += $row['profit'];
                        }
                     ?>
                     <div class="row user-cards">
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
                           <div class="single">
                              <div class="icon"><i class="anticon anticon-inbox"></i></div>
                              <div class="content">
                                 <h4><span class="count"><?= number_format($all_tx) ?></span></h4>
                                 <p>All Transactions</p>
                              </div>
                           </div>
                        </div>
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
                           <div class="single">
                              <div class="icon"><i class="anticon anticon-file-add"></i></div>
                              <div class="content">
                                 <h4><b>$</b><span class="count"><?= number_format($total_deposit) ?></span></h4>
                                 <p>Total Deposit</p>
                              </div>
                           </div>
                        </div>
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
                           <div class="single">
                              <div class="icon"><i class="anticon anticon-check-square"></i></div>
                              <div class="content">
                                 <h4><b>$</b><span class="count"><?= number_format($total_inv) ?></span></h4>
                                 <p>Total Investment</p>
                              </div>
                           </div>
                        </div>
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
                           <div class="single">
                              <div class="icon"><i class="anticon anticon-credit-card"></i></div>
                              <div class="content">
                                 <h4><b>$</b><span class="count"><?= number_format($total_profit) ?></span></h4>
                                 <p>Total Profit</p>
                              </div>
                           </div>
                        </div>
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
                           <div class="single">
                              <div class="icon"><i class="anticon anticon-arrow-right"></i></div>
                              <div class="content">
                                 <h4><b>$</b><span class="count">0</span></h4>
                                 <p>Total Transfer </p>
                              </div>
                           </div>
                        </div>
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
                           <div class="single">
                              <div class="icon"><i class="anticon anticon-money-collect"></i></div>
                              <div class="content">
                                 <h4><b>$</b><span class="count"><?= number_format($total_with) ?></span></h4>
                                 <p>Total Withdraw</p>
                              </div>
                           </div>
                        </div>
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
                           <div class="single">
                              <div class="icon"><i class="anticon anticon-gift"></i></div>
                              <div class="content">
                                 <h4><b>$</b><span class="count"><?=number_format($user_fetch['ref_earning']) ?></span>
                                 </h4>
                                 <p>Referral Bonus</p>
                              </div>
                           </div>
                        </div>
                        
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
                           <div class="single">
                              <div class="icon"><i class="anticon anticon-question"></i></div>
                              <div class="content">
                                 <h4 class="count">0</h4>
                                 <p>Total Ticket</p>
                              </div>
                           </div>
                        </div>
                     </div>
                     
                     
                     
                     
                     <div class="row">
                        <div class="col-xl-12">
                           <div class="site-card">
                              <div class="site-card-header">
                                 <h3 class="title">Recent Transactions</h3>
                              </div>
                              <div class="site-card-body table-responsive">
                                 <div class="site-datatable">
                                    <table class="display data-table">
                                       <thead>
                                          <tr>
                                             <th>Description</th>
                                             <th>Transactions ID</th>
                                             <th>Type</th>
                                             <th>Amount</th>
                                             <th>Fee</th>
                                             <th>Status</th>
                                             <th>Gateway</th>
                                          </tr>
                                       </thead>
                                       <tbody>
                                           
                                           <?php
                                                $query = mysqli_query($con, "select * from program_history where username='$username' order by id desc ");
                                                while($row = mysqli_fetch_assoc($query)){
                                                    if($row['status']== 1){
                                                        $status_text= "Completed";
                                                        $status_class= "success";
                                                    }else if($row['status']== 2){
                                                        $status_text= "Failed";
                                                        $status_class= "failed";
                                                    }else{
                                                        $status_text= "Processing";
                                                        $status_class= "warnning";
                                                    }
                                                    
                                                    if(time() - $row['time'] > 172800){
                                                        $date_show = date('d/m/y', $row['time']);
                                                    }else{
                                                        $date_show = st(time() - $row['time']). " ago";
                                                    }
                                                    
                                                    if($row['name'] == "deposit"){
                                                        $sign = "+";
                                                        $name_class = "blue";
                                                    }else if($row['name'] == "withdraw"){
                                                        $sign = "-";
                                                        $name_class = "primary";
                                                    }else if($row['name'] == "bonus"){
                                                        $sign = "+";
                                                        $name_class = "blue";
                                                    }else if($row['name'] == "buy_miner"){
                                                        $sign = "-";
                                                        $name_class = "primary";
                                                    }
                                               
                                           ?>
                                           
                                              <tr>
                                                 <td>
                                                    <div class="table-description">
                                                       <div class="icon">
                                                          <i icon-name="backpack"></i>
                                                       </div>
                                                       <div class="description">
                                                          <strong><?=$row['detail'] ?></strong>
                                                          <div class="date"><?=$date_show ?></div>
                                                       </div>
                                                    </div>
                                                 </td>
                                                 <td><strong><?= $row['code'] ?></strong></td>
                                                 <td>
                                                    <div style="text-transform: capitalize" class="site-badge <?=$name_class ?>-bg"><?= $row['name'] ?></div>
                                                 </td>
                                                 <td><strong
                                                    class="green-color"><?=$sign.number_format($row['amt']) ?> USD</strong>
                                                 </td>
                                                 <td><strong>0 USD</strong></td>
                                                 <td>
                                                    <div style="text-transform: capitalize" class="site-badge <?=$status_class ?>"><?=$status_text ?></div>
                                                 </td>
                                                 <td><strong>System</strong></td>
                                              </tr>
                                          
                                          
                                          <?php
                                                }
                                          ?>
                                       </tbody>
                                    </table>
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

        
         $("body").on('click', '#copy_link', function(){
            event.preventDefault();
            var item= $("refLink").val();
            
            navigator.clipboard.writeText(item).then(function () {
                notify('info', 'link copied!')
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