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

    // The API returns a wrapper { success, data: { data: [...] } }
    // Accept both shapes: direct array or paginated wrapper
    const resData = (res && res.data && Array.isArray(res.data))
      ? res.data
      : (res && res.data && Array.isArray(res.data?.data))
        ? res.data.data
        : (Array.isArray(res) ? res : []);

    if (resData && Array.isArray(resData)) {
      const merged = reset ? resData : [...items.value, ...resData]
      items.value = merged.filter(Boolean)
    }
  } catch (err) {
    console.error('Error fetching posts:', err)
    items.value = []
  } finally {
    loading.value = false
  }
}

// Admin create/update/delete are handled in the admin dashboard.
// The public Blog page only lists and reads posts.

const loadMore = async () => {
  page.value++
  await fetchPosts(false)
}

onMounted(() => {
  fetchPosts()
})
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 text-gray-900 py-12">
    
    <!-- Decorative Elements -->
    <div class="fixed top-10 left-5 w-80 h-80 bg-blue-100/30 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fixed bottom-20 right-10 w-96 h-96 bg-purple-100/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="container mx-auto px-4 sm:px-6 relative z-10">
      <!-- Header -->
      <header class="mb-12 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4 bg-gradient-to-r from-blue-600 via-purple-600 to-blue-600 bg-clip-text text-transparent">
          Market Insights
        </h1>
        <p class="text-gray-600 max-w-2xl mx-auto text-lg">
          Expert analysis and actionable trading insights for cryptocurrency markets.
        </p>
      </header>

      <!-- Main Content -->
      <div class="flex flex-col lg:flex-row gap-8">
        <!-- Articles -->
        <main class="flex-1">
          <!-- Search Bar -->
          <div class="flex flex-col md:flex-row gap-4 mb-8">
            <div class="flex-1 relative">
              <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              <input 
                v-model="search" 
                placeholder="Search articles..." 
                class="w-full pl-12 pr-4 py-3 rounded-xl bg-white/80 backdrop-blur-sm text-gray-900 border border-gray-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all" 
                @input="debouncedFetch"
              />
            </div>
            <select 
              v-model="category" 
              class="px-4 py-3 rounded-xl bg-white/80 backdrop-blur-sm text-gray-900 border border-gray-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all" 
              @change="() => fetchPosts(true)"
            >
              <option value="">📋 All Categories</option>
              <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
            </select>
          </div>

          <!-- Admin Dashboard Link -->
          <router-link 
            v-if="isAdmin"
            to="/admin/overview"
            class="w-full md:w-auto inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl mb-8 hover:from-blue-500 hover:to-purple-500 transition-all shadow-lg hover:shadow-xl"
          >
            ✨ Manage Articles (Admin)
          </router-link>

          <!-- Loading State -->
          <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500 mb-4"></div>
            <p class="text-gray-500">Loading articles...</p>
          </div>

          <!-- Articles Grid -->
          <div v-else>
            <div v-if="items.length === 0" class="text-center py-12">
              <div class="text-6xl mb-4">📝</div>
              <p class="text-lg font-semibold text-gray-700 mb-2">No articles found</p>
              <p class="text-gray-500">{{ search ? 'Try adjusting your search' : 'Check back later for new content' }}</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
              <ArticleCard 
                v-for="post in items" 
                :key="post?.id ?? post?.slug" 
                :post="post" 
              />
            </div>

            <!-- Load More Button -->
            <div v-if="canLoadMore" class="text-center">
              <button 
                class="px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white rounded-xl border-0 transition-all shadow-lg hover:shadow-xl" 
                @click="loadMore"
              >
                Load More
              </button>
            </div>
          </div>
        </main>

        <!-- Sidebar -->
        <aside class="lg:w-80">
          <!-- Popular Tags -->
          <div class="bg-white/80 backdrop-blur-sm border border-gray-200 p-6 rounded-2xl sticky top-24 shadow-lg">
            <h4 class="font-bold text-lg mb-4 text-gray-900">🏷️ Popular Tags</h4>
            <div class="flex flex-wrap gap-2">
              <button 
                v-for="tag in ['Bitcoin', 'Ethereum', 'DeFi', 'Trading']" 
                :key="tag"
                class="px-3 py-1.5 bg-blue-100 text-blue-700 text-sm rounded-full hover:bg-blue-200 transition-all"
                @click="category = tag; fetchPosts(true)"
              >
                #{{ tag }}
              </button>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </div>
</template>