<div class="full_container">
         <div class="inner_container">
            <!-- Sidebar  -->
            <nav id="sidebar">
               <div class="sidebar_blog_1">
                  <div class="sidebar-header">
                     <div class="logo_section">
                        <a href="index.html"><img class="logo_icon img-responsive" src="images/logo/logo_icon.png" alt="#" /></a>
                     </div>
                  </div>
                  <div class="sidebar_user_info">
                     <div class="icon_setting"></div>
                     <div class="user_profle_side">
                        <div class="user_img">
							<img class="img-responsive" src="https://masterpro.codingjourney.in/uploads/masterpro_logo.png" style="height:50px; width:100px; margin:10px; padding:left-10%" alt="#" />
						 </div>
                        <div class="user_info">
                           <h6>Admin</h6>
                           <p><span class="online_animation"></span> Online</p>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="sidebar_blog_2">
                  <h4>General</h4>
                  <ul class="list-unstyled components">
                     
                     <li><a href="{{route('dashboard')}}"><i class="fa fa-dashboard yellow_color"></i> <span>Dashboard</span></a></li>
                     <!--<li><a href="{{route('attendance.index')}}"><i class="fa fa-clock-o purple_color2"></i> <span>Attendance</span></a></li>-->
                     <li><a href="{{route('users')}}"><i class="fa fa-user orange_color"></i> <span>Players</span></a></li>
                     
                    <li><a href="{{route('block.user.list')}}"><i class="fa fa-user orange_color"></i> <span>Block Player list</span></a></li>
                    
                     <li><a href="{{route('mlmlevel')}}"><i class="fa fa-list red_color"></i> <span>MlM Levels</span></a></li>
                     
                     @php
                         $colourpredictions = DB::select("SELECT * FROM `game_settings` LIMIT 4;");
                     @endphp
                     
                     <li>
                        <a href="#Colour_prediction" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fa fa-object-group blue2_color"></i> <span>Colour_prediction</span></a>
                        <ul class="collapse list-unstyled" id="Colour_prediction">
                           @foreach($colourpredictions as $item)
                           <li><a href="{{route('colour_prediction',$item->id)}}"> <span>{{$item->name}}</span></a></li>
                           @endforeach
                        </ul>
                     </li>
                    <!--@includeIf('admin.body.aviator_sidebar')-->
    <!--            @includeIf('admin.body.dragon_sidebar')-->
				<!--@includeIf('admin.body.andarbahar_sidebar')-->
					  
					  <!--<li><a href="{{route('plinko')}}"><i class="fa fa-gamepad purple_color2"></i> <span>Plinko</span></a></li>-->
					  
					   @php
                         $game_id = DB::select("SELECT * FROM `game_settings` LIMIT 5;");
	
                       @endphp
					  
					  <li>
                        <a href="#apps-xy" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fa fa-object-group blue2_color"></i> <span>Bet History</span></a>
                        <ul class="collapse list-unstyled" id="apps-xy">
							 @foreach($game_id as $itemm)
                <li><a href="{{route('all_bet_history',$itemm->id)}}"> <span>{{$itemm->name}}</span></a></li>
							 @endforeach
                        </ul>
                     </li>
					  
					    <!--<li><a href="{{route('first.deposit.bonus')}}"><i class="fa fa-list red_color"></i> <span>First Deposit Bonus</span></a></li>-->
                     <li><a href="{{route('gift')}}"><i class="fa fa-table purple_color2"></i> <span>Gift</span></a></li>
					  <li><a href="{{route('giftredeemed')}}"><i class="fa fa-table purple_color2"></i> <span>Gift Redeemed History</span></a></li>
                    <li><a href="{{route('banner')}}"><i class="fa fa-picture-o" aria-hidden="true"></i> <span> Activity & Banner</span></a></li> 
                    <!-- <li><a href="{{route('feedback')}}"><i class="fa fa-file blue1_color"></i> <span>FeedBack</span></a></li>-->
                     
                     <li><a href="{{route('salary.list')}}"><i class="fa fa-file blue1_color"></i> <span> Salary</span></a></li>
                     
			      <!-- <li><a href="{{route('monthly_salary.list')}}"><i class="fa fa-file blue1_color"></i> <span>Monthly Salary</span></a></li>--!>
                     
					  

		<!--<li><a href="{{route('usdtqr')}}"><i class="fa fa-table purple_color2"></i> -->
  <!--          <span>USDT QR Code</span></a>-->
  <!--      </li>-->
        
        <li>
            <a href="#app13" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fa fa-tasks  green_color"></i>            <span>Payin</span></a>
                <ul class="collapse list-unstyled" id="app13">
                    <li><a href="{{ route('deposit', 1) }}">Pending</a></li>
                    <li><a href="{{ route('deposit', 2) }}">Success</a></li>
                    <li><a href="{{ route('deposit',3) }}">Reject</a></li>
                </ul>
        </li> 
        
            
        <li>
            <a href="#app20" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
            <i class="fa fa-tasks  green_color"></i><span>USDT Payin</span></a>
                <ul class="collapse list-unstyled" id="app20">
                    <li><a href="{{ route('usdt_deposit', 1) }}">Pending</a></li>
                    <li><a href="{{ route('usdt_deposit', 2) }}">Success</a></li>
                    <li><a href="{{ route('usdt_deposit',3) }}">Reject</a></li>
                </ul>
        </li>
        
        <!--<li>-->
        <!--    <a href="#OFFLINE" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">-->
        <!--        <i class="fa fa-tasks  green_color"></i> <span>OFFLINE Payin</span></a>-->
        <!--        <ul class="collapse list-unstyled" id="OFFLINE">-->
        <!--            <li><a href="{{ route('offline_deposit', 1) }}">Pending</a></li>-->
        <!--            <li><a href="{{ route('offline_deposit', 2) }}">Success</a></li>-->
        <!--            <li><a href="{{ route('offline_deposit',3) }}">Reject</a></li>-->
        <!--        </ul>-->
        <!--</li>-->
        
        
        <li>
            <a href="#app21" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fa fa-tasks  green_color"></i> <span> Withdrawal</span></a>
                <ul class="collapse list-unstyled" id="app21">
                    <li><a href="{{ route('widthdrawl', 1) }}">Pending</a></li>
                    <li><a href="{{ route('widthdrawl', 2) }}">Success</a></li>
                    <li><a href="{{ route('widthdrawl',3) }}">Reject</a></li>
                </ul>
        
        

        <li>
            <a href="#app21" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fa fa-tasks  green_color"></i> <span>USDT Withdrawal</span></a>
                <ul class="collapse list-unstyled" id="app21">
                    <li><a href="{{ route('usdt_widthdrawl', 1) }}">Pending</a></li>
                    <li><a href="{{ route('usdt_widthdrawl', 2) }}">Success</a></li>
                    <li><a href="{{ route('usdt_widthdrawl',3) }}">Reject</a></li>
                </ul>
        </li>
  
  
  
				
		<!--<li><a href="{{route('notification')}}"><i class="fa fa-bell  yellow_color"></i> <span>Notification</span></a></li>-->
  <!--      <li><a href="{{route('setting')}}"><i class="fa fa-info-circle  yellow_color"></i> <span>Setting</span></a></li>-->
		<!--<li><a href="{{route('support_setting')}}"><i class="fa fa-info-circle  yellow_color"></i> <span>Support Setting </span></a></li> -->
		<!--<li><a href="{{route('businessSetting.index')}}"><i class="fa fa-warning red_color"></i> <span>Business Setting</span></a></li>-->
  <!--      <li><a href="{{route('change_password')}}"><i class="fa fa-warning red_color"></i> <span>Change Password</span></a></li>-->
        <li><a href="{{route('auth.logout')}}"><i class="fa fa-line-chart yellow_color"></i> <span>Logout</span></a></li>
                    
                     {{-- <li>
                        <a href="contact.html">
                        <i class="fa fa-paper-plane red_color"></i> <span>Contact</span></a>
                     </li>
                     <li class="active">
                        <a href="#additional_page" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fa fa-clone yellow_color"></i> <span>Additional Pages</span></a>
                        <ul class="collapse list-unstyled" id="additional_page">
                           <li>
                              <a href="profile.html">> <span>Profile</span></a>
                           </li>
                           <li>
                              <a href="project.html">> <span>Projects</span></a>
                           </li>
                           <li>
                              <a href="login.html">> <span>Login</span></a>
                           </li>
                           <li>
                              <a href="404_error.html">> <span>404 Error</span></a>
                           </li>
                        </ul>
                     </li>
                     <li><a href="map.html"><i class="fa fa-map purple_color2"></i> <span>Map</span></a></li>
                     <li><a href="charts.html"><i class="fa fa-bar-chart-o green_color"></i> <span>Charts</span></a></li>
                     <li><a href="settings.html"><i class="fa fa-cog yellow_color"></i> <span>Settings</span></a></li> --}}
                  </ul>
               </div>
            </nav>
            <!-- end sidebar -->