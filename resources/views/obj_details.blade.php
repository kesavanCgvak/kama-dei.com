<style>
        .folder-name {
            font-weight: bold;
            font-size: 18px;
            margin-top: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
        }
        .file-list {
            display: none; /* Hide by default */
        }
        .file-info {
display: flex;
justify-content: space-between;
}
.context-menu {
    position: absolute;
    background-color: #fff;
    border: 1px solid #ccc;
    box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
    z-index: 1000;
    width: 150px;
}

.context-menu ul {
    list-style: none;
    padding: 5px 0;
    margin: 0;
}

.context-menu ul li {
    padding: 8px 12px;
    cursor: pointer;
    position: relative;
}

.context-menu ul li:hover {
    background-color: #f0f0f0;
}

.has-submenu {
    position: relative;
}

.submenu {
    display: none;
    position: absolute;
    top: 0;
    left: 150px; /* Adjust based on your layout */
    background-color: #fff;
    border: 1px solid #ccc;
    box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
    width: 150px;
    z-index: 1001;
}

.has-submenu:hover .submenu {
    display: block;
}

.submenu li {
    padding: 8px 12px;
}

.submenu li:hover {
    background-color: #f0f0f0;
}


    </style>

@foreach($filesByFolder as $folder => $files)
    @if($folder !== 'root')
        <div class="folder-name" onclick="toggleFolder('{{ $folder }}{{ $collection_name }}', event)">
            <i class="material-icons" id="icon-{{ $folder }}{{ $collection_name }}">folder</i>
            {{ $folder }}
        </div>
        <ul class="file-list collection" id="folder-{{ $folder }}{{ $collection_name }}">
    @endif

    @foreach($files as $file)
        @if(substr($file['file']['name'], -1) !== '/') <!-- Skip folders in the list -->
            <li class="file-item collection-item avatar" >
                @php
                    $fileName = $file['file']['name'];
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

<div id="context-menu" class="context-menu" style="display:none;">
    <ul>
        <li class="has-submenu">
            Copy
            <ul class="submenu">
                @foreach($local_collections as $k => $val)
                <li onclick="copyFile('name')">{{$val->collection_name}}</li>
                @endforeach
            </ul>
        </li>
    </ul>
</div>

