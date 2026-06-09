// Composables/useUniqueId.js
import { ref } from 'vue';

let id = 0;
export function useUniqueId(prefix = 'id') {
  const currentId = ref(`${prefix}-${++id}`);
  return currentId;
}