/**
 * useA11yNav — shared keyboard-accessibility primitives for the Vue 3 form.
 *
 * `useRovingTabindex(options)` implements the WAI-ARIA APG "roving tabindex"
 * pattern for composite widgets (listbox, radiogroup, grid-like card lists):
 * exactly one item in the group carries tabindex="0" (the active item), all
 * others carry tabindex="-1", so a single Tab press enters the group and
 * Arrow / Home / End keys move focus between items. Enter / Space activate.
 *
 * This is the redesigned replacement for the legacy form's per-widget
 * `bpa_handle_*_keypress` handlers + `tabindex="0"` group-wrapper focus
 * forwarding (class.bookingpress_appointment_bookings.php:7946-8232) that
 * were dropped during the Vue 3 greenfield migration. Instead of
 * document-wide `querySelectorAll` walks, items register themselves through
 * Vue 3 function refs (the `v-for` template-ref pattern — refs collected into
 * a Map, never a single ref), so multiple form instances on one page can
 * never cross-talk.
 *
 * @typedef {object} RovingOptions
 * @property {() => number}  count          Item count getter (reactive source).
 * @property {() => number}  [selectedIndex] Index that should own the tab stop
 *                                           before the user focuses the group
 *                                           (e.g. the currently-selected item).
 *                                           Return -1 for "none" → falls back
 *                                           to the first item.
 * @property {(index:number, event:KeyboardEvent) => void} [onActivate]
 *                                           Enter / Space handler.
 * @property {(index:number) => void} [onMove]
 *                                           Called after an arrow-key move —
 *                                           pass the selection setter for
 *                                           "selection follows focus" widgets
 *                                           (APG radio group). Omit for
 *                                           explicit-activation widgets
 *                                           (listbox where activating also
 *                                           advances the step).
 * @property {boolean} [grid]                When true, ArrowUp/ArrowDown move
 *                                           by one visual row — the column
 *                                           count is measured from the live
 *                                           layout (offsetTop of the first
 *                                           row), so it stays correct across
 *                                           breakpoints without media-query
 *                                           duplication in JS.
 */
import { ref, watch } from 'vue';

/**
 * @param {RovingOptions} options
 */
export function useRovingTabindex(options) {
  const count         = options.count;
  const selectedIndex = options.selectedIndex || (() => -1);
  const onActivate    = options.onActivate || null;
  const onMove        = options.onMove || null;
  const grid          = options.grid === true;

  /** @type {Map<number, HTMLElement>} index → element (v-for function refs). */
  const itemEls = new Map();

  function preferredIndex() {
    const n = count();
    if (n <= 0) return 0;
    const sel = selectedIndex();
    return (typeof sel === 'number' && sel >= 0 && sel < n) ? sel : 0;
  }

  /** Index of the single item carrying tabindex="0". */
  const activeIndex = ref(preferredIndex());

  // When the list content changes (category switch, month change, add-on
  // filter), re-seat the tab stop on the selected item — or clamp it back
  // into range so the group never loses its Tab entry point.
  watch([count, selectedIndex], () => {
    const n = count();
    if (n <= 0) { activeIndex.value = 0; return; }
    if (activeIndex.value >= n) activeIndex.value = preferredIndex();
  });

  /** Items in index order, skipping unmounted holes. */
  function orderedEls() {
    const out = [];
    const n = count();
    for (let i = 0; i < n; i++) {
      const el = itemEls.get(i);
      if (el) out.push(el);
    }
    return out;
  }

  /** Measure the current visual column count from the rendered layout. */
  function measureCols() {
    if (!grid) return 1;
    const els = orderedEls();
    if (els.length < 2) return 1;
    let cols = 1;
    const top0 = els[0].offsetTop;
    for (let i = 1; i < els.length; i++) {
      if (Math.abs(els[i].offsetTop - top0) < 2) cols++;
      else break;
    }
    return Math.max(1, cols);
  }

  /** Template function-ref collector: `:ref="el => rov.setItemRef(el, i)"`. */
  function setItemRef(el, index) {
    if (el) itemEls.set(index, el);
    else itemEls.delete(index);
  }

  /** `:tabindex` binding for item `index`. */
  function tabindexFor(index) {
    const n = count();
    if (n <= 0) return '-1';
    const active = activeIndex.value < n ? activeIndex.value : preferredIndex();
    return index === active ? '0' : '-1';
  }

  function focusItem(index) {
    const el = itemEls.get(index);
    if (!el || typeof el.focus !== 'function') return;
    try { el.focus({ preventScroll: false }); } catch (_e) { el.focus(); }
  }

  /** `@focus` binding — keeps the tab stop on whatever the user last
   *  focused (mouse click, Shift+Tab back into the group, etc.). */
  function onItemFocus(index) {
    activeIndex.value = index;
  }

  /**
   * `@keydown` binding. Wrap-around modulo movement mirrors the legacy
   * handlers' behaviour so long-time keyboard users keep their muscle
   * memory; Home/End are new (APG) additions.
   */
  function onKeydown(event, index) {
    const n = count();
    if (n <= 0) return;
    const key = event.key;
    let next = null;

    if (key === 'Enter' || key === ' ' || key === 'Spacebar') {
      if (onActivate) {
        event.preventDefault();
        onActivate(index, event);
      }
      return;
    }

    const cols = (key === 'ArrowUp' || key === 'ArrowDown') ? measureCols() : 1;
    switch (key) {
      case 'ArrowRight': next = (index + 1) % n; break;
      case 'ArrowLeft':  next = (index - 1 + n) % n; break;
      case 'ArrowDown':  next = (index + cols) % n; break;
      case 'ArrowUp':    next = (index - cols + n) % n; break;
      case 'Home':       next = 0; break;
      case 'End':        next = n - 1; break;
      default: return;
    }

    event.preventDefault();
    activeIndex.value = next;
    focusItem(next);
    if (onMove) onMove(next);
  }

  return { activeIndex, setItemRef, tabindexFor, focusItem, onItemFocus, onKeydown };
}

export default { useRovingTabindex };
