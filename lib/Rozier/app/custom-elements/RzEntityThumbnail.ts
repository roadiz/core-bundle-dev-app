import RoadizElement from '~/utils/custom-element/RoadizElement'

/**
 * Custom element that fetches and displays entity thumbnails.
 *
 * Usage:
 * <rz-entity-thumbnail entity-class="RZ\Roadiz\CoreBundle\Entity\User" entity-id="test@test.test"></rz-entity-thumbnail>
 * <rz-entity-thumbnail entity-class="RZ\Roadiz\CoreBundle\Entity\Document" entity-id="42" size="large"></rz-entity-thumbnail>
 */
export default class RzEntityThumbnail extends RoadizElement {
    private entityClass: string | null = null
    private entityId: string | null = null
    private size: 'small' | 'medium' | 'large' = 'medium'
    private loading = true
    private error: string | null = null
    private thumbnailData: {
        url: string | null
        alt: string | null
        title: string | null
        width: number | null
        height: number | null
    } | null = null
    // Added properties for lazy loading via IntersectionObserver
    private intersectionObserver: IntersectionObserver | null = null
    private hasRequested = false

    constructor() {
        super()
    }

    connectedCallback() {
        this.entityClass = this.getAttribute('entity-class')
        this.entityId = this.getAttribute('entity-id')
        const sizeAttr = this.getAttribute('size')

        if (
            sizeAttr === 'small' ||
            sizeAttr === 'medium' ||
            sizeAttr === 'large'
        ) {
            this.size = sizeAttr
        }

        // Defer fetching until element is visible; show placeholder (not loading spinner yet)
        this.loading = false
        this.render()

        // Initialize IntersectionObserver to trigger fetch when visible
        this.intersectionObserver = new IntersectionObserver(
            (entries) => {
                for (const entry of entries) {
                    if (entry.isIntersecting && !this.hasRequested) {
                        this.hasRequested = true
                        this.loading = true
                        this.render() // show spinner while fetching
                        this.fetchThumbnail()
                        // We only need to fetch once
                        if (this.intersectionObserver) {
                            this.intersectionObserver.disconnect()
                            this.intersectionObserver = null
                        }
                    }
                }
            },
            {
                root: null,
                threshold: 0.1, // trigger when at least 10% visible
            },
        )

        this.intersectionObserver.observe(this)
    }

    disconnectedCallback() {
        // Clean up observer if element is removed
        if (this.intersectionObserver) {
            this.intersectionObserver.disconnect()
            this.intersectionObserver = null
        }
    }

    private async fetchThumbnail() {
        if (!this.entityClass || !this.entityId) {
            this.error = 'Missing entity-class or entity-id attribute'
            this.loading = false
            this.render()
            return
        }

        try {
            const params = new URLSearchParams({
                class: this.entityClass,
                id: this.entityId,
            })

            const response = await fetch(
                `/rz-admin/ajax/entity-thumbnail?${params.toString()}`,
                {
                    method: 'GET',
                    headers: {
                        Accept: 'application/json',
                    },
                    credentials: 'same-origin',
                },
            )

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`)
            }

            this.thumbnailData = await response.json()
            this.loading = false
            this.render()
        } catch (err) {
            this.error = err instanceof Error ? err.message : 'Unknown error'
            this.loading = false
            this.render()
        }
    }

    private getSizeClass(): string {
        switch (this.size) {
            case 'small':
                return 'rz-entity-thumbnail--small'
            case 'large':
                return 'rz-entity-thumbnail--large'
            default:
                return 'rz-entity-thumbnail--medium'
        }
    }

    private render() {
        const sizeClass = this.getSizeClass()

        if (this.loading) {
            this.innerHTML = `
                <div class="rz-entity-thumbnail ${sizeClass} rz-entity-thumbnail--loading">
                    <div class="rz-entity-thumbnail__spinner"></div>
                </div>
            `
            return
        }

        if (this.error) {
            this.innerHTML = `
                <div class="rz-entity-thumbnail ${sizeClass} rz-entity-thumbnail--error">
                    <div class="rz-entity-thumbnail__placeholder">!</div>
                </div>
            `
            this.setAttribute('title', this.error)
            return
        }

        if (!this.thumbnailData || !this.thumbnailData.url) {
            this.innerHTML = `
                <div class="rz-entity-thumbnail ${sizeClass} rz-entity-thumbnail--empty">
                    <div class="rz-entity-thumbnail__placeholder"></div>
                </div>
            `
            if (this.thumbnailData?.title) {
                this.setAttribute('title', this.thumbnailData.title)
            }
            return
        }

        const alt = this.thumbnailData.alt || ''
        const title = this.thumbnailData.title || ''

        this.innerHTML = `
            <figure class="rz-entity-thumbnail ${sizeClass}">
                <img
                    data-uk-tooltip="{animation:true}"
                    class="uk-thumbnail rz-entity-thumbnail__image"
                    src="${this.thumbnailData.url}"
                    alt="${alt}"
                    width="${this.thumbnailData.width || ''}"
                    height="${this.thumbnailData.height || ''}"
                    loading="lazy"
                />
            </figure>
        `

        if (title) {
            this.setAttribute('title', title)
        }
    }
}
