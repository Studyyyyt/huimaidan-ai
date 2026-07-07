export const diyUtil = {
  buildBorderRadius(config) {
    const { valList, val, type } = config;
    if (type === 0) return `${val}px`;
    if (type === 1) return `${valList[0].val}px ${valList[1].val}px ${valList[2].val}px ${valList[3].val}px`;
  },
  buildLinearColor(config) {
    const linearColor = config.color.map((color, index) => {
      return color.item || 'transparent'
    })
    return `linear-gradient(to right, ${linearColor.join(',')})`;
  },
  buildShadowStyle(config) {
    const { color, x, y, blur, spread, visible } = config;
    if (visible == 0 || (x == 0 && y == 0 && blur == 0 && spread == 0)) return 'none';
    return `${x}px ${y}px ${blur}px ${spread}px ${color}`;
  },
  buildMarginTopOffset(marginTopConfig, marginTopFloatConfig) {
    // 页面上边距配置和组件上浮配置
    // 如果配置了组件上浮或者没有配置页面上边距，则以组件上浮为准，即 marginTop 负值，实现向上偏移
    // 否则采用页面上边距配置
    if (!marginTopConfig && !marginTopFloatConfig) return '0px';

    if (!marginTopConfig || marginTopFloatConfig && marginTopFloatConfig.val) {
      return `-${marginTopFloatConfig.val}px`;
    }
    return `${marginTopConfig.val}px`;
  }
};