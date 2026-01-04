<script setup lang="ts">
import { blogApi } from '@/services/api'
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const slug = route.params.slug as string

const post = ref<any>(null)
const loading = ref(true)

onMounted(async () => {
  const res = await blogApi.show(slug)
  post.value = res?.data || null
  loading.value = false
})

const publishedDate = computed(() =>
  post.value?.published_at
    ? new Date(post.value.published_at).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      })
    : ''
)
</script>

<template>
  <div class="min-h-screen bg-white font-celias">

    <!-- Loading -->
    <div v-if="loading" class="h-screen flex items-center justify-center text-[#38618C]">
      Loading…
    </div>

    <!-- Article -->
    <article
      v-else
      class="max-w-[760px] mx-auto px-4 pt-16 pb-24"
    >

      <!-- Category -->
      <div class="mb-6">
        <span
          class="inline-block px-4 py-1.5 text-sm font-semibold
          border border-[#35A7FF] rounded-full text-[#38618C]"
        >
          {{ post.category }}
        </span>
      </div>

      <!-- Title -->
      <h1
        class="text-[42px] md:text-[52px] font-extrabold
        leading-tight tracking-tight text-[#0F172A] mb-6"
      >
        {{ post.title }}
      </h1>

      <!-- Meta -->
      <div class="flex items-center gap-4 text-sm text-[#38618C] mb-12">
        <span>{{ publishedDate }}</span>
        <span>•</span>
        <span>5 min read</span>
      </div>

      <!-- Hero Image -->
      <img
        v-if="post.image"
        :src="post.image"
        :alt="post.title"
        class="w-full max-h-[460px] object-cover
        rounded-3xl mb-16 shadow-sm"
      />

      <!-- Intro -->
      <p
        v-if="post.summary"
        class="text-xl leading-relaxed text-[#38618C] mb-14"
      >
        {{ post.summary }}
      </p>

      <!-- Content -->
      <div
        class="
        prose prose-xl max-w-none
        prose-headings:font-bold
        prose-headings:text-[#0F172A]
        prose-h2:text-[32px]
        prose-h3:text-[26px]
        prose-p:text-[#38618C]
        prose-p:leading-[1.9]
        prose-p:mb-6
        prose-a:text-[#35A7FF]
        prose-strong:text-[#0F172A]
        prose-blockquote:border-l-[#35A7FF]
        prose-blockquote:text-[#38618C]
        prose-img:rounded-2xl
        prose-img:shadow-sm
        "
        v-html="post.content"
      />

      <!-- Divider -->
      <div class="my-20 h-px bg-[#35A7FF]/30"></div>

      <!-- Back -->
      <button
        class="inline-flex items-center gap-2 px-6 py-3
        border border-[#35A7FF] rounded-xl
        text-[#38618C] hover:bg-[#35A7FF]/10 transition"
        @click="router.push('/blog')"
      >
        ← Retour aux articles
      </button>

    </article>
  </div>
</template>

<style scoped>
.font-celias {
  font-family: 'Celias', system-ui, sans-serif;
}
</style>
