<script setup lang="ts">
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import {
  CheckCircle,
  ChevronLeft,
  ChevronRight,
  ChevronsLeft,
  ChevronsRight,
  Clock,
  Download,
  Edit,
  Eye,
  FileText,
  Filter,
  Plus,
  Search,
  Tag,
  Trash2,
  User,
  XCircle
} from 'lucide-vue-next'
import { computed, ref } from 'vue'

// Types
interface Author {
  id: string
  name: string
  email: string
  email_verified_at: string | null
  role: string
  last_id_change_at: string | null
  profile_picture: string | null
  profile_banner: string | null
  created_at: string
  updated_at: string
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
  author: Author
}

interface Props {
  blogs: Blog[]
  loading: boolean
  currentPage: number
  totalPages: number
  totalItems: number
}

const props = withDefaults(defineProps<Props>(), {
  blogs: () => [],
  loading: false,
  currentPage: 1,
  totalPages: 1,
  totalItems: 0
})

const emit = defineEmits<{
  'edit-blog': [blog: Blog]
  'view-details': [blog: Blog]
  'change-page': [page: number]
  'refresh': []
  'create-blog': []
  'view-public': [blog: Blog]
  'confirm-delete': [blog: Blog]
}>()

// Fonction pour tronquer l'email
function formatEmail(email: string): string {
  if (!email) return ''
  const [username, domain] = email.split('@')
  if (username && username.length > 8) {
    return username.substring(0, 8) + '...@' + domain
  }
  return email
}

// Fonction pour formater la date de manière concise
function formatShortDate(date: string | null): string {
  if (!date) return 'Not published'
  const d = new Date(date)
  const now = new Date()
  const diffTime = Math.abs(now.getTime() - d.getTime())
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  
  if (diffDays === 0) {
    const hours = Math.floor(diffTime / (1000 * 60 * 60))
    if (hours === 0) {
      const minutes = Math.floor(diffTime / (1000 * 60))
      return minutes <= 1 ? 'Just now' : `${minutes}m ago`
    }
    return `${hours}h ago`
  } else if (diffDays === 1) {
    return 'Yesterday'
  } else if (diffDays < 7) {
    return `${diffDays}d ago`
  } else if (diffDays < 30) {
    const weeks = Math.floor(diffDays / 7)
    return `${weeks}w ago`
  } else {
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
  }
}

// Fonction excerpt avec longueur réduite par défaut
function excerpt(text: string, length = 40): string {
  if (!text) return ''
  return text.length > length ? text.substring(0, length) + '...' : text
}

// Compute stats from blogs prop
const stats = computed(() => {
  const published = props.blogs.filter((b: Blog) => b.published_at).length
  const drafts = props.blogs.filter((b: Blog) => !b.published_at).length
  return {
    total: props.blogs.length,
    published,
    drafts,
  }
})

// Filters and pagination (local, for display only)
const searchQuery = ref('')
const statusFilter = ref<'all' | 'published' | 'draft'>('all')
const categoryFilter = ref('all')
const sortBy = ref<'date_desc' | 'date_asc' | 'title_asc' | 'title_desc'>('date_desc')
const itemsPerPage = 10

// Get unique categories from blogs
const categories = computed(() => {
  const uniqueCategories = new Set<string>()
  props.blogs.forEach(blog => {
    if (blog.category) {
      uniqueCategories.add(blog.category)
    }
  })
  return Array.from(uniqueCategories).sort()
})

