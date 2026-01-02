<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'

// UI
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Dialog } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'

const router = useRouter()
const blogs = ref<any[]>([])
const loading = ref(false)
const page = ref(1)
const total = ref(0)
const itemsPerPage = 12

// Dialog / form state
const dialogOpen = ref(false)
const isEdit = ref(false)
const editingId = ref<number | string | null>(null)
const form = ref({
  title: '',
  category: '',
  summary: '',
  content: '',
  tags: '',
  image: '',
  published_at: ''
})

async function fetchBlogs() {
  loading.value = true
  try {
    const res = await api.blog.list({ page: page.value })
    // support different response shapes
    const data = res && res.data && Array.isArray(res.data)
      ? res.data
      : res && res.data && Array.isArray(res.data?.data)
        ? res.data.data
        : Array.isArray(res) ? res : []

    blogs.value = data || []
    total.value = res?.total || res?.data?.total || blogs.value.length
  } catch (err) {
    console.error('Error loading blogs:', err)
    blogs.value = []
  } finally {
    loading.value = false
  }
}

function openCreate() {
  isEdit.value = false
  editingId.value = null
  form.value = { title: '', category: '', summary: '', content: '', tags: '', image: '', published_at: '' }
  dialogOpen.value = true
}

function openEdit(blog: any) {
  isEdit.value = true
  editingId.value = blog.id || blog.slug
  form.value = {
    title: blog.title || '',
    category: blog.category || '',
    summary: blog.summary || '',
    content: blog.content || '',
    tags: Array.isArray(blog.tags) ? blog.tags.join(', ') : (blog.tags || ''),
    image: blog.image || '',
    published_at: blog.published_at ? blog.published_at.split('T')[0] : ''
  }
  dialogOpen.value = true
}

async function submit() {
  try {
    const payload: any = {
      title: form.value.title.trim(),
      category: form.value.category,
      summary: form.value.summary.trim(),
      content: form.value.content,
      tags: form.value.tags ? form.value.tags.split(',').map((t: string) => t.trim()).filter(Boolean) : [],
      image: form.value.image.trim(),
      published_at: form.value.published_at || null
    }

    if (isEdit.value && editingId.value) {
      await api.blog.update(editingId.value as any, payload)
      alert('Blog updated')
    } else {
      await api.blog.create(payload)
      alert('Blog created')
    }

    dialogOpen.value = false
    await fetchBlogs()
  } catch (err: any) {
    console.error('Save error:', err)
    alert(err?.message || 'Failed to save blog. Check server logs.')
  }
}

async function remove(blog: any) {
  if (!confirm('Delete this blog?')) return
  try {
    await api.blog.delete(blog.id || blog.slug)
    await fetchBlogs()
    alert('Deleted')
  } catch (err: any) {
    console.error('Delete error:', err)
    alert(err?.message || 'Failed to delete')
  }
}

function view(blog: any) {
  const slug = blog.slug || blog.id
  router.push(`/blog/${slug}`)
}

onMounted(fetchBlogs)
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold">Manage Blogs</h1>
        <p class="text-sm text-gray-500">Create, edit and remove blog posts.</p>
      </div>
      <div class="flex items-center gap-2">
        <Button @click="fetchBlogs">Refresh</Button>
        <Button @click="openCreate">New Blog</Button>
      </div>
    </div>

    <Card>
      <CardContent>
        <div v-if="loading" class="text-center py-8">Loading...</div>

        <div v-else>
          <div v-if="blogs.length === 0" class="text-center py-8 text-gray-500">No blogs found.</div>

          <div class="space-y-3">
            <div v-for="b in blogs" :key="b.id || b.slug" class="p-4 bg-slate-800 rounded-lg flex items-start justify-between">
              <div>
                <div class="font-semibold text-lg">{{ b.title }}</div>
                <div class="text-sm text-gray-400">{{ b.category }} • {{ b.author?.name || 'Unknown' }}</div>
                <div class="text-sm text-gray-400">Published: {{ b.published_at ? b.published_at.split('T')[0] : '—' }}</div>
              </div>
              <div class="flex items-center gap-2">
                <Button variant="outline" @click="() => view(b)">View</Button>
                <Button variant="outline" @click="() => openEdit(b)">Edit</Button>
                <Button class="bg-red-600" @click="() => remove(b)">Delete</Button>
              </div>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <Dialog v-model:open="dialogOpen">
      <div class="p-4 max-w-2xl">
        <h3 class="text-lg font-semibold mb-3">{{ isEdit ? 'Edit Blog' : 'New Blog' }}</h3>
        <div class="space-y-3">
          <Input v-model="form.title" placeholder="Title" />
          <Input v-model="form.category" placeholder="Category" />
          <Input v-model="form.published_at" type="date" />
          <Input v-model="form.image" placeholder="Image URL" />
          <Input v-model="form.tags" placeholder="tags, comma separated" />
          <Input v-model="form.summary" placeholder="Summary" />
          <textarea v-model="form.content" rows="6" class="w-full p-2 rounded bg-slate-900"></textarea>
        </div>

        <div class="mt-4 flex justify-end gap-2">
          <Button variant="outline" @click="dialogOpen = false">Cancel</Button>
          <Button @click="submit">{{ isEdit ? 'Save' : 'Create' }}</Button>
        </div>
      </div>
    </Dialog>
  </div>
</template>

<style scoped>
.bg-slate-800 { background-color: #0f1724; }
</style>
