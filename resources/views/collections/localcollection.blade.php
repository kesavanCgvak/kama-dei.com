<div class="col s12">
    <div class="accordion mt-3" id="accordionLocal">
        @foreach ($local_collections as $key => $loc)
            <div class="accordion-item">
                <h2 class="accordion-header" data-id="{{ $loc->id }}" data-bucket="{{ $loc->collection_name }}">
                    <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#local-collapse{{ $key }}" aria-expanded="false"
                        aria-controls="local-collapse{{ $key }}">
                        <i class="fa fa-file me-2"></i>
                        {{ $loc->collection_name }}
                    </button>
                </h2>
                <div id="local-collapse{{ $key }}" class="accordion-collapse collapse"
                    data-bs-parent="#accordionLocal">
                    <div class="accordion-body" style="min-height: 100px">
                        <ul class="" style="min-height: 100px">

                        </ul>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
