<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Switch } from '@/components/ui/switch'
import { Textarea } from '@/components/ui/textarea'
import { Calendar, Image as ImageIcon, Upload, X } from 'lucide-vue-next'
import { computed, onMounted, ref, watch } from 'vue'

// Types
interface BlogFormData {
  title: string
  category: string
  summary: string
  content: string
  tags: string
  image: string
  published_at: string
  published: boolean
}

interface Props {
  open: boolean
  isEdit: boolean
  blog?: {
    id?: string
    title?: string
    category?: string
    summary?: string
    content?: string
    tags?: string[] | string
    image?: string
    published_at?: string | null
    created_at?: string
    updated_at?: string
    author?: {
      id: string
      name: string
      email: string
    }
  }
  categories: string[]
}

interface Emits {
  (e: 'update:open', value: boolean): void
  (e: 'submit', payload: any): void
}

const props = withDefaults(defineProps<Props>(), {
  open: false,
  isEdit: false,
  blog: () => ({}),
  categories: () => []
})

const emit = defineEmits<Emits>()

// Form state with default values
const form = ref<BlogFormData>({
  title: '',
  category: '',
  summary: '',
  content: '',
  tags: '',
  image: '',
  published_at: new Date().toISOString().split('T')[0],
  published: false
})

const loading = ref(false)
const imagePreview = ref<string | null>(null)

// Get default category from props or first available
const defaultCategory = computed(() => {
  return props.categories.length > 0 ? props.categories[0] : 'General'
})

// Initialize form on mount and when props change
onMounted(() => {
  if (props.blog && props.isEdit) {
    loadBlogData()
  } else {
    resetForm()
  }
})

watch(() => props.blog, () => {
  if (props.blog && props.isEdit) {
    loadBlogData()
  } else {
    resetForm()
  }
}, { immediate: true })

watch(() => props.open, (newValue) => {
  if (newValue && !props.isEdit) {
    resetForm()
  }
})

// Load blog data for editing
function loadBlogData() {
  if (!props.blog) return
  
  const blog = props.blog
  
  form.value = {
    title: blog.title || '',
    category: blog.category || defaultCategory.value,
    summary: blog.summary || '',
    content: blog.content || '',
    tags: Array.isArray(blog.tags) ? blog.tags.join(', ') : (blog.tags || ''),
    image: blog.image || '',
    published_at: blog.published_at ? 
      blog.published_at.split('T')[0] : 
      new Date().toISOString().split('T')[0],
    published: !!blog.published_at
  }
  
  // Set image preview if image exists
  if (blog.image) {
    imagePreview.value = blog.image
  }
}

// Computed title for dialog
const dialogTitle = computed(() => {
  return props.isEdit ? 'Edit Blog Post' : 'Create New Blog Post'
})

// Computed submit button text
const submitButtonText = computed(() => {
  if (loading.value) return 'Saving...'
  return props.isEdit ? 'Update Post' : 'Publish Post'
})

// Reset form to initial state
function resetForm() {
  form.value = {
    title: '',
    category: defaultCategory.value,
    summary: '',
    content: '',
    tags: '',
    image: '',
    published_at: new Date().toISOString().split('T')[0],
    published: false
  }
  imagePreview.value = null
}

// Handle form close
function handleClose() {
  emit('update:open', false)
}

// Handle form submission
async function handleSubmit() {
  if (loading.value) return
  loading.value = true
  try {
    // Prepare payload
    const payload: any = {
      title: form.value.title.trim(),
      category: form.value.category.trim() || defaultCategory.value,
      summary: form.value.summary.trim(),
      content: form.value.content.trim(),
      tags: form.value.tags
        ? form.value.tags.split(',').map((t: string) => t.trim()).filter(Boolean)
        : [],
      image: form.value.image.trim(),
      published_at: form.value.published ? (form.value.published_at || new Date().toISOString()) : null
    }

    // Add ID if editing
    if (props.isEdit && props.blog?.id) {
      payload.id = props.blog.id
    }

    // Emit submit event and wait for parent to handle update/create
    await emit('submit', payload)

    // Only reset form after successful creation (not update)
    if (!props.isEdit) {
      resetForm()
    }
  } catch (error) {
    console.error('Form submission error:', error)
  } finally {
    loading.value = false
  }
}

