<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import ArticleCard from '../components/ArticleCard.vue'
import { blogApi } from '../services/api'
import auth from '../services/auth'
import type { BlogPost, PaginatedResponse } from '../types/blog'

// Types
interface BlogFilters {
  search: string
  category: string
  page: number
  per_page?: number
}

// Data
const items = ref<BlogPost[]>([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 12
})
const loading = ref(false)
const loadingMore = ref(false)
const search = ref('')
const category = ref('')
const categories = ['News', 'Technical Analysis', 'Blockchain', 'Beginner Guides', 'Altcoins', 'Trends']
const popularTags = ['Bitcoin', 'Ethereum', 'DeFi', 'Trading', 'NFT', 'Web3', 'Staking', 'Layer 2']
const isAdmin = auth.isAdmin()

// Computed
const hasMorePosts = computed(() => {
  return pagination.value.current_page < pagination.value.last_page
})

const showEmptyState = computed(() => {
  return !loading.value && items.value.length === 0
})

const totalPostsText = computed(() => {
  const total = pagination.value.total
  if (total === 0) return 'No articles'
  if (total === 1) return '1 article'
  return `${total} articles`
})

// Methods
const debounce = (fn: Function, delay: number) => {
  let timeoutId: NodeJS.Timeout
  return (...args: any[]) => {
    clearTimeout(timeoutId)
    timeoutId = setTimeout(() => fn(...args), delay)
  }
}

const fetchPosts = async (reset = true) => {
  if (reset) {
    loading.value = true
  } else {
    loadingMore.value = true
  }

  try {
    const params: BlogFilters = {
      search: search.value.trim(),
      category: category.value,
      page: reset ? 1 : pagination.value.current_page + 1
    }

    const res = await blogApi.list(params)
    
    if (res?.success && res.data) {
      const data = res.data as PaginatedResponse<BlogPost>
      
      if (reset) {
        items.value = data.data
        pagination.value = {
          current_page: data.current_page,
          last_page: data.last_page,
          total: data.total,
          per_page: data.per_page
        }
      } else {
        items.value = [...items.value, ...data.data]
        pagination.value.current_page = data.current_page
      }
    } else {
      items.value = []
    }
  } catch (err) {
    console.error('Error fetching posts:', err)
    if (reset) items.value = []
  } finally {
    loading.value = false
    loadingMore.value = false
  }
}

const loadMore = async () => {
  if (!hasMorePosts.value || loadingMore.value) return
  await fetchPosts(false)
}

const applyFilter = (filterType: 'category' | 'tag', value: string) => {
  if (filterType === 'category') {
    category.value = category.value === value ? '' : value
  } else {
    search.value = value
  }
  fetchPosts(true)
}

const clearFilters = () => {
  search.value = ''
  category.value = ''
  fetchPosts(true)
}

const debouncedFetch = debounce(() => fetchPosts(true), 500)

// Watchers
watch([search, category], () => {
  debouncedFetch()
})

// Lifecycle
onMounted(() => {
  fetchPosts(true)
})
</script>

