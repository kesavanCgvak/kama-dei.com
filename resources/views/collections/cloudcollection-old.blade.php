<div class="col s12">
    @php

    dd($data);

@endphp
    <div class="accordion mt-3" id="accordionCloud">
        @foreach ($data as $key => $value)
            <div class="accordion-item">
                <h2 class="accordion-header" data-bucket="{{ $value }}">
                    <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#flush-collapse{{ $key }}" aria-expanded="false"
                        aria-controls="flush-collapse{{ $key }}">
                        <i class="fa fa-cloud me-2"></i>
                        {{ $value }}
                    </button>
                </h2>
                <div id="flush-collapse{{ $key }}" class="accordion-collapse accordion-collapse-container collapse"
                    data-bs-parent="#accordionCloud">
                    <div class="accordion-body">

                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
