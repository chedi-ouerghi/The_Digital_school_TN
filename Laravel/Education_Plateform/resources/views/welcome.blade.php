<!-- resources/views/welcome.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/Home.css">
    <link rel="stylesheet" href="css/header.css" />
    <link rel="stylesheet" href="css/footer.css" />

    <title>Welcome to our website</title>
</head>
<body>
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


<main>

{{-- first section  --}}

    <div class="hero-banner1">
      <img class="image-icon1" alt="" src="/img/eductaion.jpg" />

      <div class="content60">
        <div class="build-skills-with-container1">
          <p class="build-skills-with1">Build Skills with</p>
          <p class="build-skills-with1">Online Course</p>
        </div>
        <div class="text33">
          <div class="we-denounce-with3">
            We denounce with righteous indignation and dislike men who are so
            beguiled and demoralized that cannot trouble.
          </div>
          <div class="button31">
            <div class="posts-comment12">Posts comment</div>
          </div>
        </div>
      </div>
    </div>


    {{-- categorie  --}}
    <div class="categories2">
      <div class="top-heading13">
        <div class="title12">
          <div class="top-categories4">Top Categories</div>
          <div class="explore-our-popular4">Explore our Popular Categories</div>
        </div>
        <div class="button27">
          <div class="outlinelightnomal5">All categories</div>
        </div>
      </div>
      <div class="categories3">
        <div class="cate-115">
          <img class="icon383" alt="" src="/img/eductaion.jpg" />

          <div class="art-design-parent8">
            <div class="top-categories4">Art & Design</div>
            <div class="explore-our-popular4">38 Courses</div>
          </div>
        </div>
        <div class="cate-213">
          <div class="cate-214">
            <img class="icon383" alt="" src="/img/eductaion.jpg" />

            <div class="art-design-parent8">
              <div class="top-categories4">Development</div>
              <div class="explore-our-popular4">38 Courses</div>
            </div>
          </div>
        </div>
        <div class="cate-115">
          <img class="icon383" alt="" src="/img/eductaion.jpg" />

          <div class="art-design-parent8">
            <div class="top-categories4">Communiication</div>
            <div class="explore-our-popular4">38 Courses</div>
          </div>
        </div>
        <div class="cate-115">
          <img class="icon383" alt="" src="/img/eductaion.jpg" />

          <div class="art-design-parent8">
            <div class="top-categories4">Videography</div>
            <div class="explore-our-popular4">38 Courses</div>
          </div>
        </div>
        <div class="cate-115">
          <img class="icon383" alt="" src="/img/eductaion.jpg" />

          <div class="art-design-parent8">
            <div class="top-categories4">Photography</div>
            <div class="explore-our-popular4">38 Courses</div>
          </div>
        </div>
        <div class="cate-115">
          <img class="icon383" alt="" src="/img/eductaion.jpg" />

          <div class="art-design-parent8">
            <div class="top-categories4">Marketing</div>
            <div class="explore-our-popular4">38 Courses</div>
          </div>
        </div>
        <div class="cate-115">
          <img class="icon383" alt="" src="/img/eductaion.jpg" />

          <div class="art-design-parent8">
            <div class="top-categories4">Content writing</div>
            <div class="explore-our-popular4">38 Courses</div>
          </div>
        </div>
        <div class="cate-115">
          <img class="icon383" alt="" src="/img/eductaion.jpg" />

          <div class="art-design-parent8">
            <div class="top-categories4">Finance</div>
            <div class="explore-our-popular4">38 Courses</div>
          </div>
        </div>
        <div class="cate-115">
          <img class="icon383" alt="" src="/img/eductaion.jpg" />

          <div class="art-design-parent8">
            <div class="top-categories4">Science</div>
            <div class="explore-our-popular4">38 Courses</div>
          </div>
        </div>
        <div class="cate-115">
          <img class="icon383" alt="" src="/img/eductaion.jpg" />

          <div class="art-design-parent8">
            <div class="top-categories4">Network</div>
            <div class="explore-our-popular4">38 Courses</div>
          </div>
        </div>
      </div>
    </div>

    {{-- courses  --}}

    <div class="courses42">
      <div class="top-heading14">
        <div class="title13">
          <div class="top-categories5">Featured courses</div>
          <div class="explore-our-popular5">Explore our Popular Courses</div>
        </div>
        <div class="button28">
          <div class="outlinelightnomal6">All courses</div>
        </div>
      </div>
      <div class="courses43">
        <div class="courses44">
          <div class="image18">
            <img
              class="image-child15"
              alt=""
           src="/img/eductaion.jpg"
            />

            <div class="photography-wrapper16">
              <div class="outlinelightnomal6">Photography</div>
            </div>
          </div>
          <div class="content51">
            <div class="by-determined-poitras-parent16">
              <div class="by-determined-poitras19">
                <span>by </span>
                <span class="determined-poitras19">Determined-Poitras</span>
              </div>
              <div class="create-an-lms18">
                Create an LMS Website with LearnPress
              </div>
            </div>
            <div class="meta35">
              <div class="times35">
                <img
                  class="component-2-icon302"
                  alt=""
                  src="/img/eductaion.jpg"
                />

                <div class="by-determined-poitras19">2Weeks</div>
                <div class="home513">Home</div>
                <img class="icon392" alt="" src="/img/eductaion.jpg" />
              </div>
              <div class="times35">
                <img
                  class="component-2-icon302"
                  alt=""
                  src="/img/eductaion.jpg"
                />

                <div class="by-determined-poitras19">156 Students</div>
                <div class="home513">Home</div>
                <img class="icon392" alt="" src="/img/eductaion.jpg" />
              </div>
            </div>
            <div class="line16"></div>
            <div class="price-parent11">
              <div class="price19">
                <div class="div102">$29.0</div>
                <div class="free17">Free</div>
              </div>
              <div class="view-more18">View more</div>
            </div>
          </div>
        </div>
            <div class="courses44">
              <div class="image18">
                <img
                  class="image-child15"
                  alt=""
                  src="/img/eductaion.jpg"
                />

                <div class="photography-wrapper16">
                  <div class="outlinelightnomal6">Photography</div>
                </div>
              </div>
              <div class="content52">
                <div class="frame-parent20">
                  <div class="by-determined-poitras-parent16">
                    <div class="by-determined-poitras19">
                      <span>by </span>
                      <span class="determined-poitras19"
                        >Determined-Poitras</span
                      >
                    </div>
                    <div class="create-an-lms19">
                      Design a website with ThimPress
                    </div>
                  </div>
                  <div class="meta35">
                    <div class="times35">
                      <img
                        class="component-2-icon302"
                        alt=""
                        src="/img/eductaion.jpg"
                      />

                      <div class="by-determined-poitras19">2Weeks</div>
                      <div class="home513">Home</div>
                      <img
                        class="icon392"
                        alt=""
                        src="/img/eductaion.jpg"
                      />
                    </div>
                    <div class="times35">
                      <img
                        class="component-2-icon302"
                        alt=""
                        src="/img/eductaion.jpg"
                      />

                      <div class="by-determined-poitras19">156 Students</div>
                      <div class="home513">Home</div>
                      <img
                        class="icon392"
                        alt=""
                        src="/img/eductaion.jpg"
                      />
                    </div>
                  </div>
                </div>
                <div class="line-group">
                  <div class="line16"></div>
                  <div class="price-parent12">
                    <div class="price20">
                      <div class="div102">$59.0</div>
                      <div class="div104">$49.0</div>
                    </div>
                    <div class="view-more18">View more</div>
                  </div>
                </div>
              </div>
            </div>
        <div class="courses44">
          <div class="image18">
            <img
              class="image-child15"
              alt=""
              src="/img/eductaion.jpg"
            />

            <div class="photography-wrapper16">
              <div class="outlinelightnomal6">Photography</div>
            </div>
          </div>
          <div class="content51">
            <div class="by-determined-poitras-parent16">
              <div class="by-determined-poitras19">
                <span>by </span>
                <span class="determined-poitras19">Determined-Poitras</span>
              </div>
              <div class="create-an-lms18">
                Create an LMS Website with LearnPress
              </div>
            </div>
            <div class="meta35">
              <div class="times35">
                <img
                  class="component-2-icon302"
                  alt=""
                  src="/img/eductaion.jpg"
                />

                <div class="by-determined-poitras19">2Weeks</div>
                <div class="home513">Home</div>
                <img class="icon392" alt="" src="/img/eductaion.jpg" />
              </div>
              <div class="times35">
                <img
                  class="component-2-icon302"
                  alt=""
                  src="/img/eductaion.jpg"
                />

                <div class="by-determined-poitras19">156 Students</div>
                <div class="home513">Home</div>
                <img class="icon392" alt="" src="/img/eductaion.jpg" />
              </div>
            </div>
            <div class="line16"></div>
            <div class="price-parent11">
              <div class="price19">
                <div class="div102">$29.0</div>
                <div class="free17">Free</div>
              </div>
              <div class="view-more18">View more</div>
            </div>
          </div>
        </div>
        <div class="courses44">
          <div class="image18">
            <img
              class="image-child15"
              alt=""
              src="/img/eductaion.jpg"
            />

            <div class="photography-wrapper16">
              <div class="outlinelightnomal6">Photography</div>
            </div>
          </div>
          <div class="content51">
            <div class="by-determined-poitras-parent16">
              <div class="by-determined-poitras19">
                <span>by </span>
                <span class="determined-poitras19">Determined-Poitras</span>
              </div>
              <div class="create-an-lms18">
                Create an LMS Website with LearnPress
              </div>
            </div>
            <div class="meta35">
              <div class="times35">
                <img
                  class="component-2-icon302"
                  alt=""
                  src="/img/eductaion.jpg"
                />

                <div class="by-determined-poitras19">2Weeks</div>
                <div class="home513">Home</div>
                <img class="icon392" alt="" src="/img/eductaion.jpg" />
              </div>
              <div class="times35">
                <img
                  class="component-2-icon302"
                  alt=""
                  src="/img/eductaion.jpg"
                />

                <div class="by-determined-poitras19">156 Students</div>
                <div class="home513">Home</div>
                <img class="icon392" alt="" src="/img/eductaion.jpg" />
              </div>
            </div>
            <div class="line16"></div>
            <div class="price-parent11">
              <div class="price19">
                <div class="div102">$29.0</div>
                <div class="free17">Free</div>
              </div>
              <div class="view-more18">View more</div>
            </div>
          </div>
        </div>
        <div class="courses44">
          <div class="image18">
            <img
              class="image-child15"
              alt=""
              src="/img/eductaion.jpg"
            />

            <div class="photography-wrapper16">
              <div class="outlinelightnomal6">Photography</div>
            </div>
          </div>
          <div class="content51">
            <div class="by-determined-poitras-parent16">
              <div class="by-determined-poitras19">
                <span>by </span>
                <span class="determined-poitras19">Determined-Poitras</span>
              </div>
              <div class="create-an-lms18">
                Create an LMS Website with LearnPress
              </div>
            </div>
            <div class="meta35">
              <div class="times35">
                <img
                  class="component-2-icon302"
                  alt=""
                  src="/img/eductaion.jpg"
                />

                <div class="by-determined-poitras19">2Weeks</div>
                <div class="home513">Home</div>
                <img class="icon392" alt="" src="/img/eductaion.jpg" />
              </div>
              <div class="times35">
                <img
                  class="component-2-icon302"
                  alt=""
                  src="/img/eductaion.jpg"
                />

                <div class="by-determined-poitras19">156 Students</div>
                <div class="home513">Home</div>
                <img class="icon392" alt="" src="/img/eductaion.jpg" />
              </div>
            </div>
            <div class="line16"></div>
            <div class="price-parent11">
              <div class="price19">
                <div class="div102">$29.0</div>
                <div class="free17">Free</div>
              </div>
              <div class="view-more18">View more</div>
            </div>
          </div>
        </div>
        <div class="courses44">
          <div class="image18">
            <img
              class="image-child15"
              alt=""
              src="/img/eductaion.jpg"
            />

            <div class="photography-wrapper16">
              <div class="outlinelightnomal6">Photography</div>
            </div>
          </div>
          <div class="content51">
            <div class="by-determined-poitras-parent16">
              <div class="by-determined-poitras19">
                <span>by </span>
                <span class="determined-poitras19">Determined-Poitras</span>
              </div>
              <div class="create-an-lms18">
                Create an LMS Website with LearnPress
              </div>
            </div>
            <div class="meta35">
              <div class="times35">
                <img
                  class="component-2-icon302"
                  alt=""
                  src="/img/eductaion.jpg"
                />

                <div class="by-determined-poitras19">2Weeks</div>
                <div class="home513">Home</div>
                <img class="icon392" alt="" src="/img/eductaion.jpg" />
              </div>
              <div class="times35">
                <img
                  class="component-2-icon302"
                  alt=""
                  src="/img/eductaion.jpg"
                />

                <div class="by-determined-poitras19">156 Students</div>
                <div class="home513">Home</div>
                <img class="icon392" alt="" src="/img/eductaion.jpg" />
              </div>
            </div>
            <div class="line16"></div>
            <div class="price-parent11">
              <div class="price19">
                <div class="div102">$29.0</div>
                <div class="free17">Free</div>
              </div>
              <div class="view-more18">View more</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- groupe get more power  --}}


    <div class="banner-parent">
      <div class="banner3">
        <img class="mask-group-icon3" alt="" src="/img/eductaion.jpg" />

        <div class="content57">
          <div class="text31">
            <div class="get-more-power1">GET MORE POWER FROM</div>
            <div class="learnpress-add-ons1">LearnPress Add-Ons</div>
          </div>
          <div class="the-next-level-of-learnpress-group">
            <div class="the-next-level2">
              The next level of LearnPress - LMS WordPress Plugin. More
              Powerful, Flexible and Magical Inside.
            </div>
            <div class="button29">
              <div class="posts-comment10">Explorer course</div>
            </div>
          </div>
        </div>
      </div>
      <div class="counter-box-group">
        <div class="counter-box1">
          <div class="get-more-power1">25K+</div>
          <div class="active-students4">Active Students</div>
        </div>
        <div class="counter-box1">
          <div class="get-more-power1">899</div>
          <div class="active-students4">Total Courses</div>
        </div>
        <div class="counter-box1">
          <div class="get-more-power1">158</div>
          <div class="active-students4">Instructor</div>
        </div>
        <div class="counter-box1">
          <div class="get-more-power1">100%</div>
          <div class="active-students4">Satisfaction rate</div>
        </div>
      </div>
    </div>

    {{-- image + texte  --}}

    <div class="vector-root">
      <img class="vector-icon13" alt="" src="/img/eductaion.jpg" />

      <div class="content58">
        <div class="grow-us-your-container1">
          <p class="grow-us-your1">Grow us your skill</p>
          <p class="grow-us-your1">with LearnPress LMS</p>
        </div>
        <div class="content59">
          <div class="text32">
            <div class="we-denounce-with2">
              We denounce with righteous indignation and dislike men who are so
              beguiled and demoralized that cannot trouble.
            </div>
            <div class="parent3">
              <div class="div112">
                <img
                  class="component-2-icon314"
                  alt=""
                  src="/img/eductaion.jpg"
                />

                <div class="home536">Certification</div>
                <div class="home537">Home</div>
                <img class="icon404" alt="" src="/img/eductaion.jpg" />
              </div>
              <div class="div112">
                <img
                  class="component-2-icon314"
                  alt=""
                  src="/img/eductaion.jpg"
                />

                <div class="home536">Certification</div>
                <div class="home537">Home</div>
                <img class="icon404" alt="" src="/img/eductaion.jpg" />
              </div>
              <div class="div112">
                <img
                  class="component-2-icon314"
                  alt=""
                  src="/img/eductaion.jpg"
                />

                <div class="home536">Certification</div>
                <div class="home537">Home</div>
                <img class="icon404" alt="" src="/img/eductaion.jpg" />
              </div>
              <div class="div112">
                <img
                  class="component-2-icon314"
                  alt=""
                  src="/img/eductaion.jpg"
                />

                <div class="home536">Certification</div>
                <div class="home537">Home</div>
                <img class="icon404" alt="" src="/img/eductaion.jpg" />
              </div>
            </div>
          </div>
          <div class="button30">
            <div class="posts-comment11">Explorer course</div>
          </div>
        </div>
      </div>
    </div>



    {{-- education world  --}}

    <div class="banner4">
      <img class="mask-group-icon4" alt="" src="/img/eductaion.jpg" />

      <div class="frame-parent21">
        <div class="providing-amazing-group">
          <div class="providing-amazing1">PROVIDING AMAZING</div>
          <div class="education-wordpress-theme1">
            education wordpress theme
          </div>
        </div>
        <div class="the-next-level-of-lms-wordpres-group">
          <div class="the-next-level3">
            The next level of LMS WordPress Theme. Learn anytime and anywhere.
          </div>
          <div class="button32">
            <div class="posts-comment13">Explorer course</div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>


