<ul class="collection " style="min-height: 150px">
    @foreach ($files as $key => $file)
        <li class="file-item collection-item d-flex justify-content-between local-files" data-id="{{ $file->id }}" data-file-id="{{ $file->file_id }}">
            <div class="list-details">
                @php
                    $fileName = $file->file_name;
                    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                    $icon = 'fa fa-file-pdf-o';
                    if ($extension == 'pdf') {
                        $icon = 'fa fa-file-pdf-o';
                    } elseif (in_array($extension, ['jpg', 'png', 'gif'])) {
                        $icon = 'image';
                    } elseif (in_array($extension, ['doc', 'docx'])) {
                        $icon = 'description';
                    }
                @endphp
                <span class="circle"><i class="{{ $icon }}"></i></span>
                <span class="title">{{ basename($fileName) }}</span>
                <div class="file-info">
                    <span class="file-size">{{ $file->size }} </span>
                </div>
                <p>Last modified: <span class="file-date">{{ $file->last_modified }}</span></p>
            </div>
            <div class="list-action">
                <i class="fa fa-trash-o file-delete cursor-pointer"></i>
            </div>
        </li>
    @endforeach
</ul>
