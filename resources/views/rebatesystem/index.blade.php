@extends('admin.body.adminmaster')

@section('admin')
<div class="container mt-5">
    <h2>Illegal Bettor Management</h2>
	<form method="GET" action="{{ route('rebate.details') }}" class="mb-3">
	<div class="row">
            <div class="col-md-4">
                <input type="text" name="u_id" class="form-control" placeholder="Enter User ID" 
                       value="{{ request('u_id') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="{{ route('rebate.details') }}" class="btn btn-secondary">Reset</a>
            </div>
        </div>
	</form>

    <!-- Table for displaying results -->
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Total Illegal Count</th>
                <th>Betting Rebate</th>
                <th>Action</th>
				<th>Status</th>
            </tr>
        </thead>
        <tbody>
    @foreach($users as $user)
        <tr id="user-row-{{ $user->id }}">
            <td>{{ $user->id }}</td>
            <td>{{ $user->u_id }}</td>
            <td>{{ $user->illegal_count }}</td>
            <td>
                <p><strong>Recharge: ₹<span id="recharge-{{ $user->id }}">{{ $user->recharge }}</span></strong></p> 
                <button type="button" class="btn btn-success increase-btn" data-bs-toggle="modal" data-bs-target="#increaseModal" data-user="{{ $user->id }}">+</button>
                <button type="button" class="btn btn-danger decrease-btn" data-bs-toggle="modal" data-bs-target="#decreaseModal" data-user="{{ $user->id }}">-</button>
            </td>
            <td>
                @if($user->status == 0) 
                    <span class="badge badge-danger">Blocked</span>
                @else
                    <span class="badge badge-success">Active</span>
                @endif
            </td>
            <td>
                <button class="btn status-btn {{ $user->status == 0 ? 'btn-danger' : 'btn-success' }}" data-user="{{ $user->id }}" data-status="{{ $user->status }}">
                    {{ $user->status == 0 ? 'Unblock' : 'Block' }}
                </button>
            </td>
        </tr>
    @endforeach
</tbody>

    </table>
    
    <!-- Pagination Links -->
<!--<div class="d-flex justify-content-center">-->
<!--    {{ $users->links() }}-->
<!--</div>-->
<div class="d-flex justify-content-center mt-3">
    {{ $users->onEachSide(1)->links('pagination::bootstrap-4') }}
</div>    
</div>

<!-- Increase Modal -->
<div class="modal fade" id="increaseModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Increase Recharge Amount</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="increaseUserId">
                <input type="number" id="increaseAmount" class="form-control" min="1" required placeholder="Enter amount">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmIncrease">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Decrease Modal -->
<div class="modal fade" id="decreaseModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Decrease Recharge Amount</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="decreaseUserId">
                <input type="number" id="decreaseAmount" class="form-control" min="1" required placeholder="Enter amount">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmDecrease">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {

    // Open Increase Modal
   $(".increase-btn").click(function() {
        var userId = $(this).data("user");
        $("#increaseUserId").val(userId);
        $("#increaseModal").modal("show");
    });

    $(".decrease-btn").click(function() {
        var userId = $(this).data("user");
        $("#decreaseUserId").val(userId);
        $("#decreaseModal").modal("show");
    });


    // AJAX Request for Increase
    $("#confirmIncrease").click(function() {
        var userId = $("#increaseUserId").val();
        var amount = $("#increaseAmount").val();

        if (amount === "" || amount <= 0) {
            alert("Enter a valid amount.");
            return;
        }

        $.ajax({
            url: "{{ route('rebate.update') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                user_id: userId,
                amount: amount,
                action: "increase"
            },
            success: function(response) {
                if (response.success) {
                    $("#increaseModal").modal("hide");
                    $("#recharge-" + userId).text(response.new_balance);
                } else {
                    alert(response.error);
                }
            }
        });
    });

    // AJAX Request for Decrease
    $("#confirmDecrease").click(function() {
        var userId = $("#decreaseUserId").val();
        var amount = $("#decreaseAmount").val();

        if (amount === "" || amount <= 0) {
            alert("Enter a valid amount.");
            return;
        }

        $.ajax({
            url: "{{ route('rebate.update') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                user_id: userId,
                amount: amount,
                action: "decrease"
            },
            success: function(response) {
                if (response.success) {
                    $("#decreaseModal").modal("hide");
                    $("#recharge-" + userId).text(response.new_balance);
                } else {
                    alert(response.error);
                }
            }
        });
    });

});
</script>


@endsection
