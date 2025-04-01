@extends('admin.body.adminmaster')

@section('admin')
<div class="container mt-5">
    <h2>USDT  Details Management</h2>
    
     <form method="GET" action="{{ route('usdt.details') }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="{{ route('usdt.details') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Name</th>
                <th>USDT Wallet Address</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($usdt_details as $usdt)
                <tr>
                    <td>{{ $usdt->id }}</td>
                    <td>{{ $usdt->user_id }}</td>
                    <td>{{ $usdt->name }}</td>
                    <td>{{ $usdt->usdt_wallet_address }}</td>
                    <td>
                        <button class="btn btn-warning btn-sm editBank" 
                            data-id="{{ $usdt->id }}" 
                            data-user_id="{{ $usdt->user_id }}" 
                            data-name="{{ $usdt->name }}" 
                            data-wallet="{{ $usdt->usdt_wallet_address }}">
                            Edit
                        </button>
                        <button class="btn btn-danger btn-sm deleteBank" 
                            data-id="{{ $usdt->id }}">
                            Delete
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Custom Pagination with Page Numbers and Next/Previous Buttons -->
    <div class="d-flex justify-content-center">
        <nav>
            <ul class="pagination">
                <!-- Previous Button -->
                @if ($usdt_details->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link">Previous</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $usdt_details->previousPageUrl() }}">Previous</a>
                    </li>
                @endif

                <!-- Page Numbers -->
                @for ($i = 1; $i <= $usdt_details->lastPage(); $i++)
                    <li class="page-item {{ ($usdt_details->currentPage() == $i) ? 'active' : '' }}">
                        <a class="page-link" href="{{ $usdt_details->url($i) }}">{{ $i }}</a>
                    </li>
                @endfor

                <!-- Next Button -->
                @if ($usdt_details->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $usdt_details->nextPageUrl() }}">Next</a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link">Next</span>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
    
</div>

<!-- Modal for Editing Bank Details -->
<div class="modal fade" id="editUsdtModal" tabindex="-1" aria-labelledby="editUsdtModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUsdtModalLabel">Update Bank Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateUsdtForm">
                    @csrf
                    <input type="hidden" id="bank_id" name="id">
                    
                    <div class="mb-3">
                        <label class="form-label">User ID</label>
                        <input type="text" id="user_id" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" id="name" name="name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">USDT Wallet Address</label>
                        <input type="text" id="usdt_wallet_address" name="usdt_wallet_address" class="form-control">
                    </div>
             
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Open Modal and Fill Data
    $('.editBank').click(function() {
        $('#bank_id').val($(this).data('id'));
        $('#user_id').val($(this).data('user_id'));
        $('#name').val($(this).data('name'));
        $('#usdt_wallet_address').val($(this).data('wallet')); // Fixed wallet address field
        
        $('#editUsdtModal').modal('show');
    });

    // Update USDT Details using AJAX
    $('#updateUsdtForm').submit(function(e) {
        e.preventDefault();

        $.ajax({
            url: "{{ route('usdt.details.update') }}", // Ensure route is correct in web.php
            method: "POST",
            data: $(this).serialize(),
            success: function(response) {
                alert(response.success); // Show success message
                $('#editUsdtModal').modal('hide'); // Hide modal
                location.reload(); // Reload page to reflect changes
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText); // Log error details
                alert("Error updating USDT details. Check console for more info.");
            }
        });
    });

    // Delete USDT Wallet Entry
    $('.deleteBank').click(function() {
        let id = $(this).data('id');
        if (confirm('Are you sure you want to delete this entry?')) {
            $.ajax({
                url: "{{ route('usdt.details.delete') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                },
                success: function(response) {
                    alert(response.success);
                    location.reload();
                },
                error: function(xhr, status, error) {
                    console.log(xhr.responseText);
                    alert("Error deleting USDT details. Check console for more info.");
                }
            });
        }
    });
});
</script>
@endsection
