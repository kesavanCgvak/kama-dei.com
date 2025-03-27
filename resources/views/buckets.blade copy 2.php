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
        .container {
            width: 90%;
            /* Adjust percentage as needed */
            max-width: 100%;
        }

        .radio-label {
            margin-right: 20px;
            font-weight: 500;
        }

        #loader {
            display: none;
            /* Hidden by default */
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

        .section-container {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            background-color: #f9f9f9;
            margin-top: 15px;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 36px;
            line-height: 36px;
        }

        .section-heading {
            font-weight: 600;
            color: #333;
            margin: 0;
        }

        #add-collection {
            background-color: #1976d2;
            color: white;
            font-weight: 500;
        }

        #add-collection:hover {
            background-color: #1565c0;
        }
    </style>
</head>

<body>
    <div id="loader">
        <div class="preloader-wrapper big active">
            <div class="spinner-layer spinner-blue-only">
                <div class="circle-clipper left">
                    <div class="circle"></div>
                </div>
                <div class="gap-patch">
                    <div class="circle"></div>
                </div>
                <div class="circle-clipper right">
                    <div class="circle"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navbar -->
    <nav>
        <div class="nav-wrapper blue">
            <a href="#" class="brand-logo center">KAMA - AI</a>
            <ul id="nav-mobile" class="right hide-on-med-and-down">
                <li><a href="{{url('drives')}}">Home</a></li>
            </ul>
        </div>
    </nav>



    <!-- Collapsible List for Buckets -->
    <div class="container">

    </div>

</body>

</html>
