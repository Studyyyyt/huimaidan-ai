export const diyUtil = {
  buildBorderRadius(config, direction) {
    // direction: top / bottom，分别指左上&右上  / 左下&右下 的圆角
    const { valList, val, type } = config;
    const isUniformRadius = type === 0;
    if (direction === "top") {
      if (isUniformRadius) {
        return `${val}px ${val}px 0 0`;
      }
      return `${valList[0].val}px ${valList[1].val}px 0 0`;
    } else if (direction === "bottom") {
      if (isUniformRadius) {
        return `0 0 ${val}px ${val}px`;
      }
      return `0 0 ${valList[2].val}px ${valList[3].val}px`;
    }

    if (isUniformRadius) return `${val}px`;
    return `${valList[0].val}px ${valList[1].val}px ${valList[3].val}px ${valList[2].val}px`;
  },
  buildLinearColor(config, direction = 'to right') {
    const linearColor = config.color.map((color, index) => {
      return color.item || 'transparent'
    })
    return `linear-gradient(${direction}, ${linearColor.join(',')})`;
  },
  buildShadowStyle(config) {
    const { color, x, y, blur, spread, visible } = config;
    if (visible == 0 || (x == 0 && y == 0 && blur == 0 && spread == 0)) return 'none';
    return `${x}px ${y}px ${blur}px ${spread}px ${color}`;
  },
  hexToRgba(hex, opacity) {
    
    // 处理3位和6位十六进制颜色值
    const normalized = hex.length === 4 ? 
      hex.slice(1).split('').map(x => x + x).join('') :
      hex.slice(1);
    const [r, g, b] = normalized.match(/\w\w/g).map(x => parseInt(x, 16));
    return `rgba(${r}, ${g}, ${b}, ${opacity})`;
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