const filteredBlogs = computed(() => {
  let result = Array.isArray(props.blogs) ? [...props.blogs] : []
  
  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(blog =>
      blog.title?.toLowerCase().includes(query) ||
      blog.summary?.toLowerCase().includes(query) ||
      blog.content?.toLowerCase().includes(query) ||
      blog.author?.name?.toLowerCase().includes(query) ||
      blog.category?.toLowerCase().includes(query)
    )
  }
  
  // Status filter
  if (statusFilter.value !== 'all') {
    result = result.filter(blog =>
      statusFilter.value === 'published' ? blog.published_at : !blog.published_at
    )
  }
  
  // Category filter
  if (categoryFilter.value !== 'all') {
    result = result.filter(blog => blog.category === categoryFilter.value)
  }
  
  // Sort
  result.sort((a: Blog, b: Blog) => {
    const dateA = a.published_at || a.created_at
    const dateB = b.published_at || b.created_at
    
    switch (sortBy.value) {
      case 'date_desc':
        return new Date(dateB).getTime() - new Date(dateA).getTime()
      case 'date_asc':
        return new Date(dateA).getTime() - new Date(dateB).getTime()
      case 'title_asc':
        return (a.title || '').localeCompare(b.title || '')
      case 'title_desc':
        return (b.title || '').localeCompare(a.title || '')
      default:
        return 0
    }
  })
  
  return result
})

const paginatedBlogs = computed(() => {
  const start = (props.currentPage - 1) * itemsPerPage
  return filteredBlogs.value.slice(start, start + itemsPerPage)
})

// Event handlers
const createBlog = () => {
  emit('create-blog')
}

const editBlog = (blog: Blog) => {
  emit('edit-blog', blog)
}

const viewDetails = (blog: Blog) => {
  emit('view-details', blog)
}

const viewPublic = (blog: Blog) => {
  emit('view-public', blog)
}

const confirmDelete = (blog: Blog) => {
  emit('confirm-delete', blog)
}

const changePage = (page: number) => {
  emit('change-page', page)
}

const resetFilters = () => {
  searchQuery.value = ''
  statusFilter.value = 'all'
  categoryFilter.value = 'all'
  sortBy.value = 'date_desc'
}
</script>

