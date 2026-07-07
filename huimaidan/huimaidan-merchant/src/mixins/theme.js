import { getStyleApi } from '@/api/setting';


export default {
  data() {
    return {
      current: 3,
      colorStyle: {
        theme: '#FF448F',
        priceColor: '#FF448F',
        minorColor: 'rgba(255, 68, 143, 0.5)',
        minorColorT: 'rgba(255, 68, 143, 0.1)',
        bntColor: '#282828',
        gradient: '#FF67AD',
      }
    };
  },
  created() {
    this.getStyle();
  },
  methods: {
    getStyle() {
      getStyleApi().then(res => {
        const colorStyle = this.processStyleEnv(res.data.theme);
        Object.assign(this.colorStyle, colorStyle);
      });
    },
    processStyleEnv(str) {
      return str.split(";")
        .filter(i => i.includes("--"))
        .map(i => i.split(":"))
        .reduce((acc, [k, v]) => {
          k = k.replace("--view-", "");
          acc[k] = v.trim();
          return acc;
        }, {})
    }
  }
};
