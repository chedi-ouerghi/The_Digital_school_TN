<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        <style>
            body {
                font-family: 'Figtree', sans-serif;
                background-color: #f3f4f6;
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
            }
            .container {
                text-align: center;
                padding: 2rem;
            }
            h1 {
                color: #374151;
                font-size: 3rem;
                font-weight: 600;
                margin-bottom: 1rem;
            }
            p {
                color: #6b7280;
                font-size: 1.25rem;
                margin-bottom: 2rem;
            }
            .links {
                display: flex;
                justify-content: center;
                gap: 1rem;
            }
            a {
                color: #3b82f6;
                text-decoration: none;
                font-weight: 600;
                padding: 0.5rem 1rem;
                border: 2px solid #3b82f6;
                border-radius: 0.5rem;
                transition: all 0.3s ease;
            }
            a:hover {
                background-color: #3b82f6;
                color: white;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Laravel</h1>
            <p>Laravel application is running successfully!</p>
            <div class="links">
                <a href="https://laravel.com/docs">Documentation</a>
                <a href="https://laracasts.com">Laracasts</a>
                <a href="https://laravel-news.com">News</a>
                <a href="https://github.com/laravel/laravel">GitHub</a>
            </div>
        </div>
    </body>
</html>
