declare global {
  interface Window {
    fbq?: ((...args: unknown[]) => void) & { callMethod?: unknown; queue?: unknown[]; loaded?: boolean; version?: string }
    _fbq?: unknown
  }
}

let initializedFor: string | null = null

/** Injeta o Meta Pixel base e dispara PageView. Idempotente por pixelId. */
export function initPixel(pixelId: string): void {
  if (!pixelId || initializedFor === pixelId) return

  if (!window.fbq) {
    const fbq: any = function (...args: unknown[]) {
      fbq.callMethod ? fbq.callMethod.apply(fbq, args) : fbq.queue.push(args)
    }
    window.fbq = fbq
    if (!window._fbq) window._fbq = fbq
    fbq.push = fbq
    fbq.loaded = true
    fbq.version = '2.0'
    fbq.queue = []

    const script = document.createElement('script')
    script.async = true
    script.src = 'https://connect.facebook.net/en_US/fbevents.js'
    document.head.appendChild(script)
  }

  const fbq = window.fbq
  if (!fbq) return
  fbq('init', pixelId)
  fbq('track', 'PageView')
  initializedFor = pixelId
}

/** Dispara um evento padrão do Meta Pixel (ex: 'Lead'). */
export function trackPixelEvent(event: string, params?: Record<string, unknown>): void {
  const fbq = window.fbq
  if (!fbq) return
  fbq('track', event, params)
}
