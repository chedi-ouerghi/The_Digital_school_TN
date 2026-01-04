<script setup lang="ts">
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Calendar, CheckCircle, Clock, FileText, Tag, User, X } from 'lucide-vue-next'
import { computed } from 'vue'

interface Author {
  id: string
  name: string
  email: string
  role?: string
  profile_picture?: string | null
}

interface Blog {
  id: string
  user_id: string
  title: string
  slug: string
  category: string
  summary: string
  content: string
  tags: string[]
  image: string
  published_at: string | null
  created_at: string
  updated_at: string
  author?: Author
}

interface Props {
  open: boolean
  blog?: Blog | null
}

const props = withDefaults(defineProps<Props>(), {
  open: false,
  blog: null
})

const emit = defineEmits<{
  'update:open': [value: boolean]
  'edit': [blog: Blog]
  'close': []
}>()

function handleClose() {
  emit('update:open', false)
  emit('close')
}

function handleEdit() {
  if (props.blog) {
    emit('edit', props.blog)
    handleClose()
  }
}

// Formatage de la date
const formattedPublishDate = computed(() => {
  if (!props.blog?.published_at) return 'Not published'
  const date = new Date(props.blog.published_at)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})

const formattedUpdatedDate = computed(() => {
  if (!props.blog?.updated_at) return 'Never'
  const date = new Date(props.blog.updated_at)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})

const wordCount = computed(() => {
  if (!props.blog?.content) return 0
  return props.blog.content.split(/\s+/).length
})

const readTime = computed(() => {
  const words = wordCount.value
  return Math.ceil(words / 200) // 200 words per minute
})

const isPublished = computed(() => {
  return !!props.blog?.published_at
})
</script>

