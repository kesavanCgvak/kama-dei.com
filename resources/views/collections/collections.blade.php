@extends('layouts.kamabase')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col s12 cloud">
                <div class="card collection-card">
                    <div class="card-header">
                        <h5 class="card-title">Cloud Storage</h5>
                    </div>
                    <div class="card-body collection-card-body">
                        <div class="p-4">
                            <div class="row mt-15">
                                <div class="col-6 py-1">
                                    <select id="storage_type" name="storage_type" class="form-select">
                                        <option selected value="S3">AWS S3</option>
                                        <option value="SharePoint">SharePoint</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row" id="cloud_bucket_section">
                                @include('collections.cloudcollection')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- local storage --}}
            <div class="col s12 local">
                <div class="card collection-card">
                    <div class="card-header d-flex justify-content-between">
                        <h5 class="card-title">Local Storage</h5>
                        <button id="add-collection" data-target="modal1" class="btn btn-sm btn-primary">New
                            Collection</button>
                    </div>
                    <div class="card-body collection-card-body">
                        <div class="p-4">
                            <div class="row mt-15">
                                <div class="col-6 s2" style="height: 44px">

                                </div>
                            </div>
                            <div class="row mt-15" id="local_bucket_section">
                                @include('collections.localcollection')
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="modal fade" id="createLocalColection" tabindex="-1" aria-labelledby="createLocalColectionLabel">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createLocalColectionLabel">Create Collection</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" id="radio1" name="local_storage_type"
                                value="S3" checked>S3
                            <label class="form-check-label" for="radio1"></label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" id="radio2" name="local_storage_type"
                                value="SharePoint">SharePoint
                            <label class="form-check-label" for="radio2"></label>
                        </div>
                        <div class="mb-3 mt-3">
                            <label for="collection_name" class="form-label">Collection Name:</label>
                            <input type="text" class="form-control" id="collection_name" placeholder="Collection Name"
                                name="collection_name">
                            <span class="helper-text error-text"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="save-collection" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </div>
        </div>


        <div class="toast toast-delete toast-center" id="deleteConfirm" data-bs-autohide="false" role="alert">
            <div class="toast-body">
                Are you sure you want to delete this file?
                <div class="mt-2 pt-2 border-top text-end">
                    <button type="button" id="delete-yes" class="btn btn-primary btn-sm">Yes</button>
                    <button type="button" id="delete-no" class="btn btn-secondary btn-sm close-toast">No</button>
                </div>
            </div>
        </div>
    @endsection
    @section('script')
        <script type="text/javascript" src="{{ asset('public/assets/js/collection.js') }}"></script>
        <script>
            $(document).ready(function() {
                Collection.init();
            });
        </script>
    @endsection
