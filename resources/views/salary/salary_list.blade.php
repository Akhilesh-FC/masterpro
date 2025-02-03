@extends('admin.body.adminmaster')

@section('admin')

<div class="container-fluid mt-5">
    <div class="row">
        <div class="col-md-12">
            <div class="white_shd full margin_bottom_30">
                <div class="full graph_head">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2>Salary List</h2>
                        <div>
                            <a href="{{route('salary.index')}}" class="btn btn-info btn-sm">Back</a>
                        </div>
                    </div>
                </div>
                <div class="table_section padding_infor_info">
                    <div class="table-responsive-sm">
                        <table id="example" class="table table-striped table-bordered" style="width:100%">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Id</th>
                                    <th>User Mobile</th>
                                    <th>User Name</th>
                                    <th>Amount</th>
                                    <th>Salary Type</th>
                                    <th>Date Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($salary_list as $key => $item)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $item->mobile }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->salary }}</td>
                                        <td>
                                            @if($item->salary_type == 1)
                                                Daily Salary
                                            @elseif($item->salary_type == 2)
                                                Monthly Salary
                                            @elseif($item->salary_type == 3)
                                                Hourly Salary
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d M Y, h:i A') }}</td>

                                        <td>
                                            @if($item->status == 0)
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @elseif($item->status == 1)
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($item->status == 2)
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="badge bg-secondary">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->status == 0)
                                                <a href="{{ route('salary.approve', $item->id) }}" class="btn btn-success btn-sm">Approve</a>
                                                <a href="{{ route('salary.reject', $item->id) }}" class="btn btn-danger btn-sm">Reject</a>
                                            @elseif($item->status == 1)
                                                <span class="btn btn-secondary btn-sm" disabled>Approved</span>
                                            @elseif($item->status == 2)
                                                <span class="btn btn-secondary btn-sm" disabled>Rejected</span>
                                            @else
                                                <span class="btn btn-secondary btn-sm" disabled>N/A</span>
                                            @endif
                                        </td>
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

@endsection
