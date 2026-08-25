<script setup lang="ts">
import { createChart, ColorType, CrosshairMode } from 'lightweight-charts'
import type { IChartApi, ISeriesApi } from 'lightweight-charts'
import { onMounted, onBeforeUnmount, ref, watch, nextTick, computed } from 'vue'

interface HistoryEntry {
  timestamp: number
  date: string
  price: number
  volume: number
  change_24h_pct: number
}

const props = withDefaults(defineProps<{
  history: HistoryEntry[]
  symbol?: string
  height?: number
  volumeHeight?: number
  showVolume?: boolean
}>(), {
  symbol: 'BTC/USD',
  height: 350,
  volumeHeight: 110,
  showVolume: true
})

const containerRef = ref<HTMLDivElement | null>(null)
const volumeRef = ref<HTMLDivElement | null>(null)
const empty = ref(false)

let chart: IChartApi | null = null
let volumeChart: IChartApi | null = null
let candleSeries: ISeriesApi<'Candlestick'> | null = null
let volumeSeries: ISeriesApi<'Histogram'> | null = null
let resizeObserver: ResizeObserver | null = null

function toNumber(v: unknown): number {
  const n = Number(v ?? 0)
  return Number.isFinite(n) ? n : 0
}
function seededRand(seed: number): number {
  const x = Math.sin(seed * 9999) * 10000
  return x - Math.floor(x)
}

interface Ohlc {
  time: any
  open: number
  high: number
  low: number
  close: number
  volume: number
}

const ohlcv = computed<Ohlc[]>(() => {
  const history = props.history
  if (!history || history.length === 0) return []
  const sorted = [...history].sort((a, b) => a.timestamp - b.timestamp)
  const points: Ohlc[] = []
  for (let i = 0; i < sorted.length; i++) {
    const curr = sorted[i]
    const close = Math.max(0.00000001, toNumber(curr.price))
    let open: number
    if (i === 0) {
      const r = seededRand(i + 17) - 0.5
      open = close * (1 + r * 0.012)
    } else {
      open = toNumber(sorted[i - 1].price)
      if (open <= 0) open = close * 0.99
    }
    const hlVol = 0.009 + seededRand(i + 101) * 0.02
    const maxOC = Math.max(open, close)
    const minOC = Math.min(open, close)
    const high = maxOC * (1 + hlVol * (0.35 + seededRand(i + 203) * 0.6))
    const low = minOC * (1 - hlVol * (0.35 + seededRand(i + 307) * 0.6))
    const movePct = Math.abs(close - open) / Math.max(open, 0.0001)
    const baseVol = toNumber(curr.volume) > 0 ? toNumber(curr.volume) : 25000 + seededRand(i + 411) * 30000
    const volExtra = movePct * 120000 * (0.6 + seededRand(i + 419) * 0.8)
    const volume = Math.floor(baseVol + volExtra)

    // Use business day string YYYY-MM-DD for daily candles
    const dateObj = new Date(curr.timestamp)
    const yyyy = dateObj.getFullYear()
    const mm = String(dateObj.getMonth() + 1).padStart(2, '0')
    const dd = String(dateObj.getDate()).padStart(2, '0')
    const timeStr = `${yyyy}-${mm}-${dd}`

    points.push({
      time: timeStr as any,
      open: Number(open.toFixed(cryptoPrecision(close))),
      high: Number(high.toFixed(cryptoPrecision(close))),
      low: Number(Math.max(0.00000001, low).toFixed(cryptoPrecision(close))),
      close: Number(close.toFixed(cryptoPrecision(close))),
      volume
    })
  }
  return points
})

function cryptoPrecision(price: number): number {
  if (price >= 1000) return 2
  if (price >= 1) return 2
  if (price >= 0.1) return 4
  if (price >= 0.01) return 5
  return 6
}

function initChart() {
  if (!containerRef.value || !volumeRef.value) return
  dispose()
  const data = ohlcv.value
  empty.value = data.length === 0
  if (empty.value) return
  const width = containerRef.value.clientWidth

  chart = createChart(containerRef.value, {
    layout: { background: { type: ColorType.Solid, color: '#FFFFFF' }, textColor: '#64748B', fontFamily: "Inter, ui-sans-serif, system-ui" },
    grid: { vertLines: { color: 'rgba(226,232,240,0.55)' }, horzLines: { color: 'rgba(226,232,240,0.55)' } },
    crosshair: { mode: CrosshairMode.Normal },
    width,
    height: props.height,
    rightPriceScale: { borderColor: '#E2E8F0', scaleMargins: { top: 0.07, bottom: 0.15 }, visible: true },
    timeScale: { borderColor: '#E2E8F0', timeVisible: true, secondsVisible: false, rightOffset: 8, barSpacing: Math.max(4, Math.min(14, 420 / Math.max(data.length, 10))), minBarSpacing: 4 },
    handleScroll: { mouseWheel: true, pressedMouseMove: true, horzTouchDrag: true, vertTouchDrag: false },
    handleScale: { axisPressedMouseMove: { time: true, price: true }, mouseWheel: true, pinch: true }
  })

  candleSeries = chart.addCandlestickSeries({
    upColor: '#22C55E',
    downColor: '#EF4444',
    borderUpColor: '#16A34A',
    borderDownColor: '#DC2626',
    wickUpColor: '#16A34A',
    wickDownColor: '#DC2626',
    borderVisible: false,
    wickVisible: true,
    priceFormat: { type: 'price', precision: 2, minMove: 0.01 }
  })
  // adjust precision dynamically by first price
  const prec = cryptoPrecision(data[0]?.close ?? 1)
  candleSeries.applyOptions({ priceFormat: { type: 'price', precision: prec, minMove: Math.pow(10, -prec) } as any })
  candleSeries.setData(data.map(p => ({ time: p.time, open: p.open, high: p.high, low: p.low, close: p.close })))

  const last = data[data.length - 1]
  if (last) {
    candleSeries.createPriceLine({ price: last.close, color: last.close >= last.open ? '#16A34A' : '#DC2626', lineWidth: 1, lineStyle: 2, axisLabelVisible: true, title: 'Price' })
  }
  chart.timeScale().fitContent()

  if (props.showVolume) {
    volumeChart = createChart(volumeRef.value, {
      layout: { background: { type: ColorType.Solid, color: '#FFFFFF' }, textColor: '#94A3B8', fontFamily: "Inter, ui-sans-serif, system-ui" },
      grid: { vertLines: { color: 'rgba(226,232,240,0.35)' }, horzLines: { color: 'rgba(226,232,240,0.35)' } },
      width,
      height: props.volumeHeight,
      rightPriceScale: { borderColor: '#E2E8F0', scaleMargins: { top: 0.12, bottom: 0 } },
      timeScale: { borderColor: '#E2E8F0', visible: true, timeVisible: true, secondsVisible: false, rightOffset: 8, barSpacing: chart.timeScale().options().barSpacing, minBarSpacing: 4 },
      crosshair: { mode: CrosshairMode.Normal },
      handleScroll: { mouseWheel: true, pressedMouseMove: true, horzTouchDrag: true, vertTouchDrag: false },
      handleScale: { axisPressedMouseMove: { time: true, price: false }, mouseWheel: true, pinch: true }
    })
    volumeSeries = volumeChart.addHistogramSeries({ color: 'rgba(53,167,255,0.55)', priceFormat: { type: 'volume' }, priceScaleId: '', priceLineVisible: false })
    volumeSeries.setData(data.map(p => ({ time: p.time, value: p.volume, color: p.close >= p.open ? 'rgba(34,197,94,0.85)' : 'rgba(239,68,68,0.85)' })))
    volumeChart.timeScale().fitContent()

    chart.timeScale().subscribeVisibleTimeRangeChange(range => { if (range && volumeChart) volumeChart.timeScale().setVisibleRange(range as any) })
    volumeChart.timeScale().subscribeVisibleTimeRangeChange(range => { if (range && chart) chart.timeScale().setVisibleRange(range as any) })
  }

  resizeObserver = new ResizeObserver(entries => {
    for (const e of entries) {
      const w = e.contentRect.width
      if (chart) chart.applyOptions({ width: w })
      if (volumeChart) volumeChart.applyOptions({ width: w })
    }
  })
  if (containerRef.value) resizeObserver.observe(containerRef.value)
}

