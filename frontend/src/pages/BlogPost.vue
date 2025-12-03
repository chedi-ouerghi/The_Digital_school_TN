
<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { blogApi } from '../services/api'
import auth from '../services/auth'

const route = useRoute()
const router = useRouter()
const slug = route.params.slug as string
const post = ref<any | null>(null)
const loading = ref(true)
const isAdmin = auth.isAdmin()
const showDeleteModal = ref(false)

// --- NEW: Edit modal state & form
const showEditModal = ref(false)
const editForm = ref({
  title: '',
  category: '',
  summary: '',
  content: '',
  tags: '',
  image: '',
  published_at: ''
})
// --- end new

// Fetch article data
const fetchPost = async () => {
  loading.value = true
  try {
    const res = await blogApi.show(slug)
    post.value = res
  } catch (err) {
    console.error('Error fetching article:', err)
    post.value = null
  } finally {
    loading.value = false
  }
}

// Formatted date
const formattedDate = computed(() => {
  if (!post.value?.published_at) return 'No date'
  try {
    return new Date(post.value.published_at).toLocaleDateString('en-US', { 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric' 
    })
  } catch { 
    return post.value.published_at 
  }
})

// Estimated read time
const estimatedReadTime = computed(() => {
  if (!post.value?.content) return 3
  const words = post.value.content.split(/\s+/).length
  const minutes = Math.ceil(words / 200)
  return minutes || 1
})

// Edit article: open local dialog instead of routing
const onEdit = () => {
  if (!isAdmin || !post.value) return
  // populate editForm from post
  editForm.value = {
    title: post.value.title || '',
    category: post.value.category || '',
    summary: post.value.summary || '',
    content: post.value.content || '',
    tags: Array.isArray(post.value.tags) ? post.value.tags.join(', ') : (post.value.tags || ''),
    image: post.value.image || '',
    published_at: post.value.published_at ? post.value.published_at.split('T')[0] : new Date().toISOString().split('T')[0]
  }
  showEditModal.value = true
}

// Submit edited article
const submitEdit = async () => {
  if (!post.value) return
  try {
    const payload: any = {
      title: editForm.value.title.trim(),
      category: editForm.value.category,
      summary: editForm.value.summary.trim(),
      content: editForm.value.content,
      tags: editForm.value.tags ? editForm.value.tags.split(',').map((t: string) => t.trim()).filter(Boolean) : [],
      image: editForm.value.image.trim(),
      published_at: editForm.value.published_at || null,
    }

    await blogApi.update(post.value.id || post.value.slug, payload)
    await fetchPost()
    showEditModal.value = false
  } catch (e) {
    console.error('Update failed:', e)
    alert('Failed to update article. Please try again.')
  }
}

// Delete article
const onDelete = () => {
  if (!isAdmin) return
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  try {
    await blogApi.delete(post.value.id || post.value.slug)
    showDeleteModal.value = false
    router.push('/blog')
  } catch (e) {
    console.error('Delete failed:', e)
    alert('Failed to delete article. Please try again.')
    showDeleteModal.value = false
  }
}

// Lifecycle
onMounted(() => {
  fetchPost()
})
</script>

