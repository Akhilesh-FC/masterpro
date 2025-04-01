@extends('admin.body.adminmaster')

@section('admin')
<div class="container-fluid mt-5">
    <h2>Payment Limits</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payment_limits as $limit)
            <tr>
                <td>{{ $limit->id }}</td>
                <td>{{ $limit->name }}</td>
                <td>{{ $limit->amount }}</td>
                <td>
                    <button class="btn btn-primary editPayment" 
                        data-id="{{ $limit->id }}" 
                        data-name="{{ $limit->name }}" 
                        data-amount="{{ $limit->amount }}">
                        Edit
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editPaymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Payment Limit</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="updatePaymentForm">
                    @csrf
                    <input type="hidden" id="payment_id" name="id">
                    
                    <div class="form-group">
                        <label>Name:</label>
                        <input type="text" id="payment_name" class="form-control" disabled> 
                    </div>

                    <div class="form-group">
                        <label>Amount:</label>
                        <input type="number" id="payment_amount" name="amount" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-success">Update Amount</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- jQuery & Bootstrap (Required for Modal) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
$(document).ready(function() {
    // Modal Open Fix
    $(document).on('click', '.editPayment', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var amount = $(this).data('amount');

        $('#payment_id').val(id);
        $('#payment_name').val(name);
        $('#payment_amount').val(amount);

        $('#editPaymentModal').modal('show'); // Open Modal
    });

    // Update Payment Limit with AJAX (Only Amount)
    $('#updatePaymentForm').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: "{{ route('payment.limits.update') }}",
            method: "POST",
            data: formData,
            success: function(response) {
                alert(response.success);
                $('#editPaymentModal').modal('hide'); // Close Modal
                location.reload(); // Reload Page
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert("Error updating payment limit.");
            }
        });
    });
});
</script>

@endsection
