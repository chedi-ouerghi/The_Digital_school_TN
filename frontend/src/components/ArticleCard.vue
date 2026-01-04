<template>
  <article class="group relative bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-100 flex flex-col h-full">
    <!-- Featured Badge -->
    <div v-if="post.featured" class="absolute top-4 left-4 z-10">
      <span class="px-3 py-1 bg-gradient-to-r from-amber-500 to-orange-500 text-white text-xs font-bold rounded-full shadow-lg">
        Featured
      </span>
    </div>

    <!-- Image Container -->
    <div class="relative h-48 bg-gradient-to-br from-gray-900 to-gray-800 overflow-hidden">
      <!-- Fallback gradient if no image -->
      <div v-if="!post.image" class="absolute inset-0 bg-gradient-to-br from-blue-500/20 to-purple-600/20"></div>
      
      <!-- Article Image -->
      <img 
        v-if="post.image" 
        :src="post.image" 
        :alt="post.title"
        loading="lazy"
        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
      />
      
      <!-- Gradient Overlay -->
      <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
      
      <!-- Category Badge -->
      <div class="absolute bottom-4 left-4">
        <span class="px-3 py-1.5 bg-white/90 backdrop-blur-sm text-gray-800 text-xs font-semibold rounded-lg shadow-sm">
          {{ post.category || 'General' }}
        </span>
      </div>
    </div>

    <!-- Content Section -->
    <div class="p-6 flex-1 flex flex-col">
      <!-- Meta Information -->
      <div class="flex items-center gap-3 mb-4">
        <!-- Author -->
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center">
            <span class="text-white text-xs font-bold">
              {{ post.author?.name?.charAt(0) || 'A' }}
            </span>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-900">
              {{ post.author?.name || 'Anonymous' }}
            </p>
            <p class="text-xs text-gray-500">
              {{ formattedDate }}
            </p>
          </div>
        </div>
        
        <!-- Read Time -->
        <div class="ml-auto flex items-center gap-1 text-gray-500">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span class="text-sm">{{ estimatedReadTime }} min</span>
        </div>
      </div>

      <!-- Title -->
      <h3 class="text-gray-900 font-bold text-xl mb-3 line-clamp-2 group-hover:text-blue-600 transition-colors">
        {{ post.title }}
      </h3>

      <!-- Summary -->
      <p class="text-gray-600 text-sm mb-4 line-clamp-3 flex-1">
        {{ post.summary || 'No summary available' }}
      </p>

      <!-- Tags -->
      <div class="mb-5">
        <div class="flex flex-wrap gap-2">
          <span 
            v-for="tag in displayedTags" 
            :key="tag"
            class="px-3 py-1.5 bg-gray-50 text-gray-700 text-xs font-medium rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors cursor-default"
          >
            #{{ tag }}
          </span>
          <span 
            v-if="post.tags && post.tags.length > maxTags" 
            class="px-2 py-1 text-gray-400 text-xs"
          >
            +{{ post.tags.length - maxTags }}
          </span>
        </div>
      </div>

      <!-- Read More Button -->
      <div class="mt-auto pt-4 border-t border-gray-100">
        <router-link 
          :to="`/blog/${post.slug || post.id}`"
          class="group/btn inline-flex items-center justify-between w-full px-4 py-3 bg-gradient-to-r from-gray-50 to-white text-gray-700 rounded-xl border border-gray-200 hover:border-blue-300 hover:from-blue-50 hover:to-white transition-all duration-300"
        >
          <span class="font-medium text-sm">Read article</span>
          <svg class="w-4 h-4 text-gray-400 group-hover/btn:text-blue-600 group-hover/btn:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
          </svg>
        </router-link>
      </div>
    </div>
  </article>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { BlogPost } from '../types/blog';

interface Props {
  post: BlogPost
}

const props = defineProps<Props>()
const maxTags = 3

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

// Estimated read time
const estimatedReadTime = computed(() => {
  if (!props.post?.content) return 3
  const words = props.post.content.split(/\s+/).length
  const minutes = Math.ceil(words / 200)
  return minutes || 1
})

// Display limited number of tags
const displayedTags = computed(() => {
  if (!props.post?.tags || !Array.isArray(props.post.tags)) return []
  return props.post.tags.slice(0, maxTags)
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

/* Smooth image zoom */
img {
  will-change: transform;
  backface-visibility: hidden;
  transform: translateZ(0);
}

/* Smooth card hover */
.group {
  transform: translateZ(0);
  backface-visibility: hidden;
}
</style>