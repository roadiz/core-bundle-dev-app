/**
 * The single transport for backoffice HTTP calls.
 *
 * Owns what every call used to rebuild by hand: the endpoint lookup in
 * `RozierConfig.routes`, the CSRF token, the headers, the array encoding and
 * the error handling. It never toasts: callers decide what to show.
 *
 * Out of scope: the Lazyload navigation, Nominatim and the Dropzone upload.
 */
import type { RozierConfig } from '~/@types/rozier-config'

export type RouteName = keyof NonNullable<RozierConfig['routes']>
type Params = Record<string, unknown>

export interface RequestOptions {
    method?: 'GET' | 'POST'
    /** Encoded into the query string */
    query?: Params
    /** POST body: encoded as `application/x-www-form-urlencoded`, or sent as is when a FormData */
    body?: Params | FormData
    /**
     * Field receiving `RozierConfig.ajaxToken`. Defaults to `csrfToken` in a
     * POST body; GET sends none unless a field is given (added to the query).
     * `null` sends no token (a whole-form FormData carries its own).
     */
    csrfField?: string | null
    /** `html` for Twig fragments, `json` otherwise */
    accept?: 'json' | 'html'
    signal?: AbortSignal
}

export class HttpError extends Error {
    constructor(
        message: string,
        /** 0 on a network failure */
        readonly status: number,
        /** Parsed JSON body, or raw text when not JSON */
        readonly body: unknown = null,
        /** Final URL, after redirects */
        readonly url = '',
        readonly redirected = false,
    ) {
        super(message)
        this.name = 'HttpError'
    }
}

export function isAbortError(error: unknown) {
    return error instanceof DOMException && error.name === 'AbortError'
}

/**
 * A `RozierConfig.routes` key, or a raw URL (anything with a `/`) for the
 * contextual-menu route, form POSTs to the current page and public Twig options.
 */
export function routeUrl(target: RouteName | string): string {
    if (target.includes('/')) return target

    const url = window.RozierConfig?.routes?.[target as RouteName]
    if (!url) {
        throw new Error(`[http] Unknown RozierConfig route "${target}"`)
    }

    return url
}

/**
 * Arrays as `key[0]=…&key[1]=…` (order kept: Varnish sorts query strings),
 * objects as `key[name]=value`. `undefined` is skipped, `null` sent empty.
 */
function append(target: URLSearchParams, key: string, value: unknown) {
    if (value === undefined) return
    if (Array.isArray(value)) {
        value.forEach((item, index) => append(target, `${key}[${index}]`, item))
    } else if (value !== null && typeof value === 'object') {
        Object.entries(value).forEach(([name, item]) =>
            append(target, `${key}[${name}]`, item),
        )
    } else {
        target.append(key, value === null ? '' : String(value))
    }
}

export function encode(params: Params = {}) {
    const search = new URLSearchParams()
    Object.entries(params).forEach(([key, value]) => append(search, key, value))
    return search
}

function flatten(value: unknown): string[] {
    if (value === null || value === undefined || value === '') return []
    if (typeof value === 'object') return Object.values(value).flatMap(flatten)
    return [String(value)]
}

/**
 * One human message from any server error body, in this order:
 * `violations[].title`, `errors` (flattened), `message`, `detail`, `title`.
 */
export function errorMessage(body: unknown): string {
    const data = (body && typeof body === 'object' ? body : {}) as {
        violations?: { title?: string }[]
        errors?: unknown
        message?: string
        detail?: string
        title?: string
    }
    const violations = Array.isArray(data.violations)
        ? data.violations.map((violation) => violation?.title).filter(Boolean)
        : []
    const errors = flatten(data.errors)

    return (
        violations.join(' ') ||
        errors.join(' ') ||
        data.message ||
        data.detail ||
        data.title ||
        window.RozierConfig?.messages?.forbiddenPage ||
        'An unexpected error occurred.'
    )
}

export async function request<T = unknown>(
    target: RouteName | string,
    {
        method = 'GET',
        query = {},
        body,
        csrfField = method === 'POST' ? 'csrfToken' : null,
        accept = 'json',
        signal,
    }: RequestOptions = {},
): Promise<T> {
    const token = window.RozierConfig?.ajaxToken || ''
    const search = encode(query)
    let payload: URLSearchParams | FormData | undefined

    if (method === 'POST') {
        payload = body instanceof FormData ? body : encode(body)
        if (csrfField && !payload.has(csrfField)) {
            payload.append(csrfField, token)
        }
    } else if (csrfField) {
        search.append(csrfField, token)
    }

    const base = routeUrl(target)
    const queryString = search.toString()
    const url = queryString
        ? `${base}${base.includes('?') ? '&' : '?'}${queryString}`
        : base

    let response: Response
    let text: string
    try {
        response = await fetch(url, {
            method,
            body: payload,
            signal,
            headers: {
                Accept: accept === 'html' ? 'text/html' : 'application/json',
                // Required to prevent using this route as referer when login again
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        text = await response.text()
    } catch (error) {
        if (isAbortError(error)) throw error
        throw new HttpError(errorMessage(null), 0, null, url)
    }

    let data: unknown = text
    let isJson = false
    try {
        data = text === '' ? null : JSON.parse(text)
        isJson = true
    } catch {
        // Not JSON: keep the raw text
    }

    if (!response.ok || (accept === 'json' && !isJson)) {
        throw new HttpError(
            errorMessage(data),
            response.status,
            data,
            response.url,
            response.redirected,
        )
    }

    return (accept === 'html' ? text : data) as T
}

export function get<T = unknown>(
    target: RouteName | string,
    options: Omit<RequestOptions, 'method' | 'body'> = {},
) {
    return request<T>(target, { ...options, method: 'GET' })
}

export function post<T = unknown>(
    target: RouteName | string,
    options: Omit<RequestOptions, 'method'> = {},
) {
    return request<T>(target, { ...options, method: 'POST' })
}
