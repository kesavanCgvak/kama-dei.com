<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>S3 Buckets List - Collapsible UI</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
    .radio-label {
      margin-right: 20px;
      font-weight: 500;
    }
    #loader {
      display: none; /* Hidden by default */
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 1000;
    }
    
    .mt-15 {
            margin-top: 15px
        }

        .button-container {
            display: flex;
            align-items: end;
            justify-content: end;
        }

        .btn-close {
            background-color: #b4b4b4 !important;
        }

        .m-5 {
            margin: 5px !important;
        }

        .error-text {
            color: red !important;
        }

        .mt-0 {
            margin-top: 0px !important;
        }
  </style>
</head>
<body>
<div id="loader">
    <div class="preloader-wrapper big active">
      <div class="spinner-layer spinner-blue-only">
        <div class="circle-clipper left">
          <div class="circle"></div>
        </div><div class="gap-patch">
          <div class="circle"></div>
        </div><div class="circle-clipper right">
          <div class="circle"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Navbar -->
  <nav>
    <div class="nav-wrapper blue">
      <a href="#" class="brand-logo center">My App</a>
      <ul id="nav-mobile" class="right hide-on-med-and-down">
        <li><a href="{{url('drives')}}">Home</a></li>
      </ul>
    </div>
  </nav>

  <!-- Breadcrumb Navigation -->
  <div class="container" style="margin-top: 15px;">
    <div style="text-align:right;color: #666;">
      <a href="{{url('drives')}}">Home</a> &gt; 
      <span>Buckets</span>
    </div>
  </div>
  
  <!-- Collapsible List for Buckets -->
  <div class="container">
  <div class="row">
            <div class="col s12 m6">
                <div class="row mt-15">
      <div class="col s12">
        <label class="radio-label">
          <input name="action" checked type="radio" class="with-gap" value="s3" />
          <span class="blue-text">S3 Bucket</span>
        </label>
        <label class="radio-label">
          <input name="action" type="radio" class="with-gap" value="sharepoint" />
          <span class="red-text">Sharepoint</span>
        </label>
      </div>
    </div>
    <div class="row" id="bucket_section">
    <div class="col s12">
      <ul class="collapsible popout" id="bucket_list">
        <!-- Bucket List -->
        @foreach($data as $key => $value)
        <li data-bucket="{{ $value }}" class="parent_li">
          <div class="collapsible-header"><i class="material-icons s3-bucket-icon">cloud</i> {{$value}}</div>
          <div class="collapsible-body"></div>
        </li>
        @endforeach
      </ul>
    </div>
  </div>
  </div>
  <div class="col s12 m6">
                <div class="row mt-15">
                    <div class="col s12 button-container">
                        <button id="add-collection" data-target="modal1" class="btn modal-trigger">New
                            Collection</button>
                    </div>
                </div>
            </div>
  </div>
  </div>
    <!-- Modal Structure -->
    <div id="modal1" class="modal">
        <div class="modal-content">
            <h5 class="mt-0">New Collection</h5>


            <div class="row mt-0">
                <div class="input-field col s12">
                    <div class="col s12">
                        <label class="radio-label">
                            <input name="storage_type" checked type="radio" class="with-gap" value="S3" />
                            <span class="blue-text">S3 Bucket</span>
                        </label>
                        <label class="radio-label">
                            <input name="storage_type" type="radio" class="with-gap" value="SharePoint" />
                            <span class="red-text">Sharepoint</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="row mt-0">
                <div class="input-field col s12">
                    <input id="collection_name" type="text" class="validate">
                    <label for="collection_name" class="collection-name">Collection Name</label>
                </div>
                <span class="helper-text error-text"></span>

            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-close btn btn-close m-5">Close</button>
            <button id="save-collection" class="btn m-5">Save</button>
        </div>
    </div>
  <script>
  
  document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('.collapsible');
    M.Collapsible.init(elems);
  });

  function validateCollection() {
            let collection_name = $('#collection_name').val();
            let error = 0;

            if (collection_name.length < 3) {
                error++;
                $('.helper-text').text('Collection name must be longer than 3 characters.');
                return;
            } else {
                error = 0;
                $('.helper-text').text('');
            }

        }

        
        

  $(document).ready(function() {
    $('select').formSelect();
            $(document).on('click', '#save-collection', function(e) {
                e.preventDefault();
                validateCollection();
                let organization_id = 1;
                let collection_name = $('#collection_name').val();
                let storage_type = $('input[name="storage_type"]:checked').val();
                let _token = $('meta[name="csrf-token"]').attr('content');
                let data = {
                    organization_id,
                    _token,
                    collection_name,
                    storage_type
                }
                $.ajax({
                    type: "post",
                    url: "/collections",
                    data,
                    dataType: 'json',
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                    },
                    complete: function() {

                    }
                });
            });

            $(document).on('change blur', '#collection_name', function(e) {
                e.preventDefault();
                validateCollection();
            });

            $(document).on('click', '#add-collection', function() {
                $('#collection_name').val('');
            });
            $('.modal').modal();
    $(document).on("change", 'input[name="action"]', function() {
      $('#loader').show();
      $('input[name="action"]').prop('disabled', true);
      let id = $(this).val();
      let org_id = 1;
      let _token = $('meta[name="csrf-token"]').attr('content');
      let data = {
        id,
        _token,
        org_id
      }
      $.ajax({
        type: "post",
        url: "/getcollections",
        data,
        dataType: 'HTML',
        success: function(response) {
          $("#bucket_section").html(response);
          var elems = document.querySelectorAll('.collapsible');
          M.Collapsible.init(elems);
          console.log('Response from server:', response);
        },
        error: function(xhr, status, error) {
          console.error('AJAX Error:', status, error);
        },
        complete: function() {
          $('#loader').hide();
          $('input[name="action"]').prop('disabled', false);
        }
      });
    });
    var elems = document.querySelectorAll('.collapsible');
    var instances = M.Collapsible.init(elems);

    $(document).on("click", "#bucket_list >  li.parent_li", function(event) {

      if ($(event.target).is(this)) {
        alert("hiiii");
        console.log("Directly clicked on li.parent_li:", $(this).data("bucket"));
    }

      $('#loader').show();
      let bucketName = $(this).data('bucket');
      let serviceprovider = $('input[name="action"]:checked').val();
      let _token = $('meta[name="csrf-token"]').attr('content');
      let org_id = 1;
      let data = {
        serviceprovider,
        _token,
        org_id,
        bucketName
      }

      $.ajax({
      type: "post",
      url: "/getbucketitems",
      data: data,
      success: function(response) {
        // Assuming `this` refers to the current collapsible item
        let collapsibleBody = $(this).find('.collapsible-body');
        
        // Empty the collapsible body and append the new response
        collapsibleBody.empty();
        collapsibleBody.append(response);
        
        // Find the collapsible header of the current item
        let collapsibleHeader = $(this).find('.collapsible-header');
        
        // Initialize and open the collapsible item
        let instance = M.Collapsible.getInstance(collapsibleHeader.closest('li'));
        if (instance) {
          instance.open(); // Open the collapsible
        } else {
          console.error('Collapsible instance not found');
        }
        
      }.bind(this), // Bind `this` to maintain scope
      error: function(xhr, status, error) {
        console.error('AJAX Error:', status, error);
      },
      complete: function() {
        $('#loader').hide();
        $('input[name="action"]').prop('disabled', false); // Re-enable buttons
      }
    });
  });
  });
</script>

<script>
    function toggleFolder(folderId,event) {
      alert(folderId);
        const fileList = document.getElementById('folder-' + folderId);
        const icon = document.getElementById('icon-' + folderId);

        //alert('folder-' + folderId);
        console.log(fileList);
        // Toggle display of the file list
        if (fileList.style.display === '' || fileList.style.display === 'none') {
            fileList.style.display = 'block';
            icon.textContent = 'folder_open';
        } else {
            fileList.style.display = 'none';
            icon.textContent = 'folder';
        }
        event.stopPropagation();
        return false;
    }
</script>
</body>
</html>
