@extends('admin.body.adminmaster')

@section('admin')

<div class="container-fluid mt-5">
    <div class="row">
<div class="col-md-12">
    <div class="white_shd full margin_bottom_30">
       <div class="full graph_head">
          <div class="d-flex justify-content-between">
             <h2>Salary</h2>
			  <div> <a href="{{route('salary.list')}}" class="btn btn-info btn-sm"> Salary List</a> </div>
          </div>
       </div>
       <div class="table_section padding_infor_info">
          
			 <form action="{{route('salary.store')}}" method="post">
				 @csrf
			  <div class="row">
				  
				   <div class="col-md-4">
    					<label for="exampleInputEmail1" class="form-label">User </label>
    					<input type="text" list="tot" class="form-control" name="userid"  required>				   
					  
           @php
             $getAllUser=App\Models\User::wherenot('id',1)->get();   
            @endphp
                    <datalist id="tot">
                         @foreach ($getAllUser as $user)
            <option {{old('userid')==$user->id?"selected":""}} value="{{$user->id}}"> {{$user->username.' ( +91'.$user->mobile .' )'}}</option>
            @endforeach
                    </datalist>
					   
					   
					   
     			    </div>
				  
				   <div class="col-md-4">
    					<label for="exampleInputEmail1" class="form-label">Salary type</label>
    					<select  class="form-control" name="salary_type" required>
							
							<option value="1"> Daily Salary </option>
							<option value="2"> Weekly Salary </option>
							<option value="3"> Monthly Salary </option>
							
						</select>
     			    </div>
				  
				   <div class="col-md-4">
    					<label for="exampleInputEmail1" class="form-label">Salary Amount</label>
    					<input type="text" class="form-control" name="salary_amount" required>
     			    </div>

				  </div>
				 
				 
				 <div class="mt-3">
					 <button type="submit" class="btn btn-success btn-sm"> Submit </button>
					 </div>
				
				 
				 </form>
			  
		
			  
			  
           
          </div>
       </div>
    </div>
 </div>
</div>
</div> 
{{-- popup modal form --}}
<!-- Modal -->




<script>
    $('#myModal').on('shown.bs.modal', function () {
  $('#myInputs').trigger('focus')
  })
</script>
 @endsection