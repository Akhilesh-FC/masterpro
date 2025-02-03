@extends('admin.body.adminmaster')

@section('admin')

<div class="container-fluid mt-5">
  <div class="row">
    <div class="col-md-12">
      <div class="white_shd full margin_bottom_30">
        <div class="full graph_head">
          <div class="d-flex justify-content-between">
            <h2>Banner List</h2>
            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#exampleModalCenter">Add Banner</button>
          </div>
        </div>
        <div class="table_section padding_infor_info">
          <div class="table-responsive-sm">
            <table id="example" class="table table-striped" style="width:100%">
              <thead class="thead-dark">
                <tr>
                  <th>Sr.No</th>
                  <th>Name</th>
                  <th>Image</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($banner as $key=>$item)
                <tr>
                  <td>{{$key+1}}</td>
                  <td>{{$item->title}}</td>
                  <td><img src="{{URL::asset($item->image)}}" width="50 px" height="50 px"></td>
                  <td>
                    <i class="fa fa-edit mt-1" data-toggle="modal" data-target="#exampleModalCenterupdate1{{$item->id}}" style="font-size:30px"></i>
                    <a href="{{route('banner.delete',$item->id)}}"><i class="fa fa-trash mt-1 ml-1" style="font-size:30px;color:red;"></i></a> 
                  </td>
                </tr>
                <div class="modal fade" id="exampleModalCenterupdate1{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Edit Banner & Activity</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <form action="{{route('banner.update',$item->id)}}" method="POST" enctype="multipart/form-data">
						@csrf
						<div class="modal-body">
						  <div class="container-fluid">
							<div class="row">
							  <div class="form-group col-md-6">
								<label for="image">Name</label>
								<input type="text" class="form-control" id="title" name="title" placeholder=" " value="{{$item->title}}">
							  </div>
							  <div class="form-group col-md-6">
								<label for="image">Image</label>
								<input type="file" class="form-control" id="image" name="image" placeholder=" " value="{{$item->image}}" >
							  </div>
							</div>
							     <div class="row">
             
            
            </div>
						  </div>
						</div>
						<div class="modal-footer">
						  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
						  <button type="submit" class="btn btn-primary">Update</button>
						</div>
					  </form>
                    </div>
                  </div>
                </div>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div> 

<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Add Banner & Activity</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{route('banner.store')}}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="container-fluid">
            <div class="row">
              <div class="form-group col-md-6">
                <label for="image">Name</label>
                <input type="text" class="form-control" id="title" name="title" placeholder=" " required>
              </div>
              <div class="form-group col-md-6">
                <label for="image">Image</label>
                <input type="file" class="form-control" id="image" name="image" placeholder=" " required>
              </div>
            </div>
			   
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Add</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection
