<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { ref, watch } from 'vue'
import api from '../services/api'

const props = defineProps<{ 
  open: boolean
  clientId: number | null
}>()

const emit = defineEmits<{
  (e: 'close'): void
}>()

const client = ref<any>(null)
const portfolio = ref<any>(null)
const transactions = ref<any[]>([])
const loading = ref(false)

// Utility functions
function formatPrice(value: number | string | null): string {
  const num = typeof value === 'string' ? parseFloat(value) : (value || 0)
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR'
  }).format(num)
}

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('fr-FR')
}

watch(() => props.clientId, async (id) => {
  if (id) {
    loading.value = true
    try {
      const idString = String(id)
      const [clientData, portfolioData, transactionsData] = await Promise.all([
        api.admin.clients.show(idString),
        api.admin.clients.getPortfolio(idString),
        api.admin.clients.transactions(idString)
      ])
      client.value = clientData
      portfolio.value = (portfolioData as any)?.portfolio || (portfolioData as any)
      transactions.value = (transactionsData as any)?.transactions?.slice(0, 5) || []
    } catch (err: any) {
      console.error('Error fetching client details:', err)
    } finally {
      loading.value = false
    }
  }
})
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent class="sm:max-w-4xl max-h-[90vh] overflow-y-auto border-brand-dark">
      <DialogHeader>
        <DialogTitle class="text-brand-dark font-bold text-xl">
          {{ client?.name || 'Détails du client' }}
        </DialogTitle>
      </DialogHeader>

      <div v-if="loading" class="py-8 text-center">
        <div class="animate-spin">⌛</div>
        Chargement...
      </div>

      <div v-else-if="client" class="space-y-6">
        <!-- Informations du client -->
        <Card>
          <CardHeader>
            <CardTitle class="text-brand-dark">Informations personnelles</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <div class="text-sm text-gray-500">Nom</div>
                <div class="font-medium">{{ client.name }}</div>
              </div>
              <div>
                <div class="text-sm text-gray-500">Email</div>
                <div class="font-medium">{{ client.email }}</div>
              </div>
              <div>
                <div class="text-sm text-gray-500">Solde</div>
                <div class="font-medium">{{ formatPrice(client.solde) }}</div>
              </div>
              <div>
                <div class="text-sm text-gray-500">Inscription</div>
                <div class="font-medium">{{ formatDate(client.created_at) }}</div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- wallet -->
        <Card v-if="portfolio?.length">
          <CardHeader>
            <CardTitle class="text-brand-dark">wallet</CardTitle>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Crypto</TableHead>
                  <TableHead>Quantité</TableHead>
                  <TableHead class="text-right">Valeur actuelle</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="item in portfolio" :key="item.id">
                  <TableCell class="font-medium">
                    {{ item.cryptomoney?.symbole }}
                  </TableCell>
                  <TableCell>{{ item.quantity }}</TableCell>
                  <TableCell class="text-right">
                    {{ formatPrice(item.quantity * (item.cryptomoney?.price_eur || 0)) }}
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </CardContent>
        </Card>

        <!-- Dernières transactions -->
        <Card v-if="transactions.length">
          <CardHeader>
            <CardTitle class="text-brand-dark">Dernières transactions</CardTitle>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Date</TableHead>
                  <TableHead>Type</TableHead>
                  <TableHead>Crypto</TableHead>
                  <TableHead>Quantité</TableHead>
                  <TableHead class="text-right">Prix</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="tx in transactions" :key="tx.id">
                  <TableCell>{{ formatDate(tx.created_at) }}</TableCell>
                  <TableCell>
                    <span :class="tx.type === 'ACHAT' ? 'text-brand-green' : 'text-brand-red'">
                      {{ tx.type }}
                    </span>
                  </TableCell>
                  <TableCell>{{ tx.cryptomoney?.symbole }}</TableCell>
                  <TableCell>{{ tx.quantity }}</TableCell>
                  <TableCell class="text-right">{{ formatPrice(tx.price) }}</TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </CardContent>
        </Card>
      </div>

      <div class="mt-6 flex justify-end">
        <Button 
          variant="outline"
          class="border-brand-dark text-brand-dark hover:bg-brand-dark hover:text-white"
          @click="emit('close')"
        >
          Fermer
        </Button>
      </div>
    </DialogContent>
  </Dialog>
</template>

<style scoped>
:deep(.border-brand-dark) {
  border-color: #38618C;
}

:deep(.text-brand-dark) {
  color: #38618C;
}
</style>
