
      <meta charset="UTF-8"/>
      <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
      <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
      <meta name="keywords" content="<?=$details_site_name ?>">
      <meta name="description" content="<?=$details_site_name ?>">
      <link rel="shortcut icon" href="../assets/global/images/2Gdil1VbLvdIxyqEmh03.png" type="image/x-icon"/>
      <link rel="icon" href="../assets/global/images/2Gdil1VbLvdIxyqEmh03.png" type="image/x-icon"/>
      <link rel="stylesheet" href="../assets/global/css/fontawesome.min.css"/>
      <link rel="stylesheet" href="../assets/frontend/css/vendor/bootstrap.min.css"/>
      <link rel="stylesheet" href="../assets/frontend/css/animate.css"/>
      <link rel="stylesheet" href="../assets/frontend/css/owl.carousel.min.css"/>
      <link rel="stylesheet" href="../assets/frontend/css/nice-select.css"/>
      <link rel="stylesheet" href="../assets/global/css/datatables.min.css"/>
      <link rel="stylesheet" type="text/css" href="../assets/vendor/mckenziearts/laravel-notify/css/notify.css"/>
      <link rel="stylesheet" href="../assets/global/css/custom.css"/>
      <link rel="stylesheet" href="../assets/frontend/css/magnific-popup.css"/>
      <link rel="stylesheet" href="../assets/frontend/css/styles.css?var=6"/>
      
      <link rel="stylesheet" href="../assets/global/css/iziToast.min.css"/>
      <style>
         //The Custom CSS will be added on the site head tag 
         .site-head-tag {
         margin: 0;
         padding: 0;
         }
      
    #loader{
      position: fixed;
      text-align:center;
      width: 100%;
      height: 100%;
      top: 50%;
      left: 0;
      right: 0;
      bottom: 0;
      z-index: 10000;
      cursor: pointer;
      display:none;
      justify-content: center;
      margin-top:-50px;
    }
    
     #overlay{
      position: fixed;
      text-align:center;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: black;
      opacity: 0.6;
      z-index: 100000;
      cursor: not-allowed;
      display:none;
      justify-content: center;
    }
 
    .box-input{
        color: black;
    }
    
      </style>
      <title><?=$details_site_name ?> -     Dashboard</title>
      
      
      <script>
        function googleTranslateElementInit() {
        
        new google.translate.TranslateElement({
        
        pageLanguage: 'en'
        
        }, 'google_translate_element');
        
        }
        </script>
        <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
      
      <script src="//code.tidio.co/xrhh8xhl0xa8vo7hlarlkkfnpnd4btig.js" async></script>
      
   </head>
   <body class="light-theme">
     <div id="loader">
         <img src="../ajax-loader.gif" alt="" width="70em">
     </div>
    <div id="overlay"></div>


      <!--Full Layout-->
      <div class="panel-layout">
         <!--Header-->
         <div class="panel-header">
            <div class="logo">
               <a href="http://<?=$details_site_domain ?>">
               <img class="logo-unfold" src="../assets/global/images/zCB9V7ykDWtBq42q8hjF.png" alt="Logo"/>
               <img class="logo-fold" src="../assets/global/images/zCB9V7ykDWtBq42q8hjF.png" alt="Logo"/>
               </a>
            </div>
            <div class="nav-wrap">
               <div class="nav-left">
                  <button class="sidebar-toggle">
                  <i class="anticon anticon-arrow-left"></i>
                  </button>
               </div>
               <div class="nav-right">
                  <div class="single-nav-right">
                    
                     <div class="single-right">
                        <div id="google_translate_element"></div>
                     </div>
                     <div class="single-right">
                        <button
                           type="button"
                           class="item"
                           data-bs-toggle="dropdown"
                           aria-expanded="false"
                           >
                        <i class="anticon anticon-user"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                           <li>
                              <a href="settings" class="dropdown-item" type="button"><i
                                 class="anticon anticon-setting"></i>Settings</a>
                           </li>
                           <li>
                              <a href="change-password" class="dropdown-item" type="button">
                              <i class="anticon anticon-lock"></i>Change Password
                              </a>
                           </li>
                           
                           <li class="logout">
                                 <a href="logout" class="dropdown-item"><i
                                    class="anticon anticon-logout"></i>Logout</a>
                           </li>
                        </ul>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <!--/Header-->
         <!--Side Nav-->
         <div class="side-nav">
            <div class="side-wallet-box default-wallet mb-0">
               <div class="user-balance-card">
                  <div class="wallet-name">
                     <div class="name">Account Balance</div>
                     <div class="default">Wallet</div>
                  </div>
                  <div class="wallet-info">
                     <div class="wallet-id"><i icon-name="wallet"></i>Main Wallet</div>
                     <div class="balance">$<?=number_format($user_fetch['deposited']) ?></div>
                  </div>
                  <div class="wallet-info">
                     <div class="wallet-id"><i icon-name="landmark"></i>Profit Wallet</div>
                     <div class="balance">$<?=number_format($user_fetch['program_cash']) ?></div>
                  </div>
               </div>
               <div class="actions">
                  <a href="deposit" class="user-sidebar-btn"><i
                     class="anticon anticon-file-add"></i>Deposit</a>
                  <a href="invest" class="user-sidebar-btn red-btn"><i
                     class="anticon anticon-export"></i>Invest Now</a>
               </div>
            </div>
            <div class="side-nav-inside">
               <ul class="side-nav-menu">
                  <li class="side-nav-item <?php  if($section =='dashboard'){echo'active';}?>">
                     <a href="dashboard"><i
                        class="anticon anticon-appstore"></i><span>Dashboard</span></a>
                  </li>
                  <li class="side-nav-item <?php  if($section =='invest'){echo'active';}?>">
                     <a href="invest"><i
                        class="anticon anticon-check-square"></i><span>Invest</span></a>
                  </li>
                  <li class="side-nav-item <?php  if($section =='invest-history'){echo'active';}?>">
                     <a href="invest-history"><i
                        class="anticon anticon-copy"></i><span>Investment History</span></a>
                  </li>
                  <li class="side-nav-item <?php  if($section =='history'){echo'active';}?>">
                     <a href="history"><i
                        class="anticon anticon-inbox"></i><span>All Transactions</span></a>
                  </li>
                  <li class="side-nav-item <?php  if($section =='deposit'){echo'active';}?>">
                     <a href="deposit"><i
                        class="anticon anticon-file-add"></i><span>Add Money</span></a>
                  </li>
                  <li class="side-nav-item <?php  if($section =='deposit-history'){echo'active';}?>">
                     <a href="deposit-history"><i
                        class="anticon anticon-folder-add"></i><span>Add Money History</span></a>
                  </li>
                  <!--<li class="side-nav-item ">-->
                  <!--   <a href="wallet-exchange"><i-->
                  <!--      class="anticon anticon-transaction"></i><span>Wallet Exchange</span></a>-->
                  <!--</li>-->
                  <!--<li class="side-nav-item   ">-->
                  <!--   <a href="send-money"><i-->
                  <!--      class="anticon anticon-export"></i><span>Send Money</span></a>-->
                  <!--</li>-->
                  <!--<li class="side-nav-item ">-->
                  <!--   <a href="send-money/log"><i-->
                  <!--      class="anticon anticon-cloud"></i><span>Send Money Log</span></a>-->
                  <!--</li>-->
                  <li class="side-nav-item <?php  if($section =='withdraw'){echo'active';}?>">
                     <a href="withdraw"><i
                        class="anticon anticon-bank"></i><span>Withdraw</span></a>
                  </li>
                  <li class="side-nav-item <?php  if($section =='withdraw-history'){echo'active';}?>">
                     <a href="withdraw-history"><i
                        class="anticon anticon-credit-card"></i><span>Withdraw History</span></a>
                  </li>
                  <li class="side-nav-item <?php  if($section =='rank'){echo'active';}?>">
                     <a href="rank"><i
                        class="anticon anticon-star"></i><span>Ranking Badge</span></a>
                  </li>
                  <li class="side-nav-item <?php  if($section =='referral'){echo'active';}?>">
                     <a href="referral"><i
                        class="anticon anticon-usergroup-add"></i><span>Referral</span></a>
                  </li>
                  <li class="side-nav-item <?php  if($section =='setting'){echo'active';}?>">
                     <a href="settings"><i
                        class="anticon anticon-setting"></i><span>Settings</span></a>
                  </li>
                 
                  <li class="side-nav-item">
                     <!-- Authentication -->
                        <a href="logout"><button type="submit" class="site-btn grad-btn w-100">
                        <i class="anticon anticon-logout"></i><span>Logout</span>
                        </button></a>
                  </li>
               </ul>
            </div>
         </div>
         <!--/Side Nav-->
         <div class="page-container">
            <div class="main-content">
               <div class="section-gap">
                  <div class="container-fluid">
                      
                      <?php if($user_fetch['file_status'] == 0){ ?>
                     <!--<div class="row">-->
                     <!--   <div class="col">-->
                     <!--      <div class="alert site-alert alert-dismissible fade show" role="alert">-->
                     <!--         <div class="content">-->
                     <!--            <div class="icon"><i class="anticon anticon-warning"></i></div>-->
                     <!--            You need to submit your-->
                     <!--            <strong>KYC and Other Documents</strong> before you can proceed with the system.-->
                     <!--         </div>-->
                     <!--         <div class="action">-->
                     <!--            <a href="kyc" class="site-btn-sm grad-btn"><i-->
                     <!--               class="anticon anticon-info-circle"></i>Submit Now</a>-->
                     <!--            <a href="" class="site-btn-sm red-btn ms-2" type="button" data-bs-dismiss="alert"-->
                     <!--               aria-label="Close"><i class="anticon anticon-close"></i>Later</a>-->
                     <!--         </div>-->
                     <!--      </div>-->
                     <!--   </div>-->
                     <!--</div>-->
                     
                     <?php }else if($user_fetch['file_status'] == 1){ ?>
                     
                     <!--<div class="row">-->
                     <!--   <div class="col">-->
                     <!--      <div class="alert site-alert alert-dismissible fade show" role="alert">-->
                     <!--         <div class="content">-->
                     <!--            <div class="icon"><i class="anticon anticon-warning"></i></div>-->
                     <!--            <strong>Your KYC is being reviewed</strong>-->
                     <!--         </div>-->
                              
                     <!--      </div>-->
                     <!--   </div>-->
                     <!--</div>-->
                     
                     <?php } ?>
                     
                     
                     
                     
                     
                     