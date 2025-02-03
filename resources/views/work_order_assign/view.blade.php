@extends('admin.body.adminmaster')

@section('admin')

<script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/ckeditor.js"></script>
 
 <div class="container-fluid mt-5">
    <div class="row">
        <div class="col-md-12">
            

  
 <form  method="post" action="{{route('setting.update',$views->id)}}">
   @csrf
 
     <input type="hidden" name="id" value="{{$views->id}}" >
     
       <textarea id="editor" class="form-control" name="description">{{$views->description}}</textarea>
      <br><br>
      <div>
            <button class="btn btn-primary" type="submit" style="float:right; margin-right:5%;">Submit</button>
     </div>
 </form>
 
 </div></div></div>
 
<script>

ClassicEditor
            .create( document.querySelector( '#editor' ) )
            .catch( error => {
                console.error( error );
            } );

</script>
 @endsection