<template>
  <!-- Statistiques -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <Card>
      <CardContent class="p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Total Articles</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
          </div>
          <div class="p-3 rounded-full bg-blue-50">
            <FileText class="h-6 w-6 text-blue-600" />
          </div>
        </div>
      </CardContent>
    </Card>
    
    <Card>
      <CardContent class="p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Published</p>
            <p class="text-2xl font-bold text-green-600">{{ stats.published }}</p>
          </div>
          <div class="p-3 rounded-full bg-green-50">
            <CheckCircle class="h-6 w-6 text-green-600" />
          </div>
        </div>
      </CardContent>
    </Card>
    
    <Card>
      <CardContent class="p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Drafts</p>
            <p class="text-2xl font-bold text-amber-600">{{ stats.drafts }}</p>
          </div>
          <div class="p-3 rounded-full bg-amber-50">
            <Clock class="h-6 w-6 text-amber-600" />
          </div>
        </div>
      </CardContent>
    </Card>
   
  </div>

  <!-- Filtres et Recherche -->
  <Card>
    <CardHeader>
      <CardTitle>Filters</CardTitle>
      <CardDescription>Refine your article search</CardDescription>
    </CardHeader>
    <CardContent>
      <div class="flex flex-col lg:flex-row gap-4">
        <!-- Recherche -->
        <div class="flex-1">
          <div class="relative">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
            <Input
              v-model="searchQuery"
              placeholder="Search articles by title, content, author..."
              class="pl-10"
            />
          </div>
        </div>
        
        <!-- Filtres -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <Select v-model="statusFilter">
            <SelectTrigger>
              <div class="flex items-center gap-2">
                <CheckCircle class="h-4 w-4" />
                <SelectValue placeholder="Status" />
              </div>
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All Status</SelectItem>
              <SelectItem value="published" class="text-green-600">
                <div class="flex items-center gap-2">
                  <CheckCircle class="h-4 w-4" />
                  Published
                </div>
              </SelectItem>
              <SelectItem value="draft" class="text-amber-600">
                <div class="flex items-center gap-2">
                  <Clock class="h-4 w-4" />
                  Draft
                </div>
              </SelectItem>
            </SelectContent>
          </Select>
          
          <Select v-model="categoryFilter">
            <SelectTrigger>
              <div class="flex items-center gap-2">
                <Tag class="h-4 w-4" />
                <SelectValue placeholder="Category" />
              </div>
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All Categories</SelectItem>
              <SelectItem 
                v-for="category in categories" 
                :key="category" 
                :value="category"
              >
                {{ category }}
              </SelectItem>
            </SelectContent>
          </Select>
          
          <Select v-model="sortBy">
            <SelectTrigger>
              <div class="flex items-center gap-2">
                <Filter class="h-4 w-4" />
                <SelectValue placeholder="Sort by" />
              </div>
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="date_desc">Newest First</SelectItem>
              <SelectItem value="date_asc">Oldest First</SelectItem>
              <SelectItem value="title_asc">Title A-Z</SelectItem>
              <SelectItem value="title_desc">Title Z-A</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>
      
      <!-- Filtres actifs -->
      <div v-if="searchQuery || statusFilter !== 'all' || categoryFilter !== 'all'" class="mt-4 flex flex-wrap gap-2">
        <Badge 
          v-if="searchQuery"
          variant="secondary"
          class="gap-2"
        >
          Search: "{{ searchQuery }}"
          <Button 
            size="sm" 
            variant="ghost" 
            class="h-4 w-4 p-0 hover:bg-transparent"
            @click="searchQuery = ''"
          >
            <XCircle class="h-3 w-3" />
          </Button>
        </Badge>
        <Badge 
          v-if="statusFilter !== 'all'"
          variant="secondary"
          class="gap-2"
        >
          Status: {{ statusFilter === 'published' ? 'Published' : 'Draft' }}
          <Button 
            size="sm" 
            variant="ghost" 
            class="h-4 w-4 p-0 hover:bg-transparent"
            @click="statusFilter = 'all'"
          >
            <XCircle class="h-3 w-3" />
          </Button>
        </Badge>
        <Badge 
          v-if="categoryFilter !== 'all'"
          variant="secondary"
          class="gap-2"
        >
          Category: {{ categoryFilter }}
          <Button 
            size="sm" 
            variant="ghost" 
            class="h-4 w-4 p-0 hover:bg-transparent"
            @click="categoryFilter = 'all'"
          >
            <XCircle class="h-3 w-3" />
          </Button>
        </Badge>
        <Button 
          v-if="searchQuery || statusFilter !== 'all' || categoryFilter !== 'all'"
          size="sm"
          variant="outline"
          class="h-6"
          @click="resetFilters"
        >
          Clear All
        </Button>
      </div>
    </CardContent>
  </Card>

