@extends('admin.body.adminmaster')

@section('admin')



<div class="container-fluid mt-5">
    <form action="{{ route('colour_prediction.store') }}" method="post">
        @csrf
        <!-- Your existing form content -->
        <input type="hidden" name="game_id"  value="{{$gameid}}">
        <input type="hidden" name="games_no"  value="{{$bets[0]->games_no}}">

        <div class="row">
            <div class="col-md-12">
                <div class="white_shd full margin_bottom_30">
                    <div class="full graph_head">
                        <div class="">
                            <div class="row" style=" padding-left:30px;" id="gmsno">                       
                            </div>
                        </div>
                    </div>
                    <div class="row" style="padding-top: 30px;  padding-bottom:20px;">
                        @foreach ($bets as  $key=>$item)
						
                        @if($gameid == 1 || $gameid == 2 || $gameid == 3 ||$gameid == 4) 
						
                            @if($item->number =='1' || $item->number =='3'||$item->number =='7'||$item->number =='9')
						
                                <div class="card col-md-1 ml-3 mt-4 " style="background-color:#008000; height:60px;"  > <center><h1 class="text-white">{{$key}}</h1></center>
                               @elseif($item->number =='5')     
                                <div class="card col-md-1 ml-3 mt-4 " style="background-image: linear-gradient(to right, green , purple);"><center><h1 class="text-white">{{$key}}</h1></center>
                               @elseif($item->number =='0')        
                                <div class="card col-md-1 ml-3 mt-4 " style="background-image: linear-gradient(to right, red , purple);"><center><h1 class="text-white">{{$key}}</h1></center>
                               @else
                                <div class="card col-md-1 ml-3 mt-4 " style="background-color:#ff0000"><center><h1 class="text-white">{{$key}}</h1></center>
                            @endif
                        @else
                             @if($item->number =='1')        
                                <div class="card col-md-3 ml-3 mt-4 " style="background-image: linear-gradient(to right, red , purple);">		                       		
                                @elseif($item->number =='2')        
                                <div class="card col-md-3 ml-3 mt-4 " style="background-image: linear-gradient(to right, green , purple);">
						    @elseif($item->number =='3')        
                                <div class="card col-md-3 ml-3 mt-4 " style="background-image: linear-gradient(to right, yellow , purple);">	    
                                    @else
                                <div class="card col-md-1 ml-3 mt-4 " style="background-color:#ff0000">
                            @endif
                            @endif
                                <?php $gamid= $item->games_no;?>
                                @if($gameid==10)
                                <div class="card-body">
                                    <b style="font-size: 20px; margin-left:12px; color: white;"> 
										 @if($item->number == 1)
										 Dragan</b>
									     @elseif($item->number == 2)
									      Tiger </b>
									  @elseif($item->number == 3)
									      Tie</b>
										@else
										{{ $item->number }}</b>
										@endif
										
                                </div>
                                @else
                                @endif
									 @if($gameid==13)
                                <div class="card-body">
                                    <b style="font-size: 20px; margin-left:12px; color: white;"> 
										 @if($item->number == 1)
										 Andar</b>
									     @elseif($item->number == 2)
									      Bahar </b>
										@else
										{{ $item->number }}</b>
										@endif
										
                                </div>
                                @else
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="row" style="  padding-bottom:20px;" id="amounts-container">
                    </div>

                    <div class="row ml-4 d-flex" style="margin-bottom: 20px;">
                        
                         <input type="hidden" name="game_id"  value="{{$gameid}}">
                         
                         <div class="col-md-3 form-group d-flex">
                            <input type="text" name="game_no" class="form-control" placeholder="Period" value ="<?php echo $gamid;?>">
                        </div>
                        
                        @if($gameid == 1 || $gameid == 2 || $gameid == 3 ||$gameid == 4)
                         {{--<div class="col-md-3 form-group d-flex">
   <input type="number" name="number" class="form-control" min="0" max="9" placeholder="Result">
                        </div>--}}
						<div class="col-md-3 form-group d-flex">
                            <select name="number" class="form-control">
                                <option value="">Select Result</option>
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                            </select>
                        </div>
						@elseif($gameid == 10)
						 <div class="col-md-3 form-group d-flex">
                       <select type="number" name="number" class="form-control" placeholder="Result">
						   <option value="1"><b>Dragan</b></option>
						   <option value="2"><b>Tiger</b></option>
						   <option value="3"><b>Tie</b></option>
							 </select>
                        </div>
						@else($gameid == 13)
						 <div class="col-md-3 form-group d-flex">
                       <select type="number" name="number" class="form-control" placeholder="Result">
						   <option value="1"><b>Andar</b></option>
						   <option value="2"><b>Bahar</b></option>
						  
							 </select>
                        </div>
						@endif
						
                        <div class="col-md-2 form-group d-flex">
                          <button type="submit" class="form-control btn btn-info"><b>Submit</b></button>
                        </div>
                        <div class="col-md-2 form-group d-flex mt-1">
                            <a href=""> <i class="fa fa-refresh" aria-hidden="true" style="font-size:30px;"></i></a>
                        </div>
                    </div>
