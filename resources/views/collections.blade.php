@extends('layouts.kamabase')
@section('content')
<div class="container">
    <div class="row">
        <div class="col s12 m6 cloud">
            <div class="section-container">
                <div class="section-header">
                    <h5 class="section-heading">Cloud Storage</h5>

                </div>
                <hr>
                <div class="row mt-15">
                    <div class="col s12">
                        <label class="radio-label">
                            <input name="storage_type" checked type="radio" class="with-gap" value="s3" />
                            <span class="black-text">S3 Bucket</span>
                        </label>
                        <label class="radio-label">
                            <input name="storage_type" type="radio" class="with-gap" value="sharepoint" />
                            <span class="black-text">Sharepoint</span>
                        </label>
                    </div>
                </div>
                <div class="row" id="bucket_section">
                    <div class="col s12">
                        <ul class="collapsible popout" id="bucket_list">
                            <!-- Bucket List -->
                            @foreach ($data as $key => $value)
                                <li data-bucket="{{ $value }}" class="parent_li">
                                    <div class="collapsible-header"><i
                                            class="material-icons s3-bucket-icon">cloud</i> {{ $value }}
                                    </div>
                                    <div class="collapsible-body"></div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        {{-- local storage --}}
        <div class="col s12 m6 local">
            <div class="section-container">
                <div class="section-header">
                    <h5 class="section-heading">Local Storage</h5>
                    <button id="add-collection" data-target="modal1" class="btn modal-trigger">New
                        Collection</button>
                </div>
                <hr>
                <div class="row mt-15">
                    <div class="col s10">&nbsp;</div>
                    <div class="col s2">
                        <select class="form-control">
                            <option value="all">All</option>
                            <option value="1">S3</option>
                            <option value="2">Sharepoint</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-15">
                    <div class="col s12">
                        <ul class="collapsible popout" id="localbucket_list">
                            <!-- Bucket List -->
                            @foreach ($local_collections as $key => $loc)
                                <li data-bucket="{{ $loc->collection_name }}" class="local_parent_li droppable">
                                    <div class="collapsible-header droppable"><i
                                            class="material-icons s3-bucket-icon">collections</i>
                                        {{ $loc->collection_name }}</div>
                                    <div class="collapsible-body">
                                        <ul class="file-list collection">

                                        </ul>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
@section('script')
<script type="text/javascript" src="{{ asset('assets/js/collection.js') }}"></script>

<script>
    $(document).ready(function(){
        Collection.init();
    });

</script>
@endsection
