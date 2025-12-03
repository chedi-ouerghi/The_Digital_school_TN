<template>
  <div class="min-h-screen bg-[#0b0f19] text-white py-8">
    <div class="container mx-auto px-4">
      <!-- Header -->
      <header class="mb-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="mb-4 md:mb-0">
          <h1 class="text-3xl md:text-4xl font-bold">Blog Crypto & Market Analysis</h1>
          <p class="text-gray-300 mt-2 max-w-2xl">
            Stay informed with the latest trends, news and technical analysis from the world of cryptocurrencies.
          </p>
        </div>
        <div v-if="isAdmin" class="mt-2 w-full sm:w-auto">
          <button 
            @click="openCreateModal" 
            class="w-full sm:w-auto px-6 py-3 bg-[#3b82f6] hover:bg-[#2563eb] text-white font-semibold rounded-2xl shadow transition-colors"
          >
            Add Article
          </button>
        </div>
      </header>

      <!-- Main Content -->
      <div class="flex flex-col lg:flex-row gap-8">
        <!-- Left Column: Articles -->
        <main class="flex-1">
          <!-- Search & Filter -->
          <div class="flex flex-col md:flex-row items-stretch md:items-center gap-4 mb-8">
            <div class="flex-1 relative">
              <input 
                v-model="search" 
                @input="debouncedFetch" 
                placeholder="Search articles..." 
                class="w-full p-4 rounded-2xl bg-[#0f1724] placeholder-gray-400 text-white border border-gray-800 focus:border-[#3b82f6] focus:outline-none"
              />
            </div>
            <div class="w-full md:w-48">
              <select 
                v-model="category" 
                @change="fetchPosts" 
                class="w-full p-4 rounded-2xl bg-[#0f1724] text-white border border-gray-800 focus:border-[#3b82f6] focus:outline-none cursor-pointer"
              >
                <option value="">All Categories</option>
                <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
              </select>
            </div>
          </div>

          <!-- Loading State -->
          <div v-if="loading" class="flex justify-center items-center py-12">
            <div class="text-gray-400 text-lg">Loading articles...</div>
          </div>

          <!-- Articles Grid -->
          <div v-else>
            <div v-if="items.length === 0" class="text-center py-12 text-gray-400">
              No articles found. Try a different search.
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
              <ArticleCard 
                v-for="post in items" 
                :key="post.id" 
                :post="post" 
                @edit="onEdit" 
                @delete="onDelete" 
              />
            </div>

            <!-- Load More -->
            <div v-if="canLoadMore" class="mt-10 flex justify-center">
              <button 
                @click="loadMore" 
                class="px-8 py-3 bg-[#1e293b] hover:bg-[#334155] text-white font-medium rounded-2xl transition-colors"
              >
                Load More Articles
              </button>
            </div>
          </div>
        </main>

        <!-- Right Column: Sidebar -->
        <aside class="lg:w-80">
          <div class="bg-[#0f1724] p-6 rounded-2xl border border-gray-800">
            <h4 class="font-bold text-lg mb-4">Newsletter</h4>
            <p class="text-sm text-gray-300 mb-5">
              Subscribe to receive the latest crypto analysis and market insights directly in your inbox.
            </p>
            <div class="flex flex-col sm:flex-row gap-3">
              <input 
                v-model="email" 
                placeholder="Your email address" 
                class="flex-1 p-3 rounded-xl bg-[#1a2234] text-white border border-gray-700 focus:outline-none focus:border-[#3b82f6]"
              />
              <button class="px-5 py-3 bg-[#3b82f6] hover:bg-[#2563eb] text-white font-medium rounded-xl transition-colors whitespace-nowrap">
                Subscribe
              </button>
            </div>
            <p class="text-xs text-gray-400 mt-3">
              We respect your privacy. Unsubscribe at any time.
            </p>
          </div>
        </aside>
      </div>
    </div>

    <!-- Admin Modal (Create / Edit) -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4">
      <div class="w-full max-w-3xl max-h-[90vh] overflow-y-auto bg-[#0f1724] text-white rounded-2xl p-6 sm:p-8 shadow-2xl border border-gray-800">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-2xl font-bold">
            {{ editing ? 'Edit Article' : 'Create New Article' }}
          </h3>
          <button 
            @click="showModal = false" 
            class="text-gray-400 hover:text-white text-2xl"
          >
            &times;
          </button>
        </div>

        <form @submit.prevent="submitForm" class="space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Title -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-300 mb-2">Title *</label>
              <input 
                v-model="form.title" 
                required
                placeholder="Article title" 
                class="w-full p-4 rounded-xl bg-[#131a2a] text-white border border-gray-700 focus:border-[#3b82f6] focus:outline-none"
              />
            </div>

            <!-- Category -->
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">Category *</label>
              <select 
                v-model="form.category"
                required
                class="w-full p-4 rounded-xl bg-[#131a2a] text-white border border-gray-700 focus:border-[#3b82f6] focus:outline-none cursor-pointer"
              >
                <option value="" disabled>Select a category</option>
                <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
              </select>
            </div>

            <!-- Published Date -->
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">Published Date</label>
              <input 
                v-model="form.published_at" 
                type="date"
                class="w-full p-4 rounded-xl bg-[#131a2a] text-white border border-gray-700 focus:border-[#3b82f6] focus:outline-none"
              />
            </div>

            <!-- Image URL -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-300 mb-2">Image URL</label>
              <input 
                v-model="form.image" 
                placeholder="https://example.com/image.jpg" 
                class="w-full p-4 rounded-xl bg-[#131a2a] text-white border border-gray-700 focus:border-[#3b82f6] focus:outline-none"
              />
            </div>

            <!-- Summary -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-300 mb-2">Summary *</label>
              <textarea 
                v-model="form.summary" 
                required
                placeholder="Brief summary of the article" 
                rows="3"
                class="w-full p-4 rounded-xl bg-[#131a2a] text-white border border-gray-700 focus:border-[#3b82f6] focus:outline-none resize-none"
              ></textarea>
            </div>

            <!-- Tags -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-300 mb-2">Tags</label>
              <input 
                v-model="form.tags" 
                placeholder="Bitcoin, Ethereum, DeFi, NFTs (comma separated)" 
                class="w-full p-4 rounded-xl bg-[#131a2a] text-white border border-gray-700 focus:border-[#3b82f6] focus:outline-none"
              />
              <p class="text-xs text-gray-400 mt-2">Separate tags with commas</p>
            </div>

            <!-- Content -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-300 mb-2">Content (HTML allowed) *</label>
              <textarea 
                v-model="form.content" 
                required
                placeholder="Full article content..." 
                rows="8"
                class="w-full p-4 rounded-xl bg-[#131a2a] text-white border border-gray-700 focus:border-[#3b82f6] focus:outline-none resize-y font-mono text-sm"
              ></textarea>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="flex flex-col sm:flex-row justify-end gap-4 pt-6 border-t border-gray-800">
            <button 
              type="button"
              @click="showModal = false" 
              class="w-full sm:w-auto px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white font-medium rounded-xl transition-colors"
            >
              Cancel
            </button>
            <button 
              type="submit"
              class="w-full sm:w-auto px-6 py-3 bg-[#3b82f6] hover:bg-[#2563eb] text-white font-medium rounded-xl transition-colors"
            >
              {{ editing ? 'Update Article' : 'Publish Article' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import ArticleCard from '../components/ArticleCard.vue'
import { blogApi } from '../services/api'
import auth from '../services/auth'

// Debounce utility
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
const email = ref('')
const isAdmin = auth.isAdmin()

// Modal state
const showModal = ref(false)
const editing = ref<any | null>(null)
const form = ref({
  title: '',
  category: '',
  summary: '',
  content: '',
  tags: '',
  image: '',
  published_at: ''
})

// Computed
const canLoadMore = computed(() => {
  // Implement your pagination logic here
  // For now, return true if there are items
  return items.value.length > 0
})

// Methods
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
    
    if (res && res.data) {
      if (reset) {
        items.value = res.data
      } else {
        items.value = [...items.value, ...res.data]
      }
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

const submitForm = async () => {
  try {
    const payload: any = {
      title: form.value.title.trim(),
      category: form.value.category,
      summary: form.value.summary.trim(),
      content: form.value.content,
      tags: form.value.tags ? form.value.tags.split(',').map((t: string) => t.trim()).filter(Boolean) : [],
      image: form.value.image.trim(),
      published_at: form.value.published_at || null,
    }

    if (editing.value) {
      await blogApi.update(editing.value.id || editing.value.slug, payload)
    } else {
      await blogApi.create(payload)
    }

    showModal.value = false
    await fetchPosts(true)
  } catch (e) {
    console.error('Error saving post', e)
    alert('Error saving post. Please check your inputs and try again.')
  }
}

const onEdit = (post: any) => {
  if (!isAdmin) return
  openEditModal(post)
}

const onDelete = async (post: any) => {
  if (!isAdmin) return
  if (!confirm('Are you sure you want to delete this article? This action cannot be undone.')) return
  
  try {
    await blogApi.delete(post.id || post.slug)
    await fetchPosts(true)
  } catch (e) {
    console.error('Delete failed', e)
    alert('Failed to delete article. Please try again.')
  }
}

const loadMore = async () => {
  page.value++
  await fetchPosts(false)
}

// Lifecycle
onMounted(() => {
  fetchPosts()
})
</script>

<style scoped>
/* Custom scrollbar for modal */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #1a2234;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: #3b82f6;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #2563eb;
}
</style>