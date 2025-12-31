<template>
  <article class="group bg-[#131a2a] rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-800 hover:border-[#3b82f6]/40 flex flex-col h-full">
    <!-- Image Section -->
    <div class="relative h-40 sm:h-48 bg-gradient-to-br from-gray-900 to-gray-800 overflow-hidden">
      <img 
        v-if="post.image" 
        :src="post.image" 
        :alt="post.title" 
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
      />
      <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
      
      <!-- Category Badge -->
      <div class="absolute top-4 left-4">
        <span class="px-3 py-1.5 bg-[#3b82f6] text-white text-xs font-semibold rounded-full shadow-md">
          {{ post.category || 'Uncategorized' }}
        </span>
      </div>
      
      <!-- Date -->
      <div class="absolute top-4 right-4">
        <span class="px-2 py-1 bg-black/60 backdrop-blur-sm text-gray-200 text-xs rounded-lg">
          {{ formattedDate }}
        </span>
      </div>
    </div>

    <!-- Content Section -->
    <div class="p-5 flex-1 flex flex-col">
      <!-- Meta Information -->
      <div class="flex items-center gap-2 mb-3">
        <div class="flex items-center gap-2 text-sm text-gray-400">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
          </svg>
          <span>{{ post.author?.name || 'Unknown Author' }}</span>
        </div>
        <div class="w-1 h-1 bg-gray-600 rounded-full"></div>
        <div class="text-sm text-gray-400">
          {{ estimatedReadTime }} min read
        </div>
      </div>

      <!-- Title -->
      <h3 class="text-white font-bold text-lg mb-3 line-clamp-2 group-hover:text-[#3b82f6] transition-colors">
        {{ post.title }}
      </h3>

      <!-- Summary -->
      <p class="text-gray-300 text-sm mb-4 line-clamp-3 flex-1">
        {{ post.summary }}
      </p>

      <!-- Tags -->
      <div class="mb-4">
        <div class="flex flex-wrap gap-2">
          <span 
            v-for="tag in displayedTags" 
            :key="tag"
            class="px-2.5 py-1 bg-gray-800/50 text-gray-300 text-xs rounded-lg border border-gray-700 hover:border-[#3b82f6]/50 transition-colors"
          >
            {{ tag }}
          </span>
          <span 
            v-if="post.tags && post.tags.length > 3" 
            class="px-2.5 py-1 text-gray-400 text-xs"
          >
            +{{ post.tags.length - 3 }}
          </span>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between pt-4 border-t border-gray-800 mt-auto gap-3">
        <router-link 
          :to="`/blog/${post.slug || post.id}`" 
          class="w-full sm:w-auto inline-flex items-center justify-center sm:justify-start gap-2 px-4 py-2 text-[#3b82f6] hover:text-[#60a5fa] text-sm font-medium rounded-md transition-all duration-200 group/link focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
          Read article
          <svg class="w-4 h-4 group-hover/link:translate-x-1 transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
          </svg>
        </router-link>

        <!-- Admin Actions -->
        <div v-if="isAdmin" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
          <button 
            class="w-full sm:w-auto px-3 py-1.5 bg-[#1e293b] hover:bg-[#2d3748] text-blue-400 text-xs font-medium rounded-lg border border-blue-900/30 transition-colors flex items-center justify-center gap-1" 
            @click.stop="emit('edit', post)"
          >
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
              <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
            </svg>
            Edit
          </button>
          <button 
            class="w-full sm:w-auto px-3 py-1.5 bg-red-900/20 hover:bg-red-900/30 text-red-400 text-xs font-medium rounded-lg border border-red-900/30 transition-colors flex items-center justify-center gap-1" 
            @click.stop="emit('delete', post)"
          >
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            Delete
          </button>
        </div>
      </div>
    </div>
  </article>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import auth from '../services/auth';

const props = defineProps<{ post: any }>()
const emit = defineEmits(['edit', 'delete'])

const isAdmin = auth.isAdmin()

// Formatted date
const formattedDate = computed(() => {
  if (!props.post?.published_at) return 'No date'
  try {
    return new Date(props.post.published_at).toLocaleDateString('en-US', { 
      month: 'short', 
      day: 'numeric',
      year: 'numeric'
    })
  } catch { 
    return props.post.published_at 
  }
})

// Estimated read time (based on content length)
const estimatedReadTime = computed(() => {
  if (!props.post?.content) return 3
  const words = props.post.content.split(/\s+/).length
  const minutes = Math.ceil(words / 200) // 200 words per minute
  return minutes || 1
})

// Display only first 3 tags
const displayedTags = computed(() => {
  if (!props.post?.tags || !Array.isArray(props.post.tags)) return []
  return props.post.tags.slice(0, 3)
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Smooth image zoom effect */
img {
  will-change: transform;
  backface-visibility: hidden;
}

/* Smooth transitions */
.group {
  transform: translateZ(0);
}
</style>