// Handle image upload
function handleImageUpload(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  
  if (!file) return
  
  // Check if file is an image
  if (!file.type.startsWith('image/')) {
    alert('Please upload an image file')
    return
  }
  
  // File size limit (5MB)
  if (file.size > 5 * 1024 * 1024) {
    alert('Image size should be less than 5MB')
    return
  }
  
  // Create preview
  const reader = new FileReader()
  reader.onload = (e) => {
    imagePreview.value = e.target?.result as string
    form.value.image = imagePreview.value
  }
  reader.readAsDataURL(file)
  
  // Reset file input
  target.value = ''
}

// Remove image preview
function removeImage() {
  imagePreview.value = null
  form.value.image = ''
}
</script>

<template>
  <div 
    v-if="open" 
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 transition-opacity duration-200"
  >
    <div class="relative w-full max-w-4xl max-h-[90vh] bg-white rounded-2xl shadow-2xl overflow-hidden">
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
            <ImageIcon class="h-5 w-5 text-blue-600" />
          </div>
          <div>
            <h2 class="text-xl font-bold text-gray-900">
              {{ dialogTitle }}
            </h2>
            <p v-if="isEdit && blog?.title" class="text-sm text-gray-500 mt-0.5">
              Editing: "{{ blog.title }}"
            </p>
          </div>
        </div>
        <button
          class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
          :disabled="loading"
          @click="handleClose"
        >
          <X class="h-5 w-5 text-gray-500" />
        </button>
      </div>

      <!-- Form Content -->
      <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
        <form class="space-y-6" @submit.prevent="handleSubmit">
          <!-- Title -->
          <div class="space-y-2">
            <Label class="text-sm font-medium text-gray-700">
              Title *
            </Label>
            <Input
              v-model="form.title"
              required
              placeholder="Enter blog post title"
              class="w-full"
              :disabled="loading"
            />
          </div>

          <!-- Category and Published Date -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <Label class="text-sm font-medium text-gray-700">
                Category *
              </Label>
              <Select 
                v-model="form.category" 
                required
                :disabled="loading"
              >
                <SelectTrigger class="w-full">
                  <SelectValue placeholder="Select category" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem 
                    v-for="cat in categories" 
                    :key="cat" 
                    :value="cat"
                  >
                    {{ cat }}
                  </SelectItem>
                  <SelectItem v-if="categories.length === 0" value="General">
                    General
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
            
            <div class="space-y-3">
              <Label class="text-sm font-medium text-gray-700">
                Publish Settings
              </Label>
              <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-3">
                  <Switch
                    id="publish-switch"
                    v-model:checked="form.published"
                    :disabled="loading"
                  />
                  <div>
                    <Label for="publish-switch" class="text-sm font-medium cursor-pointer">
                      {{ form.published ? 'Published' : 'Save as Draft' }}
                    </Label>
                    <p class="text-xs text-gray-500 mt-0.5">
                      {{ form.published ? 'Article will be visible publicly' : 'Only you can see this article' }}
                    </p>
                  </div>
                </div>
                
                <div v-if="form.published" class="flex items-center gap-2 text-sm text-gray-600">
                  <Calendar class="h-4 w-4" />
                  <span>{{ new Date(form.published_at).toLocaleDateString() }}</span>
                </div>
              </div>
              
              <div v-if="form.published" class="space-y-2">
                <Label class="text-xs text-gray-600">
                  Schedule Publication Date
                </Label>
                <Input
                  v-model="form.published_at"
                  type="date"
                  class="w-full text-sm"
                  :disabled="loading"
                />
              </div>
            </div>
          </div>

          <!-- Featured Image -->
          <div class="space-y-3">
            <Label class="text-sm font-medium text-gray-700">
              Featured Image
            </Label>
            
            <!-- Image Preview -->
            <div v-if="imagePreview" class="relative group">
              <img 
                :src="imagePreview" 
                alt="Preview" 
                class="w-full h-48 object-cover rounded-lg border border-gray-200"
              />
              <Button
                type="button"
                variant="destructive"
                size="sm"
                class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity"
                :disabled="loading"
                @click="removeImage"
              >
                <X class="h-3 w-3" />
              </Button>
            </div>
            
            <!-- Image Input -->
            <div class="flex gap-3">
              <div class="flex-1">
                <Input
                  v-model="form.image"
                  type="url"
                  placeholder="https://example.com/image.jpg"
                  class="w-full"
                  :disabled="loading"
                />
              </div>
              <div class="relative">
                <Input
                  id="image-upload"
                  type="file"
                  accept="image/*"
                  class="hidden"
                  :disabled="loading"
                  @change="handleImageUpload"
                />
                <Label 
                  for="image-upload" 
                  class="cursor-pointer"
                  :class="{ 'opacity-50 cursor-not-allowed': loading }"
                >
                  <Button 
                    type="button" 
                    variant="outline" 
                    class="gap-2"
                    :disabled="loading"
                  >
                    <Upload class="h-4 w-4" />
                    Upload
                  </Button>
                </Label>
              </div>
            </div>
            <p class="text-xs text-gray-500">
              Enter image URL or upload from your device (max 5MB)
            </p>
          </div>

          <!-- Tags -->
          <div class="space-y-2">
            <Label class="text-sm font-medium text-gray-700">
              Tags
            </Label>
            <Input
              v-model="form.tags"
              placeholder="Bitcoin, Ethereum, DeFi, Trading"
              class="w-full"
              :disabled="loading"
            />
            <p class="text-xs text-gray-500">
              Separate tags with commas (e.g., "Bitcoin, Ethereum, DeFi")
            </p>
          </div>

          <!-- Summary -->
          <div class="space-y-2">
            <Label class="text-sm font-medium text-gray-700">
              Summary *
            </Label>
            <Textarea
              v-model="form.summary"
              required
              rows="3"
              placeholder="Brief summary of your blog post"
              class="w-full min-h-[100px] resize-y"
              :disabled="loading"
            />
            <p class="text-xs text-gray-500">
              A concise overview of your article (max 200 characters)
            </p>
          </div>

          <!-- Content -->
          <div class="space-y-2">
            <Label class="text-sm font-medium text-gray-700">
              Content *
            </Label>
            <Textarea
              v-model="form.content"
              required
              rows="12"
              placeholder="Write your blog post content here..."
              class="w-full min-h-[300px] resize-y font-sans text-sm"
              :disabled="loading"
            />
            <p class="text-xs text-gray-500">
              Supports markdown formatting. You can use **bold**, *italic*, and `code`.
            </p>
          </div>

          <!-- Form Actions -->
          <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
            <Button
              type="button"
              variant="outline"
              class="px-6"
              :disabled="loading"
              @click="handleClose"
            >
              Cancel
            </Button>
            <Button
              type="submit"
              class="px-6 bg-blue-600 hover:bg-blue-700 text-white"
              :disabled="loading || !form.title || !form.summary || !form.content"
            >
              <svg 
                v-if="loading" 
                class="animate-spin h-4 w-4 mr-2" 
                fill="none" 
                viewBox="0 0 24 24"
              >
                <circle 
                  class="opacity-25" 
                  cx="12" 
                  cy="12" 
                  r="10" 
                  stroke="currentColor" 
                  stroke-width="4"
                />
                <path 
                  class="opacity-75" 
                  fill="currentColor" 
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                />
              </svg>
              {{ submitButtonText }}
            </Button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Smooth transitions */
.fixed {
  animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

/* Custom scrollbar */
.max-h-\[calc\(90vh-120px\)\] {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 #f1f5f9;
}

.max-h-\[calc\(90vh-120px\)\]::-webkit-scrollbar {
  width: 6px;
}

.max-h-\[calc\(90vh-120px\)\]::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.max-h-\[calc\(90vh-120px\)\]::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.max-h-\[calc\(90vh-120px\)\]::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Textarea resizing */
textarea {
  min-height: 100px;
}

textarea[rows="12"] {
  min-height: 300px;
}
</style>