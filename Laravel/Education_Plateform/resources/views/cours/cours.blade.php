@extends('layouts.master')

@section('title', 'cours')

@section('content')


    <div class="listing10">
      <div class="listing11">
        <div class="top-heading17">
          <div class="all-courses4">All Courses</div>
          <div class="frame-parent22">
          <div class="search-parent2">
    <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
</div>
          </div>
        </div>
        <div class="courses46">
          @forelse($courses as $course)
   <div class="courses47">
    <div class="image24">
        <img class="image-child21" alt="" src="/img/eductaion.jpg" />
        <div class="photography-wrapper22">
            <div class="photography25">{{ $course->Titre }}</div>
        </div>
    </div>
    <div class="content65">
        <div class="frame-parent23">
            <div class="by-determined-poitras-parent22">
                <div class="search16">
                    <span>by </span>
                    <span class="determined-poitras25">{{ $course->NomInstructeur }}</span>
                </div>
                <div class="create-an-lms24">
                    {{ $course->Description }}
                </div>
            </div>
            <div class="meta44">
                <div class="times44">
                    <img class="component-2-icon324" alt="" src="/img/education.jpg" />
                    <div class="search16"> Duree: {{ $course->Duree }}</div>
                    <div class="search16"> Niveau: {{ $course->NiveauDifficulte }}</div>
                    <div class="search16"> Nombre Lecons: {{ $course->NombreLecons }}</div>
                    <div class="home557">Home</div>
                    <img class="icon421" alt="" src="/img/education.jpg" />
                </div>
                <div class="times44">
                    <!-- Ajoutez ici d'autres informations dynamiques sur le cours -->
                </div>
                <!-- Ajoutez d'autres blocs dynamiques si nécessaire -->
            </div>
        </div>
        <div class="bottom6">
            <div class="price25">
                <div class="search16">${{ $course->Prix }}</div>
                <div class="free22">Free</div>
            </div>
            <div class="accepter">accepter: {{ $course->NombreEtudiantsAcceptes }}</div>
            <div class="view-more24-bouton">View more</div>
        </div>
    </div>
</div>

 @empty
<div class="error-message">
    Aucun cours n'a été trouvé. En cours de réparation.
</div>

    @endforelse
