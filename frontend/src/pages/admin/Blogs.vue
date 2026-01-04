<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { blogApi } from '../../services/api'

// Components
import BlogList from './_componentsBlogs/BlogList.vue'
import BlogForm from './_componentsBlogs/BlogForm.vue'
import DeleteConfirmationDialog from './_componentsBlogs/DeleteConfirmationDialog.vue'
import { Button } from '@/components/ui/button'

const router = useRouter()

// Data
const blogs = ref<any[]>([])
const loading = ref(false)
const currentPage = ref(1)
const totalPages = ref(1)

// Form dialog state
const formOpen = ref(false)
const isEdit = ref(false)
const editingBlog = ref<any>(null)

// Delete confirmation state
const deleteOpen = ref(false)
const blogToDelete = ref<any>(null)

// Categories (you can fetch these from API or define them statically)
const categories = ref([
  'Crypto News',
  'Market Analysis',
  'Trading',
  'DeFi',
  'NFT',
  'Technology',
  'Regulation',
  'Education'
])

async function fetchBlogs() {
  loading.value = true
  try {
    const response = await blogApi.list({ page: currentPage.value })
    const data = response?.data?.data || response?.data || response || []
    
    blogs.value = Array.isArray(data) ? data : []
    totalPages.value = response?.data?.last_page || response?.last_page || 1
    currentPage.value = response?.data?.current_page || response?.current_page || 1
  } catch (err) {
    console.error('Error loading blogs:', err)
    blogs.value = []
    totalPages.value = 1
  } finally {
    loading.value = false
  }
}

function handleCreate() {
  isEdit.value = false
  editingBlog.value = null
  formOpen.value = true
}

function handleEdit(blog: any) {
  isEdit.value = true
  editingBlog.value = blog
  formOpen.value = true
}

function handleDelete(blog: any) {
  blogToDelete.value = blog
  deleteOpen.value = true
}

async function handleFormSubmit(payload: any) {
  try {
    if (isEdit.value && editingBlog.value) {
      await blogApi.update(editingBlog.value.id, payload)
    } else {
      await blogApi.create(payload)
    }
    
    formOpen.value = false
    await fetchBlogs()
  } catch (err) {
    console.error('Save error:', err)
    alert('Failed to save blog post. Please try again.')
  }
}

async function handleDeleteConfirm() {
  if (!blogToDelete.value) return
  
  try {
    await blogApi.delete(blogToDelete.value.id)
    deleteOpen.value = false
    await fetchBlogs()
  } catch (err) {
    console.error('Delete error:', err)
    alert('Failed to delete blog post. Please try again.')
  }
}

function handleViewDetails(id: number) {
  router.push(`/blog/${id}`)
}

function handleChangePage(page: number) {
  currentPage.value = page
  fetchBlogs()
}

function handleRefresh() {
  fetchBlogs()
}

onMounted(() => {
  fetchBlogs()
})
</script>

<template>
  <div class="space-y-6 p-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">📚 Blog Management</h1>
        <p class="text-gray-600 mt-1">Create, edit, and manage your blog posts</p>
      </div>
      <Button @click="handleCreate" class="bg-blue-600 hover:bg-blue-700">
        ✍️ Create New Post
      </Button>
    </div>

    <!-- Blog List -->
    <BlogList
      :blogs="blogs"
      :loading="loading"
      :current-page="currentPage"
      :total-pages="totalPages"
      @view-details="handleViewDetails"
      @edit-blog="handleEdit"
      @delete-blog="handleDelete"
      @change-page="handleChangePage"
      @refresh="handleRefresh"
    />

    <!-- Form Dialog -->
    <BlogForm
      v-model:open="formOpen"
      :is-edit="isEdit"
      :blog="editingBlog"
      :categories="categories"
      @submit="handleFormSubmit"
    />

    <!-- Delete Confirmation Dialog -->
    <DeleteConfirmationDialog
      v-model:open="deleteOpen"
      title="Delete Blog Post"
      :description="`Are you sure you want to delete '${blogToDelete?.title}'? This action cannot be undone.`"
      @confirm="handleDeleteConfirm"
    />
  </div>
</template>