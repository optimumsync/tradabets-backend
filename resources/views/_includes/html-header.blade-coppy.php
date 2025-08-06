
    <!-- Basic -->
    <meta charset="UTF-8">

    <title>{{ env('APP_NAME') }}</title>
    <meta name="keywords" content="HTML5 Admin Template" />
    <meta name="description" content="Porto Admin - Responsive HTML5 Template">
    <meta name="author" content="okler.net">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    

    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <!-- Web Fonts  -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">

    <!-- Head CSS -->
    <link rel="stylesheet" href="/themes/admin/css/default.css">
    <link rel="stylesheet" href="{{ mix('/themes/admin/css/custom-styling.css') }}">
    <link rel="stylesheet" href="/themes/admin/menu-left/common/menu-left.cleanui.css">
    <link rel="stylesheet" href="/themes/admin/footer/common/footer.cleanui.css">





    <!-- Head Libs -->
    <script src="/themes/admin/js/default-header.js"></script>




    <!-- JQuery -->


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
     <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
      <script src="/themes/admin/menu-left/common/menu-left.cleanui.js"></script>


   <script src="{{ mix('/themes/admin/js/custom-scripts.js') }}"></script>

    {{----}}