</div>
      </div>
      <div class="sidebar-courses-listing2">
        <div class="category9">
          <div class="all-courses4">Course category</div>
          <div class="cate-1-parent9">
            <div class="cate-116">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="home564">Commercial</div>
              <div class="search16">15</div>
              <img class="icon421" alt="" src="/img/eductaion.jpg" />
            </div>
        
          </div>
        </div>
        <div class="category9">
          <div class="all-courses4">Instructors</div>
          <div class="cate-1-parent9">
            <div class="cate-116">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="home564">Kenny White</div>
              <div class="search16">15</div>
              <img class="icon421" alt="" src="/img/eductaion.jpg" />
            </div>
            <div class="cate-116">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="home564">John Doe</div>
              <div class="search16">15</div>
              <img class="icon421" alt="" src="/img/eductaion.jpg" />
            </div>
            <div class="cate-314">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="home564">Shop</div>
              <div class="search16">15</div>
              <img class="icon421" alt="" src="/img/eductaion.jpg" />
            </div>
            <div class="cate-613">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="home564">Educate</div>
              <div class="search16">15</div>
              <img class="icon421" alt="" src="/img/eductaion.jpg" />
            </div>
            <div class="cate-314">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="home564">Academy</div>
              <div class="search16">15</div>
              <img class="icon421" alt="" src="/img/eductaion.jpg" />
            </div>
            <div class="cate-613">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="home564">Single family home</div>
              <div class="search16">15</div>
              <img class="icon421" alt="" src="/img/eductaion.jpg" />
            </div>
            <div class="cate-613">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="home564">Studio</div>
              <div class="search16">15</div>
              <img class="icon421" alt="" src="/img/eductaion.jpg" />
            </div>
            <div class="cate-613">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="home564">University</div>
              <div class="search16">15</div>
              <img class="icon421" alt="" src="/img/eductaion.jpg" />
            </div>
          </div>
        </div>
        <div class="category9">
          <div class="all-courses4">Price</div>
          <div class="cate-1-parent9">
            <div class="cate-313">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="home564">All</div>
              <div class="search16">15</div>
              <img class="icon421" alt="" src="/img/eductaion.jpg" />
            </div>
            <div class="cate-116">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="home564">Free</div>
              <div class="search16">15</div>
              <img class="icon421" alt="" src="/img/eductaion.jpg" />
            </div>
            <div class="cate-116">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="home564">Paid</div>
              <div class="search16">15</div>
              <img class="icon421" alt="" src="/img/eductaion.jpg" />
            </div>
            <div class="cate-613">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="home564">Educate</div>
              <div class="search16">15</div>
              <img class="icon421" alt="" src="/img/eductaion.jpg" />
            </div>
            <div class="cate-314">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="home564">Academy</div>
              <div class="search16">15</div>
              <img class="icon421" alt="" src="/img/eductaion.jpg" />
            </div>
            <div class="cate-613">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="home564">Single family home</div>
              <div class="search16">15</div>
              <img class="icon421" alt="" src="/img/eductaion.jpg" />
            </div>
            <div class="cate-613">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="home564">Studio</div>
              <div class="search16">15</div>
              <img class="icon421" alt="" src="/img/eductaion.jpg" />
            </div>
            <div class="cate-613">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="home564">University</div>
              <div class="search16">15</div>
              <img class="icon421" alt="" src="/img/eductaion.jpg" />
            </div>
          </div>
        </div>
        <div class="review-container">
          <div class="all-courses4">Review</div>
          <div class="option-1-container">
            <div class="cate-116">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="star10">
                <img
                  class="component-2-icon324"
                  alt=""
                  src="./public/component-3.svg"
                />

                <img
                  class="component-2-icon324"
                  alt=""
                  src="./public/component-3.svg"
                />

                <img
                  class="component-2-icon324"
                  alt=""
                  src="./public/component-3.svg"
                />

                <img
                  class="component-2-icon324"
                  alt=""
                  src="./public/component-3.svg"
                />

                <img
                  class="component-2-icon324"
                  alt=""
                  src="./public/component-3.svg"
                />
              </div>
              <div class="cate-116">
                <div class="div120">5.0</div>
                <div class="div121">(1,025)</div>
              </div>
            </div>
            <div class="cate-116">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="star10">
                <img
                  class="component-2-icon324"
                  alt=""
                  src="./public/component-3.svg"
                />

                <img
                  class="component-2-icon324"
                  alt=""
                  src="./public/component-3.svg"
                />

                <img
                  class="component-2-icon324"
                  alt=""
                  src="./public/component-3.svg"
                />

                <img
                  class="component-2-icon324"
                  alt=""
                  src="./public/component-3.svg"
                />

                <img
                  class="component-2-icon324"
                  alt=""
                  src="./public/component-7.svg"
                />
              </div>
              <div class="cate-116">
                <div class="div122">4.0 & Up</div>
                <div class="div121">(1,025)</div>
              </div>
            </div>
            <div class="cate-116">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="star10">
                <img
                  class="component-2-icon324"
                  alt=""
                  src="./public/component-3.svg"
                />

                <img
                  class="component-2-icon324"
                  alt=""
                  src="./public/component-3.svg"
                />

                <img
                  class="component-2-icon324"
                  alt=""
                  src="./public/component-3.svg"
                />

                <img
                  class="component-2-icon324"
                  alt=""
                  src="./public/component-7.svg"
                />

                <img
                  class="component-2-icon324"
                  alt=""
                  src="./public/component-7.svg"
                />
              </div>
              <div class="cate-116">
                <div class="div124">3.0 & Up</div>
                <div class="div121">(1,025)</div>
              </div>
            </div>
            <div class="cate-116">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="star10">
                <img
                  class="component-2-icon324"
                  alt=""
                  src="./public/component-3.svg"
                />

                <img
                  class="component-2-icon324"
                  alt=""
                  src="./public/component-3.svg"
                />

                <img
                  class="component-2-icon324"
                  alt=""
                  src="./public/component-7.svg"
                />

                <img
                  class="component-2-icon324"
                  alt=""
                  src="./public/component-7.svg"
                />

                <img
                  class="component-2-icon324"
                  alt=""
                  src="./public/component-7.svg"
                />
              </div>
              <div class="cate-116">
                <div class="div124">2.0 & Up</div>
                <div class="div121">(1,025)</div>
              </div>
            </div>
            <div class="cate-116">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="star10">
                <img
                  class="component-2-icon324"
                  alt=""
                  src="./public/component-3.svg"
                />

                <img
                  class="component-2-icon324"
                  alt=""
                  src="./public/component-7.svg"
                />

                <img
                  class="component-2-icon324"
                  alt=""
                  src="./public/component-7.svg"
                />

                <img
                  class="component-2-icon324"
                  alt=""
                  src="./public/component-7.svg"
                />

                <img
                  class="component-2-icon324"
                  alt=""
                  src="./public/component-7.svg"
                />
              </div>
              <div class="cate-116">
                <div class="div128">1.0 & Up</div>
                <div class="div121">(1,025)</div>
              </div>
            </div>
          </div>
        </div>
        <div class="category9">
          <div class="all-courses4">Level</div>
          <div class="cate-1-parent9">
            <div class="cate-116">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="home564">All levels</div>
              <div class="search16">15</div>
              <img class="icon421" alt="" src="/img/eductaion.jpg" />
            </div>
            <div class="cate-116">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="home564">Beginner</div>
              <div class="search16">15</div>
              <img class="icon421" alt="" src="/img/eductaion.jpg" />
            </div>
            <div class="cate-313">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="home564">Intermidiate</div>
              <div class="search16">15</div>
              <img class="icon421" alt="" src="/img/eductaion.jpg" />
            </div>
            <div class="cate-116">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="home564">Expert</div>
              <div class="search16">15</div>
              <img class="icon421" alt="" src="/img/eductaion.jpg" />
            </div>
            <div class="cate-314">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="home564">Academy</div>
              <div class="search16">15</div>
              <img class="icon421" alt="" src="/img/eductaion.jpg" />
            </div>
            <div class="cate-613">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="home564">Single family home</div>
              <div class="search16">15</div>
              <img class="icon421" alt="" src="/img/eductaion.jpg" />
            </div>
            <div class="cate-613">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="home564">Studio</div>
              <div class="search16">15</div>
              <img class="icon421" alt="" src="/img/eductaion.jpg" />
            </div>
            <div class="cate-613">
              <img
                class="component-2-icon324"
                alt=""
                src="/img/eductaion.jpg"
              />

              <div class="home564">University</div>
              <div class="search16">15</div>
              <img class="icon421" alt="" src="/img/eductaion.jpg" />
            </div>
          </div>
        </div>
      </div>
    </div>




@endsection
