<!DOCTYPE html>
<html lang="en">
<head>
<?php
    session_start();
    $page= "Investment History";
    $section = "invest-history";
    include 'check-login.php';
    include '../details.php';
    include 'header.php';
    
  
?>

 
    <div class="row">
   <div class="col-xl-12">
       
       
      <div class="site-card">
   <div class="site-card-header">
      <h3 class="title">All Invested Schemas</h3>
   </div>
   <div class="site-card-body">
      <div class="site-datatable">
          
          
         <div class="row">
                        <div class="col-xl-12">
                           <div class="site-card">
                              
                              <div class="site-card-body table-responsive">
                                 <div class="site-datatable">
                                    <table class="display data-table">
                                       <thead>
                                          <tr>
                                             <th>Icon</th>
                                             <th>Schema</th>
                                             <th>ROI</th>
                                             <th>Profit</th>
                                             <th>Created</th>
                                             <th>Status</th>
                                          </tr>
                                       </thead>
                                       <tbody>
                                           
                                           <?php
                                                $query = mysqli_query($con, "select * from miners where username='$username' order by id desc ");
                                                while($row = mysqli_fetch_assoc($query)){
                                                    if($row['status']== 1){
                                                        $status_text= "Active";
                                                        $status_class= "success";
                                                    }else{
                                                        $status_text= "Failed";
                                                        $status_class= "failed";
                                                    }
                                                    
                                                    if(time() - $row['time'] > 172800){
                                                        $date_show = date('d/m/y', $row['time_static']);
                                                    }else{
                                                        $date_show = st(time() - $row['time_static']). " ago";
                                                    }
                                                    
                                                    
                                           ?>
                                           
                                              <tr>
                                                 <td>
                                                    <div class="table-description">
                                                       <div class="">
                                                          <img style="width: 3em" class="investment-plan-icon" src="../assets/global/images/plan<?=$row['miner'] ?>.jpg" alt="">
                                                       </div>
                                                      
                                                    </div>
                                                 </td>
                                                 <td><strong><?= $plans_arr[$row['miner']-1]['name'] ?></strong></td>
                                                 <td><strong><?= $plans_arr[$row['miner']-1]['profit'] ?>%</strong></td>
                                                 <td><strong>$<?= number_format($row['profit'], 2) ?></strong></td>
                                                 <td><strong><?= $date_show ?></strong></td>
                                                 <td>
                                                    <div style="text-transform: capitalize" class="site-badge <?=$status_class ?>"><?=$status_text ?></div>
                                                 </td>
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
            var plan= "<?=$x ?>";
           var method= $("#method").val();
           var max= "<?=$plans_arr[$x]['max'] ?>";
           var min= "<?=$plans_arr[$x]['min'] ?>"
           var amt= $("#amt").val();

           if(amt== "" || amt == 0){
               notify('warning', 'Please enter amount to proceed')
           }else{
               if(method== "bal"){
                   loadOn();
                   $.post('ajax', {
                       buy_miner: "set",
                       amt: amt,
                       plan: plan,
                       action: "balance"
                   }, function(data, status){
                       if(data.err== 1){
                           loadOff();
                           notify('error', data.msg)
                       }else{
                           notify('success', data.msg)
                           setTimeout(function(){
                               window.location.href="https://"+site_domain+"/account/dashboard"
                           }, 2000);
                       }
                   }, "JSON")
               }else{
                   if(amt < parseInt(min)){
                       notify('warning', 'Minimum amount for this plan is $'+min)
                   }else if(amt > parseInt(max)){
                       notify('warning', 'Maximum amount for this plan is $'+max)
                   }else{
                       loadOn()
                       notify('success', 'Please wait for payment details');
                       setTimeout(function(){
                           window.location.href="https://"+site_domain+"/account/invest3?amount="+amt+"&method="+method+"&plan="+plan
                       }, 2000);
                   }
               }
           }

          
       })
       
       
       
       
        $(window).on('beforeunload', function(){
            loadOn();
        });
        
    })
</script>

</body>
</html>

