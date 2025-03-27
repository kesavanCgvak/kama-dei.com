<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KAMA AI</title>
  <!-- Materialize CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
  <!-- Material Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
    .file-card {
        width: 300px;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 16px;
      margin: 10px;
      transition: transform 0.2s;
      cursor: pointer;
    }
    

    .file-card:hover {
      transform: scale(1.05);
    }

    .thumbnail {
      width: 100%; /* Full width for thumbnail */
      height: auto; /* Maintain aspect ratio */
      max-height: 100px; /* Limit thumbnail height */
      object-fit: cover; /* Ensure image covers the area */
      border-radius: 4px; /* Slight rounding */
    }

    .file-icon {
      width: 100px; /* Fixed size for icons */
      height: 100px;
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: #f0f0f0; /* Light gray background */
      border-radius: 4px;
      color: #999; /* Icon color */
      font-size: 48px; /* Icon size */
    }

    .grid-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center; /* Center cards */
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav>
    <div class="nav-wrapper blue">
      <a href="#" class="brand-logo center">My App</a>
      <ul id="nav-mobile" class="right hide-on-med-and-down">
        <li><a href="{{url('drives')}}">Home</a></li>
      </ul>
    </div>
  </nav>

  <!-- Simple Breadcrumb -->
  <div class="container" style="margin-top: 15px;">
    <div style="text-align:right;color: #666;">
      <a href="{{url('drives')}}">Home</a> &gt; 
      <a href="{{url('s3buckets')}}">S3</a> &gt; 
      <span>Files</span>
    </div>
  </div>


  <!-- Files Grid -->
  <div class="container grid-container" style="margin-top: 20px;">
  @foreach($data as $key => $value)
    <!-- File 1 -->
    <div class="file-card col s12 m6 l4">
      <div class="file-icon">
        <img src="{{$value['file']['name']}}" alt="{{$value['file']['name']}}" class="thumbnail">
      </div>
      <p><strong>{{$value['file']['name']}}</strong></p>
      <p>Last Modified: {{$value['file']['lastModifiedDateTime']}}</p>
      <p>Size: {{$value['file']['size']}}</p>
      <div>
        <a href="#!" class="waves-effect waves-light btn-small blue"><i class="material-icons left">download</i>Download</a>
        <a href="#!" class="waves-effect waves-light btn-small red"><i class="material-icons left">delete</i>Delete</a>
      </div>
    </div>
@endforeach

    <!-- More files can be added here -->
  </div>

  <!-- Materialize JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
