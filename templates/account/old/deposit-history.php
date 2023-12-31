<!DOCTYPE html>
<html lang="en">
<head>
<?php
    session_start();
    $page= "Deposit History";
    $section = "deposit-history";
    include 'check-login.php';
    include '../details.php';
    include 'header.php';
    
  
?>

 
    <div class="row">
   <div class="col-xl-12">
       
       
      <div class="site-card">
   <div class="site-card-header">
      <h3 class="title">All Deposits</h3>
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
                                             <th>Description</th>
                                             <th>Transactions ID</th>
                                             <th>Amount</th>
                                             <th>Fee</th>
                                             <th>Status</th>
                                             <th>Method</th>
                                          </tr>
                                       </thead>
                                       <tbody>
                                           
                                           <?php
                                                $query = mysqli_query($con, "select * from program_history where username='$username' and name='deposit' or username='$username' and name='bonus' order by id desc ");
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
                                                 <td><strong
                                                    class="green-color"><?=$sign.number_format($row['amt']) ?> USD</strong>
                                                 </td>
                                                 <td><strong>0 USD</strong></td>
                                                 <td>
                                                    <div style="text-transform: capitalize" class="site-badge <?=$status_class ?>"><?=$status_text ?></div>
                                                 </td>
                                                 <td><strong><?= $row['method']; ?></strong></td>
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

