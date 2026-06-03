<template>
  <div
    ref="containerRef"
    :class="cn('pointer-events-none fixed inset-0 -z-1', className)"
    v-bind="attrs"
  />
</template>

<script setup lang="ts">
import * as THREE from 'three';
import { onMounted, onUnmounted, ref, useAttrs, watch } from 'vue';

// Props
interface Props {
  className?: string
  theme?: 'light' | 'dark'
}

const props = withDefaults(defineProps<Props>(), {
  theme: 'light'
})

const attrs = useAttrs()
const containerRef = ref<HTMLDivElement | null>(null)

// Utilité pour combiner les classes (vous devrez l'adapter à votre projet)
const cn = (...classes: (string | undefined)[]): string => {
  return classes.filter(Boolean).join(' ')
}

// Références Three.js
const sceneRef = ref<{
  scene: THREE.Scene
  camera: THREE.PerspectiveCamera
  renderer: THREE.WebGLRenderer
  particles: THREE.Points[]
  animationId: number
  count: number
} | null>(null)

const initThreeJS = () => {
  if (!containerRef.value) return

  const SEPARATION = 150
  const AMOUNTX = 40
  const AMOUNTY = 60

  // Configuration de la scène
  const scene = new THREE.Scene()
  scene.fog = new THREE.Fog(0xffffff, 2000, 10000)

  const camera = new THREE.PerspectiveCamera(
    60,
    window.innerWidth / window.innerHeight,
    1,
    10000
  )
  camera.position.set(0, 355, 1220)

  const renderer = new THREE.WebGLRenderer({
    alpha: true,
    antialias: true
  })
  renderer.setPixelRatio(window.devicePixelRatio)
  renderer.setSize(window.innerWidth, window.innerHeight)
  renderer.setClearColor(scene.fog.color, 0)

  containerRef.value.appendChild(renderer.domElement)

  // Création des particules
  const positions: number[] = []
  const colors: number[] = []

  // Création de la géométrie pour toutes les particules
  const geometry = new THREE.BufferGeometry()

  for (let ix = 0; ix < AMOUNTX; ix++) {
    for (let iy = 0; iy < AMOUNTY; iy++) {
      const x = ix * SEPARATION - (AMOUNTX * SEPARATION) / 2
      const y = 0 // Sera animé
      const z = iy * SEPARATION - (AMOUNTY * SEPARATION) / 2

      positions.push(x, y, z)
      if (props.theme === 'dark') {
        colors.push(200, 200, 200)
      } else {
        colors.push(0, 0, 0)
      }
    }
  }

  geometry.setAttribute(
    'position',
    new THREE.Float32BufferAttribute(positions, 3)
  )
  geometry.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3))

  // Création du matériau
  const material = new THREE.PointsMaterial({
    size: 8,
    vertexColors: true,
    transparent: true,
    opacity: 0.8,
    sizeAttenuation: true
  })

  // Création de l'objet points
  const points = new THREE.Points(geometry, material)
  scene.add(points)

  let count = 0
  let animationId: number

  // Fonction d'animation
  const animate = () => {
    animationId = requestAnimationFrame(animate)

    const positionAttribute = geometry.attributes.position
    const positions = positionAttribute.array as Float32Array

    let i = 0
    for (let ix = 0; ix < AMOUNTX; ix++) {
      for (let iy = 0; iy < AMOUNTY; iy++) {
        const index = i * 3

        // Animation de la position Y avec des ondes sinus
        positions[index + 1] =
          Math.sin((ix + count) * 0.3) * 50 +
          Math.sin((iy + count) * 0.5) * 50

        i++
      }
    }

    positionAttribute.needsUpdate = true

    renderer.render(scene, camera)
    count += 0.1
  }

  // Gestion du redimensionnement
  const handleResize = () => {
    camera.aspect = window.innerWidth / window.innerHeight
    camera.updateProjectionMatrix()
    renderer.setSize(window.innerWidth, window.innerHeight)
  }

  window.addEventListener('resize', handleResize)

  // Démarrer l'animation
  animate()

  // Stocker les références
  sceneRef.value = {
    scene,
    camera,
    renderer,
    particles: [points],
    animationId,
    count
  }
}

const cleanup = () => {
  window.removeEventListener('resize', handleResize)

  if (sceneRef.value) {
    cancelAnimationFrame(sceneRef.value.animationId)

    // Nettoyer les objets Three.js
    sceneRef.value.scene.traverse((object) => {
      if (object instanceof THREE.Points) {
        object.geometry.dispose()
        if (Array.isArray(object.material)) {
          object.material.forEach((material) => material.dispose())
        } else {
          object.material.dispose()
        }
      }
    })

    sceneRef.value.renderer.dispose()

    if (containerRef.value && sceneRef.value.renderer.domElement) {
      containerRef.value.removeChild(sceneRef.value.renderer.domElement)
    }
  }
}

// Gestion du redimensionnement (déclaration pour cleanup)
const handleResize = () => {
  if (sceneRef.value) {
    sceneRef.value.camera.aspect = window.innerWidth / window.innerHeight
    sceneRef.value.camera.updateProjectionMatrix()
    sceneRef.value.renderer.setSize(window.innerWidth, window.innerHeight)
  }
}

// Mettre à jour les couleurs quand le thème change
const updateColors = () => {
  if (!sceneRef.value) return

  const geometry = sceneRef.value.particles[0].geometry
  const colorAttribute = geometry.getAttribute('color')
  const colors = colorAttribute.array as Float32Array

  for (let i = 0; i < colors.length; i += 3) {
    if (props.theme === 'dark') {
      colors[i] = 200 // R
      colors[i + 1] = 200 // G
      colors[i + 2] = 200 // B
    } else {
      colors[i] = 0 // R
      colors[i + 1] = 0 // G
      colors[i + 2] = 0 // B
    }
  }

  colorAttribute.needsUpdate = true
}

// Lifecycle
onMounted(() => {
  initThreeJS()
})

onUnmounted(() => {
  cleanup()
})

// Watcher pour le changement de thème
watch(() => props.theme, () => {
  updateColors()
})
</script>