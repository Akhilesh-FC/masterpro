@extends('admin.body.adminmaster')

@section('admin')

    <style>
  @import url("https://fonts.googleapis.com/css?family=Montserrat:400,400i,700");

body {
	background-color: #111;
	display: flex;
	align-items: center;
	justify-content: center;
	font-family: montserrat;
}
.dropbtn {
	font-family: montserrat;
	background-color: #222228;
	color: white;
	padding: 5px;
	font-size: 16px;
	border: none;
	border-radius: 10px 10px 10px 10px;
	width: 100px;
	box-shadow: 0px 0px 100px rgba(190, 200, 255, 0.6);
}

/* The container <div> - needed to position the dropdown content */
.dropdown {
	color: black;
	position: relative;
	display: inline-block;
	width: 100px;
	border-radius: 10px 10px 10px 10px;
	z-index: 1;
}

.dropdown-content {
	display: none;
	position: absolute;
	background-color: #222228;
	min-width: 100px;
	z-index: 1;
	border-radius: 0px 0px 14px 14px;
	box-shadow: 0px 0px 100px rgba(190, 200, 255, 0.25);
}

.dropdown-content a {
	color: white;
	padding: 5px 8px;
	text-decoration: none;
	display: block;
	border-radius: 10px;
	margin: 2px;
}

/* Change color of dropdown links on hover */
.dropdown-content a:hover {
	background-color: #33333f;
}

/* Show the dropdown menu on hover */
.dropdown:hover .dropdown-content {
	display: block;
}

/* Change the background color of the dropdown button when the dropdown content is shown */
.dropdown:hover .dropbtn {
	background-color: #222228;
	border-radius: 10px 10px 0px 0px;
	border-bottom: none;
}

</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

<div class="container-fluid mt-3">
    <div class="row">
		
<div class="col-md-12">
    <div class="white_shd full margin_bottom_30">
       <div class="full graph_head">
          <div class="heading1 margin_0 d-flex">
             <h2>Withdrawl List</h2>
            <!-- <form action="{{route('widthdrawl.all_success')}}" method="post">-->
            <!--     @csrf-->
            <!--<button type="submit" class="btn btn-primary"  style="margin-left:550px;">All Approve</button> -->
            <!--</form>-->
          </div>
       </div>
       <div class="table_section padding_infor_info">
          <div class="table-responsive-sm">
             <table id="example" class="table table-striped" style="width:100%">
                <thead class="thead-dark">
                   <tr>
                      <th>Id</th>
                      <th>User name</th>
                      <th>Bank name</th>
                      <th>Ac. No.</th>
                      <th>IFSC</th>
                      <th>Amount</th>
                      <!--<th>Actual_amount</th>-->
                      <th>Mobile</th>
                      <th>Status</th>
					   <th>Msg</th>
                      <th>Date</th>
                   </tr>
                </thead>
                <tbody>
                  @foreach($widthdrawls as $item)
                   <tr>
                      <td>{{$item->id}}</td>
                      <td>{{$item->uname}}</td>
                       <td>{{$item->bname}}</td>
                        <td>{{$item->acno}}</td>
                         <td>{{$item->ifsc}}</td>   
                      <td>{{$item->amount}}</td>
                      <!--<td>{{$item->actual_amount}}</td>-->
                      <td>{{$item->mobile}}</td>
                      @if($item->status==1)  
                      <td>
                          
                            <div class="dropdown">
                                <button class="btn btn-warning dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                Pending
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item" href="{{ route('widthdrawl.success', $item->id) }}">Approved</a>
                                    <a class="dropdown-item" data-toggle="modal" data-target="#exampleModalCenter{{$item->id}}">Reject</a>
                                </div>
                            </div>
                          
                          
                    
						  
						  <div class="modal fade" id="exampleModalCenter{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Reject Withdrawal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('widthdrawl.reject', ['id' => $item->id]) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label for="msg">Reason for rejection</label>
                                <textarea class="form-control" id="msg" name="msg" rows="3" placeholder="Enter reason for rejection" required></textarea>
                                @error('msg')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

						  
                      </td>
                     @elseif($item->status==2)
                     <td><button class="btn btn-success">Approved</button></td>
                      @elseif($item->status==3)
                     <td><button class="btn btn-danger">Reject</button></td>
                      @else
                      <td>
                        <!--<select class="form-control">-->
                        <!--  <option>Pending</option>-->
                        <!--  <option>Success</option>-->
                        <!--  <option>Reject</option>-->
                        <!--</select>-->
                      </td> 
                      @endif
                       
                   <td><?php if($item->status==3){echo $item->rejectmsg;}elseif($item->status==1){echo "Pending";}elseif($item->status==2){echo "Success";}; ?></td>
                      <td>{{$item->created_at}}</td>
                   </tr>
                  @endforeach
                </tbody>
             </table>
          </div>
       </div>
    </div>
 </div>
</div>
</div> 


  

<script>
    $('#myModal').on('shown.bs.modal', function () {
  $('#myInputs').trigger('focus')
})
</script> 
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
 @endsection