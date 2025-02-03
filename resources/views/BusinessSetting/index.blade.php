@extends('admin.body.adminmaster')

@section('admin')
    <div class="container-fluid mt-3" style="margin-bottom: 60px;">
        <form action="{{ route('businessSettingUpdate', $businessSetting->id) }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="card">
                <div class="card-body">

                    <h3>Business Setup</h3>
                    <hr>
                    <div class="card-body">
                        <div class="form">
                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <label>Project Name</label>
                                    <input type="text" class="form-control" name="project_name"
                                        value="{{ isset($businessSetting) ? $businessSetting->project_name : old('project_name') }}">
                                        @error('project_name')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                                </div>

                                <div class="col-sm-6">
                                    <label for="">Project Title</label>
                                    <input type="text" class="form-control" name="project_title"
                                        value="{{ isset($businessSetting) ? $businessSetting->project_title : old('project_title') }}">
                                        @error('project_title')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                                </div>
                            </div>
                        </div>



                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <label>Payment Key</label>
                                <input type="text" class="form-control" name="payment_key"
                                    value="{{ isset($businessSetting) ? $businessSetting->payment_key : old('payment_key') }}">
                                    @error('payment_key')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                             <div class="col-sm-6">
                                <label>Merchant Token</label>
                                <input type="text" class="form-control" name="merchant_token"
                                    value="{{ isset($businessSetting) ? $businessSetting->merchant_token : old('merchant_token') }}">
                                    @error('merchant_token')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                           
                        </div>
                        <div class="row mb-3">
                           
                            <div class="col-sm-6">
                                <label for="">Betting Commission</label>
                                <input type="number" class="form-control" name="betting_commission"
                                    value="{{ isset($businessSetting) ? $businessSetting->betting_commission : old('betting_commission') }}">
                                    @error('betting_commission')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                             <div class="col-sm-6">
                                <label>First Deposit Amt</label>
                                <input type="text" class="form-control" name="first_deposit_amount"
                                    value="{{ isset($businessSetting) ? $businessSetting->first_deposit_amount : old('first_deposit_amount') }}">
                                    @error('first_deposit_amount')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                        </div>
                        <div class="row mb-3">
                           
                            <div class="col-sm-6">
                                <label for="">Min Deposit</label>
                                <input type="number" class="form-control" name="min_deposit"
                                    value="{{ isset($businessSetting) ? $businessSetting->min_deposit : old('min_deposit') }}">
                                    @error('min_deposit')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                             <div class="col-sm-6">
                                <label>Max Deposit</label>
                                <input type="text" class="form-control" name="max_deposit"
                                    value="{{ isset($businessSetting) ? $businessSetting->max_deposit : old('max_deposit') }}">
                                    @error('max_deposit')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                        </div>
                        <div class="row mb-3">
                           
                            <div class="col-sm-6">
                                <label for="">Min Withdrawal</label>
                                <input type="number" class="form-control" name="min_withdraw"
                                    value="{{ isset($businessSetting) ? $businessSetting->min_withdraw : old('min_withdraw') }}">
                                    @error('min_withdraw')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-sm-6">
                                <label>Max Withdrawal</label>
                                <input type="text" class="form-control" name="max_withdraw"
                                    value="{{ isset($businessSetting) ? $businessSetting->max_withdraw : old('max_withdraw') }}">
                                    @error('max_withdraw')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                        </div>


                        <div class="modal-footer">
                            <button type="submit" class="btn btn-lg btn-success">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