{{-- Student feedbacks --}}

    <div class="top-heading-root">
      <div class="top-heading15">
        <div class="title14">
          <div class="top-categories6">Student feedbacks</div>
          <div class="explore-our-popular6">
            What Students Say About Academy LMS
          </div>
        </div>
        <div class="button33">
          <div class="outlinelightnomal7">All articles</div>
        </div>
      </div>
      <div class="tes1">
        <div class="feedback-item1">
          <div class="parent4">
            <img class="icon408" alt="" src="/img/eductaion.jpg" />

            <div class="i-must-explain4">
              I must explain to you how all this mistaken . Tdea of denouncing
              pleasure and praising pain was born and I will give you a complete
              account of the system and expound
            </div>
          </div>
          <div class="roe-smith-parent2">
            <div class="top-categories6">Roe Smith</div>
            <div class="explore-our-popular6">Designer</div>
          </div>
        </div>
        <div class="feedback-item1">
          <div class="parent4">
            <img class="icon408" alt="" src="/img/eductaion.jpg" />

            <div class="i-must-explain4">
              I must explain to you how all this mistaken . Tdea of denouncing
              pleasure and praising pain was born and I will give you a complete
              account of the system and expound
            </div>
          </div>
          <div class="roe-smith-parent2">
            <div class="top-categories6">Roe Smith</div>
            <div class="explore-our-popular6">Designer</div>
          </div>
        </div>
        <div class="feedback-item1">
          <div class="parent4">
            <img class="icon408" alt="" src="/img/eductaion.jpg" />

            <div class="i-must-explain4">
              I must explain to you how all this mistaken . Tdea of denouncing
              pleasure and praising pain was born and I will give you a complete
              account of the system and expound
            </div>
          </div>
          <div class="roe-smith-parent2">
            <div class="top-categories6">Roe Smith</div>
            <div class="explore-our-popular6">Designer</div>
          </div>
        </div>
        <div class="feedback-item1">
          <div class="parent4">
            <img class="icon408" alt="" src="/img/eductaion.jpg" />

            <div class="i-must-explain4">
              I must explain to you how all this mistaken . Tdea of denouncing
              pleasure and praising pain was born and I will give you a complete
              account of the system and expound
            </div>
          </div>
          <div class="roe-smith-parent2">
            <div class="top-categories6">Roe Smith</div>
            <div class="explore-our-popular6">Designer</div>
          </div>
        </div>
      </div>
    </div>


