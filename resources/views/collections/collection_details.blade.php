@foreach ($filesByFolder as $folder => $files)
    @if ($folder !== 'root')
        <div class="folder-name" data-folder-name="{{ $folder }}{{ $collection_name }}">
            <i class="fa fa-folder-o" id="icon-{{ $folder }}{{ $collection_name }}"></i>
            <span class="ms-2">{{ $folder }}</span>
        </div>
        <ul class="file-list collection" id="folder-{{ $folder }}{{ $collection_name }}">
    @endif

    @foreach ($files as $key => $file)
        <!-- Skip hidden files -->
        @if (substr($file['file']['name'], -1) !== '/')
            @php
                $sanitized = preg_replace('/[^a-zA-Z0-9-_]+/', '-', $file['file']['name']);
                // Remove consecutive dashes and trim dashes from the beginning and end
                $sanitized = preg_replace('/-+/', '-', $sanitized);
                $sanitized = strtolower(trim($sanitized, '-'));
            @endphp

            <li class="file-item collection-item d-flex justify-content-between sortable-connected" data-file-id="{{ $sanitized }}">
                <div class="list-details">
                    @php
                        $fileName = $file['file']['name'];
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
                        <span class="file-size">{{ number_format($file['file']['size'] / 1024, 2) }}
                                KB</span>
                    </div>
                    <p>Last modified: <span class="file-date">{{ $file['file']['lastModifiedDateTime'] }}</span>
                    </p>
                </div>
                <div class="list-action">
                </div>
            </li>
        @endif
    @endforeach
    </ul>
    @if ($folder !== 'root')
        </ul>
    @endif
@endforeach
