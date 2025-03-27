@foreach($filesByFolder as $folder => $files)
    @if($folder !== 'root')
        <div class="folder-name" data-folder-name="{{ $folder }}{{ $collection_name }}" >
            <i class="material-icons" id="icon-{{ $folder }}{{ $collection_name }}">folder</i>
            {{ $folder }}
        </div>
        <ul class="file-list collection" id="folder-{{ $folder }}{{ $collection_name }}">
    @endif

    @foreach($files as $file)
        @if(substr($file['file']['name'], -1) !== '/') <!-- Skip folders in the list -->
            <li class="file-item collection-item avatar draggable" >
                @php
                    $fileName = $file['file']['name'].': files : ';
                    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                    $icon = 'insert_drive_file';
                    if ($extension == 'pdf') $icon = 'picture_as_pdf';
                    elseif (in_array($extension, ['jpg', 'png', 'gif'])) $icon = 'image';
                    elseif (in_array($extension, ['doc', 'docx'])) $icon = 'description';
                @endphp
                <i class="material-icons circle">{{ $icon }}</i>
                <span class="title">{{ basename($fileName) }}</span>
                <div class="file-info">
                    <span><strong>{{ number_format($file['file']['size'] / 1024, 2) }} KB</strong></span>
                </div>
                <p>Last modified: <b>{{ $file['file']['lastModifiedDateTime'] }}</b></p>
            </li>
        @endif
    @endforeach

    @if($folder !== 'root')
        </ul>
    @endif
@endforeach
