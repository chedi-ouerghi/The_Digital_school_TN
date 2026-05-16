<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/header.css" />
    <link rel="stylesheet" href="css/profile.css" />
    <link rel="stylesheet" href="css/footer.css" />
    <link rel="stylesheet" href="css/course.css" />

  <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Exo:wght@600;700&display=swap"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500&display=swap"
    />
    <!-- CSS de Bootstrap -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

<!-- JS de Bootstrap (nécessite jQuery) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</head>
<body>
    <!-- Header commun -->
<header>
    <div class="header10">
        <div class="logo-parent18">
            <div class="logo20">
                <img class="isolation-mode-icon20" alt="" src="/img/eductaion.jpg" />
                <b class="edupress20">EduPress</b>
            </div>
            <div class="menu10">
                <div class="nav10">
                    <img class="component-2-icon295" alt="" src="/img/eductaion.jpg" />
                    {{-- <a href="{{ route('home') }}"> --}}
                        <div class="home500">Home</div>
                    {{-- </a> --}}
                    <div class="home501">Home</div>
                    <img class="component-2-icon295" alt="" src="/img/eductaion.jpg" />
                </div>
                <div class="component-2-parent47">
                    <img class="component-2-icon295" alt="" src="/img/eductaion.jpg" />
                    <a href="{{ route('cours') }}">
                        <div class="home500">Courses </div>
                    </a>
                    <div class="home501">Home</div>
                    <img class="component-2-icon295" alt="" src="/img/eductaion.jpg" />
                </div>
                <div class="component-2-parent47">
                    <img class="component-2-icon295" alt="" src="/img/eductaion.jpg" />
                        <div class="home500">Blog</div>
                    <div class="home501">Home</div>
                    <img class="component-2-icon295" alt="" src="/img/eductaion.jpg" />
                </div>
                <div class="component-2-parent49">
                    <img class="component-2-icon295" alt="" src="/img/eductaion.jpg" />
                    {{-- <a href="{{ route('page') }}"> --}}
                        <div class="home500">Page</div>
                    {{-- </a> --}}
                    <div class="home507">Home</div>
                    <img class="icon374" alt="" src="/img/eductaion.jpg" />
                </div>
                <div class="component-2-parent47">
                    <img class="component-2-icon295" alt="" src="/img/eductaion.jpg" />
                        <div class="home500">Faq</div>
                    <div class="home501">Home</div>
                    <img class="component-2-icon295" alt="" src="/img/eductaion.jpg" />
                </div>
                <div class="component-2-parent47">
                    <img class="component-2-icon295" alt="" src="/img/eductaion.jpg" />
                        <div class="home500">Feedback</div>
                    <div class="home501">Home</div>
                    <img class="component-2-icon295" alt="" src="/img/eductaion.jpg" />
                </div>
            </div>
            <div class="login-register-parent8">
                <div class="login-register11">
                    @auth
                    <a href="{{ route('logout') }}" class="logout-link">Logout</a>
                    <a href="{{ route('profile') }}" class="profile-link">Profile</a>
                    @else
                    <a href="{{ route('login') }}" class="login-link">Login</a> /
                    <a href="{{ route('register') }}" class="register-link">Register</a>
                    @endauth
                </div>
                <div class="search15">
                    <img class="icon377" alt="" src="/img/eductaion.jpg" />
                </div>
            </div>
        </div>
    </div>
</header>

    <!-- Contenu spécifique à chaque page -->
    <div class="content">
        @yield('content')
    </div>

    <!-- Footer commun -->
    <div class="footer10">
      <div class="frame-parent19">
        <div class="logo-parent19">
          <div class="logo21">
            <img
              class="isolation-mode-icon21"
              alt=""
              src="/img/eductaion.jpg"
            />

            <b class="edupress21">EduPress</b>
          </div>
          <div class="lorem-ipsum-dolor20">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do
            eiusmod tempor incididunt ut labore et dolore magna aliqua.
          </div>
        </div>
        <div class="get-help-parent8">
          <div class="get-help10">GET HELP</div>
          <div class="contact-us-latest-container10">
            <p class="contact-us20">Contact Us</p>
            <p class="contact-us20">Latest Articles</p>
            <p class="contact-us20">FAQ</p>
          </div>
        </div>
        <div class="get-help-parent8">
          <div class="get-help10">PROGRAMS</div>
          <div class="contact-us-latest-container10">
            <p class="contact-us20">Art & Design</p>
            <p class="business10">Business</p>
            <p class="contact-us20">IT & Software</p>
            <p class="contact-us20">Languages</p>
            <p class="contact-us20">Programming</p>
          </div>
        </div>
        <div class="contact-us-parent8">
          <div class="get-help10">CONTACT US</div>
          <div class="content50">
            <div class="address-2321-new10">
              Address: 2321 New Design Str, Lorem Ipsum10 Hudson Yards, USA
            </div>
            <div class="tel-123-container10">
              <p class="contact-us20">Tel: + (123) 2500-567-8988</p>
              <p class="contact-us20">Mail: supportlms@gmail.com</p>
            </div>
            <div class="social11">
              <img class="icon378" alt="" src="./img/eductaion.jpg" />
              <img class="icon378" alt="" src="./img/eductaion.jpg" />

              <img class="icon378" alt="" src="./img/eductaion.jpg" />

              <img class="icon378" alt="" src="./img/eductaion.jpg" />

              <img class="icon378" alt="" src="./img/eductaion.jpg" />
            </div>
          </div>
        </div>
      </div>
      <div class="copyright-2024-learnpress-lm-wrapper8">
        <div class="copyright-202410">
          Copyright © 2024 LearnPress LMS | Powered by ThimPress
        </div>
      </div>
      <div class="ellipse-parent8">
        <div class="frame-child8"></div>
        <img class="vector-icon12" alt="" src="./public/vector.svg" />
      </div>
    </div>

    <!-- Scripts JavaScript communs -->
    <!-- Ajoutez ici vos scripts JavaScript communs -->
</body>
</html>