{{-- Let’s Start With Academy LMS  --}}

    <div class="banner5">
      <div class="banner-item"></div>
      <img class="mask-group-icon5" alt="" src="/img/eductaion.jpg" />

      <div class="content61">
        <div class="student-1-group">
          <div class="student-11">
            <img class="group-icon4" alt="" src="/img/eductaion.jpg" />
          </div>
          <div class="lets-start-with1">Let’s Start With Academy LMS</div>
        </div>
        <div class="group-button1">
          <div class="button34">
            <div class="outlinelightnomal8">I’m a student</div>
          </div>
          <div class="button35">
            <div class="outlinelightnomal8">Become an Instructor</div>
          </div>
        </div>
      </div>
    </div>

    {{-- lastes article  --}}

    <div class="top-heading-top">
      <div class="top-heading16">
        <div class="title15">
          <div class="top-categories7">Latest articles</div>
          <div class="explore-our-popular7">Explore our Free Acticles</div>
        </div>
        <div class="button36">
          <div class="outlinelightnomal9">All articles</div>
        </div>
      </div>
      <div class="articles16">
        <div class="articles17">
          <img
            class="articles-child13"
            alt=""
            src="/img/eductaion.jpg"
          />

          <div class="content62">
            <div class="best-learnpress-wordpress25">
              Best LearnPress WordPress Theme Collection for 2023
            </div>
            <div class="meta41">
              <div class="times41">
                <img
                  class="component-2-icon318"
                  alt=""
                  src="/img/eductaion.jpg"
                />

                <div class="home544">Determined-poitras</div>
                <div class="home545">Home</div>
                <img class="icon412" alt="" src="/img/eductaion.jpg" />
              </div>
              <div class="students66">
                <img
                  class="component-2-icon318"
                  alt=""
                  src="/img/eductaion.jpg"
                />

                <div class="home544">Jan 24, 22023</div>
                <div class="home545">Home</div>
                <img class="icon412" alt="" src="/img/eductaion.jpg" />
              </div>
            </div>
            <div class="looking-for-an15">
              Looking for an amazing & well-functional LearnPress WordPress
              Theme?...
            </div>
          </div>
        </div>
        <div class="articles-container">
          <div class="articles18">
            <img
              class="articles-child13"
              alt=""
              src="/img/eductaion.jpg"
            />

            <div class="content63">
              <div class="best-learnpress-wordpress26">
                Best LearnPress WordPress Theme Collection for 2023
              </div>
              <div class="meta41">
                <div class="times41">
                  <img
                    class="component-2-icon318"
                    alt=""
                    src="/img/eductaion.jpg"
                  />

                  <div class="home544">Determined-poitras</div>
                  <div class="home545">Home</div>
                  <img class="icon412" alt="" src="/img/eductaion.jpg" />
                </div>
                <div class="students66">
                  <img
                    class="component-2-icon318"
                    alt=""
                    src="/img/eductaion.jpg"
                  />

                  <div class="home544">Jan 24, 22023</div>
                  <div class="home545">Home</div>
                  <img class="icon412" alt="" src="/img/eductaion.jpg" />
                </div>
              </div>
              <div class="looking-for-an15">
                Looking for an amazing & well-functional LearnPress WordPress
                Theme?...
              </div>
            </div>
          </div>
        </div>
        <div class="articles17">
          <img
            class="articles-child13"
            alt=""
            src="/img/eductaion.jpg"
          />

          <div class="content62">
            <div class="best-learnpress-wordpress25">
              Best LearnPress WordPress Theme Collection for 2023
            </div>
            <div class="meta41">
              <div class="times41">
                <img
                  class="component-2-icon318"
                  alt=""
                  src="/img/eductaion.jpg"
                />

                <div class="home544">Determined-poitras</div>
                <div class="home545">Home</div>
                <img class="icon412" alt="" src="/img/eductaion.jpg" />
              </div>
              <div class="students66">
                <img
                  class="component-2-icon318"
                  alt=""
                  src="/img/eductaion.jpg"
                />

                <div class="home544">Jan 24, 22023</div>
                <div class="home545">Home</div>
                <img class="icon412" alt="" src="/img/eductaion.jpg" />
              </div>
            </div>
            <div class="looking-for-an15">
              Looking for an amazing & well-functional LearnPress WordPress
              Theme?...
            </div>
          </div>
        </div>
      </div>
    </div>

</main>

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
              <img class="icon378" alt="" src="/img/eductaion.jpg" />

              <img class="icon378" alt="" src="/img/eductaion.jpg" />

              <img class="icon378" alt="" src="/img/eductaion.jpg" />

              <img class="icon378" alt="" src="/img/eductaion.jpg" />

              <img class="icon378" alt="" src="/img/eductaion.jpg" />
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
        <img class="vector-icon12" alt="" src="/img/eductaion.jpg"/>
      </div>
    </div>

</body>
</html>