<template>
  <div class="min-h-screen bg-gradient-to-b from-slate-50 to-white text-gray-900">
    <!-- Hero Section -->
    <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600">
      <div class="absolute inset-0 bg-black/20"></div>
      <div class="relative container mx-auto px-4 sm:px-6 py-16 md:py-24">
        <div class="max-w-3xl mx-auto text-center">
          <h1 class="text-4xl md:text-6xl font-bold text-white mb-6">
            Crypto Insights
          </h1>
          <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
            Expert analysis, market trends, and educational content for cryptocurrency enthusiasts and traders.
          </p>
          
          <!-- Main Search -->
          <div class="relative max-w-2xl mx-auto">
            <div class="relative flex items-center">
              <svg class="absolute left-4 w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              <input 
                v-model="search"
                type="text"
                placeholder="Search articles, topics, or keywords..."
                class="w-full pl-12 pr-24 py-4 rounded-2xl bg-white/10 backdrop-blur-sm text-white placeholder-blue-200 border border-white/20 focus:border-white/40 focus:outline-none focus:ring-2 focus:ring-white/20 transition-all"
              />
              <div class="absolute right-2">
                <button 
                  v-if="search || category"
                  class="px-4 py-2 text-sm text-white/80 hover:text-white bg-white/10 hover:bg-white/20 rounded-lg transition-colors"
                  @click="clearFilters"
                >
                  Clear
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 sm:px-6 py-8 md:py-12">
      <div class="flex flex-col lg:flex-row gap-8">
        <!-- Main Articles Section -->
        <main class="flex-1">
          <!-- Filters & Stats -->
          <div class="mb-8 p-6 bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
              <div>
                <h2 class="text-2xl font-bold text-gray-900">Latest Articles</h2>
                <p class="text-gray-500 mt-1">{{ totalPostsText }} available</p>
              </div>
              
              <div class="flex items-center gap-4">
                <!-- Category Filter -->
                <div class="relative">
                  <select 
                    v-model="category"
                    class="appearance-none bg-white pl-4 pr-10 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 text-gray-700 cursor-pointer"
                  >
                    <option value="">All Categories</option>
                    <option v-for="cat in categories" :key="cat" :value="cat">
                      {{ cat }}
                    </option>
                  </select>
                  <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </div>

                <!-- Admin Button -->
                <router-link 
                  v-if="isAdmin"
                  to="/dashboard/admin/blogs"
                  class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all shadow-md hover:shadow-lg"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                  </svg>
                  Manage Articles
                </router-link>
              </div>
            </div>

            <!-- Quick Category Filters -->
            <div class="flex flex-wrap gap-2">
              <button
                v-for="cat in categories"
                :key="cat"
                :class="[
                  'px-4 py-2 rounded-lg text-sm font-medium transition-all',
                  category === cat 
                    ? 'bg-blue-100 text-blue-700 border border-blue-200' 
                    : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border border-gray-200'
                ]"
                @click="applyFilter('category', cat)"
              >
                {{ cat }}
              </button>
            </div>
          </div>

          <!-- Loading State -->
          <div v-if="loading" class="text-center py-16">
            <div class="inline-flex flex-col items-center">
              <div class="w-16 h-16 border-4 border-blue-100 border-t-blue-600 rounded-full animate-spin mb-4"></div>
              <p class="text-gray-600 font-medium">Loading articles...</p>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else-if="showEmptyState" class="text-center py-16">
            <div class="max-w-md mx-auto">
              <div class="text-6xl mb-6">📝</div>
              <h3 class="text-2xl font-bold text-gray-900 mb-2">No articles found</h3>
              <p class="text-gray-600 mb-6">
                {{
                  search || category 
                    ? 'Try adjusting your search or filters' 
                    : 'Check back soon for new content'
                }}
              </p>
              <button 
                v-if="search || category"
                class="px-6 py-3 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors"
                @click="clearFilters"
              >
                Clear all filters
              </button>
            </div>
          </div>

          <!-- Articles Grid -->
          <div v-else class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <ArticleCard 
                v-for="post in items" 
                :key="post.id" 
                :post="post"
              />
            </div>

            <!-- Load More -->
            <div v-if="hasMorePosts" class="text-center pt-8">
              <button 
                :disabled="loadingMore"
                :class="[
                  'px-8 py-3 rounded-xl font-semibold transition-all shadow-md hover:shadow-lg',
                  loadingMore 
                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
                    : 'bg-gradient-to-r from-blue-600 to-purple-600 text-white hover:from-blue-700 hover:to-purple-700'
                ]"
                @click="loadMore"
              >
                <span v-if="loadingMore" class="flex items-center gap-2">
                  <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                  Loading...
                </span>
                <span v-else>Load More Articles</span>
              </button>
              <p class="text-gray-500 text-sm mt-3">
                Showing {{ items.length }} of {{ pagination.total }} articles
              </p>
            </div>
          </div>
        </main>

        <!-- Sidebar -->
        <aside class="lg:w-80 space-y-6">
          <!-- Popular Tags -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-lg text-gray-900 mb-4 flex items-center gap-2">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
              </svg>
              Popular Topics
            </h3>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="tag in popularTags"
                :key="tag"
                class="px-3 py-2 bg-gray-50 hover:bg-blue-50 text-gray-700 hover:text-blue-700 text-sm rounded-lg border border-gray-200 hover:border-blue-200 transition-colors"
                @click="applyFilter('tag', tag)"
              >
                #{{ tag }}
              </button>
            </div>
          </div>

          <!-- Featured Categories -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-lg text-gray-900 mb-4 flex items-center gap-2">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
              </svg>
              Browse by Category
            </h3>
            <ul class="space-y-2">
              <li v-for="cat in categories" :key="cat">
                <button
                  :class="[
                    'w-full text-left px-3 py-2 rounded-lg transition-colors',
                    category === cat
                      ? 'bg-blue-50 text-blue-700 font-medium'
                      : 'text-gray-600 hover:bg-gray-50'
                  ]"
                  @click="applyFilter('category', cat)"
                >
                  {{ cat }}
                  <span class="float-right text-gray-400 text-sm">
                    {{ category === cat ? '✓' : '' }}
                  </span>
                </button>
              </li>
            </ul>
          </div>

          <!-- Newsletter -->
          <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl p-6 text-white">
            <h3 class="font-bold text-lg mb-2">Stay Updated</h3>
            <p class="text-blue-100 text-sm mb-4">
              Get the latest crypto insights delivered to your inbox weekly.
            </p>
            <div class="space-y-3">
              <input 
                type="email" 
                placeholder="Your email address"
                class="w-full px-4 py-3 rounded-lg bg-white/10 backdrop-blur-sm border border-white/20 text-white placeholder-blue-200 focus:outline-none focus:border-white/40"
              />
              <button class="w-full px-4 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-100 transition-colors">
                Subscribe
              </button>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Smooth scrolling for load more */
html {
  scroll-behavior: smooth;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f5f9;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>