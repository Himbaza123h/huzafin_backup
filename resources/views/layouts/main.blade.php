<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Xero Integration</title>
   <!-- Add your CSS files here -->
   <link rel="stylesheet" href="path/to/your/css/styles.css">
</head>

<body>
   <header>
      <!-- Add your header content here -->
      <h1>Xero Integration</h1>
      <nav>
         <!-- Add your navigation links here -->
         <ul>
            <li><a href="/">Home</a></li>
            <li><a href="/about">About</a></li>
            <!-- Add more navigation links as needed -->
         </ul>
      </nav>
   </header>

   <main>
      <!-- Content from blade.php will be inserted here -->
      @yield('content')
   </main>

   <footer>
      <!-- Add your footer content here -->
      <p>&copy; {{ date('Y') }} Your Company</p>
   </footer>

   <!-- Add your JavaScript files here -->
   <script src="path/to/your/js/script.js"></script>
</body>

</html>