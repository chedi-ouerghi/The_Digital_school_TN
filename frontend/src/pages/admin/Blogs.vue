<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { blogApi } from '../../services/api'

import { Button } from '@/components/ui/button'
import BlogDetailsDialog from './_componentsBlogs/BlogDetailsDialog.vue'
import BlogForm from './_componentsBlogs/BlogForm.vue'
import BlogList from './_componentsBlogs/BlogList.vue'


const blogs = ref([])
const loading = ref(false)
const currentPage = ref(1)
const totalPages = ref(1)
const totalItems = ref(0)

const formOpen = ref(false)
const isEdit = ref(false)
const editingBlog = ref(null)

const detailsDialogOpen = ref(false)
const selectedBlogForDetails = ref(null)

async function fetchBlogs() {
  loading.value = true
  const res = await blogApi.list({ page: currentPage.value })
  blogs.value = res.data.data
  totalPages.value = res.data.last_page
  totalItems.value = res.data.total || res.data.meta?.total || blogs.value.length
  loading.value = false
}

// Handle create or update from BlogForm
async function handleFormSubmit(payload: any) {
  try {
    if (isEdit.value && editingBlog.value && (editingBlog.value.id || payload.id)) {
      const id = payload.id || editingBlog.value.id
      await blogApi.update(id, payload)
    } else {
      await blogApi.create(payload)
    }

    // Close form and reset edit state
    formOpen.value = false
    isEdit.value = false
    editingBlog.value = null

    // Refresh list
    await fetchBlogs()
  } catch (err) {
    console.error('Error saving blog:', err)
    // Optionally show user-facing error here
  }
}

function createBlog() {
  isEdit.value = false
  editingBlog.value = null
  formOpen.value = true
}

function editBlog(blog: any) {
  isEdit.value = true
  editingBlog.value = blog
  formOpen.value = true
}

function viewBlogDetails(blog: any) {
  selectedBlogForDetails.value = blog
  detailsDialogOpen.value = true
}

function handleDetailsEdit(blog: any) {
  editBlog(blog)
}

onMounted(fetchBlogs)

const categories = computed(() => {
  const set = new Set<string>()
  blogs.value.forEach((b: any) => { if (b.category) set.add(typeof b.category === 'string' ? b.category : (b.category.name || b.category)); })
  return Array.from(set)
})
</script>

<template>
  <div class="min-h-screen bg-white p-8 font-celias space-y-8">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#0F172A]">
          Blog Management
        </h1>
        <p class="text-[#38618C] mt-1">
          Create, edit and publish articles
        </p>
      </div>

      <Button
        class="bg-[#FF5964] text-white hover:opacity-90"
        @click="createBlog"
      >
        + New Blog
      </Button>
    </div>

    <!-- List -->
    <BlogList
      :blogs="blogs"
      :loading="loading"
      :current-page="currentPage"
      :total-pages="totalPages"
      :total-items="totalItems"
      @view-details="viewBlogDetails"
      @edit-blog="editBlog"
      @change-page="p => { currentPage = p; fetchBlogs() }"
      @refresh="fetchBlogs"
    />

    <!-- Form -->
    <BlogForm
      v-model:open="formOpen"
      :is-edit="isEdit"
      :blog="editingBlog"
      :categories="categories"
      @submit="handleFormSubmit"
    />

    <!-- Details Dialog -->
    <BlogDetailsDialog
      v-model:open="detailsDialogOpen"
      :blog="selectedBlogForDetails"
      @edit="handleDetailsEdit"
    />
  </div>
</template>

<style scoped>
.font-celias {
  font-family: 'Celias', system-ui, sans-serif;
}
</style>