</form>
                   
               
    
					
					 <form action="{{ route('colour_percentage.update') }}" method="post">
                        @csrf
                        <div class="row" style="padding-left:30px;">
                            <div class="col-md-3 form-group d-flex">
                                <input type="hidden" name="id" value="{{ $gameid }}">
                                <input type="text" name="parsantage" value="{{ $bets[0]->parsantage }}" class="form-control" placeholder="Percentage">
                                 <span><b>%</b></span>
                            </div>
                    <div class="row">
                    @error('game_no')
                            <div class="alert alert-danger col-sm-6">{{ $message }}</div>
                    @enderror
                    </div>
                            <div class="col-md-2 form-group">
                                <button type="submit" class="form-control btn btn-info"><b>Submit</b></button>
                            </div>
                        </div>
                    </form>
									 </div>
            </div>
        </div>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
   <script>
    function fetchData() {
        var gameid = {{ $gameid }};
        fetch('/fetch/' + gameid)
            .then(response => response.json())
            .then(data => {
                console.log('Fetched data:', data);
                // Assuming data has 'bets' and 'gameid' properties
                updateBets(data.bets);
                updateGameId(data.gameid);
            })
            .catch(error => console.error('Error fetching data:', error));
    }

   function updateBets(bets) {
    console.log('Updated Bets:', bets);
    var amountdetailHTML = '';
	   var gmsno='';
 var gmssno='';
 
    bets.forEach(item => {
        amountdetailHTML += '<div class="card col-md-1 ml-3 mt-4 " style="background-color:#fff;">';
        amountdetailHTML += '<div class="card-body">';
        amountdetailHTML += '<b style="font-size: 10px; ">' + item.amount + '</b>';
        amountdetailHTML += ' </div>';
        amountdetailHTML += '</div>';
		gmsno ='<b style="font-size: 30px; ">Period No - ' + item.games_no + '</b>';
		gmssno=item.games_no;

    });

    $('#amounts-container').html(amountdetailHTML);
	 $('#gmsno').html(gmsno);
	    $('#gmsssno').html(gmssno);
}
    function updateGameId(  ) {
        // Replace the following line with your actual DOM update logic
        // For example, you may update an element with id 'gameid'
        // $('#gameid').html(...);

        // For now, let's just log the gameid to the console
        console.log('Updated Game ID:', gameid);
    }

    function refreshData() {
        fetchData();
        setInterval(fetchData, 5000); // 5000 milliseconds = 5 seconds
    }

    document.addEventListener('DOMContentLoaded', refreshData);
</script>
<script type="text/javascript">    
    setInterval(page_refresh, 1*60000); //NOTE: period is passed in milliseconds
</script>
</div>
@endsection