<!-- Vue Tableau -->
<Card class="overflow-hidden">
  <CardHeader class="border-b">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <CardTitle>Articles</CardTitle>
        <CardDescription>
          Showing {{ paginatedBlogs.length }} of {{ filteredBlogs.length }} articles
        </CardDescription>
      </div>
      <div class="flex items-center gap-2">
        <Button variant="outline" size="sm" class="gap-2">
          <Download class="h-4 w-4" />
          Export
        </Button>
      </div>
    </div>
  </CardHeader>
  
  <CardContent class="p-0">
    <!-- État de chargement -->
    <div v-if="loading" class="space-y-3 p-6">
      <div v-for="i in 5" :key="i" class="animate-pulse">
        <div class="h-16 bg-gray-100 rounded"></div>
      </div>
    </div>

    <!-- État vide -->
    <div v-else-if="filteredBlogs.length === 0" class="p-12 text-center">
      <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
        <FileText class="h-8 w-8 text-gray-400" />
      </div>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">No articles found</h3>
      <p class="text-gray-600 mb-6 max-w-md mx-auto">
        {{ searchQuery || statusFilter !== 'all' || categoryFilter !== 'all' 
          ? 'No articles match your current filters. Try adjusting your search criteria.' 
          : 'There are no articles yet. Create your first article!' 
        }}
      </p>
      <Button v-if="!searchQuery && statusFilter === 'all' && categoryFilter === 'all'" @click="createBlog">
        <Plus class="h-4 w-4 mr-2" />
        Create First Article
      </Button>
    </div>

    <!-- Tableau (sans scroll horizontal) -->
    <div v-else>
      <div class="w-full">
        <!-- En-têtes - Alignement parfait avec les cellules -->
        <div class="bg-gray-50 border-b">
          <div class="grid grid-cols-12 py-3 px-4">
            <!-- Title -->
            <div class="col-span-3 text-xs font-medium text-gray-700 flex items-center pl-2">
              <span class="truncate">Title</span>
            </div>
            
            <!-- Author -->
            <div class="col-span-2 text-xs font-medium text-gray-700 flex items-center pl-1">
              <span class="truncate">Author</span>
            </div>
            
            <!-- Category -->
            <div class="col-span-1 text-xs font-medium text-gray-700 flex items-center pl-1">
              <span class="truncate">Category</span>
            </div>
            
            <!-- Tags -->
            <div class="col-span-2 text-xs font-medium text-gray-700 flex items-center pl-1">
              <span class="truncate">Tags</span>
            </div>
            
            <!-- Published -->
            <div class="col-span-2 text-xs font-medium text-gray-700 flex items-center pl-1">
              <span class="truncate">Published</span>
            </div>
            
            <!-- Actions -->
            <div class="col-span-2 text-xs font-medium text-gray-700 flex items-center justify-end pr-2">
              <span class="truncate">Actions</span>
            </div>
          </div>
        </div>
        
        <!-- Lignes - Alignement vertical centré -->
        <div class="divide-y divide-gray-100">
          <div 
            v-for="blog in paginatedBlogs" 
            :key="blog.id"
            class="hover:bg-gray-50 transition-colors"
          >
            <div class="grid grid-cols-12 py-3 px-4 items-center min-h-[64px]">
              <!-- Title - Alignement start -->
              <div class="col-span-3 h-full">
                <div class="flex items-center gap-2 h-full">
                  <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center flex-shrink-0">
                    <FileText class="h-4 w-4 text-gray-400" />
                  </div>
                  <div class="min-w-0 flex-1">
                    <h4 class="font-medium text-gray-900 text-sm truncate leading-tight">{{ blog.title }}</h4>
                    <p class="text-xs text-gray-500 mt-0.5 truncate leading-tight">
                      {{ excerpt(blog.summary || blog.content, 45) }}
                    </p>
                  </div>
                </div>
              </div>
              
              <!-- Author - Alignement center -->
              <div class="col-span-2 h-full flex items-center">
                <div class="flex items-center gap-1.5 w-full">
                  <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                    <User class="h-3 w-3 text-gray-600" />
                  </div>
                  <div class="min-w-0 flex-1">
                    <span class="text-xs text-gray-900 truncate block leading-tight">{{ blog.author?.name || 'Admin' }}</span>
                    <span class="text-xs text-gray-500 truncate block leading-tight">{{ formatEmail(blog.author?.email) }}</span>
                  </div>
                </div>
              </div>
              
              <!-- Category - Alignement center -->
              <div class="col-span-1 h-full flex items-center justify-center px-1">
                <Badge variant="outline" class="text-xs px-2 py-0.5 truncate max-w-full">
                  <span class="truncate">{{ blog.category || 'General' }}</span>
                </Badge>
              </div>
              
              <!-- Tags - Alignement center -->
              <div class="col-span-2 h-full flex items-center px-1">
                <div class="flex flex-wrap gap-1 w-full justify-center">
                  <Badge 
                    v-for="(tag, index) in blog.tags?.slice(0, 2)" 
                    :key="index"
                    variant="secondary"
                    class="text-xs px-1.5 py-0.5 bg-blue-50 text-blue-700 border-blue-200 truncate max-w-[60px]"
                  >
                    <span class="truncate">{{ tag }}</span>
                  </Badge>
                  <span v-if="blog.tags && blog.tags.length > 2" class="text-xs text-gray-500 flex-shrink-0">
                    +{{ blog.tags.length - 2 }}
                  </span>
                </div>
              </div>
              
              <!-- Published Date & Status - Alignement center -->
              <div class="col-span-2 h-full flex flex-col items-center justify-center px-1">
                <div class="text-xs text-gray-500 truncate w-full text-center">
                  {{ formatShortDate(blog.published_at) }}
                </div>
                <Badge 
                  :class="[
                    'text-xs font-medium px-2 py-0.5 mt-1 truncate max-w-full',
                    blog.published_at 
                      ? 'bg-green-50 text-green-700 border-green-200' 
                      : 'bg-amber-50 text-amber-700 border-amber-200'
                  ]"
                >
                  <div class="flex items-center gap-1">
                    <component 
                      :is="blog.published_at ? CheckCircle : Clock" 
                      class="h-2.5 w-2.5 flex-shrink-0" 
                    />
                    <span class="truncate">{{ blog.published_at ? 'Live' : 'Draft' }}</span>
                  </div>
                </Badge>
              </div>
              
              <!-- Actions - Alignement end -->
              <div class="col-span-2 h-full flex items-center justify-end pr-2">
                <div class="flex items-center gap-1">
                  <Button
                    size="sm"
                    variant="ghost"
                    class="h-7 w-7 p-0"
                    title="View Details"
                    @click="viewDetails(blog)"
                  >
                    <Eye class="h-3.5 w-3.5" />
                  </Button>
                  <Button
                    size="sm"
                    variant="ghost"
                    class="h-7 w-7 p-0"
                    title="Edit"
                    @click="editBlog(blog)"
                  >
                    <Edit class="h-3.5 w-3.5" />
                  </Button>
                  <Button
                    size="sm"
                    variant="ghost"
                    class="h-7 w-7 p-0 text-red-600 hover:text-red-700 hover:bg-red-50"
                    title="Delete"
                    @click="confirmDelete(blog)"
                  >
                    <Trash2 class="h-3.5 w-3.5" />
                  </Button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="filteredBlogs.length > 0 && !loading" class="border-t px-4 py-3">
      <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="text-xs text-gray-600">
          Showing {{ ((props.currentPage - 1) * itemsPerPage) + 1 }} to 
          {{ Math.min(props.currentPage * itemsPerPage, filteredBlogs.length) }} of 
          {{ filteredBlogs.length }} articles
        </div>
        <div class="flex items-center gap-1">
          <Button
            variant="outline"
            size="sm"
            class="h-7 w-7 p-0"
            :disabled="props.currentPage === 1"
            @click="changePage(1)"
          >
            <ChevronsLeft class="h-3 w-3" />
          </Button>
          <Button
            variant="outline"
            size="sm"
            class="h-7 w-7 p-0"
            :disabled="props.currentPage === 1"
            @click="changePage(props.currentPage - 1)"
          >
            <ChevronLeft class="h-3 w-3" />
          </Button>
          
          <div class="flex items-center gap-1">
            <Button
              v-for="p in Math.min(5, props.totalPages)"
              :key="p"
              :variant="props.currentPage === p ? 'default' : 'outline'"
              size="sm"
              class="h-7 w-7 p-0 text-xs"
              @click="changePage(p)"
            >
              {{ p }}
            </Button>
          </div>
          
          <Button
            variant="outline"
            size="sm"
            class="h-7 w-7 p-0"
            :disabled="props.currentPage === props.totalPages"
            @click="changePage(props.currentPage + 1)"
          >
            <ChevronRight class="h-3 w-3" />
          </Button>
          <Button
            variant="outline"
            size="sm"
            class="h-7 w-7 p-0"
            :disabled="props.currentPage === props.totalPages"
            @click="changePage(props.totalPages)"
          >
            <ChevronsRight class="h-3 w-3" />
          </Button>
        </div>
      </div>
    </div>
  </CardContent>
</Card>
</template>

<style scoped>
@keyframes spin {
  to { transform: rotate(360deg); }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}
</style>