<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { ref, watch } from 'vue'

interface Props {
  open: boolean
  isEdit: boolean
  blog?: any
  categories: string[]
}

interface Emits {
  (e: 'update:open', value: boolean): void
  (e: 'submit', payload: any): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Form state
const form = ref({
  title: '',
  category: '',
  summary: '',
  content: '',
  tags: '',
  image: '',
  published_at: '',
  published: false
})

const loading = ref(false)

// Watch for blog changes
watch(() => props.blog, (newBlog) => {
  if (newBlog) {
    form.value = {
      title: newBlog.title || '',
      category: newBlog.category || '',
      summary: newBlog.summary || '',
      content: newBlog.content || '',
      tags: Array.isArray(newBlog.tags) ? newBlog.tags.join(', ') : (newBlog.tags || ''),
      image: newBlog.image || '',
      published_at: newBlog.published_at ? newBlog.published_at.split('T')[0] : '',
      published: !!newBlog.published_at
    }
  } else {
    resetForm()
  }
}, { immediate: true })

function resetForm() {
  form.value = {
    title: '',
    category: '',
    summary: '',
    content: '',
    tags: '',
    image: '',
    published_at: '',
    published: false
  }
}

function handleClose() {
  emit('update:open', false)
  resetForm()
}

async function handleSubmit() {
  loading.value = true
  try {
    const payload: any = {
      title: form.value.title.trim(),
      category: form.value.category,
      summary: form.value.summary.trim(),
      content: form.value.content,
      tags: form.value.tags ? form.value.tags.split(',').map((t: string) => t.trim()).filter(Boolean) : [],
      image: form.value.image.trim(),
      published_at: form.value.published ? (form.value.published_at || new Date().toISOString().split('T')[0]) : null
    }

    emit('submit', payload)
    handleClose()
  } catch (error) {
    console.error('Form submission error:', error)
  } finally {
    loading.value = false
  }
}

function handleImageUpload(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    // For now, just show the file name
    // In a real implementation, you would upload the file and get the URL
    const reader = new FileReader()
    reader.onload = (e) => {
      form.value.image = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="relative w-full max-w-4xl max-h-[90vh] bg-white rounded-2xl shadow-2xl overflow-hidden">
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-slate-50 to-white">
        <h2 class="text-xl font-bold text-gray-900">
          {{ isEdit ? '✏️ Edit Blog Post' : '✍️ Create New Blog Post' }}
        </h2>
        <button
          @click="handleClose"
          class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Form Content -->
      <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Title -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Title *
            </label>
            <Input
              v-model="form.title"
              required
              placeholder="Enter blog post title"
              class="w-full"
            />
          </div>

          <!-- Category and Published Date -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Category *
              </label>
              <Select v-model="form.category" required>
                <SelectTrigger>
                  <SelectValue placeholder="Select category" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="cat in categories" :key="cat" :value="cat">
                    {{ cat }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Published Date
              </label>
              <Input
                v-model="form.published_at"
                type="date"
                class="w-full"
              />
            </div>
          </div>

          <!-- Featured Image -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Featured Image URL
            </label>
            <div class="flex gap-2">
              <Input
                v-model="form.image"
                type="url"
                placeholder="https://example.com/image.jpg"
                class="flex-1"
              />
              <label class="cursor-pointer">
                <Input
                  type="file"
                  accept="image/*"
                  class="hidden"
                  @change="handleImageUpload"
                />
                <Button type="button" variant="outline">
                  Upload
                </Button>
              </label>
            </div>
            <p class="text-xs text-gray-500 mt-1">Enter image URL or upload from your device</p>
          </div>

          <!-- Tags -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Tags
            </label>
            <Input
              v-model="form.tags"
              placeholder="Bitcoin, Ethereum, DeFi, Trading (comma separated)"
              class="w-full"
            />
            <p class="text-xs text-gray-500 mt-1">Separate tags with commas</p>
          </div>

          <!-- Summary -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Summary *
            </label>
            <Textarea
              v-model="form.summary"
              required
              rows="3"
              placeholder="Brief summary of your blog post"
              class="w-full"
            />
          </div>

          <!-- Content -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Content *
            </label>
            <Textarea
              v-model="form.content"
              required
              rows="12"
              placeholder="Write your blog post content here..."
              class="w-full font-mono text-sm"
            />
            <p class="text-xs text-gray-500 mt-1">HTML is allowed for formatting</p>
          </div>

          <!-- Actions -->
          <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
            <Button
              type="button"
              variant="outline"
              @click="handleClose"
              class="px-6"
            >
              Cancel
            </Button>
            <Button
              type="submit"
              :disabled="loading"
              class="px-6"
            >
              <svg v-if="loading" class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ isEdit ? '💾 Update Post' : '🚀 Publish Post' }}
            </Button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>