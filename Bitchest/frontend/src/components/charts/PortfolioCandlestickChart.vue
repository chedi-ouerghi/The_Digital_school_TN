<script setup lang="ts">
import { createChart, ColorType, CrosshairMode } from 'lightweight-charts'
import type { IChartApi, ISeriesApi, UTCTimestamp } from 'lightweight-charts'
import { onMounted, onBeforeUnmount, ref, watch, nextTick } from 'vue'

interface HistoryPoint {
  date: string
  timestamp: number
  value_eur: number
  volume?: number
}

interface OhlcPoint {
  time: UTCTimestamp
  open: number
  high: number
  low: number
  close: number
  volume: number
  dateStr: string
}

const props = withDefaults(defineProps<{
  history: HistoryPoint[]
  height?: number
  volumeHeight?: number
  showVolume?: boolean
}>(), {
  height: 320,
  volumeHeight: 100,
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

// Deterministic pseudo-random based on index to avoid flicker
function seededRand(seed: number): number {
  const x = Math.sin(seed * 9999) * 10000
  return x - Math.floor(x)
}

function buildOhlcv(history: HistoryPoint[]): OhlcPoint[] {
  if (!history || history.length === 0) return []
  // Ensure sorted by date ascending
  const sorted = [...history].sort((a, b) => new Date(a.date).getTime() - new Date(b.date).getTime())
  const points: OhlcPoint[] = []

  for (let i = 0; i < sorted.length; i++) {
    const curr = sorted[i]
    const close = Math.max(0.01, toNumber(curr.value_eur))
    // open = previous close (or slightly offset for first point)
    let open: number
    if (i === 0) {
      const r = seededRand(i + 1) - 0.5 // -0.5..0.5
      open = close * (1 + r * 0.015)
    } else {
      open = toNumber(sorted[i - 1].value_eur)
      // if portfolio empty early, fallback to close
      if (open <= 0) open = close * 0.99
    }

    const isUp = close >= open
    // volatility 1.5% - 3.5% depending on trend
    const hlVol = 0.008 + seededRand(i + 100) * 0.018 // 0.8% .. 2.6%
    const maxOC = Math.max(open, close)
    const minOC = Math.min(open, close)

    // high slightly above max, low slightly below min
    const high = maxOC * (1 + hlVol * (0.3 + seededRand(i + 200) * 0.7))
    const low = minOC * (1 - hlVol * (0.3 + seededRand(i + 300) * 0.7))

    // Volume correlated with price move + base
    const movePct = Math.abs(close - open) / Math.max(open, 0.01)
    const baseVol = curr.volume ? toNumber(curr.volume) : 40000 + toNumber(close * 0.8)
    const volExtra = movePct * 80000 * (0.5 + seededRand(i + 400))
    const volume = Math.floor(baseVol + volExtra + seededRand(i + 500) * 12000)

    // lightweight-charts expects time as YYYY-MM-DD string or UTCTimestamp (seconds)
    // Use YYYY-MM-DD for daily
    const timeStr = curr.date.slice(0, 10) as unknown as UTCTimestamp
    // But we want business day format; use string directly via type assertion
    points.push({
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      time: (curr.date.slice(0, 10) as any),
      open: Number(open.toFixed(2)),
      high: Number(high.toFixed(2)),
      low: Number(Math.max(0.01, low).toFixed(2)),
      close: Number(close.toFixed(2)),
      volume,
      dateStr: curr.date
    })
  }
  return points
}

function initChart() {
  if (!containerRef.value || !volumeRef.value) return
  dispose()

  const ohlcv = buildOhlcv(props.history)
  empty.value = ohlcv.length === 0
  if (empty.value) return

  const width = containerRef.value.clientWidth

  // ----- Price chart (candlestick) -----
  chart = createChart(containerRef.value, {
    layout: {
      background: { type: ColorType.Solid, color: '#FFFFFF' },
      textColor: '#64748B',
      fontFamily: "Inter, ui-sans-serif, system-ui"
    },
    grid: {
      vertLines: { color: 'rgba(226, 232, 240, 0.5)' },
      horzLines: { color: 'rgba(226, 232, 240, 0.5)' }
    },
    crosshair: { mode: CrosshairMode.Normal },
    width,
    height: props.height,
    rightPriceScale: {
      borderColor: '#E2E8F0',
      scaleMargins: { top: 0.08, bottom: 0.12 },
      entireTextOnly: false,
      visible: true
    },
    timeScale: {
      borderColor: '#E2E8F0',
      timeVisible: true,
      secondsVisible: false,
      rightOffset: 6,
      barSpacing: Math.max(4, Math.min(12, 400 / Math.max(ohlcv.length, 10))),
      minBarSpacing: 4
    },
    handleScroll: { mouseWheel: true, pressedMouseMove: true, horzTouchDrag: true, vertTouchDrag: false },
    handleScale: { axisPressedMouseMove: { time: true, price: true }, mouseWheel: true, pinch: true }
  })

  candleSeries = chart.addCandlestickSeries({
    upColor: '#10B981',
    downColor: '#EF4444',
    borderUpColor: '#059669',
    borderDownColor: '#DC2626',
    wickUpColor: '#059669',
    wickDownColor: '#DC2626',
    borderVisible: false,
    wickVisible: true,
    priceFormat: { type: 'price', precision: 2, minMove: 0.01 }
  })
  // lightweight-charts v4 expects {time, open, high, low, close}
  candleSeries.setData(ohlcv.map(p => ({ time: p.time, open: p.open, high: p.high, low: p.low, close: p.close })))

  // Price line for last close
  const last = ohlcv[ohlcv.length - 1]
  if (last) {
    candleSeries.createPriceLine({
      price: last.close,
      color: last.close >= last.open ? '#10B981' : '#EF4444',
      lineWidth: 1,
      lineStyle: 2,
      axisLabelVisible: true,
      title: 'Price'
    })
  }

  chart.timeScale().fitContent()

  // ----- Volume chart (histogram) -----
  if (props.showVolume) {
    volumeChart = createChart(volumeRef.value, {
      layout: { background: { type: ColorType.Solid, color: '#FFFFFF' }, textColor: '#94A3B8', fontFamily: "Inter, ui-sans-serif, system-ui" },
      grid: { vertLines: { color: 'rgba(226,232,240,0.35)' }, horzLines: { color: 'rgba(226,232,240,0.35)' } },
      width,
      height: props.volumeHeight,
      rightPriceScale: { borderColor: '#E2E8F0', scaleMargins: { top: 0.1, bottom: 0 } },
      timeScale: { borderColor: '#E2E8F0', visible: true, timeVisible: true, secondsVisible: false, rightOffset: 6, barSpacing: chart.timeScale().options().barSpacing, minBarSpacing: 4 },
      crosshair: { mode: CrosshairMode.Normal },
      handleScroll: { mouseWheel: true, pressedMouseMove: true, horzTouchDrag: true, vertTouchDrag: false },
      handleScale: { axisPressedMouseMove: { time: true, price: false }, mouseWheel: true, pinch: true }
    })

    volumeSeries = volumeChart.addHistogramSeries({
      color: 'rgba(53,167,255,0.55)',
      priceFormat: { type: 'volume' },
      priceScaleId: '',
      priceLineVisible: false
    })

    volumeSeries.setData(ohlcv.map(p => ({
      time: p.time,
      value: p.volume,
      color: p.close >= p.open ? 'rgba(16,185,129,0.75)' : 'rgba(239,68,68,0.75)'
    })))

    volumeChart.timeScale().fitContent()

    // Sync time scales
    const sync = () => {
      if (!chart || !volumeChart) return
    }
    chart.timeScale().subscribeVisibleTimeRangeChange(range => {
      if (range && volumeChart) volumeChart.timeScale().setVisibleRange(range as any)
    })
    volumeChart.timeScale().subscribeVisibleTimeRangeChange(range => {
      if (range && chart) chart.timeScale().setVisibleRange(range as any)
    })

    // Sync crosshair
    chart.subscribeCrosshairMove(param => {
      if (!volumeChart || !param.time) return
      // lightweight-charts crosshair sync is automatic via timeScale; explicit not needed for v4
    })
  }

  // Resize observer
  resizeObserver = new ResizeObserver(entries => {
    for (const entry of entries) {
      const w = entry.contentRect.width
      if (chart) chart.applyOptions({ width: w })
      if (volumeChart) volumeChart.applyOptions({ width: w })
    }
  })
  if (containerRef.value) resizeObserver.observe(containerRef.value)
}

function dispose() {
  if (resizeObserver) {
    resizeObserver.disconnect()
    resizeObserver = null
  }
  if (chart) {
    chart.remove()
    chart = null
    candleSeries = null
  }
  if (volumeChart) {
    volumeChart.remove()
    volumeChart = null
    volumeSeries = null
  }
}

watch(() => props.history, async () => {
  await nextTick()
  initChart()
}, { deep: true })

watch(() => props.showVolume, async () => {
  await nextTick()
  initChart()
})

onMounted(() => {
  nextTick(() => initChart())
})

onBeforeUnmount(() => dispose())

// Exposed for parent to call fitContent
defineExpose({ fitContent: () => { chart?.timeScale().fitContent(); volumeChart?.timeScale().fitContent() } })
</script>

<template>
  <div class="w-full select-none">
    <!-- Header like image: Price | BTC/USD 01 -> adapted to PORTFOLIO / EUR -->
    <div class="flex items-center justify-between px-1 pb-2">
      <div class="flex items-center gap-2">
        <span class="text-[11px] font-semibold tracking-widest text-slate-500 uppercase">Price</span>
        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-slate-900 text-white text-[11px] font-bold">
          <span class="w-2 h-2 rounded-sm bg-emerald-400"></span>
          PORTFOLIO / EUR
        </span>
        <span class="text-[11px] text-slate-400 hidden sm:inline">7,149.07 · Japanese candlesticks</span>
      </div>
      <div class="text-[11px] text-slate-400 hidden sm:block">JS chart by lightweight-charts</div>
    </div>

    <!-- Price pane -->
    <div ref="containerRef" class="w-full border border-slate-200 rounded-t-lg overflow-hidden bg-white" :style="{ height: height + 'px' }"></div>

    <!-- Volume pane -->
    <div v-if="showVolume" ref="volumeRef" class="w-full border-x border-b border-slate-200 rounded-b-lg overflow-hidden bg-white -mt-[1px]" :style="{ height: volumeHeight + 'px' }"></div>

    <div v-if="empty" class="flex flex-col items-center justify-center py-8 text-slate-500 border border-dashed border-slate-200 rounded-lg mt-2">
      <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center mb-2">
        <span class="text-slate-400 text-lg">◐</span>
      </div>
      <p class="text-sm font-medium text-slate-600">No history yet</p>
      <p class="text-xs text-slate-400">Volume will appear after first transactions</p>
    </div>

    <!-- Footer like image -->
    <div v-if="!empty" class="flex items-center justify-between px-1 pt-2">
      <div class="text-[11px] text-slate-500">Volume <span class="text-slate-700 font-medium">{{ history.length ? (history[history.length-1].value_eur ?? 0).toLocaleString('en-US', { style:'currency', currency:'EUR', maximumFractionDigits:0 }) : '' }}</span></div>
      <div class="text-[11px] text-slate-400">JS chart by lightweight-charts</div>
    </div>
  </div>
</template>
