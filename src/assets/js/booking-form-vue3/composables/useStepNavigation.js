/**
 * useStepNavigation — current tab, navigation, sidebar clickability.
 *
 * The step schema lives in `state.steps` (per §M0.9.B). This composable
 * exposes a reactive `currentStep`, `goTo(id)`, `goNext()`, `goPrev()`, and
 * a per-step `isClickable(id)` predicate that combines the descriptor's
 * `is_display_step` with the readiness `canEnterStep()` check.
 */
import { computed } from 'vue';

export function useStepNavigation(state, readiness, bus) {
  const visibleSteps = computed(() => state.steps.filter(s => s.is_display_step));

  const currentStep = computed(() => {
    return state.steps.find(s => s.id === state.currentTab) || state.steps[0];
  });

  function isClickable(stepId) {
    const step = state.steps.find(s => s.id === stepId);
    if (!step || !step.is_display_step) return false;
    return readiness.canEnterStep(step);
  }

  function goTo(stepId) {
    const step = state.steps.find(s => s.id === stepId);
    if (!step) return false;
    if (!readiness.canEnterStep(step)) {
      bus && bus.emit('bp-v3:step-rejected', { instanceId: state.instanceId, targetId: stepId, reason: 'entry_gates' });
      return false;
    }
    const prev = state.currentTab;
    state.currentTab = stepId;
    bus && bus.emit('bp-v3:step-changed', { instanceId: state.instanceId, from: prev, to: stepId });
    return true;
  }

  // Walk the static next_step/previous_step chain, skipping any descriptor that
  // is currently hidden (`is_display_step=0`). An add-on may hide a step at
  // runtime (e.g. Recurring hides the Cart step) — without this skip, goNext/
  // goPrev would land on the hidden step. Inert in Lite (no step is hidden at
  // runtime). Guards against cycles by capping the walk at the step count.
  function nextVisibleId(startId, edge) {
    let id = startId;
    let guard = state.steps.length + 1;
    while (id && guard-- > 0) {
      const step = state.steps.find(s => s.id === id);
      if (!step) return '';
      if (step.is_display_step) return id;
      id = step[edge];
    }
    return '';
  }

  // Whether the current step has a *visible* previous step — the same walk
  // goPrev() performs. `previous_step` alone is not enough: a hidden step
  // (e.g. hide_category_service keeps the Service descriptor in the array
  // with is_display_step=0) still appears as the previous_step of its
  // neighbour, but goPrev() would be a no-op. Step footers use this to
  // decide whether to render the "Go Back" button.
  const hasPrev = computed(() => {
    const cur = currentStep.value;
    if (!cur || !cur.previous_step) return false;
    return '' !== nextVisibleId(cur.previous_step, 'previous_step');
  });

  function goNext() {
    const cur = currentStep.value;
    if (!cur || !cur.next_step) return false;
    const target = nextVisibleId(cur.next_step, 'next_step');
    if (!target) return false;
    return goTo(target);
  }

  function goPrev() {
    const cur = currentStep.value;
    if (!cur || !cur.previous_step) return false;
    const target = nextVisibleId(cur.previous_step, 'previous_step');
    if (!target) return false;
    return goTo(target);
  }

  return { visibleSteps, currentStep, isClickable, hasPrev, goTo, goNext, goPrev };
}
