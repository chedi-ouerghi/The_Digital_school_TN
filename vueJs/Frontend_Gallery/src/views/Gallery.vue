<template>
  <div class="gallery-container">
    <video autoplay muted loop class="background-video">
      <source src="https://cdn.pixabay.com/video/2022/03/18/111179-689949805_large.mp4" type="video/mp4">
    </video>
    <div class="gallery-content">

        <div class="gallery-section">
      <h1>Galerie d'images</h1>
      <p class="description">
        Bienvenue dans la galerie de Rise Architecture, une firme d'architecture polyvalente dédiée à créer des espaces inspirants et innovants. Explorez nos projets ci-dessous.
      </p>
      <!-- <b>read more</b> -->
      <div class="gallery">
        <div v-for="(image, index) in images" :key="image.id" class="image-card" :class="{ 'margin-top-15': index % 2 !== 0 }">
          <img :src="image.url" :alt="image.title" />
          <div class="image-overlay">
            <p>{{ image.title }}</p>
          </div>
        </div>
      </div>
        </div>

      <div class="Top-artist">
        <h2>Nos meilleurs artistes</h2>
        <section class="wrapper" v-if="artists.length >= 6">
          <h1 class="title artists__title">Artists</h1>
          <div class="artists">
            <div class="artists__body">
              <div class="scene">
                <div class="box" :data-show="currentFace">
                  <div class="box__face front"><img :src="artists[0].image" :alt="artists[0].name" /></div>
                  <div class="box__face back"><img :src="artists[1].image" :alt="artists[1].name" /></div>
                  <div class="box__face left"><img :src="artists[2].image" :alt="artists[2].name" /></div>
                  <div class="box__face right"><img :src="artists[3].image" :alt="artists[3].name" /></div>
                  <div class="box__face top"><img :src="artists[4].image" :alt="artists[4].name" /></div>
                  <div class="box__face bottom"><img :src="artists[5].image" :alt="artists[5].name" /></div>
                </div>
              </div>
              <div class="content">
                <h2 class="title content__title" id="artistName">{{ currentArtist.name }}</h2>
                <p class="text" id="artistDesc">{{ currentArtist.description }}</p>
              </div>
            </div>
          </div>
          <div class="controls">
            <button @click="prev" class="btn">
              <svg xmlns="http://www.w3.org/2000/svg" height="16" width="8" viewBox="0 0 256 512">
                <path d="M9.4 278.6c-12.5-12.5-12.5-32.8 0-45.3l128-128c9.2-9.2 22.9-11.9 34.9-6.9s19.8 16.6 19.8 29.6v256c0 12.9-7.8 24.6-19.8 29.6s-25.7 2.2-34.9-6.9l-128-128z"/>
              </svg>
            </button>
            <p><span id="counter">{{ currentIndex + 1 }}</span>/<span>{{ artists.length }}</span></p>
            <button @click="next" class="btn">
              <svg xmlns="http://www.w3.org/2000/svg" height="16" width="8" viewBox="0 0 256 512">
                <path d="M246.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-128-128c-9.2-9.2-22.9-11.9-34.9-6.9s-19.8 16.6-19.8 29.6v256c0 12.9 7.8 24.6 19.8 29.6s25.7 2.2 34.9-6.9l128-128z"/>
              </svg>
            </button>
          </div>
        </section>
        <p v-else>Loading artists...</p>
      </div>
      
    </div>

     <footer>
    <p>&copy; 2024 Rise Architecture. All rights reserved.</p>
  </footer>

  </div>
</template>

<script>
export default {
  name: 'Gallery',
  data() {
    return {
      images: [
        { id: 1, url: 'https://images.unsplash.com/photo-1683022927321-97cd6eeda374?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTB8fG1vZGVybiUyMG9mZmljZSUyMGJ1aWxkaW5nfGVufDB8fDB8fHww', title: 'Modern Office' },
        { id: 2, url: 'https://images.unsplash.com/photo-1533280385001-c32ffcbd52a7?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D', title: 'Residential Building' },
        { id: 3, url: 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=MnwyMzAzMjV8MHwxfGFsbHwzfHx8fHx8fDE2MjI1MzcwMTQ&ixlib=rb-1.2.1&q=80&w=400', title: 'Skyscraper' },
        { id: 4, url: 'https://images.unsplash.com/photo-1537726235470-8504e3beef77?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D', title: 'Interior Design' },
        { id: 5, url: 'https://plus.unsplash.com/premium_photo-1664304066233-80febd2729b2?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D', title: 'Commercial Space' },
        { id: 6, url: 'https://images.unsplash.com/photo-1557227065-a68aa0bb1673?q=80&w=1595&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D', title: 'Urban Planning' }
      ],
      artists: [
        { id: 1, name: 'Jean Nouvel', image: 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ae/Jean_Nouvel_2009_Vienna_frontal.jpg/280px-Jean_Nouvel_2009_Vienna_frontal.jpg', description: 'Architecte célèbre pour ses conceptions novatrices.' },
        { id: 2, name: 'Zaha Hadid', image: 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/73/Zaha_Hadid_in_Heydar_Aliyev_Cultural_center_in_Baku_nov_2013.jpg/220px-Zaha_Hadid_in_Heydar_Aliyev_Cultural_center_in_Baku_nov_2013.jpg', description: 'Renommée pour son architecture déconstructiviste.' },
        { id: 3, name: 'Frank Gehry', image: 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c0/Turner_07_FGLecture.JPG/201px-Turner_07_FGLecture.JPG', description: 'Connu pour ses bâtiments sculpturaux et innovants.' },
        { id: 4, name: 'Renzo Piano', image: 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0f/Renzo_Piano%2C_portrait.jpg/260px-Renzo_Piano%2C_portrait.jpg', description: 'Réputé pour ses projets internationaux emblématiques.' },
        { id: 5, name: 'Norman Foster', image: 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a6/Norman_Foster_dresden_061110.jpg/200px-Norman_Foster_dresden_061110.jpg', description: 'Architecte reconnu pour ses designs modernes et technologiques.' },
        { id: 6, name: 'Tadao Ando', image: 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/68/Tadao_Ando_2004.jpg/280px-Tadao_Ando_2004.jpg', description: 'Célèbre pour ses œuvres en béton et ses jeux de lumière.' }
      ],
      currentIndex: 0,
      faces: ['front', 'back', 'left', 'right', 'top', 'bottom']
    };
  },
  computed: {
    currentArtist() {
      return this.artists[this.currentIndex];
    },
    currentFace() {
      return this.faces[this.currentIndex % this.faces.length];
    }
  },
  methods: {
    next() {
      if (this.currentIndex < this.artists.length - 1) {
        this.currentIndex++;
      }
    },
    prev() {
      if (this.currentIndex > 0) {
        this.currentIndex--;
      }
    }
  }
}
</script>


<style src="./gallery.css"></style>
