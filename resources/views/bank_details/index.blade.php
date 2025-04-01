@extends('admin.body.adminmaster')

@section('admin')
<div class="container mt-5">
    <h2>Bank Details Management</h2>
    
    <!-- Search Form -->
    <form method="GET" action="{{ route('bank.details') }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="{{ route('bank.details') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Name</th>
                <th>Account Number</th>
                <th>Bank Name</th>
                <th>IFSC Code</th>
                <th>UPI ID</th>
                <th>Branch Name</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bank_details as $bank)
                <tr>
                    <td>{{ $bank->id }}</td>
                    <td>{{ $bank->userid }}</td>
                    <td>{{ $bank->name }}</td>
                    <td>{{ $bank->account_num }}</td>
                    <td>{{ $bank->bank_name }}</td>
                    <td>{{ $bank->ifsc_code }}</td>
                    <td>{{ $bank->upi_id }}</td>
                    <td>{{ $bank->branch_name }}</td>
                    <td>
                        <!-- Edit Button -->
                        <button class="btn btn-warning btn-sm editBank" 
                            data-id="{{ $bank->id }}" 
                            data-userid="{{ $bank->userid }}" 
                            data-name="{{ $bank->name }}" 
                            data-account="{{ $bank->account_num }}" 
                            data-bank="{{ $bank->bank_name }}" 
                            data-ifsc="{{ $bank->ifsc_code }}" 
                            data-upi_id="{{ $bank->upi_id }}" 
                            data-branch="{{ $bank->branch_name }}">
                            Edit
                        </button>

                        <!-- Delete Button -->
                        <button class="btn btn-danger btn-sm deleteBank" 
                            data-id="{{ $bank->id }}">
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
                @if ($bank_details->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link">Previous</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $bank_details->previousPageUrl() }}">Previous</a>
                    </li>
                @endif

                <!-- Page Numbers -->
                @for ($i = 1; $i <= $bank_details->lastPage(); $i++)
                    <li class="page-item {{ ($bank_details->currentPage() == $i) ? 'active' : '' }}">
                        <a class="page-link" href="{{ $bank_details->url($i) }}">{{ $i }}</a>
                    </li>
                @endfor

                <!-- Next Button -->
                @if ($bank_details->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $bank_details->nextPageUrl() }}">Next</a>
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
<div class="modal fade" id="editBankModal" tabindex="-1" aria-labelledby="editBankModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editBankModalLabel">Update Bank Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateBankForm">
                    @csrf
                    <input type="hidden" id="bank_id" name="id">
                    
                    <div class="mb-3">
                        <label class="form-label">User ID</label>
                        <input type="text" id="userid" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" id="name" name="name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account Number</label>
                        <input type="text" id="account_num" name="account_num" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bank Name</label>
                        <input type="text" id="bank_name" name="bank_name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">IFSC Code</label>
                        <input type="text" id="ifsc_code" name="ifsc_code" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">UPI ID</label>
                        <input type="text" id="upi_id" name="upi_id" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Branch Name</label>
                        <input type="text" id="branch_name" name="branch_name" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteBankModal" tabindex="-1" aria-labelledby="deleteBankModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this bank detail?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Open Edit Modal and Fill Data
    $('.editBank').click(function() {
        $('#bank_id').val($(this).data('id'));
        $('#userid').val($(this).data('userid'));
        $('#name').val($(this).data('name'));
        $('#account_num').val($(this).data('account'));
        $('#bank_name').val($(this).data('bank'));
        $('#ifsc_code').val($(this).data('ifsc'));
        $('#upi_id').val($(this).data('upi_id'));
        $('#branch_name').val($(this).data('branch'));

        $('#editBankModal').modal('show');
    });

    // Update Bank Details using AJAX
    $('#updateBankForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: "{{ route('bank.details.update') }}",
            method: "POST",
            data: $('#updateBankForm').serialize(),
            success: function(response) {
                alert(response.success);
                $('#editBankModal').modal('hide');
                location.reload();
            },
            error: function(xhr) {
                alert("Error updating bank details.");
            }
        });
    });

    // Open Delete Modal and Set ID
    let deleteId = null;
    $('.deleteBank').click(function() {
        deleteId = $(this).data('id');
        $('#deleteBankModal').modal('show');
    });

    // Confirm Delete AJAX
    $('#confirmDelete').click(function() {
        if (deleteId) {
            $.ajax({
                url: "{{ route('bank.details.delete') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: deleteId
                },
                success: function(response) {
                    alert(response.success);
                    $('#deleteBankModal').modal('hide');
                    location.reload();
                },
                error: function(xhr) {
                    alert("Error deleting bank details.");
                }
            });
        }
    });
});
</script>
@endsection
