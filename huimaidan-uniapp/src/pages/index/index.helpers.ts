export interface IStickySearchBarLayoutInput {
  menuButtonLeft?: number
  safeAreaRight?: number
  statusBarHeight?: number
  windowWidth?: number
}

export interface IStickySearchBarLayout {
  paddingBottom: number
  paddingLeft: number
  paddingRight: number
  paddingTop: number
}

export interface ICategoryTreeNode {
  children?: ICategoryTreeNode[]
  label?: string
  name?: string
  store_group_id?: number
  value?: number
}

export interface ICategoryPanelItem {
  children?: ICategoryTreeNode[]
  name: string
  store_group_id: number
}

export function buildStickySearchBarLayout(input: IStickySearchBarLayoutInput): IStickySearchBarLayout {
  const baseRightPadding = 16
  const paddingLeft = 12
  const paddingBottom = 8
  const paddingTop = Math.max(input.statusBarHeight || 0, 0)
  const safeAreaRightPadding = Math.max((input.safeAreaRight || 0) + paddingLeft, baseRightPadding)
  const capsuleRightPadding = input.windowWidth && input.menuButtonLeft
    ? Math.max(input.windowWidth - input.menuButtonLeft + paddingLeft, 0)
    : 0

  return {
    paddingTop,
    paddingRight: Math.max(baseRightPadding, safeAreaRightPadding, capsuleRightPadding),
    paddingBottom,
    paddingLeft,
  }
}

export function getCategoryNodeId(node: ICategoryTreeNode): number {
  return Number(node.value ?? node.store_group_id)
}

export function findCategoryPath(
  tree: ICategoryTreeNode[],
  id: string | number,
): ICategoryTreeNode[] {
  for (const item of tree) {
    if (String(getCategoryNodeId(item)) === String(id)) {
      return [item]
    }

    const childPath = findCategoryPath(item.children || [], id)
    if (childPath.length > 0) {
      return [item, ...childPath]
    }
  }

  return []
}

export function getCategoryPanelAllTargetId(
  tree: ICategoryTreeNode[],
  activeCategory: string | number,
): number | 'all' {
  if (!activeCategory || activeCategory === 'all') {
    return 'all'
  }

  const path = findCategoryPath(tree, activeCategory)
  return path[0] ? getCategoryNodeId(path[0]) : 'all'
}

export function buildCategoryPanelItems(
  tree: ICategoryTreeNode[],
  activeCategory: string | number,
): ICategoryPanelItem[] {
  if (!activeCategory || activeCategory === 'all') {
    return mapCategoryPanelItems(tree)
  }

  const path = findCategoryPath(tree, activeCategory)
  if (path.length === 0) {
    return mapCategoryPanelItems(tree)
  }

  const selected = path[path.length - 1]
  const parent = path[path.length - 2]
  const source = selected?.children?.length
    ? selected.children
    : parent?.children || []

  return mapCategoryPanelItems(source)
}

function mapCategoryPanelItems(source: ICategoryTreeNode[]): ICategoryPanelItem[] {
  return source.map(item => ({
    store_group_id: getCategoryNodeId(item),
    name: item.label ?? item.name ?? '',
    children: item.children,
  }))
}
