<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import ArticleCard from '../components/ArticleCard.vue'
import { blogApi } from '../services/api'
import auth from '../services/auth'

// Debounce function
function debounceFn(fn: Function, wait = 300) {
  let timeoutId: any = null
  return (...args: any[]) => {
    if (timeoutId) clearTimeout(timeoutId)
    timeoutId = setTimeout(() => fn(...args), wait)
  }
}

// Data
const items = ref<any[]>([])
const page = ref(1)
const loading = ref(false)
const search = ref('')
const category = ref('')
const categories = ['News', 'Technical Analysis', 'Blockchain', 'Beginner Guides', 'Altcoins', 'Trends']
const isAdmin = auth.isAdmin()
const showModal = ref(false)
const editing = ref<any | null>(null)

const form = ref({
  title: '',
  category: '',
  summary: '',
  content: '',
  tags: '',
  image: '',
  published_at: new Date().toISOString().split('T')[0]
})

const canLoadMore = computed(() => items.value.length > 0)
const debouncedFetch = debounceFn(() => fetchPosts(true), 400)

const fetchPosts = async (reset = true) => {
  loading.value = true
  try {
    if (reset) {
      page.value = 1
      items.value = []
    }
    const res = await blogApi.list({
      page: page.value,
      search: search.value,
      category: category.value
    })
    if (res?.data) {
      items.value = reset ? res.data : [...items.value, ...res.data]
    }
  } catch (err) {
    console.error('Error fetching posts:', err)
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  editing.value = null
  form.value = {
    title: '',
    category: '',
    summary: '',
    content: '',
    tags: '',
    image: '',
    published_at: new Date().toISOString().split('T')[0]
  }
  showModal.value = true
}

const openEditModal = (post: any) => {
  editing.value = post
  form.value = {
    title: post.title || '',
    category: post.category || '',
    summary: post.summary || '',
    content: post.content || '',
    tags: Array.isArray(post.tags) ? post.tags.join(', ') : post.tags || '',
    image: post.image || '',
    published_at: post.published_at ? post.published_at.split(' ')[0] : new Date().toISOString().split('T')[0]
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const submitForm = async () => {
  try {
    const payload = {
      title: form.value.title.trim(),
      category: form.value.category,
      summary: form.value.summary.trim(),
      content: form.value.content,
      tags: form.value.tags ? form.value.tags.split(',').map(t => t.trim()).filter(Boolean) : [],
      image: form.value.image.trim(),
      published_at: form.value.published_at || null,
    }

    if (editing.value) {
      await blogApi.update(editing.value.id || editing.value.slug, payload)
    } else {
      await blogApi.create(payload)
    }

    closeModal()
    await fetchPosts(true)
  } catch (e) {
    console.error('Error saving post', e)
    alert('Error saving post')
  }
}

const onEdit = (post: any) => {
  if (!isAdmin) return
  openEditModal(post)
}

const onDelete = async (post: any) => {
  if (!isAdmin) return
  if (!confirm('Delete this article?')) return
  
  try {
    await blogApi.delete(post.id || post.slug)
    await fetchPosts(true)
  } catch (e) {
    console.error('Delete failed', e)
    alert('Failed to delete article')
  }
}

const loadMore = async () => {
  page.value++
  await fetchPosts(false)
}

onMounted(() => {
  fetchPosts()
})
</script>

<template>
  <div class="min-h-screen bg-gradient-to-b from-[#0b0f19] via-[#0f1724] to-[#0a0e17] text-white py-12">
    
    <!-- Blobs décoratifs -->
    <div class="fixed top-10 left-5 w-80 h-80 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fixed bottom-20 right-10 w-96 h-96 bg-purple-500/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="container mx-auto px-4 sm:px-6 relative z-10">
      <!-- Header simplifié -->
      <header class="mb-12 text-center">
        
        <h1 class="text-4xl md:text-5xl font-bold mb-4 pb-2 bg-gradient-to-r from-white via-blue-200 to-white bg-clip-text text-transparent">
          Market Insights
        </h1>
        <p class="text-gray-300 max-w-2xl mx-auto">
          Expert analysis and actionable trading insights for cryptocurrency markets.
        </p>
      </header>

      <!-- Contenu principal -->
      <div class="flex flex-col lg:flex-row gap-8">
        <!-- Articles -->
        <main class="flex-1">
          <!-- Barre de recherche -->
          <div class="flex flex-col md:flex-row gap-4 mb-8">
            <div class="flex-1 relative">
              <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              <input 
                v-model="search" 
                placeholder="Search articles..." 
                class="w-full pl-12 pr-4 py-3 rounded-xl bg-[#0f1724]/80 text-white border border-gray-800 focus:border-blue-500 focus:outline-none" 
                @input="debouncedFetch"
              />
            </div>
            <select 
              v-model="category" 
              class="px-4 py-3 rounded-xl bg-[#0f1724]/80 text-white border border-gray-800 focus:border-blue-500 focus:outline-none" 
              @change="() => fetchPosts(true)"
            >
              <option value="">📋 All Categories</option>
              <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
            </select>
          </div>

          <!-- Bouton Créer Article -->
          <button 
            v-if="isAdmin"
            class="w-full md:w-auto px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-500 text-white font-semibold rounded-xl mb-8 hover:from-blue-500 hover:to-blue-400 transition-all" 
            @click="openCreateModal"
          >
            ✨ Create New Article
          </button>

          <!-- État de chargement -->
          <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500 mb-4"></div>
            <p class="text-gray-400">Loading articles...</p>
          </div>

          <!-- Grille d'articles -->
          <div v-else>
            <div v-if="items.length === 0" class="text-center py-12">
              <p class="text-gray-400">No articles found</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
              <ArticleCard 
                v-for="post in items" 
                :key="post.id" 
                :post="post" 
                @edit="onEdit" 
                @delete="onDelete" 
              />
            </div>

            <!-- Bouton Load More -->
            <div v-if="canLoadMore" class="text-center">
              <button 
                class="px-8 py-3 bg-gray-800 hover:bg-gray-700 text-white rounded-xl border border-gray-700 transition-all" 
                @click="loadMore"
              >
                Load More
              </button>
            </div>
          </div>
        </main>

        <!-- Sidebar -->
        <aside class="lg:w-80">
          <!-- Tags populaires -->
          <div class="bg-gradient-to-b from-[#0f1724] to-[#0a0e17] border border-gray-800 p-6 rounded-2xl sticky top-24">
            <h4 class="font-bold text-lg mb-4">🏷️ Popular Tags</h4>
            <div class="flex flex-wrap gap-2">
              <button 
                v-for="tag in ['Bitcoin', 'Ethereum', 'DeFi', 'Trading']" 
                :key="tag"
                class="px-3 py-1.5 bg-blue-500/10 border border-blue-500/30 text-blue-400 text-sm rounded-full hover:bg-blue-500/20 transition-all"
                @click="category = tag; fetchPosts()"
              >
                #{{ tag }}
              </button>
            </div>
          </div>
        </aside>
      </div>
    </div>

    <!-- Modal Admin -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm px-4"
    >
      <!-- Modal container -->
      <div
        class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto
               bg-gradient-to-br from-[#0f1724] to-[#0a0e17]
               text-white rounded-2xl p-6
               border border-blue-500/20 shadow-2xl"
      >
        <!-- Header Modal -->
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-800">
          <h3 class="text-xl font-bold">
            {{ editing ? '✏️ Edit Article' : '✍️ Create Article' }}
          </h3>
          <button
            class="text-gray-400 hover:text-white text-2xl leading-none"
            @click="closeModal"
          >
            &times;
          </button>
        </div>

        <!-- Formulaire -->
        <form class="space-y-4" @submit.prevent="submitForm">
          <!-- Title -->
          <div>
            <label class="block text-sm font-semibold text-gray-300 mb-2">
              Title *
            </label>
            <input
              v-model="form.title"
              required
              placeholder="Article title"
              class="w-full p-3 rounded-lg bg-[#1a2332]
                     border border-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all"
            />
          </div>

          <!-- Category + Date -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-300 mb-2">
                Category *
              </label>
              <select
                v-model="form.category"
                required
                class="w-full p-3 rounded-lg bg-[#1a2332]
                       border border-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none cursor-pointer transition-all"
              >
                <option value="" disabled>Select category</option>
                <option v-for="c in categories" :key="c" :value="c">
                  {{ c }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-300 mb-2">
                Published Date
              </label>
              <input
                v-model="form.published_at"
                type="date"
                class="w-full p-3 rounded-lg bg-[#1a2332]
                       border border-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all"
              />
            </div>
          </div>

          <!-- Image URL -->
          <div>
            <label class="block text-sm font-semibold text-gray-300 mb-2">
              Featured Image URL
            </label>
            <input
              v-model="form.image"
              type="url"
              placeholder="https://example.com/image.jpg"
              class="w-full p-3 rounded-lg bg-[#1a2332]
                     border border-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all"
            />
          </div>

          <!-- Summary -->
          <div>
            <label class="block text-sm font-semibold text-gray-300 mb-2">
              Summary *
            </label>
            <textarea
              v-model="form.summary"
              required
              rows="3"
              placeholder="Brief summary of your article"
              class="w-full p-3 rounded-lg bg-[#1a2332]
                     border border-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 resize-none outline-none transition-all"
            ></textarea>
          </div>

          <!-- Tags -->
          <div>
            <label class="block text-sm font-semibold text-gray-300 mb-2">
              Tags
            </label>
            <input
              v-model="form.tags"
              placeholder="Bitcoin, Ethereum, DeFi, Trading (comma separated)"
              class="w-full p-3 rounded-lg bg-[#1a2332]
                     border border-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all"
            />
            <p class="text-xs text-gray-400 mt-1">Separate tags with commas</p>
          </div>

          <!-- Content -->
          <div>
            <label class="block text-sm font-semibold text-gray-300 mb-2">
              Content *
            </label>
            <textarea
              v-model="form.content"
              required
              rows="8"
              placeholder="Article content (HTML allowed)"
              class="w-full p-3 rounded-lg bg-[#1a2332]
                     border border-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 resize-y outline-none transition-all font-mono text-sm"
            ></textarea>
          </div>

          <!-- Actions -->
          <div class="flex justify-end gap-3 pt-6 border-t border-gray-800">
            <button
              type="button"
              class="px-6 py-2.5 bg-gray-700/60 hover:bg-gray-700 text-white font-medium rounded-lg transition-all"
              @click="closeModal"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-500
                     hover:from-blue-500 hover:to-blue-400 text-white font-medium rounded-lg transition-all"
            >
              {{ editing ? '💾 Update' : '🚀 Publish' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>


<style scoped>
/* Scrollbar personnalisée */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #0f1724;
}

::-webkit-scrollbar-thumb {
  background: #3b82f6;
  border-radius: 3px;
}

* {
  transition: all 0.2s ease;
}
</style>