<template>
  <div 
    v-if="open && blog"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 transition-opacity duration-200"
    @click="handleClose"
  >
    <div 
      class="relative w-full max-w-3xl max-h-[90vh] bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col"
      @click.stop
    >
      <!-- Header avec image de fond -->
      <div class="relative h-56 overflow-hidden bg-gradient-to-br from-blue-500 to-purple-600">
        <!-- Image du blog si disponible -->
        <img 
          v-if="blog.image" 
          :src="blog.image" 
          :alt="blog.title"
          class="w-full h-full object-cover"
        />
        
        <!-- Gradient overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent" />
        
        <!-- Close button -->
        <button
          class="absolute top-4 right-4 p-2 bg-white/20 hover:bg-white/30 rounded-lg transition-colors backdrop-blur-sm"
          @click="handleClose"
        >
          <X class="h-5 w-5 text-white" />
        </button>
        
        <!-- Status badge -->
        <div class="absolute top-4 left-4 flex gap-2">
          <Badge 
            :class="[
              'text-xs font-medium px-3 py-1.5 gap-1.5 backdrop-blur-sm',
              isPublished
                ? 'bg-green-500/90 text-white border-green-400'
                : 'bg-amber-500/90 text-white border-amber-400'
            ]"
          >
            <component :is="isPublished ? CheckCircle : Clock" class="h-3 w-3" />
            {{ isPublished ? 'Published' : 'Draft' }}
          </Badge>
        </div>
      </div>

      <!-- Content -->
      <div class="overflow-y-auto flex-1">
        <div class="p-8 space-y-6">
          <!-- Titre principal -->
          <div class="space-y-2">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 leading-tight">
              {{ blog.title }}
            </h1>
            <p class="text-lg text-gray-600 leading-relaxed">
              {{ blog.summary }}
            </p>
          </div>

          <!-- Meta informations -->
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Auteur -->
            <Card class="border-0 shadow-sm bg-gradient-to-br from-blue-50 to-blue-100/50">
              <CardContent class="p-4 space-y-2">
                <div class="flex items-center gap-2 text-blue-600">
                  <User class="h-4 w-4" />
                  <p class="text-xs font-medium uppercase tracking-wide">Author</p>
                </div>
                <div>
                  <p class="font-semibold text-gray-900">{{ blog.author?.name || 'Admin' }}</p>
                  <p class="text-xs text-gray-600 truncate">{{ blog.author?.email }}</p>
                </div>
              </CardContent>
            </Card>

            <!-- Catégorie -->
            <Card class="border-0 shadow-sm bg-gradient-to-br from-purple-50 to-purple-100/50">
              <CardContent class="p-4 space-y-2">
                <div class="flex items-center gap-2 text-purple-600">
                  <Tag class="h-4 w-4" />
                  <p class="text-xs font-medium uppercase tracking-wide">Category</p>
                </div>
                <p class="font-semibold text-gray-900">{{ blog.category || 'General' }}</p>
              </CardContent>
            </Card>

            <!-- Date de publication -->
            <Card class="border-0 shadow-sm bg-gradient-to-br from-green-50 to-green-100/50">
              <CardContent class="p-4 space-y-2">
                <div class="flex items-center gap-2 text-green-600">
                  <Calendar class="h-4 w-4" />
                  <p class="text-xs font-medium uppercase tracking-wide">Published</p>
                </div>
                <p class="font-semibold text-gray-900 text-sm">{{ formattedPublishDate }}</p>
              </CardContent>
            </Card>

            <!-- Temps de lecture -->
            <Card class="border-0 shadow-sm bg-gradient-to-br from-orange-50 to-orange-100/50">
              <CardContent class="p-4 space-y-2">
                <div class="flex items-center gap-2 text-orange-600">
                  <FileText class="h-4 w-4" />
                  <p class="text-xs font-medium uppercase tracking-wide">Read Time</p>
                </div>
                <p class="font-semibold text-gray-900">{{ readTime }} min</p>
                <p class="text-xs text-gray-600">{{ wordCount }} words</p>
              </CardContent>
            </Card>
          </div>

          <!-- Tags -->
          <div v-if="blog.tags && blog.tags.length > 0" class="space-y-3">
            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Tags</h3>
            <div class="flex flex-wrap gap-2">
              <Badge 
                v-for="(tag, index) in blog.tags" 
                :key="index"
                variant="secondary"
                class="bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100 cursor-default"
              >
                {{ tag }}
              </Badge>
            </div>
          </div>

          <!-- Contenu -->
          <div class="space-y-3">
            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Content Preview</h3>
            <div class="prose prose-sm max-w-none bg-gray-50 rounded-lg p-6 border border-gray-200">
              <p class="text-gray-700 leading-relaxed whitespace-pre-wrap break-words max-h-64 overflow-hidden">
                {{ blog.content }}
              </p>
              <div v-if="blog.content.length > 500" class="mt-4 pt-4 border-t border-gray-300 flex items-center justify-center">
                <div class="w-full h-8 bg-gradient-to-t from-gray-50 to-transparent flex items-center justify-center text-xs text-gray-500">
                  ... content continues
                </div>
              </div>
            </div>
          </div>

          <!-- Dates supplémentaires -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-gray-200">
            <div class="space-y-1">
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Created</p>
              <p class="text-sm text-gray-900">{{ new Date(blog.created_at).toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
              }) }}</p>
            </div>
            <div class="space-y-1">
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Last Updated</p>
              <p class="text-sm text-gray-900">{{ formattedUpdatedDate }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer / Actions -->
      <div class="border-t border-gray-200 p-6 bg-gray-50 flex items-center justify-end gap-3">
        <Button
          variant="outline"
          class="px-6"
          @click="handleClose"
        >
          Close
        </Button>
        <Button
          class="px-6 bg-blue-600 hover:bg-blue-700 text-white gap-2"
          @click="handleEdit"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-7-4l7-7m0 0l-7 7" />
          </svg>
          Edit Post
        </Button>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Custom scrollbar pour la section content */
.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 #f1f5f9;
}

.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
