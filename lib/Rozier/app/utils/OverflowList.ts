/**
 * Responsive overflow list: folds items into an rz_overflow_list popover when the row is too
 * narrow for them, and unfolds them again as it widens, never beyond what the server left
 * inline. The server state is the maximum, so the page is right without JS on a wide screen.
 *
 * Two DOM shapes, told apart by `overflow-inline-side` (macros/rz_overflow_list.html.twig):
 *
 * - `after` (breadcrumb): the N inline items are siblings *after* the wrapper, the popover holds
 *   the *first* levels. Folding takes the first inline item; the whole wrapper hides once nothing
 *   is folded.
 *   row > wrapper.rz-overflow-list > rz-popover > [popover] > .rz-overflow-list__list > folded
 *   row > N inline items
 * - `before` (node tree tags): the N inline items sit *inside* the wrapper, before the
 *   <rz-popover>, the popover holds the *last* ones. Folding takes the last inline item; only the
 *   <rz-popover> hides, the wrapper keeps the inline items.
 *   row > wrapper.rz-overflow-list > N inline items, rz-popover > [popover] > list > folded
 *
 * Items keep the same markup in both places, so they are moved as is.
 *
 * Measuring: items shrink or wrap rather than overflow, so overflow detection is useless. With
 * the row at `width: max-content`, three layouts are read once: every item inline (item widths),
 * nothing inline (fixed width), popover hidden too. Every decision is then arithmetic against
 * `row.clientWidth`. The row's width must not depend on its content (a `flex-grow: 1` container
 * for the breadcrumb, `width: 100%` for a tree row), otherwise unfolding would widen the row,
 * notify the observer and loop. Row children with `flex-grow` collapse to their `min-width`
 * during the measure: that is the room reserved for them before items fold.
 *
 * ponytail: three forced reflows per instance and one instance per tagged tree row. Fine for a
 * stack tree of a few dozen rows; batch all instances into one frame if it ever shows.
 */
export const ATTRIBUTES_OPTIONS_MAP = {
    responsive: 'overflow-responsive',
    inline: 'overflow-inline',
    side: 'overflow-inline-side',
    count: 'overflow-count',
} as const

type Side = 'before' | 'after'

export class OverflowList {
    private readonly wrapper: HTMLElement
    private readonly row: HTMLElement
    private readonly list: HTMLElement
    private readonly popover: HTMLElement | null
    private readonly side: Side
    /** Document order: inline then folded for `before`, folded then inline for `after`. */
    private readonly items: HTMLElement[]
    /** Element hidden once nothing is folded: the wrapper (`after`) or the <rz-popover> (`before`). */
    private readonly foldTarget: HTMLElement
    /** Flex container of the inline items, whose `gap` adds to every item. */
    private readonly itemsContainer: HTMLElement
    /** Button label showing the folded count, when the caller keeps it. */
    private readonly counter: HTMLElement | null

    /** Per item, `offsetWidth` plus the container gap. */
    private costs: number[] = []
    /** Row width with nothing inline and the popover shown. */
    private fixedWidth = 0
    /** Row width with nothing inline and the popover hidden. */
    private fixedNoPopover = 0
    private gap = 0
    private measured = false
    /** Number of items currently inline. */
    private visible: number
    /** What the server left inline: the cap, never unfold past it. */
    private readonly maxVisible: number
    private lastAvailable = -1
    private frame = 0
    private dirty = false
    private destroyed = false

    private readonly observer: ResizeObserver

    constructor(private readonly context: HTMLElement) {
        const wrapper = context.closest<HTMLElement>('.rz-overflow-list')
        const list = context.querySelector<HTMLElement>(
            '.rz-overflow-list__list',
        )
        if (!wrapper?.parentElement || !list) {
            throw new Error('OverflowList: missing wrapper or list')
        }
        this.wrapper = wrapper
        this.row = wrapper.parentElement
        this.list = list
        this.popover = context.querySelector<HTMLElement>('[popover]')
        this.side =
            context.getAttribute(ATTRIBUTES_OPTIONS_MAP.side) === 'before'
                ? 'before'
                : 'after'
        this.foldTarget = this.side === 'before' ? context : wrapper
        this.itemsContainer = this.side === 'before' ? wrapper : this.row

        const inlineCount =
            parseInt(
                context.getAttribute(ATTRIBUTES_OPTIONS_MAP.inline) || '',
            ) || 0
        const inline: HTMLElement[] = []
        let sibling =
            this.side === 'before'
                ? context.previousElementSibling
                : wrapper.nextElementSibling
        while (sibling && inline.length < inlineCount) {
            inline.push(sibling as HTMLElement)
            sibling =
                this.side === 'before'
                    ? sibling.previousElementSibling
                    : sibling.nextElementSibling
        }
        if (this.side === 'before') inline.reverse()
        const folded = Array.from(list.children) as HTMLElement[]
        this.items =
            this.side === 'before'
                ? [...inline, ...folded]
                : [...folded, ...inline]
        this.visible = inline.length
        this.maxVisible = inline.length

        this.counter = context.hasAttribute(ATTRIBUTES_OPTIONS_MAP.count)
            ? this.findCounter()
            : null

        this.observer = new ResizeObserver(() => this.schedule())
        this.observer.observe(this.row)
        this.popover?.addEventListener('toggle', this.onRelease)
        this.row.addEventListener('focusout', this.onRelease)
        // Widths read while a fallback font paints are wrong: measure again when fonts land.
        // Not `fonts.ready`: it resolves at once when nothing is loading *yet*.
        document.fonts.addEventListener('loadingdone', this.onFontsLoaded)

        this.schedule()
    }

