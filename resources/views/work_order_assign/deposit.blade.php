@extends('admin.body.adminmaster')

@section('admin')

<div class="container-fluid mt-5">
    <div class="row">
<div class="col-md-12">
    <div class="white_shd full margin_bottom_30">
       <div class="full graph_head">
          <div class="heading1 margin_0 d-flex">
             <h2>Deposit List</h2>
             {{-- <button type="button" class="btn btn-info" data-toggle="modal" data-target="#exampleModalCenter" style="margin-left:620px;">Add Work Name</button> --}}
          </div>
       </div>
       <div class="table_section padding_infor_info">
          <div class="table-responsive-sm">
             <table id="example" class="table table-striped" style="width:100%">
                <thead class="thead-dark">
                   <tr>
                      <th>Id</th>
					   <th>User Id</th>
                      <th>User Name</th>
					  <th>Mobile</th>
                      <th>Order Id</th>
                      <th>Amount</th>
                      <th>Status</th>
                      <th>Date</th>
                   </tr>
                </thead>
                <tbody>
                  @foreach($deposits as $item)
                  
                   <tr>
                      <td>{{$item->id}}</td>
					  <td>{{$item->userid}}</td>
                      <td>{{$item->uname}}</td>
					  <td>{{$item->mobile}}</td>
                      <td>{{$item->order_id}}</td>
                      @if($item->type == '2')
                        <td> $ {{$item->usdt_amount}} - ₹ {{$item->cash}}</td>
                      @else
                        <td>{{$item->cash}}</td>
                      @endif
					   @if($item->status=='2')
                      <td><div class="btn btn-success btn-sm">Approved</div></td>
					   @elseif($item->status=='3')
					   <td><div class="btn btn-danger btn-sm">Reject</div></td>
					   @elseif($item->status=='1')
					   <td><div class="btn btn-warning btn-sm">Pending</div></td>
					   @else
						  <td></td>
					   @endif
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


 @endsection