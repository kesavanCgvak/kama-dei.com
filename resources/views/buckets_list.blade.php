    <div class="col s12 ">
      <ul class="collapsible popout collapsible" id="bucket_list">
        <!-- Bucket List -->
        @foreach($data as $key => $value)
        <li data-bucket="{{ $value }}" class="parent_li">
          <div class="collapsible-header"><i class="material-icons s3-bucket-icon">cloud</i> {{$value}}</div>
          <div class="collapsible-body"></div>
        </li>
        @endforeach
      </ul>
    </div>


    <script>
      document.addEventListener('DOMContentLoaded', function() {
      var elems = document.querySelectorAll('.collapsible3');
      M.Collapsible.init(elems);
    });
    </script>