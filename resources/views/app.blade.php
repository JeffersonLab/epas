<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Theo">

    <title>
        Plant Items
    </title>

    @routes
    <link href="{{ mix('css/app.css', 'vendor/jlab-epas') }}" rel="stylesheet" />
    <script src="{{ mix('js/app.js', 'vendor/jlab-epas') }}" defer></script>


    <script>
        window.Laravel = <?php echo json_encode([
            'csrfToken' => csrf_token(),
        ]); ?>
    </script>


</head>

<body>
    @inertia
</body>


</html>