<template>
  <div class="min-h-screen bg-gradient-to-b from-[#0b0f19] to-[#0f1724] text-white py-8">
    <div class="container mx-auto px-4">
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-20">
        <div class="text-center">
          <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-[#3b82f6] mb-4"></div>
          <p class="text-gray-400 text-lg">Loading article...</p>
        </div>
      </div>

      <!-- Not Found State -->
      <div v-else-if="!post" class="text-center py-20">
        <div class="max-w-md mx-auto">
          <div class="text-6xl mb-4">📄</div>
          <h2 class="text-2xl font-bold text-gray-300 mb-3">Article Not Found</h2>
          <p class="text-gray-400 mb-6">The article you're looking for doesn't exist or has been removed.</p>
          <router-link 
            to="/blog" 
            class="inline-flex items-center gap-2 px-6 py-3 bg-[#3b82f6] hover:bg-[#2563eb] text-white font-medium rounded-xl transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Blog
          </router-link>
        </div>
      </div>

      <!-- Article Content -->
      <article v-else class="max-w-4xl mx-auto">
        <!-- Hero Image Section -->
        <div v-if="post.image" class="relative rounded-2xl overflow-hidden mb-8 shadow-2xl">
          <div class="relative h-56 sm:h-72 md:h-96 overflow-hidden">
            <img 
              :src="post.image" 
              :alt="post.title" 
              class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
          </div>
          
          <!-- Article Header Overlay -->
          <div class="absolute bottom-0 left-0 right-0 p-4 sm:p-6 md:p-8">
            <div class="max-w-3xl mx-auto">
              <!-- Category -->
              <div class="inline-block px-4 py-2 bg-[#3b82f6] text-white text-sm font-semibold rounded-full mb-4 shadow-lg">
                {{ post.category || 'Uncategorized' }}
              </div>
              
              <!-- Title -->
              <h1 class="text-2xl sm:text-3xl md:text-5xl font-bold text-white mb-4 leading-tight">
                {{ post.title }}
              </h1>
              
              <!-- Meta Information -->
              <div class="flex flex-wrap items-center gap-4 text-gray-200">
                <div class="flex items-center gap-2">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                  </svg>
                  <span class="font-medium">{{ post.author?.name || 'Unknown Author' }}</span>
                </div>
                <div class="w-1 h-1 bg-gray-400 rounded-full"></div>
                <div class="flex items-center gap-2">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                  </svg>
                  <span>{{ formattedDate }}</span>
                </div>
                <div class="w-1 h-1 bg-gray-400 rounded-full"></div>
                <div class="flex items-center gap-2">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                  </svg>
                  <span>{{ estimatedReadTime }} min read</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Article without Image -->
        <div v-else class="mb-8">
          <div class="mb-6">
            <div class="inline-block px-4 py-2 bg-[#3b82f6] text-white text-sm font-semibold rounded-full mb-4">
              {{ post.category || 'Uncategorized' }}
            </div>
            <h1 class="text-2xl sm:text-4xl md:text-5xl font-bold text-white mb-4 leading-tight">
              {{ post.title }}
            </h1>
            <div class="flex flex-wrap items-center gap-4 text-gray-300 mb-6">
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                </svg>
                <span class="font-medium">{{ post.author?.name || 'Unknown Author' }}</span>
              </div>
              <div class="w-1 h-1 bg-gray-400 rounded-full"></div>
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                </svg>
                <span>{{ formattedDate }}</span>
              </div>
              <div class="w-1 h-1 bg-gray-400 rounded-full"></div>
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                </svg>
                <span>{{ estimatedReadTime }} min read</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Tags -->
        <div v-if="post.tags && post.tags.length > 0" class="mb-8">
          <div class="flex flex-wrap gap-2">
            <span 
              v-for="tag in post.tags" 
              :key="tag"
              class="px-3 py-1.5 bg-gray-800/50 text-gray-300 text-sm rounded-lg border border-gray-700 hover:border-[#3b82f6]/50 transition-colors"
            >
              #{{ tag }}
            </span>
          </div>
        </div>

        <!-- Article Content -->
        <div class="bg-[#0f1724] rounded-2xl p-6 md:p-8 shadow-lg border border-gray-800/50">
          <div 
            class="prose prose-lg prose-invert max-w-none prose-headings:text-white prose-p:text-gray-300 prose-li:text-gray-300 prose-strong:text-white prose-a:text-[#3b82f6] hover:prose-a:text-[#60a5fa] prose-code:text-gray-300 prose-pre:bg-gray-900 prose-blockquote:text-gray-400 prose-blockquote:border-l-[#3b82f6]"
            v-html="post.content"
          ></div>
        </div>

        <!-- Summary Box -->
        <div v-if="post.summary" class="mt-8 p-6 bg-gradient-to-r from-[#1e293b] to-[#0f1724] rounded-2xl border border-gray-800">
          <h3 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-[#3b82f6]" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            Key Takeaways
          </h3>
          <p class="text-gray-300 leading-relaxed">{{ post.summary }}</p>
        </div>

        <!-- Actions -->
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-between gap-4 pt-8 border-t border-gray-800/50">
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
            <router-link 
              to="/blog" 
              class="w-full sm:w-auto inline-flex items-center justify-center sm:justify-start gap-2 px-6 py-3 bg-[#131a2a] hover:bg-[#1e293b] text-gray-300 hover:text-white font-medium rounded-xl border border-gray-700 transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
              </svg>
              Back to Blog
            </router-link>
            
            <button 
              v-if="isAdmin" 
              @click="onEdit" 
              class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-900/30 hover:bg-blue-900/40 text-blue-400 hover:text-blue-300 font-medium rounded-xl border border-blue-800/30 transition-colors"
            >
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
              </svg>
              Edit Article
            </button>
          </div>
          
          <button 
            v-if="isAdmin" 
            @click="onDelete" 
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-red-900/20 hover:bg-red-900/30 text-red-400 hover:text-red-300 font-medium rounded-xl border border-red-800/30 transition-colors"
          >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            Delete Article
          </button>
        </div>
      </article>
    </div>

    <!-- Edit Modal (admin) -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4">
      <div class="w-full max-w-3xl max-h-[90vh] overflow-y-auto bg-[#0f1724] text-white rounded-2xl p-6 sm:p-8 shadow-2xl border border-gray-800">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-2xl font-bold">Edit Article</h3>
          <button @click="showEditModal = false" class="text-gray-400 hover:text-white text-2xl">&times;</button>
        </div>

        <form @submit.prevent="submitEdit" class="space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-300 mb-2">Title *</label>
              <input v-model="editForm.title" required placeholder="Article title" class="w-full p-3 rounded-xl bg-[#131a2a] text-white border border-gray-700 focus:border-[#3b82f6] focus:outline-none"/>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">Category *</label>
              <input v-model="editForm.category" placeholder="Category" class="w-full p-3 rounded-xl bg-[#131a2a] text-white border border-gray-700 focus:border-[#3b82f6] focus:outline-none"/>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">Published Date</label>
              <input v-model="editForm.published_at" type="date" class="w-full p-3 rounded-xl bg-[#131a2a] text-white border border-gray-700 focus:border-[#3b82f6] focus:outline-none"/>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-300 mb-2">Image URL</label>
              <input v-model="editForm.image" placeholder="https://example.com/image.jpg" class="w-full p-3 rounded-xl bg-[#131a2a] text-white border border-gray-700 focus:border-[#3b82f6] focus:outline-none"/>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-300 mb-2">Summary *</label>
              <textarea v-model="editForm.summary" required rows="3" class="w-full p-3 rounded-xl bg-[#131a2a] text-white border border-gray-700 focus:border-[#3b82f6] focus:outline-none resize-none"></textarea>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-300 mb-2">Tags</label>
              <input v-model="editForm.tags" placeholder="bitcoin, ethereum" class="w-full p-3 rounded-xl bg-[#131a2a] text-white border border-gray-700 focus:border-[#3b82f6] focus:outline-none"/>
              <p class="text-xs text-gray-400 mt-1">Séparez les tags par des virgules</p>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-300 mb-2">Content (HTML allowed) *</label>
              <textarea v-model="editForm.content" required rows="8" class="w-full p-3 rounded-xl bg-[#131a2a] text-white border border-gray-700 focus:border-[#3b82f6] focus:outline-none font-mono text-sm"></textarea>
            </div>
          </div>

          <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t border-gray-800">
            <button type="button" @click="showEditModal = false" class="w-full sm:w-auto px-5 py-2.5 bg-gray-700 hover:bg-gray-600 text-white font-medium rounded-lg">Cancel</button>
            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-[#3b82f6] hover:bg-[#2563eb] text-white font-medium rounded-lg">Save Changes</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4">
      <div class="w-full max-w-md bg-[#0f1724] text-white rounded-2xl p-6 shadow-2xl border border-red-900/30">
        <div class="flex items-center gap-3 mb-4">
          <div class="p-2 bg-red-900/20 rounded-lg">
            <svg class="w-6 h-6 text-red-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
          </div>
          <h3 class="text-xl font-bold">Confirm Deletion</h3>
        </div>
        
        <p class="text-gray-300 mb-6">
          Are you sure you want to delete "<strong class="text-white">{{ post?.title }}</strong>"? This action cannot be undone.
        </p>
        
        <div class="flex justify-end gap-3">
          <button 
            @click="showDeleteModal = false" 
            class="px-5 py-2.5 bg-gray-700 hover:bg-gray-600 text-white font-medium rounded-lg transition-colors"
          >
            Cancel
          </button>
          <button 
            @click="confirmDelete" 
            class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors"
          >
            Delete Article
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Custom scrollbar for content */
.prose::-webkit-scrollbar {
  width: 6px;
}

.prose::-webkit-scrollbar-track {
  background: #1a2234;
  border-radius: 3px;
}

.prose::-webkit-scrollbar-thumb {
  background: #3b82f6;
  border-radius: 3px;
}

/* Smooth image transition */
img {
  will-change: transform;
  backface-visibility: hidden;
}

/* Animation for loading spinner */
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.animate-spin {
  animation: spin 1s linear infinite;
}
</style>