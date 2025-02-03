@extends('admin.body.adminmaster')

@section('admin')


<div class="container mt-5">
  <div class="row ">
	  <div class="col-md-6">
  <div class="white_shd full margin_bottom_30">
     <div class="full graph_head">
        <div class="heading1 margin_0 d-flex">
           <h2>Results</h2>  
			<div class="row" style=" padding-left:30px;" id="gmsno">    </div>    
        </div>
     </div>
     <div class="container">
        
		  <form action="{{route('aviator.store')}}" enctype="multipart/form-data" method="post">
            @csrf
		 <div class="row">
              <div class="col-md-5">
                    <div class="form-group">                                         
                        <label for="parsantage">Game period</label>
						 <input type="hidden" name="game_id"  value="{{$game_id}}">
                        <input type="number" class="form-control"  name="game_sr_num"  value="{{$results->game_sr_num + 2}}">
                     </div>
                </div>
		        <div class="col-md-4">
		            <div class="form-group">                                         
                        <label for="multiplier">Multiplier</label>
                         <input type="number" step="any" class="form-control" name="multiplier">
                     </div>
			    </div>
			   <div class="col-md-3" style="margin-top:28px;">
		            <div class="form-group">                                         
                        <label for="parsantage"></label>
                         <button type="submit" name="submit" class="btn btn-primary btn-sm">submit</button>
                     </div>
			    </div>
		 </div>
			  	 </form>
	 <form action="{{ route('aviator_percentage.update')}}" enctype="multipart/form-data" method="post">
		 @csrf
		 <div class="row">
		  <div class="col-md-5">
                    <div class="form-group">                                         
                        <label for="parsantage">Percentage %</label>
						 <input type="hidden" name="game_id"  value="{{$game_id}}">
                        <input type="number" step="any" class="form-control"  name="winning_percentage"  value="{{$results->winning_percentage}}">
                     </div>
                </div>
			   <div class="col-md-3" style="margin-top:28px;">
		            <div class="form-group">                                         
                        <label for="parsantage"></label>
                         <button type="submit" name="submit" class="btn btn-primary btn-sm">submit</button>
                     </div>
			    </div>
			 <div class="col-md-2" style="margin-top:28px;">
			          <label for="parsantage"></label>
                      <a href=""><i class="fa fa-refresh" aria-hidden="true" style="font-size:30px;"></i></a>
			 </div>
			 <div class="col-md-2"></div>
		 </div>
		 </form> 
		 
		 
     </div>
  </div>
	  </div>
	  
<!--	  <div class="col-md-6">-->
<!--  <div class="white_shd full margin_bottom_30">-->
<!--     <div class="full graph_head">-->
<!--        <div class="heading1 margin_0 d-flex">-->
<!--           <h2>Results</h2>  -->
<!--			<div class="row" style=" padding-left:30px;" id="gmsno">    </div>-->
		                    
                  
<!--        </div>-->
<!--     </div>-->
<!--<form class="p-4" method="post" action="https://winplay.apponrent.in/import_excel_php/import.php" enctype="multipart/form-data">-->
<!--                        <div class="mb-3">-->
<!--                            <label for="exampleDropdownFormEmail1" class="form-label">File</label>-->
<!--                            <input type="file" class="form-control" id="exampleDropdownFormEmail1" name="excel_file" accept=".csv">-->
<!--                        </div>-->
<!--                        <input type="submit" class="btn btn-sm btn-primary" name="import" value="Import">-->
                        
<!--                    </form>-->
<!--  </div>-->
<!--</div>-->

</div>
	  
</div> 



<!--Aviator result Table-->
<div class="container-fluid">
  <div class="row">
<div class="col-md-12">
  <div class="white_shd full margin_bottom_30">
     <div class="full graph_head">
        <div class="heading1 margin_0 d-flex">
           <h2>Aviator Result</h2>
        </div>
     </div>
     <div class="table_section padding_infor_info">
        <div class="table-responsive-sm">
           <table id="exam" class="table table-striped" style="width:100%">
              <thead class="thead-dark">
                 <tr>
                    <th>Id</th>
                    <th>Game Serial No</th>
                    <th>Result</th>
                    <th>Date</th>
                 </tr>
              </thead>
              <tbody>
                @foreach($aviator_res as $item)
                 <tr>
                    <td>{{$item->id}}</td>
                    <td>{{$item->game_sr_num}}</td>
                    <td>{{$item->price}}</td>
                    <td>{{$item->created_at}}</td>
                    
                 </tr>
                 @endforeach
              </tbody>
           </table>
			
			
			<nav aria-label="Page navigation example">
    <ul class="pagination justify-content-center">
        <li class="page-item {{ $aviator_res->onFirstPage() ? 'disabled' : '' }}">
            <a class="page-link" href="{{ $aviator_res->url(1) }}" aria-label="First">
                <span aria-hidden="true">&laquo;&laquo;</span>
            </a>
        </li>
        <li class="page-item {{ $aviator_res->onFirstPage() ? 'disabled' : '' }}">
            <a class="page-link" href="{{ $aviator_res->previousPageUrl() }}" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>

        @php
            $half_total_links = floor(9 / 2);
            $from = $aviator_res->currentPage() - $half_total_links;
            $to = $aviator_res->currentPage() + $half_total_links;

            if ($aviator_res->currentPage() < $half_total_links) {
                $to += $half_total_links - $aviator_res->currentPage();
            }

            if ($aviator_res->lastPage() - $aviator_res->currentPage() < $half_total_links) {
                $from -= $half_total_links - ($aviator_res->lastPage() - $aviator_res->currentPage()) - 1;
            }
        @endphp

        @for ($i = $from; $i <= $to; $i++)
            @if ($i > 0 && $i <= $aviator_res->lastPage())
                <li class="page-item {{ $aviator_res->currentPage() == $i ? 'active' : '' }}">
                    <a class="page-link" href="{{ $aviator_res->url($i) }}">{{ $i }}</a>
                </li>
            @endif
        @endfor

        <li class="page-item {{ $aviator_res->hasMorePages() ? '' : 'disabled' }}">
            <a class="page-link" href="{{ $aviator_res->nextPageUrl() }}" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
        <li class="page-item {{ $aviator_res->currentPage() == $aviator_res->lastPage() ? 'disabled' : '' }}">
            <a class="page-link" href="{{ $aviator_res->url($aviator_res->lastPage()) }}" aria-label="Last">
                <span aria-hidden="true">&raquo;&raquo;</span>
            </a>
        </li>
    </ul>
</nav>
        </div>
     </div>
  </div>
</div>
</div>
</div> 


<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
   <script>
    function fetchData() {
		
        var gameid = {{ $results->game_sr_num }};
	  
        fetch('/aviator_fetchs/' + gameid)
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
		gmsno ='<b style="font-size: 30px; ">Period No - ' + item.game_sr_num + '</b>';
		gmssno=item.game_sr_num;

    });

    $('#amounts-container').html(amountdetailHTML);
	 $('#gmsno').html(gmsno);
	    $('#gmsssno').html(gmssno);
}
    function updateGameId(gameid) {
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



@endsection