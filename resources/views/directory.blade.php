<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Materialize Card Layout</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/css/materialize.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/js/materialize.min.js"></script>
</head>
<body>

  <!-- Navbar -->
  <nav>
    <div class="nav-wrapper blue">
      <a href="#" class="brand-logo center">My App</a>
      <!-- <ul id="nav-mobile" class="right hide-on-med-and-down">
        <li><a href="#!">Home</a></li>
        <li><a href="#!">About</a></li>
        <li><a href="#!">Contact</a></li>
      </ul> -->
    </div>
  </nav>

  <!-- Card Layout -->
  <div class="container">
    <div class="row">&nbsp;</div>
    <div class="row">
      <div class="col s12 m6">
        <div class="card">
          <div class="card-content">
          <span class="card-title">AWS S3</span>
          <hr>
          <h6>&nbsp;</h6>
          <a href="{{url('s3buckets')}}">
          <img src="{{ asset('/public/assets/img/amazon-s3.png')}}" width="350px" height="100px" alt="Random Image">
          </a>
          </div>
          <div class="card-action">
            <a href="{{url('s3buckets')}}">View Buckets</a>
          </div>
        </div>
      </div>

      <!-- Add more cards as needed -->
      <div class="col s12 m6">
        <div class="card">
          <div class="card-content">
            <span class="card-title">Share Point</span>
            <hr>
            <h6>&nbsp;</h6>
            <img src="{{ asset('/public/assets/img/sharepoint-logo.png')}}" width="350px" height="100px" alt="Random Image">
          </div>
          <div class="card-action">
            <a href="{{url('sharepoint-sites')}}">View Sites</a>
          </div>
        </div>
      </div>
    </div>
  </div>

</body>
</html>
