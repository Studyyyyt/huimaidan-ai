export function isStandalonePage(path) {
  return typeof path === 'string' && /\.html(?:[?#].*)?$/.test(path);
}

export function openMenuPath(vm, path) {
  if (!path) return;
  if (isStandalonePage(path)) {
    window.location.href = path;
    return;
  }
  if (vm.$route && vm.$route.path === path) return;
  vm.$router.push(path);
}
