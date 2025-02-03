@extends('admin.body.adminmaster')

@section('admin')

<div class="container-fluid mt-5">
    <div class="row">
<div class="col-md-12">
    <div class="white_shd full margin_bottom_30">
       <div class="full graph_head">
          <div class="d-flex justify-content-between">
             <h2>First Recharge Bonus</h2>
            
          </div>
       </div>
       <div class="table_section padding_infor_info">
          <div class="table-responsive-sm">
             <table id="example" class="table table-striped" style="width:100%">
                <thead class="thead-dark">
                   <tr>
                      <th>Id</th>
                      <th>Recharge min</th>
                      <th>Recharge max</th>
                      <th>Member</th>
                      <th>Agent</th>
					   <th>Action</th>
                   </tr>
                </thead>
                <tbody>
                  @foreach($first_deposit_bonus as $item)
                   <tr>
                      <td>{{$item->id}}</td>
                      <td>{{$item->recharge_min}}</td>
                      
                      <td>{{$item->recharge_max}}</td>
                      <td>{{$item->member}}</td>
					   <td>{{$item->agent}}</td>
					   
                 
                      
                      <td>
                      <i class="fa fa-edit mt-1" data-toggle="modal" data-target="#exampleModalCenterupdate1{{$item->id}}" style="font-size:30px"></i>  
                      </td>
					   
					    {{-- edit form --}}
                      <div class="modal fade" id="exampleModalCenterupdate1{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title" id="exampleModalLongTitle">First Recharge Bonus</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <form action="{{route('first_deposit_bonus.update',$item->id)}}" method="post" enctype="multipart/form-data">
                              @csrf
                            <div class="modal-body">
                              <div class="container-fluid">
                                <div class="row">
                                  <div class="form-group col-md-6">
                                    <label for="name">Recharge Min</label>
                                    <input type="text" class="form-control" id="name" name="recharge_min" value="{{$item->recharge_min}}" placeholder="Enter name">
                                    @error('recharge_min')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                  </div>
                                  <div class="form-group col-md-6">
                                    <label for="count">Recharge Max</label>
                                    <input type="text" class="form-control" id="recharge_max" value="{{$item->recharge_max}}" name="recharge_max" placeholder="Enter count">
                                    @error('recharge_max')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                  </div>
                                  
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="commission">Member</label>
                                        <input type="text" class="form-control" name="member" value="{{$item->member}}" id="commission" placeholder="">
                                        
                                        @error('member')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                      </div>
									 <div class="form-group col-md-6">
                                        <label for="commission">Agent</label>
                                        <input type="text" class="form-control" name="agent" value="{{$item->agent}}" id="commission" placeholder="">
                                        
                                        @error('agent')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
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
{{-- popup modal form --}}



<script>
    $('#myModal').on('shown.bs.modal', function () {
  $('#myInputs').trigger('focus')
})
</script>
 @endsection