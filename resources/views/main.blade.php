<!DOCTYPE HTML>
<html>
<head>
<title>@yield('title') - Sora</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
{{-- <meta http-equiv="refresh" content="30"> --}}
<meta name="keywords" content="Shoppy Responsive web template, Bootstrap Web Templates, Flat Web Templates, Android Compatible web template, 
Smartphone Compatible web template, free webdesigns for Nokia, Samsung, LG, SonyEricsson, Motorola web design" />
<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>

<!-- Include Bootstrap CSS -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

<!-- Include jQuery and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Datatable -->
<link rel="stylesheet" href="//cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<link href="{{ asset('style/css/bootstrap.css') }}" rel="stylesheet" type="text/css" media="all">
<!-- Custom Theme files -->
<link href="{{ asset('style/css/style.css') }}" rel="stylesheet" type="text/css" media="all"/>
<!--js-->
<script src="{{ asset('style/js/jquery-2.1.1.min.js') }}"></script> 
<!--icons-css-->
<script src="https://kit.fontawesome.com/37df1c11da.js" crossorigin="anonymous"></script>
<link href="{{ asset('style/css/font-awesome-1.css') }}" rel="stylesheet"> 
<!--Google Fonts-->
<link href='//fonts.googleapis.com/css?family=Carrois+Gothic' rel='stylesheet' type='text/css'>
<link href='//fonts.googleapis.com/css?family=Work+Sans:400,500,600' rel='stylesheet' type='text/css'>
</head>
<style>
    tr[data-href] {
    cursor: pointer;
    }
</style>
<body>
    @yield('page')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="//cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <script>
        let scrollerID;
        let paused = true;
        let speed = 2; // 1 - Fast | 2 - Medium | 3 - Slow
        let interval = speed * 5;

        function startScroll(){
            let id = setInterval(function() {
                window.scrollBy(0, 2);
                if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight) {
                    // Reached end of page
                    stopScroll();
                }
            }, interval);
            return id;
        }

        function stopScroll() {
            clearInterval(scrollerID);
        }

        document.body.addEventListener('keypress', function (event)
        {
            if (event.which == 13 || event.keyCode == 13) {
                // It's the 'Enter' key
                if(paused == true) {
                    scrollerID = startScroll();
                    paused = false;
                }
                else {
                    stopScroll();
                    paused = true;
                }
            }
        }, true);
    </script>
</body>
</html>