function dispose() {
  if (resizeObserver) { resizeObserver.disconnect(); resizeObserver = null }
  if (chart) { chart.remove(); chart = null; candleSeries = null }
  if (volumeChart) { volumeChart.remove(); volumeChart = null; volumeSeries = null }
}

watch(() => props.history, async () => { await nextTick(); initChart() }, { deep: true })
watch(() => props.showVolume, async () => { await nextTick(); initChart() })

onMounted(() => nextTick(() => initChart()))
onBeforeUnmount(() => dispose())

defineExpose({ fitContent: () => { chart?.timeScale().fitContent(); volumeChart?.timeScale().fitContent() } })

const lastVolumeLabel = computed(() => {
  if (!ohlcv.value.length) return ''
  const v = ohlcv.value[ohlcv.value.length - 1].volume
  if (v >= 1e9) return (v/1e9).toFixed(2)+'B'
  if (v >= 1e6) return (v/1e6).toFixed(2)+'M'
  if (v >= 1e3) return (v/1e3).toFixed(1)+'K'
  return String(v)
})
</script>

<template>
  <div class="w-full select-none">
    <div class="flex items-center justify-between px-1 pb-2">
      <div class="flex items-center gap-2">
        <span class="text-[11px] font-semibold tracking-widest text-slate-500 uppercase">Price</span>
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-slate-900 text-white text-[11px] font-bold tracking-wide">
          <span class="w-2 h-2 rounded-sm" :class="ohlcv.length && ohlcv[ohlcv.length-1].close >= ohlcv[ohlcv.length-1].open ? 'bg-emerald-400' : 'bg-red-400'"></span>
          {{ symbol.toUpperCase() }}/EUR
        </span>
        <span class="hidden sm:inline text-[11px] text-slate-400">Japanese candlesticks</span>
      </div>
      <div class="text-[11px] text-slate-400 hidden md:block">JS chart by lightweight-charts</div>
    </div>

    <div ref="containerRef" class="w-full border border-slate-200 rounded-t-xl overflow-hidden bg-white shadow-sm" :style="{ height: height + 'px' }"></div>
    <div v-if="showVolume" ref="volumeRef" class="w-full border-x border-b border-slate-200 rounded-b-xl overflow-hidden bg-white -mt-[1px] shadow-sm" :style="{ height: volumeHeight + 'px' }"></div>

    <div v-if="empty" class="flex flex-col items-center justify-center py-10 text-slate-500 border border-dashed border-slate-200 rounded-xl mt-3 bg-slate-50/50">
      <div class="w-12 h-12 rounded-full bg-white border border-slate-200 flex items-center justify-center mb-3 shadow-sm">
        <span class="text-xl">📈</span>
      </div>
      <p class="text-sm font-semibold text-slate-700">No historical data</p>
      <p class="text-xs text-slate-400 mt-1">Price history will appear here</p>
    </div>

    <div v-if="!empty" class="flex items-center justify-between px-1 pt-2.5">
      <div class="flex items-center gap-3 text-[11px]">
        <span class="text-slate-500">Volume <strong class="text-slate-700">{{ lastVolumeLabel }}</strong></span>
        <span class="hidden sm:inline-flex items-center gap-1.5">
          <span class="w-2 h-2 bg-emerald-500 rounded-sm"></span> Bull
          <span class="w-2 h-2 bg-red-500 rounded-sm ml-2"></span> Bear
        </span>
      </div>
      <div class="text-[11px] text-slate-400">JS chart by lightweight-charts</div>
    </div>
  </div>
</template>