    destroy() {
        this.destroyed = true
        this.observer.disconnect()
        this.popover?.removeEventListener('toggle', this.onRelease)
        this.row.removeEventListener('focusout', this.onRelease)
        document.fonts.removeEventListener('loadingdone', this.onFontsLoaded)
        cancelAnimationFrame(this.frame)
    }

    /** rz_button.html.twig skips the label span for a `0` count: create it then. */
    private findCounter(): HTMLElement | null {
        const button = this.context.querySelector<HTMLElement>(
            '.rz-overflow-list__button',
        )
        if (!button) return null
        let label = button.querySelector<HTMLElement>('.rz-button__label')
        if (!label) {
            label = document.createElement('span')
            label.className = 'rz-button__label'
            button.prepend(label)
        }
        return label
    }

    private onRelease = () => {
        if (this.dirty) this.schedule()
    }

    private onFontsLoaded = () => {
        this.measured = false
        this.schedule()
    }

    private schedule() {
        cancelAnimationFrame(this.frame)
        this.frame = requestAnimationFrame(() => this.apply())
    }

    private apply() {
        if (this.destroyed) return

        // Moving a focused node loses focus; emptying an open popover is unpleasant.
        if (
            this.popover?.matches(':popover-open') ||
            this.items.some((item) => item.contains(document.activeElement))
        ) {
            this.dirty = true
            return
        }
        this.dirty = false

        if (!this.measured) this.measure()
        if (!this.measured) return

        const available = this.row.clientWidth
        if (available === this.lastAvailable) return
        this.lastAvailable = available

        this.setVisible(this.decide(available))
    }

    /**
     * Reads the three reference layouts. The caller decides and moves items in the same tick,
     * so the browser only paints the final state.
     */
    private measure() {
        const { row, wrapper, items, foldTarget } = this
        const previousWidth = row.style.width
        row.style.width = 'max-content'

        // Elastic children (a title with flex-grow) collapse to their min-width: what must stay.
        const elastic = (Array.from(row.children) as HTMLElement[]).filter(
            (child) =>
                child !== wrapper &&
                !items.includes(child) &&
                parseFloat(getComputedStyle(child).flexGrow) > 0,
        )
        const elasticWidths = elastic.map((child) => child.style.width)
        elastic.forEach((child) => (child.style.width = '0'))

        // Every item inline, even past the cap: the row is clipped by its container anyway.
        this.setVisible(items.length)
        foldTarget.hidden = false
        this.gap =
            parseFloat(getComputedStyle(this.itemsContainer).columnGap) || 0
        const rowWidth = row.offsetWidth
        this.costs = items.map((item) => item.offsetWidth + this.gap)

        this.setVisible(0)
        foldTarget.hidden = false
        this.fixedWidth = row.offsetWidth
        foldTarget.hidden = true
        this.fixedNoPopover = row.offsetWidth
        foldTarget.hidden = false

        elastic.forEach((child, i) => (child.style.width = elasticWidths[i]))
        row.style.width = previousWidth
        this.lastAvailable = -1
        // A hidden row measures 0 everywhere: try again on the next resize.
        this.measured = rowWidth > 0
    }

    /** Number of items that fit next to the fixed content, capped by the server state. */
    private decide(available: number): number {
        const { costs, fixedWidth, fixedNoPopover, gap, maxVisible } = this
        const n = costs.length
        const total = costs.reduce((a, b) => a + b, 0)

        // n items share n - 1 gaps once the popover is gone.
        if (
            n <= maxVisible &&
            fixedNoPopover + total - (n ? gap : 0) <= available
        ) {
            return n
        }

        let used = fixedWidth
        let k = 0
        while (k < n && k < maxVisible) {
            const cost = costs[this.side === 'before' ? k : n - 1 - k]
            if (used + cost > available) break
            used += cost
            k++
        }
        return k
    }

    private setVisible(k: number) {
        const { items, wrapper, list, context, visible, side } = this
        const n = items.length

        if (side === 'before') {
            // Inline items are items[0 .. visible - 1], right before the <rz-popover>, in order.
            for (let i = visible; i < k; i++) context.before(items[i])
            for (let i = visible - 1; i >= k; i--) list.prepend(items[i])
        } else {
            // Inline items are items[n - visible .. n - 1], right after the wrapper, in order.
            for (let i = n - visible - 1; i >= n - k; i--)
                wrapper.after(items[i])
            for (let i = n - visible; i < n - k; i++) list.append(items[i])
        }

        this.visible = k
        this.foldTarget.hidden = k === n
        if (this.counter) this.counter.textContent = String(n - k)
    }
}
