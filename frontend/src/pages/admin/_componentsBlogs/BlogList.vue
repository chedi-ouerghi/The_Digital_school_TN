<script setup lang="ts">
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog'
import { Badge } from '@/components/ui/badge'
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
import { computed, ref } from 'vue'
import api from '../../../services/api'

interface Props {
  blogs: any[]
  loading: boolean
  currentPage: number
  totalPages: number
}

interface Emits {
  (e: 'viewDetails', id: number): void
  (e: 'editBlog', blog: any): void
  (e: 'deleteBlog', id: number): void
  (e: 'changePage', page: number): void
  (e: 'refresh'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// États locaux
const query = ref('')
const sortBy = ref<'title'|'category'|'date'>('date')
const viewMode = ref<'grid'|'list'>('list')
const deleteLoading = ref<number | null>(null)

// Fonctions utilitaires
function formatDate(dateString: string): string {
  if (!dateString) return '—'
  try {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    })
  } catch {
    return '—'
  }
}

function truncateText(text: string, maxLength: number = 100): string {
  if (!text) return ''
  return text.length > maxLength ? text.substring(0, maxLength) + '...' : text
}

function getStatusVariant(published: boolean): string {
  return published ? 'default' : 'secondary'
}

function getStatusText(published: boolean): string {
  return published ? 'Published' : 'Draft'
}

// Computed
const filteredBlogs = computed(() => {
  let filtered = props.blogs

  // Filter by search query
  if (query.value) {
    const q = query.value.toLowerCase()
    filtered = filtered.filter(blog => 
      blog.title?.toLowerCase().includes(q) ||
      blog.category?.toLowerCase().includes(q) ||
      blog.summary?.toLowerCase().includes(q)
    )
  }

  // Sort
  filtered.sort((a, b) => {
    switch (sortBy.value) {
      case 'title':
        return (a.title || '').localeCompare(b.title || '')
      case 'category':
        return (a.category || '').localeCompare(b.category || '')
      case 'date':
      default:
        return new Date(b.created_at || 0).getTime() - new Date(a.created_at || 0).getTime()
    }
  })

  return filtered
})

const paginatedBlogs = computed(() => {
  const start = (props.currentPage - 1) * 10
  const end = start + 10
  return filteredBlogs.value.slice(start, end)
})

const totalFilteredPages = computed(() => {
  return Math.ceil(filteredBlogs.value.length / 10)
})

// Méthodes
function handleView(id: number) {
  emit('viewDetails', id)
}

function handleEdit(blog: any) {
  emit('editBlog', blog)
}

async function handleDelete(id: number) {
  deleteLoading.value = id
  try {
    await api.blog.delete(id)
    emit('deleteBlog', id)
  } catch (error) {
    console.error('Error deleting blog:', error)
  } finally {
    deleteLoading.value = null
  }
}

function changePage(page: number) {
  emit('changePage', page)
}

function refresh() {
  emit('refresh')
}
</script>

<template>
  <div class="space-y-4">
    <!-- Barre de recherche et filtres -->
    <div class="flex flex-col sm:flex-row gap-4">
      <div class="flex-1">
        <Input
          v-model="query"
          placeholder="Search blogs by title, category, or content..."
          class="w-full"
        />
      </div>
      <div class="flex gap-2">
        <Select v-model="sortBy">
          <SelectTrigger class="w-[140px]">
            <SelectValue placeholder="Sort by" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="date">Date</SelectItem>
            <SelectItem value="title">Title</SelectItem>
            <SelectItem value="category">Category</SelectItem>
          </SelectContent>
        </Select>
        <Button variant="outline" @click="refresh">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
        </Button>
      </div>
    </div>

    <!-- Liste des blogs -->
    <Card>
      <CardContent class="p-0">
        <div v-if="loading" class="p-8 text-center">
          <div class="animate-spin h-8 w-8 border-b-2 border-blue-500 rounded-full mx-auto mb-4"></div>
          <p class="text-gray-500">Loading blogs...</p>
        </div>

        <div v-else-if="filteredBlogs.length === 0" class="p-8 text-center">
          <div class="text-6xl mb-4">📝</div>
          <p class="text-lg font-semibold text-gray-700 mb-2">No blogs found</p>
          <p class="text-gray-500">{{ query ? 'Try adjusting your search' : 'Create your first blog post' }}</p>
        </div>

        <div v-else class="divide-y divide-gray-100">
          <div
            v-for="blog in paginatedBlogs"
            :key="blog.id"
            class="p-4 hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-2">
                  <h3 class="font-semibold text-lg text-gray-900 truncate">
                    {{ blog.title }}
                  </h3>
                  <Badge :variant="getStatusVariant(!!blog.published_at)">
                    {{ getStatusText(!!blog.published_at) }}
                  </Badge>
                </div>

                <div class="flex items-center gap-4 text-sm text-gray-500 mb-2">
                  <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 8V5a2 2 0 012-2z" />
                    </svg>
                    {{ blog.category || 'Uncategorized' }}
                  </span>
                  <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ formatDate(blog.created_at) }}
                  </span>
                  <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    {{ blog.author?.name || 'Unknown' }}
                  </span>
                </div>

                <p class="text-gray-600 text-sm line-clamp-2">
                  {{ truncateText(blog.summary) }}
                </p>

                <div v-if="blog.tags && blog.tags.length > 0" class="flex flex-wrap gap-1 mt-2">
                  <Badge
                    v-for="tag in (Array.isArray(blog.tags) ? blog.tags : []).slice(0, 3)"
                    :key="tag"
                    variant="outline"
                    class="text-xs"
                  >
                    {{ tag }}
                  </Badge>
                </div>
              </div>

              <div class="flex items-center gap-2">
                <Button
                  variant="outline"
                  size="sm"
                  @click="handleView(blog.id)"
                >
                  View
                </Button>
                <Button
                  variant="outline"
                  size="sm"
                  @click="handleEdit(blog)"
                >
                  Edit
                </Button>
                
                <AlertDialog>
                  <AlertDialogTrigger as-child>
                    <Button
                      variant="outline"
                      size="sm"
                      class="text-red-600 hover:text-red-700"
                      :disabled="deleteLoading === blog.id"
                    >
                      <svg v-if="deleteLoading === blog.id" class="animate-spin h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      Delete
                    </Button>
                  </AlertDialogTrigger>
                  <AlertDialogContent>
                    <AlertDialogHeader>
                      <AlertDialogTitle>Delete Blog Post</AlertDialogTitle>
                      <AlertDialogDescription>
                        Are you sure you want to delete "{{ blog.title }}"? This action cannot be undone.
                      </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                      <AlertDialogCancel>Cancel</AlertDialogCancel>
                      <AlertDialogAction
                        @click="handleDelete(blog.id)"
                        class="bg-red-600 hover:bg-red-700"
                      >
                        Delete
                      </AlertDialogAction>
                    </AlertDialogFooter>
                  </AlertDialogContent>
                </AlertDialog>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="totalFilteredPages > 1" class="p-4 border-t border-gray-100">
          <div class="flex items-center justify-between">
            <Button
              variant="outline"
              :disabled="currentPage === 1"
              @click="changePage(currentPage - 1)"
            >
              Previous
            </Button>
            <span class="text-sm text-gray-600">
              Page {{ currentPage }} of {{ totalFilteredPages }}
            </span>
            <Button
              variant="outline"
              :disabled="currentPage >= totalFilteredPages"
              @click="changePage(currentPage + 1)"
            >
              Next
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>